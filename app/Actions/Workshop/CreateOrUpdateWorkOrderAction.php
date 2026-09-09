<?php

namespace App\Actions\Workshop;

use App\Actions\LogEquipmentHistoricalAction;
use App\Actions\LogUserHistoricalAction;
use App\Enums\QuotationStatus;
use App\Enums\WorkOrderStatus;
use App\Models\Client;
use App\Models\Coupon;
use App\Models\Equipment;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\Quotation;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateWorkOrderAction
{
    use AsAction;

    /**
     * @param  list<int>  $equipment_ids
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $items
     */
    public function handle(
        int $business_id,
        ?int $work_order_id,
        int $client_id,
        array $equipment_ids,
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

        $equipment_ids = $this->normalizeEquipmentIds($equipment_ids);
        $this->assertEquipmentsBelongToClient($client_id, $equipment_ids);

        $quotation_id = ! empty($data['quotation_id']) ? (int) $data['quotation_id'] : null;
        $quotation = null;

        if ($quotation_id) {
            $quotation = $this->assertAcceptedQuotationAvailable($business_id, $quotation_id, $work_order_id);
            $client_id = (int) $quotation->client_id;
            $quotation->loadMissing('equipments:id');
            $equipment_ids = $quotation->equipments->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        }

        return DB::transaction(function () use ($business_id, $work_order_id, $client_id, $equipment_ids, $data, $items, $quotation_id, $quotation) {
            $payload = [
                'client_id'          => $client_id,
                'bill_to_final_consumer' => (bool) ($data['bill_to_final_consumer'] ?? false),
                'quotation_id'       => $quotation_id,
                'step'               => (int) ($data['step'] ?? 1),
                'final_step'         => (int) ($data['final_step'] ?? WorkOrder::DEFAULT_FINAL_STEP),
                'diagnosis'          => $data['diagnosis'] ?? null,
                'estimated_delivery' => $data['estimated_delivery'] ?? null,
                'notes'              => $data['notes'] ?? null,
                'observations'       => $data['observations'] ?? null,
            ];

            if ($work_order_id) {
                $work_order = WorkOrder::query()->forAuthUser()->findOrFail($work_order_id);
                abort_unless((int) $work_order->business_id === $business_id, 403);
                abort_unless($work_order->status?->isOpen() ?? false, 422);

                $work_order->update($payload);
            } else {
                $work_order = WorkOrder::create([
                    ...$payload,
                    'business_id' => $business_id,
                    'reference'   => WorkOrder::generateReference($business_id),
                    'status'      => WorkOrderStatus::Draft,
                    'created_by'  => $data['created_by'] ?? auth()->id(),
                    'advance_percentage' => 0,
                    'advance_amount' => 0,
                ]);

                RecordWorkOrderStatusHistoryAction::run(
                    work_order: $work_order,
                    to_status: WorkOrderStatus::Draft,
                    metadata: ['origin' => $quotation_id ? 'quotation' : 'manual'],
                );
            }

            $work_order->equipments()->sync($equipment_ids);
            $this->syncItems($work_order, $items, $equipment_ids);

            // El cupón se resuelve con los ítems ya guardados: su validación
            // depende del subtotal (monto mínimo) y del descuento resultante.
            $this->applyCoupon($work_order, $data, $business_id);

            $custom_tax_ids = $data['custom_tax_ids'] ?? null;

            if ($custom_tax_ids === null && $quotation && ! $work_order_id) {
                $quotation->loadMissing('appliedTaxes');
                $custom_tax_ids = $quotation->appliedCustomTaxIds();
            }

            SyncAppliedTaxesAction::run($work_order, $custom_tax_ids ?? [], $business_id, 0);
            $work_order->recalculateTotals();

            $advance_percentage = $quotation && ! $work_order_id
                ? (float) ($quotation->advance_percentage ?? 0)
                : (float) ($data['advance_percentage'] ?? 0);

            SyncWorkOrderAdvanceCommitmentAction::run($work_order, $advance_percentage);

            $work_order = $work_order->fresh([
                'items.productType',
                'items.catalogProduct',
                'items.equipment',
                'client:id,name',
                'equipments',
            ]);

            if ($work_order->isDraft() && $work_order->isComplete()) {
                $work_order->update(['status' => WorkOrderStatus::Created]);
                $work_order->refresh();

                RecordWorkOrderStatusHistoryAction::run(
                    work_order: $work_order,
                    to_status: WorkOrderStatus::Created,
                    from_status: WorkOrderStatus::Draft,
                    metadata: ['origin' => 'wizard_completed'],
                );
            }

            $action = $work_order_id ? 'updated' : 'created';
            $description = ($work_order_id ? 'Actualizó' : 'Creó') . " la orden de trabajo {$work_order->reference}";
            $properties = [
                'status'         => $work_order->status,
                'client_id'      => $work_order->client_id,
                'equipment_ids'  => $work_order->equipments->pluck('id')->all(),
                'quotation_id'   => $work_order->quotation_id,
                'coupon_code'    => $work_order->coupon_code,
                'discount_amount' => $work_order->discount_amount,
                'total'          => $work_order->total,
                'items_count'    => $work_order->items->count(),
                'advance_percentage' => $work_order->advance_percentage,
                'advance_amount' => $work_order->advance_amount,
            ];

            LogUserHistoricalAction::run(
                action: $action,
                module: 'workshop.work-orders',
                description: $description,
                subject: $work_order,
                subject_label: $work_order->reference,
                properties: $properties,
                business_id: $business_id,
            );

            foreach ($work_order->equipments as $equipment) {
                LogEquipmentHistoricalAction::run(
                    action: $action,
                    module: 'workshop.work-orders',
                    description: $description,
                    equipment: $equipment,
                    subject: $work_order,
                    properties: $properties,
                    business_id: $business_id,
                );
            }

            return $work_order;
        });
    }

    /**
     * Guarda en la OT el cupón enviado, validando que sea del negocio y que se
     * pueda usar con el subtotal que quedó.
     *
     * @param  array<string, mixed>  $data
     */
    private function applyCoupon(WorkOrder $work_order, array $data, int $business_id): void
    {
        $coupon_id = ! empty($data['coupon_id']) ? (int) $data['coupon_id'] : null;

        if (! $coupon_id) {
            $work_order->forceFill(['coupon_id' => null, 'coupon_code' => null])->save();

            return;
        }

        $coupon = Coupon::query()
            ->where('business_id', $business_id)
            ->whereKey($coupon_id)
            ->first();

        if (! $coupon) {
            throw ValidationException::withMessages([
                'coupon_code' => 'El cupón seleccionado no está disponible.',
            ]);
        }

        $subtotal = round((float) $work_order->items()->sum('subtotal'), 2);
        $reason   = $coupon->unavailableReason($subtotal, $work_order->id);

        if ($reason !== null) {
            throw ValidationException::withMessages(['coupon_code' => $reason]);
        }

        $work_order->forceFill([
            'coupon_id'   => $coupon->id,
            'coupon_code' => $coupon->code,
        ])->save();
    }

    /** @param  list<int|string>  $equipment_ids
     *  @return list<int>
     */
    private function normalizeEquipmentIds(array $equipment_ids): array
    {
        return array_values(array_unique(array_filter(
            array_map(fn ($id) => (int) $id, $equipment_ids),
            fn (int $id) => $id > 0
        )));
    }

    /** @param  list<int>  $equipment_ids */
    private function assertEquipmentsBelongToClient(int $client_id, array $equipment_ids): void
    {
        if ($equipment_ids === []) {
            return;
        }

        $count = Equipment::query()
            ->forAuthUser()
            ->where('client_id', $client_id)
            ->whereIn('id', $equipment_ids)
            ->count();

        abort_unless($count === count($equipment_ids), 422, 'Uno o más equipos no pertenecen al cliente.');
    }

    private function assertAcceptedQuotationAvailable(int $business_id, int $quotation_id, ?int $work_order_id): Quotation
    {
        $quotation = Quotation::query()
            ->forAuthUser()
            ->where('business_id', $business_id)
            ->where('status', QuotationStatus::Accepted)
            ->whereKey($quotation_id)
            ->firstOrFail();

        $linked = WorkOrder::query()
            ->where('quotation_id', $quotation->id)
            ->when($work_order_id, fn ($q) => $q->whereKeyNot($work_order_id))
            ->exists();

        abort_unless(! $linked, 422, 'La cotización ya tiene una orden de trabajo asociada.');

        return $quotation;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  list<int>  $equipment_ids
     */
    private function syncItems(WorkOrder $work_order, array $items, array $equipment_ids): void
    {
        $kept_ids = [];
        $allowed = array_flip($equipment_ids);
        $product_ids = [];

        foreach ($items as $row) {
            $product_id = (int) ($row['product_id'] ?? 0);
            if ($product_id > 0) {
                $product_ids[] = $product_id;
            }
        }

        $catalog = $product_ids === []
            ? collect()
            : Product::query()
                ->forAuthUser()
                ->complete()
                ->where('business_id', $work_order->business_id)
                ->whereIn('id', array_unique($product_ids))
                ->get()
                ->keyBy('id');

        foreach ($items as $row) {
            $description = trim((string) ($row['description'] ?? ''));
            $product_id = (int) ($row['product_id'] ?? 0);

            if ($product_id <= 0 && $description === '') {
                continue;
            }

            if (! empty($row['product_type_id'])) {
                abort_unless(ProductType::query()->visibleToUser()->whereKey($row['product_type_id'])->exists(), 422);
            }

            $product = $product_id > 0 ? $catalog->get($product_id) : null;
            abort_unless($product_id <= 0 || $product !== null, 422, 'Uno o más productos no están disponibles en el catálogo.');

            $equipment_id = ! empty($row['equipment_id']) ? (int) $row['equipment_id'] : null;

            abort_unless(
                $equipment_id === null || isset($allowed[$equipment_id]),
                422,
                'El equipo del ítem no pertenece a la OT.'
            );

            $qty = (float) ($row['quantity'] ?? 1);

            if ($product) {
                $price = (float) $product->sale_price;
                $apply_discount = $product->hasDiscount() && filter_var($row['apply_discount'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $discount = $apply_discount ? $product->discountPercentage() : 0.0;
                $description = $product->name;
                $row['product_type_id'] = $product->product_type_id;
            } else {
                $price = (float) ($row['unit_price'] ?? 0);
                $discount = (float) ($row['discount_percentage'] ?? 0);
            }

            $subtotal = WorkOrderItem::lineSubtotal($qty, $price, $discount);

            $payload = [
                'equipment_id'        => $equipment_id,
                'product_id'          => $product?->id,
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
                $item = $work_order->items()->create($payload);
                $kept_ids[] = (int) $item->id;
            }
        }

        $work_order->items()->whereNotIn('id', $kept_ids)->delete();
    }
}
