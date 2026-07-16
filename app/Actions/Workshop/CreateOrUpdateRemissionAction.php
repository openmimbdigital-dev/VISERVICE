<?php

namespace App\Actions\Workshop;

use App\Actions\LogUserHistoricalAction;
use App\Models\Remission;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateRemissionAction
{
    use AsAction;

    /** @param  array<string, mixed>  $data */
    public function handle(int $business_id, ?int $remission_id, array $data): Remission
    {
        abort_unless(
            auth()->user()->can($remission_id ? 'workshop.remissions.edit' : 'workshop.remissions.create'),
            403
        );

        $user = auth()->user();
        abort_unless((int) $user->business_id === $business_id || $user->hasRole('superAdmin'), 403);

        $work_order = $this->assertEligibleWorkOrder($business_id, (int) $data['work_order_id']);
        $this->assertWorkOrderHasNoRemission($work_order, $remission_id);

        return DB::transaction(function () use ($business_id, $remission_id, $data, $work_order) {
            $payload = [
                'work_order_id'             => $work_order->id,
                'client_id'                 => $work_order->client_id,
                'equipment_id'              => $work_order->equipment_id,
                'type'                      => $data['type'],
                'status'                    => $data['status'] ?? 'borrador',
                'quotation_or_po_reference' => $data['quotation_or_po_reference'] ?? null,
                'issue_date'                => $data['issue_date'] ?? null,
                'delivery_address'          => $data['delivery_address'] ?? null,
                'delivery_city'             => $data['delivery_city'] ?? null,
                'delivery_contact'          => $data['delivery_contact'] ?? null,
                'delivery_phone'            => $data['delivery_phone'] ?? null,
                'delivery_observations'     => $data['delivery_observations'] ?? null,
                'observations'              => $data['observations'] ?? null,
                'delivered_by_name'         => $data['delivered_by_name'] ?? null,
                'delivered_by_position'     => $data['delivered_by_position'] ?? null,
                'delivered_by_document'     => $data['delivered_by_document'] ?? null,
                'received_by_name'          => $data['received_by_name'] ?? null,
                'received_by_position'      => $data['received_by_position'] ?? null,
                'received_by_document'      => $data['received_by_document'] ?? null,
            ];

            if (($payload['status'] ?? null) === 'emitida' && empty($data['issue_date'])) {
                $payload['issue_date'] = now()->toDateString();
                $payload['issued_at'] = now();
            }

            if (($payload['status'] ?? null) === 'entregada') {
                $payload['delivered_at'] = $payload['delivered_at'] ?? now();
                $payload['issued_at'] = $payload['issued_at'] ?? now();
                $payload['issue_date'] = $payload['issue_date'] ?? now()->toDateString();
            }

            $work_order_changed = false;

            if ($remission_id) {
                $remission = Remission::query()->forAuthUser()->findOrFail($remission_id);
                abort_unless((int) $remission->business_id === $business_id, 403);
                $work_order_changed = (int) $remission->work_order_id !== (int) $work_order->id;
                $remission->update($payload);
            } else {
                $remission = Remission::create([
                    ...$payload,
                    'business_id' => $business_id,
                    'reference'   => Remission::generateReference($business_id),
                    'created_by'  => $data['created_by'] ?? auth()->id(),
                ]);
                $work_order_changed = true;
            }

            if ($work_order_changed || $remission->items()->doesntExist()) {
                $this->syncItemsFromWorkOrder($remission, $work_order);
            }

            $remission->recalculateTotalItems();

            $remission = $remission->fresh(['items', 'client:id,name', 'equipment', 'workOrder']);

            $action = $remission_id ? 'updated' : 'created';
            $description = ($remission_id ? 'Actualizó' : 'Creó') . " la remisión {$remission->reference}";

            LogUserHistoricalAction::run(
                action: $action,
                module: 'workshop.remissions',
                description: $description,
                subject: $remission,
                subject_label: $remission->reference,
                properties: [
                    'status'        => $remission->status,
                    'type'          => $remission->type,
                    'work_order_id' => $remission->work_order_id,
                    'total_items'   => $remission->total_items,
                ],
                business_id: $business_id,
            );

            return $remission;
        });
    }

    private function assertEligibleWorkOrder(int $business_id, int $work_order_id): WorkOrder
    {
        $work_order = WorkOrder::query()
            ->forAuthUser()
            ->where('business_id', $business_id)
            ->whereKey($work_order_id)
            ->firstOrFail();

        abort_unless(
            in_array($work_order->status, ['abierta', 'en_proceso'], true),
            422,
            'La remisión solo puede asociarse a una OT abierta o en proceso.'
        );

        return $work_order;
    }

    private function assertWorkOrderHasNoRemission(WorkOrder $work_order, ?int $remission_id): void
    {
        $exists = Remission::query()
            ->where('work_order_id', $work_order->id)
            ->when($remission_id, fn ($q) => $q->whereKeyNot($remission_id))
            ->exists();

        abort_unless(! $exists, 422, 'Esta OT ya tiene una remisión generada.');
    }

    private function syncItemsFromWorkOrder(Remission $remission, WorkOrder $work_order): void
    {
        $work_order->loadMissing([
            'items.catalogProduct.unit',
            'items.catalogProduct.brand',
            'items.catalogProduct.product_category',
            'items.productType',
        ]);

        $remission->items()->delete();

        $sort = 0;

        foreach ($work_order->items as $item) {
            if ($item->status === 'cancelado') {
                continue;
            }

            $product = $item->catalogProduct;

            $remission->items()->create([
                'work_order_item_id'  => $item->id,
                'product_id'          => $item->product_id,
                'product_type_id'     => $item->product_type_id,
                'product_category_id' => $product?->product_category_id,
                'unit_id'             => $product?->unit_id,
                'description'         => $item->description,
                'reference_brand'     => $product?->code
                    ?: $product?->brand?->name
                    ?: null,
                'unit_name'           => $product?->unit?->name,
                'quantity'            => $item->quantity,
                'observations'        => $item->technician_notes,
                'sort_order'          => $sort++,
            ]);
        }
    }
}
