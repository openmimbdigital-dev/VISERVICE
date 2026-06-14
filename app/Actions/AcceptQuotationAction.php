<?php

namespace App\Actions;

use App\Models\Quotation;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class AcceptQuotationAction
{
    use AsAction;

    /**
     * Acepta la cotización y crea automáticamente la OT correspondiente.
     *
     * @return \App\Models\WorkOrder
     */
    public function handle(Quotation $quotation): \App\Models\WorkOrder
    {
        return DB::transaction(function () use ($quotation) {
            $quotation->update([
                'status'      => 'aceptada',
                'accepted_at' => now(),
            ]);

            return CreateWorkOrderAction::run(
                $quotation->business_id,
                $quotation->client_id,
                $quotation->vehicle_id,
                [
                    'quotation_id'    => $quotation->id,
                    'diagnosis'       => $quotation->diagnosis,
                    'km_entry'        => $quotation->km_entry,
                    'tax_percentage'  => $quotation->tax_percentage,
                    'notes'           => $quotation->notes,
                    'created_by'      => auth()->id(),
                ],
                $quotation->items->map(fn ($item) => [
                    'item_type'           => $item->item_type,
                    'description'         => $item->description,
                    'quantity'            => $item->quantity,
                    'unit_price'          => $item->unit_price,
                    'discount_percentage' => $item->discount_percentage,
                    'catalog_item_id'     => $item->catalog_item_id,
                    'catalog_item_type'   => $item->catalog_item_type,
                ])->toArray()
            );
        });
    }
}
