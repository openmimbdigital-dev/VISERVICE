<?php

namespace App\Livewire\Admin\Events\Manage;

use App\Actions\Events\CreateOrUpdateEventAction;
use App\Actions\Events\CreatePeriodicEventsAction;
use App\Enums\Weekday;
use App\Livewire\Forms\Admin\Events\EventForm;
use App\Models\Event;
use App\Models\EventCategory;
use App\Support\ChurchEventsAccess;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Form extends Component
{
    public EventForm $form;

    public EventCategory $event_category;

    public function mount(EventCategory $eventCategory, ?Event $event = null): void
    {
        ChurchEventsAccess::authorize();

        $this->event_category = $eventCategory;

        if ($event?->exists) {
            abort_unless(auth()->user()?->can('events.events.edit'), 403);

            $record = Event::query()
                ->forAuthUser()
                ->where('event_category_id', $eventCategory->id)
                ->findOrFail($event->id);

            $this->form->setEvent($record);

            return;
        }

        abort_unless(auth()->user()?->can('events.events.create'), 403);
        $this->form->reset();
        $this->form->setCategory($eventCategory);
        $this->form->year = (string) now()->year;

        if (! $this->form->isSuperAdmin()) {
            $this->form->business_id = auth()->user()?->business_id;
        }
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()?->can(
                $this->form->isEditing()
                    ? 'events.events.edit'
                    : 'events.events.create'
            ),
            403
        );

        $this->form->event_category_id = $this->event_category->id;

        $business_id = $this->form->resolvedBusinessId();
        $data = $this->form->validated();

        if (! $this->form->isEditing() && $this->form->isPeriodicCategory()) {
            $events = CreatePeriodicEventsAction::run($business_id, $data);

            $this->dispatch('swal', [
                'title' => 'Se crearon '.$events->count().' eventos correctamente.',
                'icon' => 'success',
            ]);

            $this->redirectRoute('admin.events.manage.category.index', $this->event_category, navigate: true);

            return;
        }

        CreateOrUpdateEventAction::run(
            $business_id,
            $this->form->event_id,
            $data
        );

        $this->dispatch('swal', [
            'title' => $this->form->isEditing()
                ? 'Evento actualizado correctamente.'
                : 'Evento creado correctamente.',
            'icon' => 'success',
        ]);

        $this->redirectRoute('admin.events.manage.category.index', $this->event_category, navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.events.manage.form', [
            'is_super_admin' => $this->form->isSuperAdmin(),
            'is_periodic' => $this->form->isPeriodicCategory(),
            'businesses' => $this->form->isSuperAdmin() ? $this->form->getBusinesses() : collect(),
            'month_options' => $this->form->monthOptions(),
            'weekday_options' => Weekday::options(),
        ])->layoutData([
            'title' => $this->form->isEditing()
                ? 'Gestión de eventos — Editar evento'
                : 'Gestión de eventos — Nuevo evento',
        ]);
    }
}
