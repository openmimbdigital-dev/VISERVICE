<?php

namespace App\Actions\Settings\General;

use App\Models\Status;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateStatusAction
{
    use AsAction;

    /**
     * @param  array{name: string, label: string, active: bool, type: list<string>}  $data
     */
    public function handle(?int $status_id, array $data): Status
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
        abort_unless(
            auth()->user()->can($status_id ? 'settings.statuses.edit' : 'settings.statuses.create'),
            403
        );

        $attributes = [
            'name' => $data['name'],
            'label' => $data['label'],
            'active' => $data['active'],
            'type' => $data['type'],
        ];

        if ($status_id) {
            $status = Status::query()->findOrFail($status_id);

            if ($status->isInUse()) {
                throw ValidationException::withMessages([
                    'status' => 'No se puede editar: el estado está en uso en registros del sistema.',
                ]);
            }

            // name es FK en varias tablas: no se permite cambiarlo al editar.
            $status->update([
                'label' => $attributes['label'],
                'active' => $attributes['active'],
                'type' => $attributes['type'],
            ]);

            return $status->fresh();
        }

        return Status::query()->create($attributes);
    }
}
