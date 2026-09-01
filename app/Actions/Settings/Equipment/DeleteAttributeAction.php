<?php

namespace App\Actions\Settings\Equipment;

use App\Models\Attribute;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteAttributeAction
{
    use AsAction;

    public function handle(int $attribute_id): void
    {
        abort_unless(auth()->user()->can('settings.attributes.delete'), 403);

        $attribute = Attribute::query()->findOrFail($attribute_id);

        abort_unless($attribute->isAccessibleBy(), 403);
        abort_unless($attribute->isEditableBy(), 403);

        $attribute->delete();
    }
}
