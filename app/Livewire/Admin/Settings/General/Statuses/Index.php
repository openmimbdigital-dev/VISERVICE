<?php

namespace App\Livewire\Admin\Settings\General\Statuses;

use App\Actions\Settings\General\CreateOrUpdateStatusAction;
use App\Actions\Settings\General\DeleteStatusAction;
use App\Livewire\Admin\Settings\General\GeneralSettingsConfig;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Livewire\Forms\Admin\Settings\General\StatusForm;
use App\Models\Status;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Configuración — Estados')]
class Index extends Component
{
    use ConfirmsDeletionWithLivewireAlert;
    use WithPagination;

    public StatusForm $form;

    public bool $showModal = false;

    public string $search = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
        abort_unless(auth()->user()->can('settings.statuses.view'), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()->can('settings.statuses.create'), 403);

        $this->form->reset();
        $this->form->active = true;
        $this->showModal = true;
        $this->resetValidation();
    }

    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()->can('settings.statuses.edit'), 403);

        $status = Status::query()->findOrFail($id);

        if ($status->isInUse()) {
            $this->dispatch('swal', [
                'title' => 'Estado en uso',
                'text' => 'No se puede editar un estado que está siendo utilizado.',
                'icon' => 'warning',
            ]);

            return;
        }

        $this->form->setStatus($status);
        $this->showModal = true;
        $this->resetValidation();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->form->reset();
        $this->form->active = true;
        $this->resetValidation();
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()->can($this->form->isEditing() ? 'settings.statuses.edit' : 'settings.statuses.create'),
            403
        );

        $was_editing = $this->form->isEditing();

        try {
            CreateOrUpdateStatusAction::run(
                $this->form->status_id,
                $this->form->validated()
            );
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first()
                ?? 'No se pudo guardar el estado.';

            $this->dispatch('swal', [
                'title' => $message,
                'icon' => 'error',
            ]);

            return;
        }

        $this->closeModal();

        $this->dispatch('swal', [
            'title' => $was_editing ? 'Estado actualizado' : 'Estado creado',
            'icon' => 'success',
        ]);
    }

    public function confirmDelete(int $id): void
    {
        abort_unless(auth()->user()->can('settings.statuses.delete'), 403);

        $status = Status::query()->findOrFail($id);

        if ($status->isInUse()) {
            $this->dispatch('swal', [
                'title' => 'Estado en uso',
                'text' => 'No se puede eliminar un estado que está siendo utilizado.',
                'icon' => 'warning',
            ]);

            return;
        }

        $this->askDeleteConfirmation($id, '¿Eliminar este estado? Esta acción no se puede deshacer.');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteStatusAction::run($this->delete_id);
            $this->alertDeleteSuccess('Estado eliminado correctamente.');
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first()
                ?? 'No se pudo eliminar el estado.';
            $this->alertDeleteError($message);
        } catch (\Throwable $e) {
            $this->alertDeleteError($e->getMessage() ?: 'No se pudo eliminar el estado.');
        }
    }

    public function render()
    {
        $statuses = Status::query()
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                        ->orWhere('label', 'like', $term);
                });
            })
            ->orderBy('id')
            ->paginate(25);

        $statuses_in_use = array_fill_keys(Status::namesInUse(), true);

        return view('livewire.admin.settings.general.statuses.index', [
            'config' => GeneralSettingsConfig::sectionOrFail('statuses'),
            'statuses' => $statuses,
            'statuses_in_use' => $statuses_in_use,
            'module_options' => StatusForm::moduleOptions(),
        ]);
    }
}
