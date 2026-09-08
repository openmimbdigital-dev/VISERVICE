<?php

namespace App\Livewire\Admin\Guides;

use App\Actions\CreateOrUpdateGuideAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Livewire\Forms\Admin\GuideForm;
use App\Models\Guide;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Guías y documentación')]
class Index extends Component
{
    use ConfirmsDeletionWithLivewireAlert;

    public GuideForm $form;

    public bool $showModal = false;

    public string $search = '';

    public string $module_filter = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('guides.view'), 403);
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()?->can('guides.manage'), 403);

        $this->form->reset();

        // Si está filtrando por un módulo, la guía nueva nace en ese módulo.
        $this->form->module = $this->module_filter;

        $this->showModal = true;
        $this->resetValidation();
    }

    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()?->can('guides.manage'), 403);

        $this->form->setGuide(Guide::query()->findOrFail($id));
        $this->showModal = true;
        $this->resetValidation();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->form->reset();
        $this->resetValidation();
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can('guides.manage'), 403);

        $was_editing = $this->form->isEditing();

        CreateOrUpdateGuideAction::run($this->form->guide_id, $this->form->validated());

        $this->closeModal();

        $this->dispatch('swal', [
            'title' => $was_editing ? 'Guía actualizada' : 'Guía creada',
            'icon'  => 'success',
        ]);
    }

    public function deleteGuide(int $id): void
    {
        abort_unless(auth()->user()?->can('guides.manage'), 403);
        abort_unless(Guide::query()->whereKey($id)->exists(), 404);

        $this->askDeleteConfirmation($id, '¿Eliminar esta guía?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            Guide::query()->whereKey($this->delete_id)->delete();
            $this->alertDeleteSuccess('Guía eliminada correctamente.');
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar la guía.');
        }
    }

    public function render()
    {
        $term = trim($this->search);

        $guides = Guide::query()
            ->readableBy()
            ->when($this->module_filter !== '', fn ($query) => $query->where('module', $this->module_filter))
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($sub) use ($term) {
                    $sub->where('title', 'like', "%{$term}%")
                        ->orWhere('summary', 'like', "%{$term}%")
                        ->orWhere('content', 'like', "%{$term}%");
                });
            })
            ->orderBy('module')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->groupBy('module');

        return view('livewire.admin.guides.index', [
            'grouped_guides' => $guides,
            'modules'        => Guide::query()->readableBy()->distinct()->orderBy('module')->pluck('module'),
            'types'          => Guide::types(),
            'total'          => Guide::query()->readableBy()->count(),
            'can_manage'     => (bool) auth()->user()?->can('guides.manage'),
        ]);
    }
}
