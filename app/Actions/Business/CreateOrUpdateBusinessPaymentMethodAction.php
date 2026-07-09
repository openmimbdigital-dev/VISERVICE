<?php

namespace App\Actions\Business;

use App\Models\BusinessPaymentMethod;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateBusinessPaymentMethodAction
{
    use AsAction;

    /**
     * @param  array{
     *   business_id: int|null,
     *   general: bool,
     *   name: string,
     *   active: bool,
     *   is_default: bool,
     *   sort_order: int
     * }  $data
     */
    public function handle(?int $payment_method_id, array $data): BusinessPaymentMethod
    {
        abort_unless(
            auth()->user()->can($payment_method_id ? 'business_payment_methods.edit' : 'business_payment_methods.create'),
            403
        );

        $user    = auth()->user();
        $general = (bool) $data['general'];

        if (! $user->hasRole('superAdmin')) {
            $general     = false;
            $business_id = $user->businessIds()[0] ?? null;
            abort_unless($business_id !== null && $user->belongsToBusiness($business_id), 403);
        } else {
            $business_id = $general ? null : (int) $data['business_id'];
        }

        return DB::transaction(function () use ($payment_method_id, $data, $business_id, $general) {
            $attributes = [
                'business_id' => $business_id,
                'general'     => $general,
                'name'        => $data['name'],
                'label'       => BusinessPaymentMethod::normalizeLabel($data['name']),
                'active'      => $data['active'],
                'is_default'  => $data['is_default'],
                'sort_order'  => $data['sort_order'],
            ];

            if ($payment_method_id) {
                $method = BusinessPaymentMethod::query()->visibleToUser()->findOrFail($payment_method_id);
                abort_unless($method->isEditableBy(auth()->user(), 'business_payment_methods.edit'), 403);

                if (! auth()->user()->hasRole('superAdmin')) {
                    abort_unless((int) $method->business_id === $business_id, 403);
                }

                $method->update($attributes);
            } else {
                $method = BusinessPaymentMethod::create($attributes);
            }

            if ($data['is_default']) {
                $default_query = BusinessPaymentMethod::query()->whereKeyNot($method->id);

                if ($general) {
                    $default_query->where('general', true)->whereNull('business_id');
                } else {
                    $default_query->where('business_id', $business_id)->where('general', false);
                }

                $default_query->update(['is_default' => false]);
            }

            return $method->fresh(['business']);
        });
    }
}
