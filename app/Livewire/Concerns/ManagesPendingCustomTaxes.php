<?php

namespace App\Livewire\Concerns;

trait ManagesPendingCustomTaxes
{
    public mixed $pending_custom_tax_id = null;

    public function updatedPendingCustomTaxId(mixed $value): void
    {
        $id = (int) $value;

        if ($id <= 0) {
            $this->pending_custom_tax_id = null;

            return;
        }

        $ids = array_map('intval', $this->form->custom_tax_ids);

        if (! in_array($id, $ids, true)) {
            $this->form->custom_tax_ids[] = $id;
        }

        $this->pending_custom_tax_id = null;
    }

    public function removeCustomTax(int $custom_tax_id): void
    {
        $this->form->custom_tax_ids = array_values(array_filter(
            array_map('intval', $this->form->custom_tax_ids),
            fn (int $id) => $id !== $custom_tax_id
        ));
    }
}
