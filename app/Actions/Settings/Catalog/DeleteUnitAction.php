<?php

namespace App\Actions\Settings\Catalog;

use App\Models\Unit;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteUnitAction
{
    use AsAction;

    public function handle(int $unit_id): void
    {
        abort_unless(auth()->user()?->can('settings.units.delete'), 403);

        $unit = Unit::query()->visibleToUser()->findOrFail($unit_id);

        abort_unless($unit->canDelete(), 403);

        $unit->delete();
    }
}
