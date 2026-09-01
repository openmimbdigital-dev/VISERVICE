<?php

namespace App\Livewire\Admin\Workshop\WorkOrders\AssociatedDocuments;

use App\Actions\Workshop\DeleteAssociatedDocumentTypeAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\AssociatedDocumentType;
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

    #[On('associated-document-type-saved')]
    public function onSaved(): void {}

    public function builder(): Builder
    {
        $query = AssociatedDocumentType::query()
            ->forAuthUser()
            ->select('associated_document_types.*')
            ->orderByDesc('associated_document_types.created_at');

        if (auth()->user()->hasRole('superAdmin')) {
            $query->leftJoin('businesses', 'associated_document_types.business_id', '=', 'businesses.id');
        }

        return $query;
    }

    public function getColumns(): Model|array
    {
        $columns = [
            Column::name('associated_document_types.name')->label('Documento')->searchable()->sortable(),
            Column::name('associated_document_types.key')->label('Key')->searchable()->sortable(),
        ];

        if (auth()->user()->hasRole('superAdmin')) {
            $columns[] = Column::raw('businesses.name AS business_name')->label('Negocio')->searchable();
        }

        $columns[] = Column::callback(['associated_document_types.active'], function ($active) {
            return $active
                ? '<span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-emerald-600/20">Activo</span>'
                : '<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600 ring-1 ring-slate-500/20">Inactivo</span>';
        })->label('Estado');

        $columns[] = Column::callback(['associated_document_types.document_send'], function ($document_send) {
            return $document_send
                ? '<span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700 ring-1 ring-indigo-600/20">Sí</span>'
                : '<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600 ring-1 ring-slate-500/20">No</span>';
        })->label('Envío doc.');

        $columns[] = Column::callback(
            ['associated_document_types.id'],
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
        $this->dispatch('open-associated-document-type-edit', id: $id);
    }

    public function deleteRecord(int $id): void
    {
        abort_unless(auth()->user()->can('workshop.work-orders.associated-documents.delete'), 403);
        $this->askDeleteConfirmation($id, '¿Eliminar este documento asociado?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteAssociatedDocumentTypeAction::run($this->delete_id);
            $this->alertDeleteSuccess('Documento eliminado correctamente.');
            $this->dispatch('associated-document-type-deleted');
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
