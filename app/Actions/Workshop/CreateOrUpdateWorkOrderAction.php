<?php

namespace App\Actions\Workshop;

use App\Enums\QuotationStatus;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\Quotation;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateWorkOrderAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $items
     */
    public function handle(
        int $business_id,
        ?int $work_order_id,
        int $client_id,
        int $equipment_id,
        array $data,
        array $items = []
    ): WorkOrder {
        abort_unless(
            auth()->user()->can($work_order_id ? 'workshop.work-orders.edit' : 'workshop.work-orders.create'),
            403
        );

        $user = auth()->user();
        abort_unless((int) $user->business_id === $business_id || $user->hasRole('superAdmin'), 403);

        abort_unless(Client::query()->forAuthUser()->whereKey($client_id)->exists(), 422);
        abort_unless(
            Equipment::query()
                ->forAuthUser()
                ->where('client_id', $client_id)
                ->whereKey($equipment_id)
                ->exists(),
            422
        );

        $quotation_id = ! empty($data['quotation_id']) ? (int) $data['quotation_id'] : null;

        if ($quotation_id) {
            $quotation = $this->assertAcceptedQuotationAvailable($business_id, $quotation_id, $work_order_id);
            $client_id = (int) $quotation->client_id;
            $equipment_id = (int) $quotation->equipment_id;
        }

        return DB::transaction(function () use ($business_id, $work_order_id, $client_id, $equipment_id, $data, $items, $quotation_id) {
            $payload = [
                'client_id'          => $client_id,
                'equipment_id'       => $equipment_id,
                'quotation_id'       => $quotation_id,
                'km_entry'           => (int) ($data['km_entry'] ?? 0),
                'diagnosis'          => $data['diagnosis'] ?? null,
                'estimated_delivery' => $data['estimated_delivery'] ?? null,
                'tax_percentage'     => $data['tax_percentage'] ?? 19,
                'notes'              => $data['notes'] ?? null,
                'observations'       => $data['observations'] ?? null,
            ];

            if ($work_order_id) {
                $work_order = WorkOrder::query()->forAuthUser()->findOrFail($work_order_id);
                abort_unless((int) $work_order->business_id === $business_id, 403);
                abort_unless(in_array($work_order->status, ['abierta', 'en_proceso'], true), 422);

                $work_order->update($payload);
            } else {
                $work_order = WorkOrder::create([
                    ...$payload,
                    'business_id' => $business_id,
                    'reference'   => WorkOrder::generateReference($business_id),
                    'status'      => 'abierta',
                    'created_by'  => $data['created_by'] ?? auth()->id(),
                ]);
            }

            $this->syncItems($work_order, $items);
            $work_order->recalculateTotals();

            $equipment = $work_order->equipment;
            if ($equipment && $work_order->km_entry > (int) $equipment->km_current) {
                $equipment->update(['km_current' => $work_order->km_entry]);
            }

            return $work_order->fresh(['items.productType', 'items.catalogProduct']);
        });
    }

    private function assertAcceptedQuotationAvailable(int $business_id, int $quotation_id, ?int $work_order_id): Quotation
    {
        $quotation = Quotation::query()
            ->forAuthUser()
            ->where('business_id', $business_id)
            ->where('status', QuotationStatus::Aceptada)
            ->whereKey($quotation_id)
            ->firstOrFail();

        $linked = WorkOrder::query()
            ->where('quotation_id', $quotation->id)
            ->when($work_order_id, fn ($q) => $q->whereKeyNot($work_order_id))
            ->exists();

        abort_unless(! $linked, 422, 'La cotización ya tiene una orden de trabajo asociada.');

        return $quotation;
    }

    /** @param  array<int, array<string, mixed>>  $items */
    private function syncItems(WorkOrder $work_order, array $items): void
    {
        $kept_ids = [];

        foreach ($items as $row) {
            $description = trim((string) ($row['description'] ?? ''));
            if ($description === '') {
                continue;
            }

            if (! empty($row['product_type_id'])) {
                abort_unless(ProductType::query()->visibleToUser()->whereKey($row['product_type_id'])->exists(), 422);
            }

            if (! empty($row['product_id'])) {
                abort_unless(
                    Product::query()
                        ->forAuthUser()
                        ->where('business_id', $work_order->business_id)
                        ->whereKey($row['product_id'])
                        ->exists(),
                    422
                );
            }

            $qty      = (float) ($row['quantity'] ?? 1);
            $price    = (float) ($row['unit_price'] ?? 0);
            $discount = (float) ($row['discount_percentage'] ?? 0);
            $subtotal = round($qty * $price * (1 - $discount / 100), 2);

            $payload = [
                'product_id'          => $row['product_id'] ?: null,
                'product_type_id'     => $row['product_type_id'] ?: null,
                'description'         => $description,
                'quantity'            => $qty,
                'unit_price'          => $price,
                'discount_percentage' => $discount,
                'subtotal'            => $subtotal,
            ];

            if (! empty($row['id'])) {
                $item = WorkOrderItem::query()
                    ->where('work_order_id', $work_order->id)
                    ->whereKey($row['id'])
                    ->firstOrFail();
                $item->update($payload);
                $kept_ids[] = (int) $item->id;
            } else {
                $item = $work_order->items()->create([
                    ...$payload,
                    'status' => 'pendiente',
                ]);
                $kept_ids[] = (int) $item->id;
            }
        }

        $work_order->items()->whereNotIn('id', $kept_ids)->delete();
    }
}
