<?php

namespace App\Livewire\Admin\OrganizationTypes;

use App\Actions\Business\CreateOrUpdateOrganizationTypeAction;
use App\Actions\Business\DeleteOrganizationTypeAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Livewire\Forms\Admin\OrganizationTypeForm;
use App\Models\BusinessType;
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

    public string $filter_business_type = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterBusinessType(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->form->reset();
        $this->form->active = true;
        $this->showModal    = true;
        $this->resetValidation();
    }

    public function openEdit(int $id): void
    {
        $organization_type = OrganizationType::query()->with('business_type')->findOrFail($id);
        $this->form->setOrganizationType($organization_type);
        $this->showModal = true;
        $this->resetValidation();
    }

    public function save(): void
    {
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

    public function toggleActive(int $id): void
    {
        $organization_type = OrganizationType::query()->findOrFail($id);
        $organization_type->update(['active' => ! $organization_type->active]);

        $label = $organization_type->fresh()->active ? 'activado' : 'desactivado';
        $this->dispatch('swal', ['title' => "Tipo de organización {$label}.", 'icon' => 'success']);
    }

    public function delete(int $id): void
    {
        $this->askDeleteConfirmation($id, '¿Eliminar este tipo de organización?');
    }

    protected function onDeleteConfirmed(): void
    {
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
        $this->form->active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $query = OrganizationType::query()
            ->with('business_type')
            ->withCount('businesses')
            ->when($this->search, fn ($q) => $q->where(function ($s) {
                $s->where('name', 'like', "%{$this->search}%")
                    ->orWhere('label', 'like', '%' . OrganizationType::normalizeLabel($this->search) . '%')
                    ->orWhereHas('business_type', fn ($bt) => $bt->where('name', 'like', "%{$this->search}%"));
            }))
            ->when($this->filter_status !== '', fn ($q) => $q->where('active', (bool) $this->filter_status))
            ->when($this->filter_business_type !== '', fn ($q) => $q->where('business_type_id', $this->filter_business_type))
            ->orderByDesc('created_at');

        $stats = [
            'total'      => OrganizationType::count(),
            'active'     => OrganizationType::where('active', true)->count(),
            'with_items' => OrganizationType::has('businesses')->count(),
        ];

        return view('livewire.admin.organization-types.index', [
            'organization_types' => $query->paginate(15),
            'business_types'     => BusinessType::query()->where('status', true)->orderBy('name')->get(),
            'stats'              => $stats,
            'label_preview'      => $this->form->name !== ''
                ? OrganizationType::normalizeLabel($this->form->name)
                : '',
        ]);
    }
}
