<?php

namespace App\Actions\Business;

use App\Actions\LogUserHistoricalAction;
use App\Models\CustomTax;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateCustomTaxAction
{
    use AsAction;

    /**
     * @param  array{
     *   business_id: int,
     *   name: string,
     *   description: string|null,
     *   percentage: float,
     *   active: bool
     * }  $data
     */
    public function handle(?int $custom_tax_id, array $data): CustomTax
    {
        abort_unless(
            auth()->user()->can($custom_tax_id ? 'custom_taxes.edit' : 'custom_taxes.create'),
            403
        );

        $user        = auth()->user();
        $business_id = (int) $data['business_id'];

        if (! $user->hasRole('superAdmin')) {
            abort_unless($user->belongsToBusiness($business_id), 403);
        }

        return DB::transaction(function () use ($custom_tax_id, $data, $business_id) {
            $attributes = [
                'business_id' => $business_id,
                'name'        => $data['name'],
                'description' => $data['description'],
                'percentage'  => $data['percentage'],
                'active'      => $data['active'],
            ];

            if ($custom_tax_id) {
                $tax = CustomTax::query()->forAuthUser()->findOrFail($custom_tax_id);
                abort_unless($tax->isEditableBy(auth()->user(), 'custom_taxes.edit'), 403);
                abort_unless((int) $tax->business_id === $business_id, 403);

                $tax->update($attributes);
            } else {
                $tax = CustomTax::query()->create($attributes);
            }

            $tax = $tax->fresh(['business']);

            LogUserHistoricalAction::run(
                action: $custom_tax_id ? 'updated' : 'created',
                module: 'business.custom_taxes',
                description: ($custom_tax_id ? 'Actualizó' : 'Creó') . " el impuesto {$tax->name}",
                subject: $tax,
                subject_label: $tax->name,
                properties: [
                    'percentage' => $tax->percentage,
                    'active'     => $tax->active,
                ],
                business_id: $business_id,
            );

            return $tax;
        });
    }
}
