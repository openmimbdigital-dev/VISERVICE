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
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $items
     */
    public function handle(
        int $business_id,
        ?int $quotation_id,
        int $client_id,
        int $equipment_id,
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
        abort_unless(Equipment::query()->forAuthUser()->whereKey($equipment_id)->exists(), 422);

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

        return DB::transaction(function () use ($business_id, $quotation_id, $client_id, $equipment_id, $data, $items) {
            $payload = [
                'client_id'                  => $client_id,
                'equipment_id'               => $equipment_id,
                'quotation_service_type_id'  => $data['quotation_service_type_id'] ?? null,
                'business_payment_method_id' => $data['business_payment_method_id'] ?? null,
                'business_bank_account_id'   => $data['business_bank_account_id'] ?? null,
                'diagnosis'                  => $data['diagnosis'] ?? null,
                'hours_entry'                => $data['hours_entry'] ?? null,
                'validity_days'              => (int) ($data['validity_days'] ?? 15),
                'execution_time'             => $data['execution_time'] ?? null,
                'tax_percentage'             => $data['tax_percentage'] ?? 19,
                'notes'                      => $data['notes'] ?? null,
                'observations'               => $data['observations'] ?? null,
            ];

            if ($quotation_id) {
                $quotation = Quotation::query()->forAuthUser()->findOrFail($quotation_id);
                abort_unless((int) $quotation->business_id === $business_id, 403);

                if ($quotation->isRejected()) {
                    throw ValidationException::withMessages([
                        'form.client_id' => 'No se puede editar: la cotización está rechazada.',
                    ]);
                }

                $quotation->update($payload);
            } else {
                $quotation = Quotation::create([
                    ...$payload,
                    'business_id' => $business_id,
                    'reference'   => Quotation::generateReference($business_id),
                    'status'      => QuotationStatus::Creada,
                    'created_by'  => $data['created_by'] ?? auth()->id(),
                ]);
            }

            $quotation->syncValidUntil();
            $quotation->save();
            $this->syncItems($quotation, $items);
            $quotation->recalculateTotals();

            $quotation = $quotation->fresh([
                'items.productType',
                'items.productCategory',
                'client:id,name',
                'equipment',
            ]);

            $action = $quotation_id ? 'updated' : 'created';
            $description = ($quotation_id ? 'Actualizó' : 'Creó') . " la cotización {$quotation->reference}";
            $properties = [
                'status'       => $quotation->status?->value ?? $quotation->status,
                'client_id'    => $quotation->client_id,
                'equipment_id' => $quotation->equipment_id,
                'total'        => $quotation->total,
                'items_count'  => $quotation->items->count(),
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

            LogEquipmentHistoricalAction::run(
                action: $action,
                module: 'workshop.quotations',
                description: $description,
                subject: $quotation,
                properties: $properties,
                business_id: $business_id,
            );

            return $quotation;
        });
    }

    /** @param  array<int, array<string, mixed>>  $items */
    private function syncItems(Quotation $quotation, array $items): void
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

            $qty      = (float) ($row['quantity'] ?? 1);
            $price    = (float) ($row['unit_price'] ?? 0);
            $discount = (float) ($row['discount_percentage'] ?? 0);
            $subtotal = round($qty * $price * (1 - $discount / 100), 2);

            $payload = [
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
