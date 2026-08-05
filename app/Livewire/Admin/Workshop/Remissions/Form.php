<?php

namespace App\Livewire\Admin\Workshop\Remissions;

use App\Actions\Workshop\CreateOrUpdateRemissionAction;
use App\Actions\Workshop\DeleteRemissionAction;
use App\Enums\WorkOrderStatus;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Livewire\Forms\Admin\Workshop\RemissionForm;
use App\Models\City;
use App\Models\Remission;
use App\Models\WorkOrder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Remisión')]
class Form extends Component
{
    use ConfirmsDeletionWithLivewireAlert;

    public RemissionForm $form;

    public ?string $reference = null;

    public function mount(?Remission $remission = null): void
    {
        if ($remission) {
            abort_unless(auth()->user()?->can('workshop.remissions.edit'), 403);
            abort_unless(
                Remission::query()->forAuthUser()->whereKey($remission->id)->exists(),
                404
            );
            abort_unless($remission->isEditable(), 403);

            $this->form->setRemission($remission);
            $this->reference = $remission->reference;

            return;
        }

        abort_unless(auth()->user()?->can('workshop.remissions.create'), 403);

        $this->form->issue_date = now()->format('Y-m-d');
        $this->form->status = WorkOrderStatus::Created->value;
        $this->form->type = 'entrega';

        $work_order_id = request()->integer('work_order');
        if ($work_order_id > 0) {
            $work_order = WorkOrder::query()
                ->forAuthUser()
                ->where('business_id', $this->form->resolvedBusinessId())
                ->whereIn('status', WorkOrderStatus::openValues())
                ->with(['client.city', 'equipment'])
                ->find($work_order_id);

            if ($work_order) {
                $this->form->work_order_id = $work_order->id;
                $this->prefillFromWorkOrder($work_order);
            }
        }
    }

    public function updatedFormWorkOrderId(mixed $value): void
    {
        if (! $value) {
            return;
        }

        $work_order = WorkOrder::query()
            ->forAuthUser()
            ->where('business_id', $this->form->resolvedBusinessId())
            ->whereIn('status', WorkOrderStatus::openValues())
            ->with(['client.city', 'equipment'])
            ->find((int) $value);

        if (! $work_order) {
            $this->form->work_order_id = null;
            $this->addError('form.work_order_id', 'La OT no está disponible o no está creada/en proceso.');

            return;
        }

        $this->prefillFromWorkOrder($work_order);
    }

    private function prefillFromWorkOrder(WorkOrder $work_order): void
    {
        $work_order->loadMissing(['client.city', 'quotation:id,reference']);

        $client = $work_order->client;
        $this->form->delivery_address = $this->form->delivery_address ?: ($client?->address ?? '');
        $this->form->delivery_contact = $this->form->delivery_contact ?: ($client?->contact_name ?? $client?->name ?? '');
        $this->form->delivery_phone = $this->form->delivery_phone ?: ($client?->phone ?? '');
        $this->form->delivery_city = $client?->city?->name ?? '';
        $this->form->quotation_or_po_reference = $work_order->quotation?->reference ?? '';
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()->can($this->form->isEditing() ? 'workshop.remissions.edit' : 'workshop.remissions.create'),
            403
        );

        $remission = CreateOrUpdateRemissionAction::run(
            $this->form->resolvedBusinessId(),
            $this->form->remission_id,
            $this->form->validated()
        );

        $this->dispatch('swal', [
            'title' => $this->form->isEditing() ? 'Remisión actualizada' : 'Remisión creada',
            'icon'  => 'success',
        ]);

        if ($this->form->isEditing()) {
            $this->redirectRoute('admin.workshop.remissions.show', $remission, navigate: true);

            return;
        }

        $this->redirectRoute('admin.workshop.remissions.index', navigate: true);
    }

    public function deleteRemission(): void
    {
        abort_unless(auth()->user()?->can('workshop.remissions.delete'), 403);
        abort_unless($this->form->remission_id, 404);
        $this->askDeleteConfirmation($this->form->remission_id, '¿Eliminar esta remisión?');
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
        return view('livewire.admin.workshop.remissions.form', [
            'is_editing'           => $this->form->isEditing(),
            'eligible_work_orders' => $this->form->getEligibleWorkOrders(),
            'cities'               => City::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'state_province']),
            'can_delete'           => $this->form->isEditing()
                && auth()->user()->can('workshop.remissions.delete')
                && ! (WorkOrderStatus::tryFrom($this->form->status)?->isTerminal() ?? false),
        ]);
    }
}
