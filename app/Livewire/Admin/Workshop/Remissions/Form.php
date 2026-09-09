<?php

namespace App\Livewire\Admin\Workshop\Remissions;

use App\Actions\Workshop\CreateOrUpdateRemissionAction;
use App\Actions\Workshop\DeleteRemissionAction;
use App\Enums\WorkOrderStatus;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Livewire\Concerns\TracksWizardProgress;
use App\Livewire\Forms\Admin\Workshop\RemissionForm;
use App\Models\City;
use App\Models\Client;
use App\Models\Remission;
use App\Models\WorkOrder;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Remisión')]
class Form extends Component
{
    use ConfirmsDeletionWithLivewireAlert;
    use TracksWizardProgress;

    public RemissionForm $form;

    public int $step = RemissionForm::STEP_GENERAL;

    public ?string $reference = null;

    public ?string $remission_status = null;

    /** Bloquea el select de OT cuando se abre el form desde una OT. */
    public bool $work_order_locked = false;

    public function mount(?Remission $remission = null): void
    {
        if ($remission) {
            abort_unless(auth()->user()?->can('workshop.remissions.edit'), 403);
            abort_unless(
                Remission::query()->forAuthUser()->whereKey($remission->id)->exists(),
                404
            );
            $remission->load(['workOrder.statusDefinition', 'workOrder.client']);
            $this->form->setRemission($remission);
            $this->syncWizardProgress($remission);
            $this->step = $remission->isComplete()
                ? RemissionForm::STEP_GENERAL
                : max(RemissionForm::STEP_GENERAL, min((int) $remission->step, RemissionForm::TOTAL_STEPS));
            $this->reference = $remission->reference;
            $this->remission_status = $remission->status instanceof WorkOrderStatus
                ? $remission->status->value
                : (string) $remission->status;
            $this->work_order_locked = true;
            $this->prefillResponsibles($remission->workOrder?->client);

            return;
        }

        abort_unless(auth()->user()?->can('workshop.remissions.create'), 403);

        $this->form->issue_date = now()->format('Y-m-d');
        $this->form->status = WorkOrderStatus::Draft->value;
        $this->form->type = 'entrega';
        $this->prefillDeliveredByFromUser();

        $work_order_id = request()->integer('work_order');
        if ($work_order_id > 0) {
            $work_order = WorkOrder::query()
                ->forAuthUser()
                ->where('business_id', $this->form->resolvedBusinessId())
                ->whereIn('status', WorkOrderStatus::remissionEligibleValues())
                ->whereDoesntHave('remissions')
                ->with(['client.city', 'equipments'])
                ->find($work_order_id);

            if ($work_order) {
                $this->form->work_order_id = $work_order->id;
                $this->work_order_locked = true;
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
            ->whereIn('status', WorkOrderStatus::remissionEligibleValues())
            ->with(['client.city', 'equipments'])
            ->find((int) $value);

        if (! $work_order) {
            $this->form->work_order_id = null;
            $this->addError('form.work_order_id', 'La OT no está disponible o no está creada, en proceso o finalizada.');

            return;
        }

        $this->prefillFromWorkOrder($work_order);
    }

    private function prefillFromWorkOrder(WorkOrder $work_order): void
    {
        $work_order->loadMissing(['client.city', 'quotation:id,reference', 'statusDefinition']);

        $client = $work_order->client;
        $this->form->delivery_address = $this->form->delivery_address ?: ($client?->address ?? '');
        $this->form->delivery_contact = $this->form->delivery_contact ?: ($client?->contact_name ?? $client?->name ?? '');
        $this->form->delivery_phone = $this->form->delivery_phone ?: ($client?->phone ?? '');
        $this->form->delivery_city = $client?->city?->name ?? '';
        $this->form->quotation_or_po_reference = $work_order->quotation?->reference ?? '';
        $this->prefillResponsibles($client);
    }

    private function prefillResponsibles(?Client $client = null): void
    {
        $this->prefillDeliveredByFromUser();
        $this->prefillReceivedByFromClient($client);
    }

    private function prefillDeliveredByFromUser(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        if (trim($this->form->delivered_by_name) === '') {
            $this->form->delivered_by_name = $user->full_name;
        }

        if (trim($this->form->delivered_by_position) === '') {
            $this->form->delivered_by_position = $user->name_team_position ?? '';
        }

        if (trim($this->form->delivered_by_document) === '') {
            $this->form->delivered_by_document = $user->document_number ? (string) $user->document_number : '';
        }
    }

    private function prefillReceivedByFromClient(?Client $client): void
    {
        if (! $client) {
            return;
        }

        if (trim($this->form->received_by_name) === '') {
            $this->form->received_by_name = $client->contact_name ?: $client->name ?: '';
        }

        if (trim($this->form->received_by_position) === '') {
            $this->form->received_by_position = $client->contact_name ? 'Contacto' : '';
        }

        if (trim($this->form->received_by_document) === '') {
            $this->form->received_by_document = $client->document_number ? (string) $client->document_number : '';
        }
    }

    public function nextStep(): void
    {
        $this->form->validate($this->form->rulesForStep($this->step));
        $this->advanceToStep(min($this->step + 1, RemissionForm::TOTAL_STEPS));
    }

    public function previousStep(): void
    {
        $this->step = max($this->step - 1, RemissionForm::STEP_GENERAL);
    }

    public function goToStep(int $step): void
    {
        if ($step < RemissionForm::STEP_GENERAL || $step > RemissionForm::TOTAL_STEPS) {
            return;
        }

        if ($step > $this->step) {
            for ($current = $this->step; $current < $step; $current++) {
                $this->form->validate($this->form->rulesForStep($current));
            }
        }

        $this->advanceToStep($step);
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()->can($this->form->isEditing() ? 'workshop.remissions.edit' : 'workshop.remissions.create'),
            403
        );

        try {
            $remission = CreateOrUpdateRemissionAction::run(
                $this->form->resolvedBusinessId(),
                $this->form->remission_id,
                $this->form->validated()
            );
        } catch (ValidationException $exception) {
            $this->step = $this->form->firstStepWithErrors($exception->errors());

            throw $exception;
        }

        $this->dispatch('swal', [
            'title' => $this->form->isEditing()
                ? 'Remisión actualizada'
                : "Remisión {$remission->reference} creada",
            'icon'  => 'success',
        ]);

        $this->redirectRoute('admin.workshop.remissions.show', $remission, navigate: true);
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

    protected function isFlowComplete(): bool
    {
        return $this->form->remission_id
            && $this->step >= RemissionForm::TOTAL_STEPS
            && trim($this->form->delivered_by_name) !== ''
            && trim($this->form->received_by_name) !== '';
    }

    protected function advanceToStep(int $step): void
    {
        if ($step > $this->step && ! $this->isFlowComplete() && ! $this->saved_complete) {
            $was_new = ! $this->form->isEditing();
            $remission = $this->persistProgress($step);

            if ($was_new) {
                $this->redirectRoute('admin.workshop.remissions.form.edit', $remission, navigate: true);

                return;
            }
        }

        $this->step = $step;
    }

    protected function persistProgress(int $step): Remission
    {
        $remission = CreateOrUpdateRemissionAction::run(
            $this->form->resolvedBusinessId(),
            $this->form->remission_id,
            $this->form->payload($step)
        );

        $this->form->remission_id = $remission->id;
        $this->reference = $remission->reference;
        $this->remission_status = $remission->status instanceof WorkOrderStatus
            ? $remission->status->value
            : (string) $remission->status;
        $this->form->status = $this->remission_status;
        $this->syncWizardProgress($remission);

        return $remission;
    }

    public function render()
    {
        $status_enum = WorkOrderStatus::tryFrom($this->remission_status ?: $this->form->status);
        $status_label = $status_enum?->label() ?? ($this->remission_status ?: $this->form->status ?: '—');
        $status_badge_class = $status_enum?->badgeClass()
            ?? 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20';

        $work_order_items = collect();
        $client_name = null;
        if ($this->form->work_order_id) {
            $work_order = WorkOrder::query()
                ->forAuthUser()
                ->whereKey($this->form->work_order_id)
                ->with(['client:id,name', 'items.productType', 'items.equipment', 'items.catalogProduct'])
                ->first();

            $work_order_items = $work_order?->items ?? collect();
            $client_name = $work_order?->client?->name;
        }

        $total_steps = RemissionForm::TOTAL_STEPS;

        return view('livewire.admin.workshop.remissions.form', [
            'is_editing'           => $this->form->isEditing(),
            'eligible_work_orders' => $this->form->getEligibleWorkOrders(),
            'cities'               => City::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'state_province']),
            'status_label'         => $status_label,
            'status_badge_class'   => $status_badge_class,
            'work_order_items'     => $work_order_items,
            'client_name'          => $client_name,
            'step'                 => $this->step,
            'total_steps'          => $total_steps,
            ...$this->wizardProgressViewData($total_steps),
            'steps'                => [
                RemissionForm::STEP_GENERAL => [
                    'title'       => 'Datos',
                    'description' => 'OT y tipo',
                ],
                RemissionForm::STEP_DELIVERY => [
                    'title'       => 'Destino',
                    'description' => 'Entrega',
                ],
                RemissionForm::STEP_RESPONSIBLES => [
                    'title'       => 'Responsables',
                    'description' => 'Firmas',
                ],
                RemissionForm::STEP_ITEMS => [
                    'title'       => 'Ítems',
                    'description' => 'Ítems de la OT',
                ],
            ],
            'can_delete'           => $this->form->isEditing()
                && auth()->user()->can('workshop.remissions.delete')
                && ! ($status_enum?->isTerminal() ?? false),
        ]);
    }
}
