<?php

namespace App\Actions\Dian;

use App\Enums\ElectronicInvoiceStatus;
use App\Models\ElectronicInvoice;
use App\Services\Dian\TitanioClient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Descarga del proveedor el XML y el PDF oficiales del documento electrónico
 * y los guarda en almacenamiento privado.
 */
class DownloadElectronicInvoiceFilesAction
{
    use AsAction;

    /** Disco privado del volumen de datos. */
    public const DISK = 'documents';

    /**
     * @return array{invoice: ElectronicInvoice, notice: ?string}
     *         El aviso explica qué archivo no entregó el proveedor y por qué.
     */
    public function handle(ElectronicInvoice $electronic_invoice): array
    {
        abort_unless(auth()->user()?->can('workshop.invoices.dian.download'), 403);

        if (! $electronic_invoice->transaction_id) {
            throw ValidationException::withMessages([
                'dian' => 'La factura aún no tiene una transacción en el proveedor.',
            ]);
        }

        $client = TitanioClient::for($electronic_invoice->environment)->forInvoice($electronic_invoice);
        $directory = self::directory($electronic_invoice);
        $paths = [];
        $missing = [];

        // Se piden por separado: con dos o más archivos el proveedor devuelve un ZIP.
        foreach ([
            'xml_path' => [TitanioClient::DOWNLOAD_XML, 'xml', 'XML'],
            'pdf_path' => [TitanioClient::DOWNLOAD_PDF, 'pdf', 'PDF'],
        ] as $attribute => [$download_type, $extension, $label]) {
            $file = $client->download((int) $electronic_invoice->transaction_id, $download_type);
            $contents = filled($file['data']) ? base64_decode($file['data'], strict: true) : false;

            if ($contents === false || $contents === '') {
                $missing[] = $label.': '.($file['mensaje'] ?? 'el proveedor no entregó el archivo');

                continue;
            }

            $path = "{$directory}/{$electronic_invoice->document_number}.{$extension}";
            Storage::disk(self::DISK)->put($path, $contents);

            $paths[$attribute] = $path;
        }

        $notice = self::explain($missing, $electronic_invoice);

        if ($paths === []) {
            throw ValidationException::withMessages([
                'dian' => $notice ?? 'El proveedor aún no tiene archivos disponibles para esta factura.',
            ]);
        }

        $electronic_invoice->forceFill($paths)->save();

        return [
            'invoice' => $electronic_invoice->refresh(),
            'notice'  => $notice,
        ];
    }

    public static function directory(ElectronicInvoice $electronic_invoice): string
    {
        return "electronic-invoices/{$electronic_invoice->business_id}/{$electronic_invoice->id}";
    }

    /**
     * Traduce lo que faltó a una explicación útil: el proveedor solo genera la
     * representación gráfica de los documentos que la DIAN aceptó.
     *
     * @param  list<string>  $missing
     */
    private static function explain(array $missing, ElectronicInvoice $electronic_invoice): ?string
    {
        if ($missing === []) {
            return null;
        }

        $notice = implode('. ', $missing).'.';

        if ($electronic_invoice->status === ElectronicInvoiceStatus::Rejected) {
            $notice .= ' El proveedor genera el PDF solo cuando la DIAN acepta el documento;'
                .' corrige el motivo del rechazo y vuelve a emitir.';
        }

        return $notice;
    }
}
