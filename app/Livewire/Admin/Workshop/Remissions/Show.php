<?php

namespace App\Livewire\Admin\Workshop\Remissions;

use App\Actions\Workshop\DeleteRemissionAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\GeneralConfig;
use App\Models\Remission;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Remisión')]
class Show extends Component
{
    use ConfirmsDeletionWithLivewireAlert;

    public Remission $remission;

    public function mount(Remission $remission): void
    {
        abort_unless(auth()->user()?->can('workshop.remissions.view'), 403);

        abort_unless(
            Remission::query()->forAuthUser()->whereKey($remission->id)->exists(),
            404
        );

        $this->remission = $remission;
    }

    public function deleteRemission(): void
    {
        abort_unless(auth()->user()?->can('workshop.remissions.delete'), 403);
        abort_unless($this->remission->status !== 'entregada', 403);
        $this->askDeleteConfirmation($this->remission->id, '¿Eliminar esta remisión?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteRemissionAction::run($this->delete_id);
            $this->alertDeleteSuccess('Remisión eliminada correctamente.');
            $this->redirectRoute('admin.workshop.remissions.index', navigate: true);
        } catch (\Throwable $e) {
            $this->alertDeleteError($e->getMessage() ?: 'No se pudo eliminar la remisión.');
        }
    }

    public function render()
    {
        $this->remission->load([
            'items.productType',
            'items.productCategory',
            'items.unit',
            'client',
            'equipment',
            'workOrder',
        ]);

        $document_client = $this->remission->workOrder?->document_client ?? [];
        $document_labels = [];

        if ($document_client !== []) {
            $document_labels = GeneralConfig::query()
                ->forAuthUser()
                ->associatedDocumentsOt()
                ->where('business_id', $this->remission->business_id)
                ->whereIn('label', array_keys($document_client))
                ->pluck('value', 'label')
                ->all();
        }

        return view('livewire.admin.workshop.remissions.show', [
            'document_client' => $document_client,
            'document_labels' => $document_labels,
            'can_edit'        => auth()->user()->can('workshop.remissions.edit')
                && $this->remission->status !== 'entregada',
            'can_delete'      => auth()->user()->can('workshop.remissions.delete')
                && $this->remission->status !== 'entregada',
        ]);
    }
}
