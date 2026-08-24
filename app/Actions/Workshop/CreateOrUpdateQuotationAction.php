<?php

namespace App\Actions\Workshop;

use App\Actions\LogEquipmentHistoricalAction;
use App\Actions\LogUserHistoricalAction;
use App\Enums\QuotationStatus;
use App\Models\BusinessBankAccount;
use App\Models\BusinessPaymentMethod;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\QuotationServiceType;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateQuotationAction
{
    use AsAction;

    /**
     * @param  list<int>  $equipment_ids
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $items
     */
    public function handle(
        int $business_id,
        ?int $quotation_id,
        int $client_id,
        array $equipment_ids,
        array $data,
        array $items = []
    ): Quotation {
        abort_unless(
            auth()->user()->can($quotation_id ? 'workshop.quotations.edit' : 'workshop.quotations.create'),
            403
        );

        $user = auth()->user();
        abort_unless((int) $user->business_id === $business_id || $user->hasRole('superAdmin'), 403);

        abort_unless(Client::query()->forAuthUser()->whereKey($client_id)->exists(), 422);

        $equipment_ids = $this->normalizeEquipmentIds($equipment_ids);
        abort_unless($equipment_ids !== [], 422, 'Selecciona al menos un equipo.');
        $this->assertEquipmentsBelongToClient($client_id, $equipment_ids);

        if (! empty($data['quotation_service_type_id'])) {
            abort_unless(
                QuotationServiceType::query()->visibleToUser()->whereKey($data['quotation_service_type_id'])->exists(),
                422
            );
        }

        if (! empty($data['business_payment_method_id'])) {
            abort_unless(
                BusinessPaymentMethod::query()->visibleToUser()->whereKey($data['business_payment_method_id'])->exists(),
                422
            );
        }

        if (! empty($data['business_bank_account_id'])) {
            abort_unless(
                BusinessBankAccount::query()->forAuthUser()
                    ->where('business_id', $business_id)
                    ->whereKey($data['business_bank_account_id'])
                    ->exists(),
                422
            );
        }

        return DB::transaction(function () use ($business_id, $quotation_id, $client_id, $equipment_ids, $data, $items) {
            $payload = [
                'client_id'                  => $client_id,
                'quotation_service_type_id'  => $data['quotation_service_type_id'] ?? null,
                'business_payment_method_id' => $data['business_payment_method_id'] ?? null,
                'business_bank_account_id'   => $data['business_bank_account_id'] ?? null,
                'diagnosis'                  => $data['diagnosis'] ?? null,
                'hours_entry'                => $data['hours_entry'] ?? null,
                'validity_days'              => (int) ($data['validity_days'] ?? 15),
                'execution_time'             => $data['execution_time'] ?? null,
                'tax_percentage'             => $data['tax_percentage'] ?? 19,
                'advance_percentage'         => $data['advance_percentage'] ?? 0,
                'notes'                      => $data['notes'] ?? null,
                'observations'               => $data['observations'] ?? null,
            ];

            if ($quotation_id) {
                $quotation = Quotation::query()->forAuthUser()->findOrFail($quotation_id);
                abort_unless((int) $quotation->business_id === $business_id, 403);

                if (! $quotation->isEditable()) {
                    throw ValidationException::withMessages([
                        'form.client_id' => $quotation->isAccepted()
                            ? 'No se puede editar: la cotización está aceptada.'
                            : 'No se puede editar: la cotización está rechazada.',
                    ]);
                }

                $quotation->update($payload);
            } else {
                $quotation = Quotation::create([
                    ...$payload,
                    'business_id' => $business_id,
                    'reference'   => Quotation::generateReference($business_id),
                    'status'      => QuotationStatus::Created,
                    'created_by'  => $data['created_by'] ?? auth()->id(),
                ]);
            }

            $quotation->equipments()->sync($equipment_ids);
            $quotation->syncValidUntil();
            $quotation->save();
            $this->syncItems($quotation, $items, $equipment_ids);
            $quotation->recalculateTotals();

            $advance_percentage = (float) ($data['advance_percentage'] ?? 0);
            $quotation->update([
                'advance_percentage' => $advance_percentage,
                'advance_amount'     => round((float) $quotation->subtotal * ($advance_percentage / 100), 2),
            ]);

            $quotation = $quotation->fresh([
                'items.productType',
                'items.productCategory',
                'items.equipment',
                'client:id,name',
                'equipments',
            ]);

            $action = $quotation_id ? 'updated' : 'created';
            $description = ($quotation_id ? 'Actualizó' : 'Creó') . " la cotización {$quotation->reference}";
            $properties = [
                'status'         => $quotation->status?->value ?? $quotation->status,
                'client_id'      => $quotation->client_id,
                'equipment_ids'  => $quotation->equipments->pluck('id')->all(),
                'total'          => $quotation->total,
                'items_count'    => $quotation->items->count(),
                'advance_percentage' => $quotation->advance_percentage,
                'advance_amount' => $quotation->advance_amount,
            ];

            LogUserHistoricalAction::run(
                action: $action,
                module: 'workshop.quotations',
                description: $description,
                subject: $quotation,
                subject_label: $quotation->reference,
                properties: $properties,
                business_id: $business_id,
            );

            foreach ($quotation->equipments as $equipment) {
                LogEquipmentHistoricalAction::run(
                    action: $action,
                    module: 'workshop.quotations',
                    description: $description,
                    equipment: $equipment,
                    subject: $quotation,
                    properties: $properties,
                    business_id: $business_id,
                );
            }

            return $quotation;
        });
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
        $count = Equipment::query()
            ->forAuthUser()
            ->where('client_id', $client_id)
            ->whereIn('id', $equipment_ids)
            ->count();

        abort_unless($count === count($equipment_ids), 422, 'Uno o más equipos no pertenecen al cliente.');
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  list<int>  $equipment_ids
     */
    private function syncItems(Quotation $quotation, array $items, array $equipment_ids): void
    {
        $kept_ids = [];
        $allowed = array_flip($equipment_ids);
        $default_equipment_id = $equipment_ids[0] ?? null;

        foreach ($items as $row) {
            $description = trim((string) ($row['description'] ?? ''));
            if ($description === '') {
                continue;
            }

            if (! empty($row['product_type_id'])) {
                abort_unless(ProductType::query()->visibleToUser()->whereKey($row['product_type_id'])->exists(), 422);
            }

            if (! empty($row['product_category_id'])) {
                abort_unless(ProductCategory::query()->visibleToUser()->whereKey($row['product_category_id'])->exists(), 422);
            }

            if (! empty($row['product_id'])) {
                abort_unless(
                    Product::query()->forAuthUser()
                        ->where('business_id', $quotation->business_id)
                        ->whereKey($row['product_id'])
                        ->exists(),
                    422
                );
            }

            $equipment_id = ! empty($row['equipment_id'])
                ? (int) $row['equipment_id']
                : $default_equipment_id;

            abort_unless(
                $equipment_id !== null && isset($allowed[$equipment_id]),
                422,
                'Cada ítem debe asociarse a un equipo de la cotización.'
            );

            $qty      = (float) ($row['quantity'] ?? 1);
            $price    = (float) ($row['unit_price'] ?? 0);
            $discount = (float) ($row['discount_percentage'] ?? 0);
            $subtotal = round($qty * $price * (1 - $discount / 100), 2);

            $payload = [
                'equipment_id'        => $equipment_id,
                'product_id'          => $row['product_id'] ?: null,
                'product_type_id'     => $row['product_type_id'] ?: null,
                'product_category_id' => $row['product_category_id'] ?: null,
                'description'         => $description,
                'quantity'            => $qty,
                'unit_price'          => $price,
                'discount_percentage' => $discount,
                'subtotal'            => $subtotal,
            ];

            if (! empty($row['id'])) {
                $item = QuotationItem::query()
                    ->where('quotation_id', $quotation->id)
                    ->whereKey($row['id'])
                    ->firstOrFail();
                $item->update($payload);
                $kept_ids[] = (int) $item->id;
            } else {
                $item = $quotation->items()->create($payload);
                $kept_ids[] = (int) $item->id;
            }
        }

        $quotation->items()->whereNotIn('id', $kept_ids)->delete();
    }
}
