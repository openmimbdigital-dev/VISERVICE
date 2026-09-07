<?php

namespace App\Actions\Dian;

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

    public function handle(ElectronicInvoice $electronic_invoice): ElectronicInvoice
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

        // Se piden por separado: con dos o más archivos el proveedor devuelve un ZIP.
        foreach ([
            'xml_path' => [TitanioClient::DOWNLOAD_XML, 'xml'],
            'pdf_path' => [TitanioClient::DOWNLOAD_PDF, 'pdf'],
        ] as $attribute => [$download_type, $extension]) {
            $file = $client->download((int) $electronic_invoice->transaction_id, $download_type);

            if ($file === null) {
                continue;
            }

            $contents = base64_decode($file['data'], strict: true);

            if ($contents === false || $contents === '') {
                continue;
            }

            $path = "{$directory}/{$electronic_invoice->document_number}.{$extension}";
            Storage::disk('local')->put($path, $contents);

            $paths[$attribute] = $path;
        }

        if ($paths === []) {
            throw ValidationException::withMessages([
                'dian' => 'El proveedor aún no tiene archivos disponibles para esta factura.',
            ]);
        }

        $electronic_invoice->forceFill($paths)->save();

        return $electronic_invoice->refresh();
    }

    public static function directory(ElectronicInvoice $electronic_invoice): string
    {
        return "dian/{$electronic_invoice->business_id}/{$electronic_invoice->id}";
    }
}
