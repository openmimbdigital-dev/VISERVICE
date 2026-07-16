<?php

namespace App\Livewire\Admin\Workshop\WorkOrders\AssociatedDocuments;

use App\Actions\Workshop\DeleteAssociatedDocumentOtAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\GeneralConfig;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;

class DatatableAssociatedDocuments extends LivewireDatatable
{
    use ConfirmsDeletionWithLivewireAlert;

    public bool $exportable = true;

    public ?int $perPage = 25;

    #[On('associated-document-ot-saved')]
    public function onSaved(): void {}

    public function builder(): Builder
    {
        $query = GeneralConfig::query()
            ->forAuthUser()
            ->associatedDocumentsOt()
            ->select('general_configs.*')
            ->orderByDesc('general_configs.created_at');

        if (auth()->user()->hasRole('superAdmin')) {
            $query->leftJoin('businesses', 'general_configs.business_id', '=', 'businesses.id');
        }

        return $query;
    }

    public function getColumns(): Model|array
    {
        $columns = [
            Column::name('general_configs.value')->label('Documento')->searchable()->sortable(),
            Column::name('general_configs.label')->label('Label')->searchable()->sortable(),
            Column::name('general_configs.key')->label('Key')->sortable(),
        ];

        if (auth()->user()->hasRole('superAdmin')) {
            $columns[] = Column::raw('businesses.name AS business_name')->label('Negocio')->searchable();
        }

        $columns[] = Column::callback(
            ['general_configs.id'],
            function ($id) {
                return view('livewire.admin.workshop.work-orders.associated-documents.actions', [
                    'id' => $id,
                ]);
            }
        )->label('Acciones')->unsortable();

        return $columns;
    }

    public function openEditEvent(int $id): void
    {
        $this->dispatch('open-associated-document-ot-edit', id: $id);
    }

    public function deleteRecord(int $id): void
    {
        abort_unless(auth()->user()->can('workshop.work-orders.associated-documents.delete'), 403);
        $this->askDeleteConfirmation($id, '¿Eliminar este documento asociado?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteAssociatedDocumentOtAction::run($this->delete_id);
            $this->alertDeleteSuccess('Documento eliminado correctamente.');
            $this->dispatch('associated-document-ot-deleted');
        } catch (\Throwable $e) {
            $this->alertDeleteError($e->getMessage() ?: 'No se pudo eliminar el documento.');
        }
    }

    public function render()
    {
        $this->dispatch('refreshDynamic');

        if ($this->persistPerPage) {
            session()->put([$this->sessionStorageKey() . '_perpage' => $this->perPage]);
        }

        return view('datatables::datatable');
    }
}
