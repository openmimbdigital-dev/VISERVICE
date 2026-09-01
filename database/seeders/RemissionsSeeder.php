<?php

namespace Database\Seeders;

use App\Enums\WorkOrderStatus;
use App\Models\Remission;
use App\Models\RemissionItem;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Database\Seeder;

class RemissionsSeeder extends Seeder
{
    public function run(): void
    {
        $created_by = User::query()->where('username', 'admin')->value('id');

        $plan = [
            [
                'work_order' => 'SEED-OT-TRANSAD-001',
                'reference'  => 'SEED-REM-TRANSAD-001',
                'type'       => 'entrega',
            ],
            [
                'work_order' => 'SEED-OT-TRANSAD-002',
                'reference'  => 'SEED-REM-TRANSAD-002',
                'type'       => 'entrega',
            ],
            [
                'work_order' => 'SEED-OT-TRANSAD-003',
                'reference'  => 'SEED-REM-TRANSAD-003',
                'type'       => 'entrega',
            ],
            [
                'work_order' => 'SEED-OT-TRANSAD-004',
                'reference'  => 'SEED-REM-TRANSAD-004',
                'type'       => 'devolucion',
            ],
            [
                'work_order' => 'SEED-OT-CARGA-001',
                'reference'  => 'SEED-REM-CARGA-001',
                'type'       => 'entrega',
            ],
            [
                'work_order' => 'SEED-OT-CARGA-002',
                'reference'  => 'SEED-REM-CARGA-002',
                'type'       => 'traslado',
            ],
            [
                'work_order' => 'SEED-OT-VALLE-001',
                'reference'  => 'SEED-REM-VALLE-001',
                'type'       => 'entrega',
            ],
        ];

        $created = 0;

        foreach ($plan as $index => $entry) {
            $work_order = WorkOrder::query()
                ->where('reference', $entry['work_order'])
                ->with([
                    'client.city',
                    'quotation:id,reference',
                    'items.catalogProduct.unit',
                    'items.catalogProduct.brand',
                    'items.productType',
                ])
                ->first();

            if (! $work_order) {
                $this->command?->warn("Remisiones: OT {$entry['work_order']} no encontrada.");

                continue;
            }

            if (! ($work_order->status?->isOpen() ?? false)) {
                $this->command?->warn("Remisiones: OT {$entry['work_order']} no está creada/en proceso, se omite.");

                continue;
            }

            $client = $work_order->client;
            $status = $work_order->status instanceof WorkOrderStatus
                ? $work_order->status
                : (WorkOrderStatus::tryFrom((string) $work_order->status) ?? WorkOrderStatus::Created);
            $is_issued = in_array($status, [WorkOrderStatus::InProgress, WorkOrderStatus::Completed], true);
            $is_delivered = $status === WorkOrderStatus::Completed;

            $remission = Remission::withTrashed()->updateOrCreate(
                [
                    'business_id' => $work_order->business_id,
                    'reference'   => $entry['reference'],
                ],
                [
                    'work_order_id'             => $work_order->id,
                    'client_id'                 => $work_order->client_id,
                    'type'                      => $entry['type'],
                    'status'                    => $status->value,
                    'quotation_or_po_reference' => $work_order->quotation?->reference,
                    'issue_date'                => $is_issued ? now()->subDays($index + 1)->toDateString() : null,
                    'issued_at'                 => $is_issued ? now()->subDays($index + 1) : null,
                    'delivery_address'          => $client?->address ?? 'Dirección de entrega demo',
                    'delivery_city'             => $client?->city?->name,
                    'delivery_contact'          => $client?->contact_name ?? $client?->name,
                    'delivery_phone'            => $client?->phone,
                    'delivery_observations'     => 'Entrega generada por RemissionsSeeder.',
                    'observations'              => 'Remisión de demostración.',
                    'delivered_by_name'         => 'Carlos Mendoza',
                    'delivered_by_position'     => 'Técnico de taller',
                    'delivered_by_document'     => '80123456',
                    'delivered_at'              => $is_delivered ? now()->subDays($index) : null,
                    'received_by_name'          => $client?->contact_name ?? $client?->name ?? 'Receptor demo',
                    'received_by_position'      => 'Encargado de flota',
                    'received_by_document'      => $client?->document_number ?? '90000000',
                    'received_at'               => $is_delivered ? now()->subDays($index) : null,
                    'created_by'                => $created_by,
                    'deleted_at'                => null,
                ]
            );

            if ($remission->trashed()) {
                $remission->restore();
            }

            $this->syncItems($remission, $work_order);
            $work_order->loadMissing('equipments:id');
            $remission->equipments()->sync(
                $work_order->equipments->pluck('id')->map(fn ($id) => (int) $id)->all()
            );
            $remission->recalculateTotalItems();

            $created++;
        }

        $this->command?->info("Remisiones demo: {$created} registros.");
    }

    private function syncItems(Remission $remission, WorkOrder $work_order): void
    {
        $remission->items()->delete();

        $sort = 0;

            foreach ($work_order->items as $item) {
                $product = $item->catalogProduct;

            RemissionItem::query()->create([
                'remission_id'        => $remission->id,
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
