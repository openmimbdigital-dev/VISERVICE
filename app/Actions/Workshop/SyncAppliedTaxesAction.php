<?php

namespace App\Actions\Workshop;

use App\Models\CustomTax;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncAppliedTaxesAction
{
    use AsAction;

    /**
     * @param  list<int|string>  $custom_tax_ids
     */
    public function handle(Model $taxable, array $custom_tax_ids, int $business_id, float $taxable_base): float
    {
        $ids = array_values(array_unique(array_filter(
            array_map(fn ($id) => (int) $id, $custom_tax_ids),
            fn (int $id) => $id > 0
        )));

        if ($ids === []) {
            $taxable->appliedTaxes()->delete();

            return 0.0;
        }

        $query = CustomTax::query()
            ->where('business_id', $business_id)
            ->whereIn('id', $ids);

        if (auth()->user()) {
            $query->forAuthUser();
        }

        $taxes = $query->get()->keyBy('id');

        abort_unless($taxes->count() === count($ids), 422, 'Uno o más impuestos no son válidos.');

        $kept_ids = [];
        $total = 0.0;

        foreach ($ids as $id) {
            $tax = $taxes->get($id);
            $percentage = (float) $tax->percentage;
            $amount = round($taxable_base * ($percentage / 100), 2);

            $row = $taxable->appliedTaxes()->updateOrCreate(
                ['custom_tax_id' => $tax->id],
                [
                    'custom_tax_name' => $tax->name,
                    'tax_percentage'  => $percentage,
                    'tax_amount'      => $amount,
                ]
            );

            $kept_ids[] = (int) $row->id;
            $total += $amount;
        }

        $obsolete = $taxable->appliedTaxes();

        if ($kept_ids !== []) {
            $obsolete->whereNotIn('id', $kept_ids);
        }

        $obsolete->delete();

        return round($total, 2);
    }
}
