<?php

namespace App\Http\Controllers\Workshop;

use App\Actions\Dian\DownloadElectronicInvoiceFilesAction;
use App\Http\Controllers\Controller;
use App\Models\ElectronicInvoice;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ElectronicInvoiceFileController extends Controller
{
    /**
     * Entrega el XML o el PDF oficial descargado del proveedor.
     * Los archivos viven en almacenamiento privado, nunca en public/.
     */
    public function __invoke(ElectronicInvoice $electronicInvoice, string $type): StreamedResponse
    {
        abort_unless(auth()->user()?->can('workshop.invoices.dian.download'), 403);

        abort_unless(
            ElectronicInvoice::query()->forAuthUser()->whereKey($electronicInvoice->id)->exists(),
            404
        );

        abort_unless(in_array($type, ['xml', 'pdf'], true), 404);

        $path = $type === 'xml' ? $electronicInvoice->xml_path : $electronicInvoice->pdf_path;

        abort_if(blank($path) || ! Storage::disk(DownloadElectronicInvoiceFilesAction::DISK)->exists($path), 404);

        return Storage::disk(DownloadElectronicInvoiceFilesAction::DISK)->download(
            $path,
            "{$electronicInvoice->document_number}.{$type}"
        );
    }
}
