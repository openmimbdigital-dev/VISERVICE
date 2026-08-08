<?php

namespace App\Actions\Business;

use App\Models\Participant;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteParticipantAction
{
    use AsAction;

    public function handle(int $participant_id): void
    {
        abort_unless(auth()->user()?->can('participants.delete'), 403);

        $participant = Participant::query()->forAuthUser()->findOrFail($participant_id);

        if ($participant->hasDependencies()) {
            abort(422, 'No se puede eliminar: está siendo utilizado en otras referencias.');
        }

        $participant->delete();
    }
}
