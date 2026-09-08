<?php

namespace App\Actions;

use App\Models\Guide;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateGuideAction
{
    use AsAction;

    /** @param  array<string, mixed>  $data */
    public function handle(?int $guide_id, array $data): Guide
    {
        abort_unless(auth()->user()?->can('guides.manage'), 403);

        if ($guide_id) {
            $guide = Guide::query()->findOrFail($guide_id);

            // El slug solo se recalcula si cambió el título: los enlaces ya
            // compartidos a la guía deben seguir funcionando.
            if ($guide->title !== $data['title']) {
                $data['slug'] = Guide::generateSlug($data['title'], $guide->id);
            }

            $guide->update($data);

            return $guide->fresh();
        }

        return Guide::create([
            ...$data,
            'slug'       => Guide::generateSlug($data['title']),
            'created_by' => auth()->id(),
        ]);
    }
}
