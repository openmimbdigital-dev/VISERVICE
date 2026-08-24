<?php

namespace App\Livewire\Admin\Workshop\AdvancePayments;

use App\Actions\Workshop\RegisterWorkOrderAdvancePaymentAction;
use App\Actions\Workshop\VoidWorkOrderAdvancePaymentAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\BusinessBankAccount;
use App\Models\BusinessPaymentMethod;
use App\Models\WorkOrder;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Anticipo OT — Taller')]
class Show extends Component
{
    use ConfirmsDeletionWithLivewireAlert;

    public WorkOrder $workOrder;

    public string $amount = '';

    public ?int $business_payment_method_id = null;

    public ?int $business_bank_account_id = null;

    public string $payment_reference = '';

    public string $paid_at = '';

    public string $notes = '';

    public bool $showPaymentModal = false;

    public function mount(WorkOrder $workOrder): void
    {
        abort_unless(auth()->user()?->can('workshop.advance-payments.view'), 403);

        abort_unless(
            WorkOrder::query()->forAuthUser()->whereKey($workOrder->id)->where('advance_amount', '>', 0)->exists(),
            404
        );

        $this->workOrder = $workOrder;
        $this->paid_at = now()->format('Y-m-d\TH:i');
    }

    public function openPaymentModal(): void
    {
        abort_unless(auth()->user()?->can('workshop.advance-payments.pay'), 403);

        if ($this->workOrder->advanceRemainingAmount() <= 0) {
            $this->dispatch('swal', [
                'title' => 'Anticipo cubierto',
                'text' => 'No hay saldo pendiente por registrar.',
                'icon' => 'info',
            ]);

            return;
        }

        $this->resetPaymentForm();
        $this->amount = (string) $this->workOrder->advanceRemainingAmount();
        $this->showPaymentModal = true;
        $this->resetValidation();
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->resetPaymentForm();
        $this->resetValidation();
    }

    public function savePayment(): void
    {
        abort_unless(auth()->user()->can('workshop.advance-payments.pay'), 403);

        $data = $this->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'business_payment_method_id' => [
                'nullable',
                'integer',
                Rule::exists('business_payment_methods', 'id')->whereNull('deleted_at'),
            ],
            'business_bank_account_id' => [
                'nullable',
                'integer',
                Rule::exists('business_bank_accounts', 'id')->where(fn ($q) => $q
                    ->where('business_id', $this->workOrder->business_id)
                    ->whereNull('deleted_at')),
            ],
            'payment_reference' => ['nullable', 'string', 'max:120'],
            'paid_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'amount.required' => 'Indica el monto del abono.',
            'amount.min' => 'El monto debe ser mayor a cero.',
            'paid_at.required' => 'Indica la fecha del pago.',
        ]);

        try {
            RegisterWorkOrderAdvancePaymentAction::run($this->workOrder, [
                'amount' => $data['amount'],
                'business_payment_method_id' => $data['business_payment_method_id'] ?: null,
                'business_bank_account_id' => $data['business_bank_account_id'] ?: null,
                'payment_reference' => $data['payment_reference'] ?: null,
                'paid_at' => $data['paid_at'],
                'notes' => $data['notes'] ?: null,
            ]);
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                $this->addError($field, $messages[0] ?? 'Error de validación.');
            }

            return;
        }

        $this->closePaymentModal();
        $this->workOrder->refresh();

        $this->dispatch('swal', [
            'title' => 'Abono registrado',
            'icon' => 'success',
        ]);
    }

    public function voidPayment(int $id): void
    {
        abort_unless(auth()->user()->can('workshop.advance-payments.void'), 403);
        $this->askDeleteConfirmation($id, '¿Anular este abono de anticipo?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            VoidWorkOrderAdvancePaymentAction::run($this->delete_id);
            $this->delete_id = null;
            $this->workOrder->refresh();
            $this->alertDeleteSuccess('Abono anulado correctamente.');
        } catch (ValidationException $e) {
            $this->delete_id = null;
            $this->alertDeleteError(collect($e->errors())->flatten()->first() ?: 'No se pudo anular.');
        } catch (\Throwable $e) {
            $this->delete_id = null;
            $this->alertDeleteError($e->getMessage() ?: 'No se pudo anular.');
        }
    }

    private function resetPaymentForm(): void
    {
        $this->amount = '';
        $this->business_payment_method_id = null;
        $this->business_bank_account_id = null;
        $this->payment_reference = '';
        $this->paid_at = now()->format('Y-m-d\TH:i');
        $this->notes = '';
    }

    public function render()
    {
        $this->workOrder->load([
            'client:id,name',
            'equipments',
            'payments' => fn ($q) => $q
                ->with(['paymentMethod', 'createdBy'])
                ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
                ->orderByDesc('paid_at')
                ->orderByDesc('id'),
        ]);

        $payment_methods = BusinessPaymentMethod::query()
            ->visibleToUser()
            ->where('active', true)
            ->orderBy('sort_order')
            ->get();

        $bank_accounts = BusinessBankAccount::query()
            ->forAuthUser()
            ->where('business_id', $this->workOrder->business_id)
            ->where('active', true)
            ->orderBy('bank_name')
            ->get();

        return view('livewire.admin.workshop.advance-payments.show', [
            'advance_agreed' => (float) $this->workOrder->advance_amount,
            'advance_remaining' => $this->workOrder->advanceRemainingAmount(),
            'payment_methods' => $payment_methods,
            'bank_accounts' => $bank_accounts,
            'can_create' => auth()->user()->can('workshop.advance-payments.pay'),
            'can_void' => auth()->user()->can('workshop.advance-payments.void'),
        ]);
    }
}
