<?php

namespace Database\Seeders;

use App\Enums\QuotationStatus;
use App\Models\Business;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\GeneralConfig;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class WorkOrdersSeeder extends Seeder
{
    private const TRANSAD_SLUG = 'transportes-transad';

    public function run(): void
    {
        $transad = Business::query()->where('slug', self::TRANSAD_SLUG)->first();

        if (! $transad) {
            $this->command?->warn('Órdenes de trabajo: no se encontró Transportes TRANSAD.');

            return;
        }

        $created_by = User::query()->where('username', 'admin')->value('id');

        $catalog_products = Product::query()
            ->where('business_id', $transad->id)
            ->where('status', true)
            ->orderBy('id')
            ->get();

        $plan = [
            ['business' => $transad, 'reference' => 'SEED-OT-TRANSAD-001', 'status' => 'abierta', 'from_quotation' => 'SEED-COT-TRANSAD-004'],
            ['business' => $transad, 'reference' => 'SEED-OT-TRANSAD-002', 'status' => 'abierta', 'from_quotation' => null],
            ['business' => $transad, 'reference' => 'SEED-OT-TRANSAD-003', 'status' => 'en_proceso', 'from_quotation' => null],
            ['business' => $transad, 'reference' => 'SEED-OT-TRANSAD-004', 'status' => 'en_proceso', 'from_quotation' => null],
            ['business' => $transad, 'reference' => 'SEED-OT-TRANSAD-005', 'status' => 'finalizada', 'from_quotation' => null],
            ['business' => $transad, 'reference' => 'SEED-OT-TRANSAD-006', 'status' => 'cancelada', 'from_quotation' => null],
            ['business_slug' => 'carga-rapida-sas', 'reference' => 'SEED-OT-CARGA-001', 'status' => 'abierta', 'from_quotation' => null],
            ['business_slug' => 'carga-rapida-sas', 'reference' => 'SEED-OT-CARGA-002', 'status' => 'en_proceso', 'from_quotation' => null],
            ['business_slug' => 'transportes-del-valle', 'reference' => 'SEED-OT-VALLE-001', 'status' => 'abierta', 'from_quotation' => 'SEED-COT-VALLE-002'],
            ['business_slug' => 'transportes-del-valle', 'reference' => 'SEED-OT-VALLE-002', 'status' => 'finalizada', 'from_quotation' => null],
        ];

        $created = 0;
        $sequence = 0;

        foreach ($plan as $entry) {
            $business = $entry['business'] ?? Business::query()->where('slug', $entry['business_slug'])->first();

            if (! $business) {
                $this->command?->warn("Órdenes de trabajo: negocio no encontrado para {$entry['reference']}.");

                continue;
            }

            $quotation = $this->resolveQuotation($business, $entry['from_quotation'] ?? null, $entry['reference']);

            $pair = $quotation
                ? ['client' => $quotation->client, 'equipment' => $quotation->equipment]
                : $this->resolveClientEquipmentPair($business, $sequence);

            if (! $pair || ! $pair['client'] || ! $pair['equipment']) {
                $this->command?->warn("Órdenes de trabajo: sin cliente/equipo para {$business->slug}, se omite {$entry['reference']}.");

                continue;
            }

            $sequence++;
            $is_finalized = $entry['status'] === 'finalizada';
            $km_entry = 45000 + ($sequence * 1250);

            $work_order = WorkOrder::withTrashed()->updateOrCreate(
                [
                    'business_id' => $business->id,
                    'reference'   => $entry['reference'],
                ],
                [
                    'client_id'          => $pair['client']->id,
                    'equipment_id'       => $pair['equipment']->id,
                    'quotation_id'       => $quotation?->id,
                    'status'             => $entry['status'],
                    'km_entry'           => $km_entry,
                    'km_exit'            => $is_finalized ? $km_entry + 35 : null,
                    'diagnosis'          => $quotation?->diagnosis ?? 'Diagnóstico inicial demo generado por seeder.',
                    'work_description'   => $is_finalized ? 'Trabajos de mantenimiento realizados según cotización/inspección.' : null,
                    'observations'       => 'OT de demostración.',
                    'notes'              => 'Generada por WorkOrdersSeeder.',
                    'estimated_delivery' => now()->addDays(2 + ($sequence % 5))->toDateString(),
                    'tax_percentage'     => $quotation?->tax_percentage ?? 19,
                    'document_client'    => $this->buildDocumentClient($business, $sequence, $pair['client']),
                    'created_by'         => $created_by,
                    'finalized_at'       => $is_finalized ? now()->subDays($sequence) : null,
                    'deleted_at'         => null,
                ]
            );

            if ($work_order->trashed()) {
                $work_order->restore();
            }

            $products = Product::query()
                ->where('business_id', $business->id)
                ->where('status', true)
                ->orderBy('id')
                ->get();

            if ($products->isEmpty()) {
                $products = $catalog_products;
            }

            $this->syncItems($work_order, $products, $quotation, $sequence);
            $work_order->recalculateTotals();

            $created++;
        }

        $this->command?->info("Órdenes de trabajo demo: {$created} registros.");
    }

    private function resolveQuotation(Business $business, ?string $reference, string $work_order_reference): ?Quotation
    {
        if (! $reference) {
            return null;
        }

        $quotation = Quotation::query()
            ->where('business_id', $business->id)
            ->where('reference', $reference)
            ->where('status', QuotationStatus::Aceptada)
            ->with(['client', 'equipment', 'items'])
            ->first();

        if (! $quotation) {
            $this->command?->warn("Órdenes de trabajo: cotización {$reference} no disponible (aceptada).");

            return null;
        }

        $linked_to_other = WorkOrder::withTrashed()
            ->where('quotation_id', $quotation->id)
            ->where('reference', '!=', $work_order_reference)
            ->exists();

        if ($linked_to_other) {
            $this->command?->warn("Órdenes de trabajo: cotización {$reference} ya tiene otra OT asociada.");

            return null;
        }

        return $quotation;
    }

    /** @return array{client: Client, equipment: Equipment}|null */
    private function resolveClientEquipmentPair(Business $business, int $sequence): ?array
    {
        $clients = Client::query()
            ->where('business_id', $business->id)
            ->where('status', true)
            ->orderBy('id')
            ->get();

        if ($clients->isEmpty()) {
            return null;
        }

        $client = $clients[$sequence % $clients->count()];

        $equipment = Equipment::query()
            ->where('business_id', $business->id)
            ->where('client_id', $client->id)
            ->where('status', true)
            ->orderBy('id')
            ->skip($sequence % 3)
            ->first();

        if (! $equipment) {
            $equipment = Equipment::query()
                ->where('business_id', $business->id)
                ->where('status', true)
                ->orderBy('id')
                ->skip($sequence % 5)
                ->first();
        }

        if (! $equipment) {
            return null;
        }

        return [
            'client'    => $client,
            'equipment' => $equipment,
        ];
    }

    /** @return array<string, string>|null */
    private function buildDocumentClient(Business $business, int $sequence, Client $client): ?array
    {
        $configs = GeneralConfig::query()
            ->where('business_id', $business->id)
            ->associatedDocumentsOt()
            ->orderBy('id')
            ->get(['label', 'value']);

        if ($configs->isEmpty()) {
            $label = GeneralConfig::makeLabelFromValue('Cédula del cliente');

            return [$label => $client->document_number ?: ('DOC-' . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT))];
        }

        $samples = [
            'Cédula del cliente'         => $client->document_number ?: '1020304050',
            'Tarjeta de propiedad'       => 'TP-' . str_pad((string) (1000 + $sequence), 4, '0', STR_PAD_LEFT),
            'SOAT vigente'               => 'SOAT-' . now()->format('Y') . '-' . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
            'Revisión técnico-mecánica'  => 'RTM-' . now()->addMonths(6)->format('Ym') . '-' . $sequence,
            'Póliza de seguro'           => 'POL-' . str_pad((string) (5000 + $sequence), 5, '0', STR_PAD_LEFT),
        ];

        $config = $configs[$sequence % $configs->count()];
        $value = $samples[$config->value] ?? ('DEMO-' . $sequence);

        return [$config->label => $value];
    }

    /** @param  Collection<int, Product>  $catalog_products */
    private function syncItems(WorkOrder $work_order, Collection $catalog_products, ?Quotation $quotation, int $sequence): void
    {
        $work_order->items()->delete();

        if ($quotation && $quotation->items->isNotEmpty()) {
            foreach ($quotation->items as $item) {
                WorkOrderItem::query()->create([
                    'work_order_id'       => $work_order->id,
                    'product_id'          => $item->product_id,
                    'product_type_id'     => $item->product_type_id,
                    'description'         => $item->description,
                    'quantity'            => $item->quantity,
                    'unit_price'          => $item->unit_price,
                    'discount_percentage' => $item->discount_percentage,
                    'subtotal'            => $item->subtotal,
                    'status'              => $work_order->status === 'finalizada' ? 'completado' : 'pendiente',
                ]);
            }

            return;
        }

        $rows = $catalog_products->isNotEmpty()
            ? $this->catalogProductRows($catalog_products, $sequence)
            : $this->genericProductRows($sequence);

        foreach ($rows as $row) {
            $qty      = (float) $row['quantity'];
            $price    = (float) $row['unit_price'];
            $discount = (float) $row['discount_percentage'];
            $subtotal = round($qty * $price * (1 - $discount / 100), 2);

            $item_status = match ($work_order->status) {
                'finalizada' => 'completado',
                'en_proceso' => 'en_proceso',
                default      => 'pendiente',
            };

            WorkOrderItem::query()->create([
                'work_order_id'       => $work_order->id,
                'product_id'          => $row['product_id'] ?? null,
                'product_type_id'     => $row['product_type_id'] ?? null,
                'description'         => $row['description'],
                'quantity'            => $qty,
                'unit_price'          => $price,
                'discount_percentage' => $discount,
                'subtotal'            => $subtotal,
                'status'              => $item_status,
            ]);
        }
    }

    /** @param  Collection<int, Product>  $catalog_products */
    /** @return list<array<string, mixed>> */
    private function catalogProductRows(Collection $catalog_products, int $sequence): array
    {
        $picked = $catalog_products->values()->slice($sequence % max($catalog_products->count() - 2, 1), 3);

        if ($picked->isEmpty()) {
            return $this->genericProductRows($sequence);
        }

        return $picked->map(function (Product $product, int $index) {
            return [
                'product_id'          => $product->id,
                'product_type_id'     => $product->product_type_id,
                'description'         => $product->name,
                'quantity'            => $index === 0 ? 1 : ($index + 1),
                'unit_price'          => (float) $product->sale_price,
                'discount_percentage' => $index === 2 ? 5 : 0,
            ];
        })->values()->all();
    }

    /** @return list<array<string, mixed>> */
    private function genericProductRows(int $sequence): array
    {
        $base = 80000 + ($sequence * 10000);

        return [
            [
                'description'         => 'Mano de obra — servicio OT demo',
                'quantity'            => 1,
                'unit_price'          => $base,
                'discount_percentage' => 0,
            ],
            [
                'description'         => 'Repuesto — mantenimiento preventivo',
                'quantity'            => 2,
                'unit_price'          => round($base * 0.55, 2),
                'discount_percentage' => 0,
            ],
        ];
    }
}
