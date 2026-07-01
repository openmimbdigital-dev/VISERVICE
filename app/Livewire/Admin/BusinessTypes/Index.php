<?php

namespace App\Livewire\Admin\BusinessTypes;

use App\Actions\Business\CreateOrUpdateBusinessTypeAction;
use App\Actions\Business\DeleteBusinessTypeAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Livewire\Forms\Admin\BusinessTypeForm;
use App\Models\BusinessType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Tipos de negocio')]
class Index extends Component
{
    use ConfirmsDeletionWithLivewireAlert;
    use WithPagination;

    public BusinessTypeForm $form;

    public bool $showModal = false;

    public string $search = '';

    public string $filter_status = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('business_types.view'), 403);
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
        abort_unless(auth()->user()?->can('business_types.create'), 403);

        $this->form->reset();
        $this->form->status = true;
        $this->showModal    = true;
        $this->resetValidation();
    }

    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()?->can('business_types.edit'), 403);
        $business_type = BusinessType::query()->findOrFail($id);
        $this->form->setBusinessType($business_type);
        $this->showModal = true;
        $this->resetValidation();
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()?->can($this->form->isEditing() ? 'business_types.edit' : 'business_types.create'),
            403
        );

        $was_editing = $this->form->isEditing();

        CreateOrUpdateBusinessTypeAction::run(
            $this->form->business_type_id,
            $this->form->validated()
        );

        $this->closeModal();

        $this->dispatch('swal', [
            'title' => $was_editing ? 'Tipo de negocio actualizado.' : 'Tipo de negocio creado.',
            'icon'  => 'success',
        ]);
    }

    public function toggleStatus(int $id): void
    {
        abort_unless(auth()->user()?->can('business_types.edit'), 403);

        $business_type = BusinessType::query()->findOrFail($id);
        $business_type->update(['status' => ! $business_type->status]);

        $label = $business_type->fresh()->status ? 'activado' : 'desactivado';
        $this->dispatch('swal', ['title' => "Tipo de negocio {$label}.", 'icon' => 'success']);
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()?->can('business_types.delete'), 403);

        $this->askDeleteConfirmation($id, '¿Eliminar este tipo de negocio?');
    }

    protected function onDeleteConfirmed(): void
    {
        abort_unless(auth()->user()?->can('business_types.delete'), 403);

        try {
            DeleteBusinessTypeAction::run($this->delete_id);
            $this->alertDeleteSuccess('Tipo de negocio eliminado.');
        } catch (\Throwable $e) {
            $this->alertDeleteError($e->getMessage() ?: 'No se pudo eliminar el tipo de negocio.');
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
        $query = BusinessType::query()
            ->withCount('businesses')
            ->when($this->search, fn ($q) => $q->where(function ($s) {
                $s->where('name', 'like', "%{$this->search}%")
                    ->orWhere('label', 'like', '%' . BusinessType::normalizeLabel($this->search) . '%');
            }))
            ->when($this->filter_status !== '', fn ($q) => $q->where('status', (bool) $this->filter_status))
            ->orderByDesc('created_at');

        $stats = [
            'total'      => BusinessType::count(),
            'active'     => BusinessType::where('status', true)->count(),
            'with_items' => BusinessType::has('businesses')->count(),
        ];

        return view('livewire.admin.business-types.index', [
            'business_types' => $query->paginate(15),
            'stats'          => $stats,
            'label_preview'  => $this->form->name !== ''
                ? BusinessType::normalizeLabel($this->form->name)
                : '',
        ]);
    }
}
