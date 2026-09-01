<?php

namespace App\Livewire\Admin\Participants;

use App\Models\Participant;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Participantes — Negocios')]
class Index extends Component
{
    #[On('participant-deleted')]
    public function onRecordDeleted(): void {}

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('participants.view'), 403);
    }

    public function render()
    {
        $stats_query = Participant::query()->forAuthUser();

        return view('livewire.admin.participants.index', [
            'stats' => [
                'total' => (clone $stats_query)->count(),
                'active' => (clone $stats_query)->where('status', true)->count(),
            ],
        ]);
    }
}
