<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
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
            'workOrder.associatedDocuments',
            'createdBy',
        ]);

        return Pdf::loadView('pdf.remission', [
            'remission' => $remission,
            'title'     => 'Remisión ' . $remission->reference,
        ])
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
            'associatedDocuments',
        ]);

        return Pdf::loadView('pdf.work-order', [
            'workOrder' => $workOrder,
            'title'     => 'OT ' . $workOrder->reference,
        ])
            ->setPaper('letter')
            ->download($workOrder->reference . '.pdf');
    }
}
