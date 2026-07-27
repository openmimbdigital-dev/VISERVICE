<?php

namespace App\Livewire\Admin\Events\Manage;

use App\Actions\Events\CreateOrUpdateEventAction;
use App\Actions\Events\CreatePeriodicEventsAction;
use App\Enums\Weekday;
use App\Livewire\Forms\Admin\Events\EventForm;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventTeam;
use App\Support\EventsAccess;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Form extends Component
{
    public EventForm $form;

    public EventCategory $event_category;

    public bool $show_team_modal = false;

    public ?int $preview_team_id = null;

    public function mount(EventCategory $eventCategory, ?Event $event = null): void
    {
        $this->event_category = $eventCategory;

        if ($event?->exists) {
            $record = Event::query()
                ->forAuthUser()
                ->where('event_category_id', $eventCategory->id)
                ->with('teams:id')
                ->findOrFail($event->id);

            EventsAccess::authorizeEditEvent($record);

            $this->form->setEvent($record);

            return;
        }

        EventsAccess::authorizeCreateEvents();
        $this->form->reset();
        $this->form->setCategory($eventCategory);
        $this->form->year = (string) now()->year;

        if (! $this->form->isSuperAdmin()) {
            $this->form->business_id = auth()->user()?->business_id;
        }
    }

    public function updatedFormBusinessId(): void
    {
        $this->form->event_team_ids = [];
        $this->closeTeamDetail();
    }

    public function openTeamDetail(int $team_id): void
    {
        $business_id = $this->form->resolvedBusinessId();

        abort_unless(
            EventTeam::query()
                ->forAuthUser()
                ->when($business_id > 0, fn ($query) => $query->where('business_id', $business_id))
                ->whereKey($team_id)
                ->exists(),
            404
        );

        $this->preview_team_id = $team_id;
        $this->show_team_modal = true;
    }

    public function closeTeamDetail(): void
    {
        $this->show_team_modal = false;
        $this->preview_team_id = null;
    }

    private function previewTeam(): ?EventTeam
    {
        if (! $this->show_team_modal || ! $this->preview_team_id) {
            return null;
        }

        return EventTeam::query()
            ->forAuthUser()
            ->with([
                'roles' => fn ($query) => $query->orderBy('name')->select('event_team_roles.id', 'event_team_roles.name', 'event_team_roles.functions'),
                'members' => fn ($query) => $query->with([
                    'user:id,first_name,last_name',
                    'role:id,name',
                ]),
            ])
            ->find($this->preview_team_id);
    }

    private function authorizeSave(): void
    {
        if (! $this->form->isEditing()) {
            EventsAccess::authorizeCreateEvents();

            return;
        }

        $record = Event::query()
            ->forAuthUser()
            ->where('event_category_id', $this->event_category->id)
            ->findOrFail($this->form->event_id);

        EventsAccess::authorizeEditEvent($record);
    }

    public function save(): void
    {
        $this->authorizeSave();

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
            'teams' => $this->form->getTeams(),
            'preview_team' => $this->previewTeam(),
            'month_options' => $this->form->monthOptions(),
            'year_options' => $this->form->yearOptions(),
            'weekday_options' => Weekday::options(),
        ])->layoutData([
            'title' => $this->form->isEditing()
                ? 'Gestión de eventos — Editar evento'
                : 'Gestión de eventos — Nuevo evento',
        ]);
    }
}
