<?php

namespace Database\Seeders;

use App\Enums\QuotationStatus;
use App\Models\AssociatedDocumentType;
use App\Models\Business;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderAssociatedDocument;
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
            ['business' => $transad, 'reference' => 'SEED-OT-TRANSAD-001', 'status' => 'created', 'from_quotation' => 'SEED-COT-TRANSAD-004', 'equipment_count' => 1],
            ['business' => $transad, 'reference' => 'SEED-OT-TRANSAD-002', 'status' => 'created', 'from_quotation' => null, 'equipment_count' => 2],
            ['business' => $transad, 'reference' => 'SEED-OT-TRANSAD-003', 'status' => 'in_progress', 'from_quotation' => null, 'equipment_count' => 1],
            ['business' => $transad, 'reference' => 'SEED-OT-TRANSAD-004', 'status' => 'in_progress', 'from_quotation' => null, 'equipment_count' => 2],
            ['business' => $transad, 'reference' => 'SEED-OT-TRANSAD-005', 'status' => 'completed', 'from_quotation' => null, 'equipment_count' => 1],
            ['business' => $transad, 'reference' => 'SEED-OT-TRANSAD-006', 'status' => 'cancelled', 'from_quotation' => null, 'equipment_count' => 1],
            ['business_slug' => 'carga-rapida-sas', 'reference' => 'SEED-OT-CARGA-001', 'status' => 'created', 'from_quotation' => null, 'equipment_count' => 2],
            ['business_slug' => 'carga-rapida-sas', 'reference' => 'SEED-OT-CARGA-002', 'status' => 'in_progress', 'from_quotation' => null, 'equipment_count' => 1],
            ['business_slug' => 'transportes-del-valle', 'reference' => 'SEED-OT-VALLE-001', 'status' => 'created', 'from_quotation' => 'SEED-COT-VALLE-002', 'equipment_count' => 1],
            ['business_slug' => 'transportes-del-valle', 'reference' => 'SEED-OT-VALLE-002', 'status' => 'completed', 'from_quotation' => null, 'equipment_count' => 2],
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

            $bundle = $quotation
                ? $this->bundleFromQuotation($quotation)
                : $this->resolveClientEquipments($business, $sequence, (int) ($entry['equipment_count'] ?? 1));

            if ($bundle === null) {
                $this->command?->warn("Órdenes de trabajo: sin cliente/equipos para {$business->slug}, se omite {$entry['reference']}.");

                continue;
            }

            $sequence++;
            $is_finalized = $entry['status'] === 'completed';

            $work_order = WorkOrder::withTrashed()->updateOrCreate(
                [
                    'business_id' => $business->id,
                    'reference'   => $entry['reference'],
                ],
                [
                    'client_id'          => $bundle['client']->id,
                    'quotation_id'       => $quotation?->id,
                    'status'             => $entry['status'],
                    'diagnosis'          => $quotation?->diagnosis ?? 'Diagnóstico inicial demo generado por seeder.',
                    'work_description'   => $is_finalized ? 'Trabajos de mantenimiento realizados según cotización/inspección.' : null,
                    'observations'       => 'OT de demostración.',
                    'notes'              => 'Generada por WorkOrdersSeeder.',
                    'estimated_delivery' => now()->addDays(2 + ($sequence % 5))->toDateString(),
                    'tax_percentage'     => $quotation?->tax_percentage ?? 19,
                    'created_by'         => $created_by,
                    'finalized_at'       => $is_finalized ? now()->subDays($sequence) : null,
                    'deleted_at'         => null,
                ]
            );

            if ($work_order->trashed()) {
                $work_order->restore();
            }

            $equipment_ids = $bundle['equipments']->pluck('id')->map(fn ($id) => (int) $id)->all();
            $work_order->equipments()->sync($equipment_ids);

            $products = Product::query()
                ->where('business_id', $business->id)
                ->where('status', true)
                ->orderBy('id')
                ->get();

            if ($products->isEmpty()) {
                $products = $catalog_products;
            }

            $this->syncItems($work_order, $bundle['equipments'], $products, $quotation, $sequence);
            $this->syncAssociatedDocuments($work_order, $business, $sequence, $bundle['client']);
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
            ->where('status', QuotationStatus::Accepted)
            ->with(['client', 'equipments', 'items'])
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

    /**
     * @return array{client: Client, equipments: Collection<int, Equipment>}|null
     */
    private function bundleFromQuotation(Quotation $quotation): ?array
    {
        if (! $quotation->client) {
            return null;
        }

        $equipments = $quotation->equipments;

        if ($equipments->isEmpty()) {
            return null;
        }

        return [
            'client' => $quotation->client,
            'equipments' => $equipments->values(),
        ];
    }

    /**
     * Resuelve cliente + 1..N equipos del mismo cliente.
     * Reutiliza equipos entre OTs (un equipo puede aparecer en varias OT del año).
     *
     * @return array{client: Client, equipments: Collection<int, Equipment>}|null
     */
    private function resolveClientEquipments(Business $business, int $sequence, int $equipment_count): ?array
    {
        $equipment_count = max(1, $equipment_count);

        $clients = Client::query()
            ->where('business_id', $business->id)
            ->where('status', true)
            ->orderBy('id')
            ->get();

        if ($clients->isEmpty()) {
            return null;
        }

        $client = $clients[$sequence % $clients->count()];

        $client_equipments = Equipment::query()
            ->where('business_id', $business->id)
            ->where('client_id', $client->id)
            ->where('status', true)
            ->orderBy('id')
            ->get();

        if ($client_equipments->isEmpty()) {
            $client_equipments = Equipment::query()
                ->where('business_id', $business->id)
                ->where('status', true)
                ->orderBy('id')
                ->get();

            if ($client_equipments->isEmpty()) {
                return null;
            }

            $client = $client_equipments->first()->client
                ?? Client::query()->whereKey($client_equipments->first()->client_id)->first()
                ?? $client;
        }

        // Rotación: el offset cambia por secuencia para reusar equipos en distintas OT.
        $offset = $sequence % max($client_equipments->count(), 1);
        $picked = collect();

        for ($i = 0; $i < min($equipment_count, $client_equipments->count()); $i++) {
            $picked->push($client_equipments[($offset + $i) % $client_equipments->count()]);
        }

        return [
            'client' => $client,
            'equipments' => $picked->unique('id')->values(),
        ];
    }

    private function syncAssociatedDocuments(WorkOrder $work_order, Business $business, int $sequence, Client $client): void
    {
        $work_order->associatedDocuments()->delete();

        $types = AssociatedDocumentType::query()
            ->where('business_id', $business->id)
            ->where('active', true)
            ->orderBy('id')
            ->get(['id', 'name', 'send_invoice']);

        $samples = [
            'Cédula del cliente'         => $client->document_number ?: '1020304050',
            'Tarjeta de propiedad'       => 'TP-' . str_pad((string) (1000 + $sequence), 4, '0', STR_PAD_LEFT),
            'SOAT vigente'               => 'SOAT-' . now()->format('Y') . '-' . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
            'Revisión técnico-mecánica'  => 'RTM-' . now()->addMonths(6)->format('Ym') . '-' . $sequence,
            'Póliza de seguro'           => 'POL-' . str_pad((string) (5000 + $sequence), 5, '0', STR_PAD_LEFT),
        ];

        if ($types->isEmpty()) {
            $this->command?->warn("Órdenes de trabajo: sin tipos de documento asociado para {$business->slug}, se omiten documentos de la OT {$work_order->reference}.");

            return;
        }

        $rows = $types->take(2)->map(fn ($type) => [
            'associated_document_type_id' => $type->id,
            'name'                        => $type->name,
            'value'                       => $samples[$type->name] ?? ('DEMO-' . $sequence),
            'send_invoice'                => $type->send_invoice,
        ]);

        foreach ($rows as $row) {
            WorkOrderAssociatedDocument::query()->create([
                'work_order_id' => $work_order->id,
                ...$row,
            ]);
        }
    }

    /**
     * @param  Collection<int, Equipment>  $equipments
     * @param  Collection<int, Product>  $catalog_products
     */
    private function syncItems(
        WorkOrder $work_order,
        Collection $equipments,
        Collection $catalog_products,
        ?Quotation $quotation,
        int $sequence
    ): void {
        $work_order->items()->delete();

        $equipment_ids = $equipments->pluck('id')->values();

        if ($equipment_ids->isEmpty()) {
            return;
        }

        if ($quotation && $quotation->items->isNotEmpty()) {
            foreach ($quotation->items as $index => $item) {
                $equipment_id = (int) ($item->equipment_id ?: $equipment_ids[$index % $equipment_ids->count()]);

                WorkOrderItem::query()->create([
                    'work_order_id'       => $work_order->id,
                    'equipment_id'        => $equipment_id,
                    'product_id'          => $item->product_id,
                    'product_type_id'     => $item->product_type_id,
                    'description'         => $item->description,
                    'quantity'            => $item->quantity,
                    'unit_price'          => $item->unit_price,
                    'discount_percentage' => $item->discount_percentage,
                    'subtotal'            => $item->subtotal,
                ]);
            }

            return;
        }

        $rows = $catalog_products->isNotEmpty()
            ? $this->catalogProductRows($catalog_products, $sequence)
            : $this->genericProductRows($sequence);

        foreach ($rows as $index => $row) {
            $qty      = (float) $row['quantity'];
            $price    = (float) $row['unit_price'];
            $discount = (float) $row['discount_percentage'];
            $subtotal = round($qty * $price * (1 - $discount / 100), 2);
            $equipment_id = (int) $equipment_ids[$index % $equipment_ids->count()];

            WorkOrderItem::query()->create([
                'work_order_id'       => $work_order->id,
                'equipment_id'        => $equipment_id,
                'product_id'          => $row['product_id'] ?? null,
                'product_type_id'     => $row['product_type_id'] ?? null,
                'description'         => $row['description'],
                'quantity'            => $qty,
                'unit_price'          => $price,
                'discount_percentage' => $discount,
                'subtotal'            => $subtotal,
            ]);
        }
    }

    /** @param  Collection<int, Product>  $catalog_products
     *  @return list<array<string, mixed>>
     */
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
