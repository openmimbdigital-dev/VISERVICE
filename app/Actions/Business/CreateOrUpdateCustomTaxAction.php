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
     *   business_id: int|null,
     *   general: bool,
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

        $user    = auth()->user();
        $general = (bool) ($data['general'] ?? false);

        if (! $user->hasRole('superAdmin')) {
            $general     = false;
            $business_id = $user->businessIds()[0] ?? null;
            abort_unless($business_id !== null && $user->belongsToBusiness($business_id), 403);
        } else {
            $business_id = $general ? null : (int) $data['business_id'];
        }

        return DB::transaction(function () use ($custom_tax_id, $data, $business_id, $general) {
            $attributes = [
                'business_id' => $business_id,
                'general'     => $general,
                'name'        => $data['name'],
                'description' => $data['description'],
                'percentage'  => $data['percentage'],
                'active'      => $data['active'],
            ];

            if ($custom_tax_id) {
                $tax = CustomTax::query()->forAuthUser()->findOrFail($custom_tax_id);
                abort_unless($tax->isEditableBy(auth()->user(), 'custom_taxes.edit'), 403);

                if (! auth()->user()->hasRole('superAdmin')) {
                    abort_unless((int) $tax->business_id === (int) $business_id, 403);
                    abort_unless(! $tax->general, 403);
                }

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
                    'general'    => $tax->general,
                ],
                business_id: $tax->business_id ? (int) $tax->business_id : null,
            );

            return $tax;
        });
    }
}
