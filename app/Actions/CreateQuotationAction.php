<?php

namespace App\Actions;

use App\Models\Quotation;
use App\Models\QuotationItem;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateQuotationAction
{
    use AsAction;

    /**
     * Crea una cotización con sus items en una transacción atómica.
     *
     * @param  int    $business_id
     * @param  int    $client_id
     * @param  int    $equipment_id
     * @param  array  $data        Campos adicionales de la cotización
     * @param  array  $items       Array de items: [description, item_type, quantity, unit_price, discount_percentage]
     * @return Quotation
     */
    public function handle(int $business_id, int $client_id, int $equipment_id, array $data, array $items = []): Quotation
    {
        return DB::transaction(function () use ($business_id, $client_id, $equipment_id, $data, $items) {
            $quotation = Quotation::create([
                'business_id'     => $business_id,
                'client_id'       => $client_id,
                'equipment_id'    => $equipment_id,
                'reference'       => Quotation::generateReference($business_id),
                'status'          => 'borrador',
                'diagnosis'       => $data['diagnosis'] ?? null,
                'km_entry'        => $data['km_entry'] ?? 0,
                'valid_until'     => $data['valid_until'] ?? null,
                'tax_percentage'  => $data['tax_percentage'] ?? 0,
                'notes'           => $data['notes'] ?? null,
                'observations'    => $data['observations'] ?? null,
                'created_by'      => $data['created_by'] ?? auth()->id(),
            ]);

            foreach ($items as $item) {
                $this->addItem($quotation, $item);
            }

            $quotation->recalculateTotals();

            return $quotation->fresh();
        });
    }

    protected function addItem(Quotation $quotation, array $item): QuotationItem
    {
        $qty      = (float) ($item['quantity'] ?? 1);
        $price    = (float) ($item['unit_price'] ?? 0);
        $discount = (float) ($item['discount_percentage'] ?? 0);
        $subtotal = round(($qty * $price) * (1 - $discount / 100), 2);

        return QuotationItem::create([
            'quotation_id'        => $quotation->id,
            'item_type'           => $item['item_type'] ?? 'servicio',
            'description'         => $item['description'],
            'quantity'            => $qty,
            'unit_price'          => $price,
            'discount_percentage' => $discount,
            'subtotal'            => $subtotal,
        ]);
    }
}
