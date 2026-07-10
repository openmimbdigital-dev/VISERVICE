<?php

namespace App\Actions\Workshop;

use App\Models\BusinessBankAccount;
use App\Models\BusinessPaymentMethod;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemType;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\QuotationServiceType;
use Illuminate\Support\Facades\DB;
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
                'km_entry'                   => $data['km_entry'] ?? 0,
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
                abort_unless($quotation->isEditable(), 422, 'La cotización no se puede editar en su estado actual.');

                $quotation->update($payload);
            } else {
                $quotation = Quotation::create([
                    ...$payload,
                    'business_id' => $business_id,
                    'reference'   => Quotation::generateReference($business_id),
                    'status'      => 'borrador',
                    'created_by'  => $data['created_by'] ?? auth()->id(),
                ]);
            }

            $quotation->syncValidUntil();
            $quotation->save();
            $this->syncItems($quotation, $items);
            $quotation->recalculateTotals();

            return $quotation->fresh(['items.itemType', 'items.itemCategory']);
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

            if (! empty($row['item_type_id'])) {
                abort_unless(ItemType::query()->visibleToUser()->whereKey($row['item_type_id'])->exists(), 422);
            }

            if (! empty($row['item_category_id'])) {
                abort_unless(ItemCategory::query()->visibleToUser()->whereKey($row['item_category_id'])->exists(), 422);
            }

            if (! empty($row['item_id'])) {
                abort_unless(
                    Item::query()->forAuthUser()
                        ->where('business_id', $quotation->business_id)
                        ->whereKey($row['item_id'])
                        ->exists(),
                    422
                );
            }

            $qty      = (float) ($row['quantity'] ?? 1);
            $price    = (float) ($row['unit_price'] ?? 0);
            $discount = (float) ($row['discount_percentage'] ?? 0);
            $subtotal = round($qty * $price * (1 - $discount / 100), 2);

            $payload = [
                'item_id'             => $row['item_id'] ?: null,
                'item_type_id'        => $row['item_type_id'] ?: null,
                'item_category_id'    => $row['item_category_id'] ?: null,
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
