<?php

namespace App\Console\Commands;

use App\Models\BusinessDianSetting;
use App\Models\ElectronicInvoice;
use Illuminate\Console\Command;

/**
 * Recalcula el CUFE de un documento emitido y lo compara con el que devolvió el
 * proveedor.
 *
 * Sirve para responder una sola pregunta cuando la DIAN rechaza con FAD06
 * («Valor del CUFE no está calculado correctamente»): ¿el problema son nuestros
 * datos o el CUFE que calculó el proveedor? La fórmula está verificada contra el
 * ejemplo del numeral 11.1.2.1 del anexo técnico de la DIAN (Resolución 000012
 * de 2021, versión 1.8).
 */
class DianVerifyCufe extends Command
{
    protected $signature = 'dian:verify-cufe
                            {transaction? : ID de transacción del proveedor; por defecto, todas las emitidas}
                            {--key= : Clave técnica a probar en lugar de la configurada}';

    protected $description = 'Recalcula el CUFE de los documentos emitidos y lo compara con el del proveedor';

    /** Ejemplo oficial del anexo técnico, para comprobar la implementación antes de usarla. */
    private const DIAN_SAMPLE = [
        'fields' => [
            '323200000129', '2019-01-16', '10:53:10-05:00', '1500000.00',
            '01', '285000.00', '04', '0.00', '03', '0.00',
            '1785000.00', '700085371', '800199436',
            '693ff6f2a553c3646a063436fd4dd9ded0311471', '1',
        ],
        'cufe' => '8bb918b19ba22a694f1da11c643b5e9de39adf60311cf179179e9b33381030bcd4c3c3f156c506ed5908f9276f5bd9b4',
    ];

    public function handle(): int
    {
        if (! $this->assertFormulaIsCorrect()) {
            return self::FAILURE;
        }

        $documents = ElectronicInvoice::query()
            ->whereNotNull('cufe')
            ->when($this->argument('transaction'), fn ($query, $id) => $query->where('transaction_id', $id))
            ->orderBy('id')
            ->get();

        if ($documents->isEmpty()) {
            $this->warn('No hay documentos emitidos con CUFE para verificar.');

            return self::SUCCESS;
        }

        $mismatches = 0;

        foreach ($documents as $document) {
            $mismatches += $this->verify($document) ? 0 : 1;
        }

        $this->newLine();

        if ($mismatches === 0) {
            $this->info('Todos los CUFE coinciden: el rechazo, si lo hay, no viene del CUFE.');

            return self::SUCCESS;
        }

        $this->error("{$mismatches} documento(s) con CUFE distinto al que corresponde a los datos enviados.");
        $this->line('Si la clave técnica configurada es la del portal de la DIAN, el CUFE');
        $this->line('lo está calculando mal el proveedor.');

        return self::FAILURE;
    }

    /** Comprueba la implementación contra el ejemplo publicado por la DIAN. */
    private function assertFormulaIsCorrect(): bool
    {
        $cufe = hash('sha384', implode('', self::DIAN_SAMPLE['fields']));

        if ($cufe === self::DIAN_SAMPLE['cufe']) {
            $this->line('<fg=gray>Fórmula verificada contra el ejemplo del anexo técnico de la DIAN.</>');
            $this->newLine();

            return true;
        }

        $this->error('La implementación del CUFE no reproduce el ejemplo oficial de la DIAN.');

        return false;
    }

    private function verify(ElectronicInvoice $document): bool
    {
        $setting = BusinessDianSetting::query()->where('business_id', $document->business_id)->first();
        $key = (string) ($this->option('key') ?: $setting?->technical_key);

        if ($key === '') {
            $this->warn("{$document->document_number}: sin clave técnica configurada, no se puede verificar.");

            return true;
        }

        $qr = $this->parseQr((string) $document->qr_code);

        if (! isset($qr['NumFac'], $qr['FecFac'], $qr['HorFac'], $qr['ValFac'], $qr['ValTolFac'], $qr['NitFac'], $qr['DocAdq'])) {
            $this->warn("{$document->document_number}: el QR no trae todos los campos del CUFE.");

            return true;
        }

        // El QR agrupa INC e ICA en «ValOtroIm»; el CUFE los pide por separado, así
        // que se leen del documento enviado, donde cada impuesto va con su código.
        $taxes = $this->taxAmounts($document);

        $environment = (string) ($document->environment ?: $setting?->environment ?: 'test');
        $tipo_ambiente = $environment === 'production' ? '1' : '2';

        $expected = hash('sha384', implode('', [
            $qr['NumFac'], $qr['FecFac'], $qr['HorFac'], $qr['ValFac'],
            '01', $taxes['01'], '04', $taxes['04'], '03', $taxes['03'],
            $qr['ValTolFac'], $qr['NitFac'], $qr['DocAdq'], $key, $tipo_ambiente,
        ]));

        $matches = hash_equals($expected, (string) $document->cufe);

        $this->line(sprintf(
            '<options=bold>%s</> (transacción %s, ambiente %s)',
            $document->document_number,
            $document->transaction_id ?: '—',
            $environment
        ));
        $this->line('  proveedor : '.$document->cufe);
        $this->line('  calculado : '.$expected);
        $this->line($matches
            ? '  <fg=green>coinciden</>'
            : '  <fg=red>NO coinciden</>');
        $this->newLine();

        return $matches;
    }

    /** @return array<string, string> */
    private function parseQr(string $qr): array
    {
        $values = [];

        foreach (preg_split('/\R/', $qr) ?: [] as $line) {
            if (! str_contains($line, ':')) {
                continue;
            }

            [$key, $value] = explode(':', $line, 2);
            $values[trim($key)] = trim($value);
        }

        return $values;
    }

    /**
     * Importe por código de impuesto, con el formato del CUFE: punto decimal,
     * dos decimales, y «0.00» cuando el impuesto no se referenció.
     *
     * @return array<string, string>
     */
    private function taxAmounts(ElectronicInvoice $document): array
    {
        $amounts = ['01' => '0.00', '04' => '0.00', '03' => '0.00'];

        $payload = is_string($document->request_document)
            ? json_decode($document->request_document, true)
            : $document->request_document;

        foreach ((array) data_get($payload, 'Document.TXT', []) as $tax) {
            $scheme = (string) ($tax['SchemeID'] ?? '');

            if (array_key_exists($scheme, $amounts)) {
                $amounts[$scheme] = number_format((float) ($tax['TaxAmount'] ?? 0), 2, '.', '');
            }
        }

        return $amounts;
    }
}
