<?php

namespace App\Actions\Settings\General;

use App\Models\Status;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteStatusAction
{
    use AsAction;

    public function handle(int $status_id): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
        abort_unless(auth()->user()->can('settings.statuses.delete'), 403);

        $status = Status::query()->findOrFail($status_id);

        if ($status->isInUse()) {
            throw ValidationException::withMessages([
                'status' => 'No se puede eliminar: el estado está en uso en registros del sistema.',
            ]);
        }

        $status->delete();
    }
}
