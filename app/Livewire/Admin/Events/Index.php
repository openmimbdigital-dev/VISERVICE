<?php

namespace App\Livewire\Admin\Events;

use App\Support\ChurchEventsAccess;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Gestión de eventos — Eventos')]
class Index extends Component
{
    public function mount(): void
    {
        ChurchEventsAccess::authorize();

        abort_unless(
            collect(static::cards())->contains(
                fn (array $card) => auth()->user()?->can($card['permission'])
            ),
            403
        );
    }

    /** @return array<string, array<string, string|null>> */
    public static function cards(): array
    {
        return [
            'manage' => [
                'title'       => 'Administrar eventos',
                'description' => 'Elige una categoría y gestiona sus eventos periódicos o eventuales.',
                'button_text' => 'Administrar eventos',
                'route'       => 'admin.events.manage.index',
                'permission'  => 'events.events.view',
                'card_bg'     => 'bg-violet-50/60 border-violet-100/80',
                'icon_bg'     => 'bg-violet-100',
                'icon_c'      => 'text-violet-600',
                'btn_class'   => 'btn-primary',
                'icon'        => 'M8 7V3m8 4V3M5 11h14M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z',
            ],
            'agenda' => [
                'title'       => 'Agenda de eventos',
                'description' => 'Visualiza los eventos programados en formato de agenda.',
                'button_text' => 'Ver agenda',
                'route'       => null,
                'permission'  => 'events.schedule.view',
                'card_bg'     => 'bg-sky-50/60 border-sky-100/80',
                'icon_bg'     => 'bg-sky-100',
                'icon_c'      => 'text-sky-600',
                'btn_class'   => 'btn-primary',
                'icon'        => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.admin.events.index', [
            'cards' => collect(static::cards())
                ->filter(fn (array $card) => auth()->user()->can($card['permission']))
                ->all(),
        ]);
    }
}
