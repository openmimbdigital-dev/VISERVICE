<?php

namespace App\Livewire\Admin\Settings\Events\EventCategories;

use App\Models\EventCategory;
use App\Support\ChurchEventsAccess;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuración — Categoría de evento')]
class Show extends Component
{
    public EventCategory $event_category;

    public function mount(EventCategory $eventCategory): void
    {
        ChurchEventsAccess::authorize();
        abort_unless(auth()->user()?->can('settings.event_categories.view'), 403);

        $this->event_category = EventCategory::query()
            ->visibleToUser()
            ->with('business:id,name')
            ->findOrFail($eventCategory->id);
    }

    public function render()
    {
        return view('livewire.admin.settings.events.event-categories.show', [
            'can_edit' => auth()->user()->can('settings.event_categories.edit')
                && $this->event_category->isEditableBy(),
        ]);
    }
}
