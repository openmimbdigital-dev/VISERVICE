<?php

namespace Database\Seeders;

use App\Enums\QuotationStatus;
use App\Models\Business;
use App\Models\BusinessBankAccount;
use App\Models\BusinessPaymentMethod;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\QuotationServiceType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class QuotationsSeeder extends Seeder
{
    private const TRANSAD_SLUG = 'transportes-transad';

    public function run(): void
    {
        $transad = Business::query()->where('slug', self::TRANSAD_SLUG)->first();

        if (! $transad) {
            $this->command?->warn('Cotizaciones: no se encontró Transportes TRANSAD.');

            return;
        }

        $created_by = User::query()->where('username', 'admin')->value('id');
        $service_types = QuotationServiceType::query()
            ->where('active', true)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get();

        if ($service_types->isEmpty()) {
            $this->command?->warn('Cotizaciones: ejecuta QuotationServiceTypesSeeder primero.');

            return;
        }

        $payment_method = BusinessPaymentMethod::query()
            ->where('name', 'Transferencia bancaria')
            ->where('general', true)
            ->first();

        $bank_account = BusinessBankAccount::query()
            ->where('business_id', $transad->id)
            ->where('active', true)
            ->first();

        $catalog_products = Product::query()
            ->where('business_id', $transad->id)
            ->where('status', true)
            ->orderBy('id')
            ->get();

        $plan = [
            ['business' => $transad, 'reference' => 'SEED-COT-TRANSAD-001', 'status' => QuotationStatus::Created, 'reject_reason' => null, 'equipment_count' => 1],
            ['business' => $transad, 'reference' => 'SEED-COT-TRANSAD-002', 'status' => QuotationStatus::Created, 'reject_reason' => null, 'equipment_count' => 2],
            ['business' => $transad, 'reference' => 'SEED-COT-TRANSAD-003', 'status' => QuotationStatus::Sent, 'reject_reason' => null, 'equipment_count' => 1],
            ['business' => $transad, 'reference' => 'SEED-COT-TRANSAD-004', 'status' => QuotationStatus::Accepted, 'reject_reason' => null, 'equipment_count' => 1],
            ['business' => $transad, 'reference' => 'SEED-COT-TRANSAD-005', 'status' => QuotationStatus::Rejected, 'reject_reason' => 'El cliente solicitó replantear el alcance del mantenimiento.', 'equipment_count' => 2],
            ['business_slug' => 'carga-rapida-sas', 'reference' => 'SEED-COT-CARGA-001', 'status' => QuotationStatus::Created, 'reject_reason' => null, 'equipment_count' => 2],
            ['business_slug' => 'carga-rapida-sas', 'reference' => 'SEED-COT-CARGA-002', 'status' => QuotationStatus::Sent, 'reject_reason' => null, 'equipment_count' => 1],
            ['business_slug' => 'carga-rapida-sas', 'reference' => 'SEED-COT-CARGA-003', 'status' => QuotationStatus::Expired, 'reject_reason' => null, 'equipment_count' => 1],
            ['business_slug' => 'transportes-del-valle', 'reference' => 'SEED-COT-VALLE-001', 'status' => QuotationStatus::Created, 'reject_reason' => null, 'equipment_count' => 1],
            ['business_slug' => 'transportes-del-valle', 'reference' => 'SEED-COT-VALLE-002', 'status' => QuotationStatus::Accepted, 'reject_reason' => null, 'equipment_count' => 2],
        ];

        $created = 0;
        $sequence = 0;

        foreach ($plan as $entry) {
            $business = $entry['business'] ?? Business::query()->where('slug', $entry['business_slug'])->first();

            if (! $business) {
                $this->command?->warn("Cotizaciones: negocio no encontrado para {$entry['reference']}.");

                continue;
            }

            $bundle = $this->resolveClientEquipments($business, $sequence, (int) ($entry['equipment_count'] ?? 1));

            if ($bundle === null) {
                $this->command?->warn("Cotizaciones: sin cliente/equipos activos para {$business->slug}, se omite {$entry['reference']}.");

                continue;
            }

            $sequence++;
            $service_type = $service_types[$sequence % $service_types->count()];

            $quotation = Quotation::withTrashed()->updateOrCreate(
                [
                    'business_id' => $business->id,
                    'reference'   => $entry['reference'],
                ],
                [
                    'client_id'                  => $bundle['client']->id,
                    'quotation_service_type_id'  => $service_type->id,
                    'business_payment_method_id' => $business->id === $transad->id ? $payment_method?->id : null,
                    'business_bank_account_id'   => $business->id === $transad->id ? $bank_account?->id : null,
                    'status'                     => $entry['status'],
                    'diagnosis'                  => 'Diagnóstico demo generado por seeder.',
                    'hours_entry'                => sprintf('%02d:%02d:00', 8 + ($sequence % 4), ($sequence * 7) % 60),
                    'validity_days'              => 15,
                    'execution_time'             => ($sequence % 2 === 0) ? '2 días hábiles' : '1 día hábil',
                    'tax_percentage'             => 19,
                    'notes'                      => 'Cotización de demostración.',
                    'observations'               => 'Vigencia sujeta a disponibilidad de repuestos.',
                    'reject_reason'              => $entry['reject_reason'],
                    'created_by'                 => $created_by,
                    'deleted_at'                 => null,
                ]
            );

            if ($quotation->trashed()) {
                $quotation->restore();
            }

            $equipment_ids = $bundle['equipments']->pluck('id')->map(fn ($id) => (int) $id)->all();
            $quotation->equipments()->sync($equipment_ids);

            $this->syncItems(
                $quotation,
                $bundle['equipments'],
                $catalog_products,
                $business->id === $transad->id,
                $sequence
            );
            $quotation->syncValidUntil();
            $quotation->recalculateTotals();

            $created++;
        }

        $this->command?->info("Cotizaciones demo: {$created} registros (5 TRANSAD + 5 otros negocios).");
    }

    /**
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

    /**
     * @param  Collection<int, Equipment>  $equipments
     * @param  Collection<int, Product>  $catalog_products
     */
    private function syncItems(
        Quotation $quotation,
        Collection $equipments,
        Collection $catalog_products,
        bool $use_catalog,
        int $sequence
    ): void {
        $quotation->items()->delete();

        $equipment_ids = $equipments->pluck('id')->values();

        if ($equipment_ids->isEmpty()) {
            return;
        }

        $rows = $use_catalog && $catalog_products->isNotEmpty()
            ? $this->catalogProductRows($catalog_products, $sequence)
            : $this->genericProductRows($sequence);

        foreach ($rows as $index => $row) {
            $qty      = (float) $row['quantity'];
            $price    = (float) $row['unit_price'];
            $discount = (float) $row['discount_percentage'];
            $subtotal = round($qty * $price * (1 - $discount / 100), 2);
            $equipment_id = (int) $equipment_ids[$index % $equipment_ids->count()];

            QuotationItem::query()->create([
                'quotation_id'        => $quotation->id,
                'equipment_id'        => $equipment_id,
                'product_id'          => $row['product_id'] ?? null,
                'product_type_id'     => $row['product_type_id'] ?? null,
                'product_category_id' => $row['product_category_id'] ?? null,
                'description'         => $row['description'],
                'quantity'            => $qty,
                'unit_price'          => $price,
                'discount_percentage' => $discount,
                'subtotal'            => $subtotal,
            ]);
        }
    }

    /**
     * @param  Collection<int, Product>  $catalog_products
     * @return list<array<string, mixed>>
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
                'product_category_id' => $product->product_category_id,
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
        $base = 75000 + ($sequence * 12500);

        return [
            [
                'description'         => 'Mano de obra — revisión general',
                'quantity'            => 1,
                'unit_price'          => $base,
                'discount_percentage' => 0,
            ],
            [
                'description'         => 'Repuesto — kit de mantenimiento',
                'quantity'            => 2,
                'unit_price'          => round($base * 0.6, 2),
                'discount_percentage' => 0,
            ],
        ];
    }
}
