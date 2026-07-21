<?php

namespace App\Livewire\Admin\Settings\Events\EventCategories;

use App\Actions\Settings\Events\CreateOrUpdateEventCategoryAction;
use App\Enums\EventCategoryType;
use App\Livewire\Forms\Admin\Settings\Events\EventCategoryForm;
use App\Models\EventCategory;
use App\Support\ChurchEventsAccess;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Form extends Component
{
    public EventCategoryForm $form;

    public function mount(?EventCategory $eventCategory = null): void
    {
        ChurchEventsAccess::authorize();

        if ($eventCategory?->exists) {
            abort_unless(auth()->user()?->can('settings.event_categories.edit'), 403);

            $event_category = EventCategory::query()
                ->visibleToUser()
                ->findOrFail($eventCategory->id);

            abort_unless($event_category->isEditableBy(), 403);
            $this->form->setEventCategory($event_category);

            return;
        }

        abort_unless(auth()->user()?->can('settings.event_categories.create'), 403);
        $this->form->reset();
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()?->can(
                $this->form->isEditing()
                    ? 'settings.event_categories.edit'
                    : 'settings.event_categories.create'
            ),
            403
        );

        CreateOrUpdateEventCategoryAction::run(
            $this->form->event_category_id,
            $this->form->validated()
        );

        $this->dispatch('swal', [
            'title' => $this->form->isEditing()
                ? 'Categoría actualizada correctamente.'
                : 'Categoría creada correctamente.',
            'icon' => 'success',
        ]);

        $this->redirectRoute('admin.settings.events.event-categories.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.settings.events.event-categories.form', [
            'is_super_admin' => $this->form->isSuperAdmin(),
            'type_options'   => EventCategoryType::options(),
        ])->layoutData([
            'title' => $this->form->isEditing()
                ? 'Configuración — Editar categoría de evento'
                : 'Configuración — Nueva categoría de evento',
        ]);
    }
}
