<?php

namespace App\Livewire\Admin\OrganizationTypes;

use App\Actions\Business\CreateOrUpdateOrganizationTypeAction;
use App\Actions\Business\DeleteOrganizationTypeAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Livewire\Forms\Admin\OrganizationTypeForm;
use App\Models\OrganizationType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Tipos de organización')]
class Index extends Component
{
    use ConfirmsDeletionWithLivewireAlert;
    use WithPagination;

    public OrganizationTypeForm $form;

    public bool $showModal = false;

    public string $search = '';

    public string $filter_status = '';

    public function mount(): void
    {
        abort_unless(
            auth()->user()?->hasRole('superAdmin') && auth()->user()?->can('organization_types.view'),
            403
        );
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()?->can('organization_types.create'), 403);

        $this->form->reset();
        $this->form->status = true;
        $this->showModal    = true;
        $this->resetValidation();
    }

    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()?->can('organization_types.edit'), 403);
        $organization_type = OrganizationType::query()->findOrFail($id);
        $this->form->setOrganizationType($organization_type);
        $this->showModal = true;
        $this->resetValidation();
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()?->can($this->form->isEditing() ? 'organization_types.edit' : 'organization_types.create'),
            403
        );

        $was_editing = $this->form->isEditing();

        CreateOrUpdateOrganizationTypeAction::run(
            $this->form->organization_type_id,
            $this->form->validated()
        );

        $this->closeModal();

        $this->dispatch('swal', [
            'title' => $was_editing ? 'Tipo de organización actualizado.' : 'Tipo de organización creado.',
            'icon'  => 'success',
        ]);
    }

    public function toggleStatus(int $id): void
    {
        abort_unless(auth()->user()?->can('organization_types.edit'), 403);

        $organization_type = OrganizationType::query()->findOrFail($id);
        $organization_type->update(['status' => ! $organization_type->status]);

        $label = $organization_type->fresh()->status ? 'activado' : 'desactivado';
        $this->dispatch('swal', ['title' => "Tipo de organización {$label}.", 'icon' => 'success']);
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()?->can('organization_types.delete'), 403);

        $this->askDeleteConfirmation($id, '¿Eliminar este tipo de organización?');
    }

    protected function onDeleteConfirmed(): void
    {
        abort_unless(auth()->user()?->can('organization_types.delete'), 403);

        try {
            DeleteOrganizationTypeAction::run($this->delete_id);
            $this->alertDeleteSuccess('Tipo de organización eliminado.');
        } catch (\Throwable $e) {
            $this->alertDeleteError($e->getMessage() ?: 'No se pudo eliminar el tipo de organización.');
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->form->reset();
        $this->form->status = true;
        $this->resetValidation();
    }

    public function render()
    {
        $query = OrganizationType::query()
            ->withCount('businesses')
            ->when($this->search, fn ($q) => $q->where(function ($s) {
                $s->where('name', 'like', "%{$this->search}%")
                    ->orWhere('label', 'like', '%' . OrganizationType::normalizeLabel($this->search) . '%');
            }))
            ->when($this->filter_status !== '', fn ($q) => $q->where('status', (bool) $this->filter_status))
            ->orderByDesc('created_at');

        $stats = [
            'total'      => OrganizationType::count(),
            'active'     => OrganizationType::where('status', true)->count(),
            'with_items' => OrganizationType::has('businesses')->count(),
        ];

        return view('livewire.admin.organization-types.index', [
            'organization_types' => $query->paginate(15),
            'stats'              => $stats,
        ]);
    }
}
