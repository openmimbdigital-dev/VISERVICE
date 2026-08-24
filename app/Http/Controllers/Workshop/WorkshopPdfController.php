<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\GeneralConfig;
use App\Models\Quotation;
use App\Models\Remission;
use App\Models\WorkOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class WorkshopPdfController extends Controller
{
    public function quotation(Quotation $quotation): Response
    {
        abort_unless(auth()->user()?->can('workshop.quotations.view'), 403);

        $quotation->load([
            'business', 'client', 'equipments', 'quotationServiceType',
            'paymentMethod', 'bankAccount', 'createdBy',
            'items.productType', 'items.productCategory', 'items.catalogProduct', 'items.equipment',
        ]);

        return Pdf::loadView('pdf.quotation', [
            'quotation'          => $quotation,
            'category_subtotals' => $quotation->subtotalsByPdfCategory(),
            'title'              => 'Cotización ' . $quotation->reference,
        ])
            ->setPaper('letter')
            ->download($quotation->reference . '.pdf');
    }

    public function remission(Remission $remission): Response
    {
        abort_unless(auth()->user()?->can('workshop.remissions.view'), 403);

        abort_unless(
            Remission::query()->forAuthUser()->whereKey($remission->id)->exists(),
            404
        );

        $remission->load([
            'business',
            'client',
            'equipments',
            'workOrder.items.productType',
            'workOrder.items.equipment',
            'workOrder.items.catalogProduct',
            'createdBy',
        ]);

        return Pdf::loadView('pdf.remission', array_merge([
            'remission' => $remission,
            'title'     => 'Remisión ' . $remission->reference,
        ], $this->documentClientData(
            $remission->business_id,
            $remission->workOrder?->document_client ?? []
        )))
            ->setPaper('letter')
            ->download($remission->reference . '.pdf');
    }

    public function workOrder(WorkOrder $workOrder): Response
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.view'), 403);

        abort_unless(
            WorkOrder::query()->forAuthUser()->whereKey($workOrder->id)->exists(),
            404
        );

        $workOrder->load([
            'business',
            'client',
            'equipments',
            'quotation',
            'createdBy',
            'items.productType',
            'items.catalogProduct',
            'items.equipment',
        ]);

        return Pdf::loadView('pdf.work-order', array_merge([
            'workOrder' => $workOrder,
            'title'     => 'OT ' . $workOrder->reference,
        ], $this->documentClientData(
            $workOrder->business_id,
            $workOrder->document_client ?? []
        )))
            ->setPaper('letter')
            ->download($workOrder->reference . '.pdf');
    }

    private function documentClientData(int $business_id, array $document_client): array
    {
        $document_labels = [];

        if ($document_client !== []) {
            $document_labels = GeneralConfig::query()
                ->forAuthUser()
                ->associatedDocumentsOt()
                ->where('business_id', $business_id)
                ->whereIn('label', array_keys($document_client))
                ->pluck('value', 'label')
                ->all();
        }

        return [
            'document_client' => $document_client,
            'document_labels' => $document_labels,
        ];
    }
}
