<?php

namespace App\Livewire\Admin\Workshop\Invoices;

use App\Actions\RegisterInvoicePaymentAction;
use App\Enums\ElectronicInvoiceStatus;
use App\Models\BusinessDianSetting;
use App\Models\BusinessPaymentMethod;
use App\Models\WorkOrderInvoice;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Factura')]
class Show extends Component
{
    public WorkOrderInvoice $invoice;

    public bool $showPaymentModal = false;

    public ?int $business_payment_method_id = null;

    public string $payment_reference = '';

    public string $paid_at = '';

    public string $payment_notes = '';

    /** Caché de solo lectura por petición; Livewire ignora lo que no es público. */
    private ?bool $dian_applies = null;

    public function mount(WorkOrderInvoice $workOrderInvoice): void
    {
        abort_unless(auth()->user()?->can('workshop.invoices.view'), 403);

        abort_unless(
            WorkOrderInvoice::query()->forAuthUser()->whereKey($workOrderInvoice->id)->exists(),
            404
        );

        $this->invoice = $workOrderInvoice;
    }

    public function openPaymentModal(): void
    {
        abort_unless(auth()->user()?->can('workshop.invoices.pay'), 403);

        if (! $this->canRegisterPayment()) {
            return;
        }

        $default = BusinessPaymentMethod::query()
            ->visibleToUser()
            ->where('active', true)
            ->orderByDesc('is_default')
            ->orderBy('sort_order')
            ->first();

        $this->business_payment_method_id = $default?->id;
        $this->payment_reference = '';
        $this->payment_notes = '';
        $this->paid_at = now()->toDateString();

        $this->resetValidation();
        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->resetValidation();
    }

    public function savePayment(): void
    {
        abort_unless(auth()->user()?->can('workshop.invoices.pay'), 403);

        $data = $this->validate([
            'business_payment_method_id' => [
                'required',
                'integer',
                Rule::exists('business_payment_methods', 'id')->whereNull('deleted_at'),
            ],
            'payment_reference' => ['nullable', 'string', 'max:120'],
            'paid_at'           => ['required', 'date', 'before_or_equal:today'],
            'payment_notes'     => ['nullable', 'string', 'max:1000'],
        ], [
            'business_payment_method_id.required' => 'Selecciona el método de pago.',
            'business_payment_method_id.exists'   => 'El método de pago seleccionado no es válido.',
            'paid_at.required'                    => 'Indica la fecha del pago.',
            'paid_at.before_or_equal'             => 'La fecha del pago no puede ser futura.',
            'payment_notes.max'                   => 'La nota no puede superar 1000 caracteres.',
        ]);

        $method = BusinessPaymentMethod::query()
            ->visibleToUser()
            ->where('active', true)
            ->find($data['business_payment_method_id']);

        if (! $method) {
            $this->addError('business_payment_method_id', 'El método de pago seleccionado no está disponible.');

            return;
        }

        try {
            $this->invoice = RegisterInvoicePaymentAction::run(
                invoice: $this->invoice,
                payment_method: $method->name,
                payment_reference: $data['payment_reference'] !== '' ? $data['payment_reference'] : null,
                paid_at: $data['paid_at'],
                notes: $data['payment_notes'] !== '' ? $data['payment_notes'] : null,
            );
        } catch (ValidationException $exception) {
            $this->dispatch('swal', [
                'title' => collect($exception->errors())->flatten()->first() ?? 'No se pudo registrar el pago.',
                'icon'  => 'error',
            ]);

            return;
        }

        $this->closePaymentModal();

        $this->dispatch('swal', [
            'title' => 'Pago registrado',
            'text'  => "La factura {$this->invoice->reference} quedó como pagada.",
            'icon'  => 'success',
        ]);
    }

    /** Solo se cobra una factura que siga viva y sin pagar. */
    private function canRegisterPayment(): bool
    {
        return ! in_array($this->invoice->status, ['pagada', 'anulada'], true);
    }

    public function render()
    {
        $this->invoice->load([
            'items.workOrderItem.productType',
            'items.workOrderItem.equipment',
            'workOrder.client',
            'workOrder.equipments',
            'createdBy',
            'electronicInvoice',
            'statusHistories.user:id,first_name,last_name,username',
        ]);

        return view('livewire.admin.workshop.invoices.show', [
            'electronic_invoice' => $this->invoice->electronicInvoice,
            'status_timeline'    => $this->buildStatusTimeline(),
            'status_flow'        => $this->buildStatusFlow(),
            'items_summary'      => $this->buildItemsSummary(),
            'due_state'          => $this->buildDueState(),
            'dian_applies'       => $this->dianApplies(),
            'payment_methods'    => BusinessPaymentMethod::query()
                ->visibleToUser()
                ->where('active', true)
                ->orderByDesc('is_default')
                ->orderBy('sort_order')
                ->get(),
            'can_register_payment' => auth()->user()->can('workshop.invoices.pay') && $this->canRegisterPayment(),
        ]);
    }

    /**
     * Cronología completa —cobro y emisión— del paso más reciente al más antiguo.
     *
     * @return list<array<string, mixed>>
     */
    private function buildStatusTimeline(): array
    {
        return $this->invoice->statusHistories
            ->reverse()
            ->values()
            ->map(fn ($history) => [
                'kind'          => $history->kind,
                'is_emission'   => $history->isEmission(),
                'from_label'    => $history->fromLabel(),
                'to_label'      => $history->toLabel(),
                'comment'       => $history->comment,
                'user_name'     => $history->user_name
                    ?: trim(($history->user?->first_name ?? '').' '.($history->user?->last_name ?? '')) ?: null,
                'changed_at'    => $history->created_at?->format('d/m/Y H:i'),
                'changed_ago'   => $history->created_at?->diffForHumans(),
                'duration'      => $history->durationLabel(),
                'is_opening'    => $history->isOpening(),
                'is_backfilled' => $history->isBackfilled(),
                'dot_class'     => $history->dotClass(),
                'document_number' => $history->metadata['document_number'] ?? null,
            ])
            ->all();
    }

    /**
     * Pasos por los que pasa la factura. La parte DIAN solo aparece cuando el
     * negocio factura electrónicamente; si no, el recorrido es creada → pagada.
     *
     * @return list<array<string, mixed>>
     */
    private function buildStatusFlow(): array
    {
        $electronic = $this->invoice->electronicInvoice;
        $steps = [];

        $steps[] = [
            'label'     => 'Creada',
            'reached'   => true,
            'dot_class' => 'bg-blue-500',
            'hint'      => $this->invoice->created_at?->format('d/m/Y'),
        ];

        if ($this->dianApplies()) {
            $status = $electronic?->status;

            $steps[] = [
                'label'     => 'Enviada a la DIAN',
                'reached'   => $status?->hasTransaction() ?? false,
                'dot_class' => 'bg-indigo-500',
                'hint'      => $electronic?->sent_at?->format('d/m/Y'),
            ];

            // Un rechazo o un error reemplazan el paso de validación: es donde
            // el documento se quedó, y esconderlo sería mentir sobre el estado.
            if ($status === ElectronicInvoiceStatus::Rejected || $status === ElectronicInvoiceStatus::Error) {
                $steps[] = [
                    'label'     => $status->label(),
                    'reached'   => true,
                    'dot_class' => $status === ElectronicInvoiceStatus::Rejected ? 'bg-rose-500' : 'bg-amber-500',
                    'hint'      => $electronic?->status_checked_at?->format('d/m/Y'),
                ];
            } else {
                $steps[] = [
                    'label'     => 'Validada por la DIAN',
                    'reached'   => $status === ElectronicInvoiceStatus::Accepted,
                    'dot_class' => 'bg-emerald-500',
                    'hint'      => $electronic?->accepted_at?->format('d/m/Y'),
                ];
            }
        }

        if ($this->invoice->status === 'anulada') {
            $steps[] = [
                'label'     => 'Anulada',
                'reached'   => true,
                'dot_class' => 'bg-slate-400',
                'hint'      => null,
            ];
        } else {
            // Vencerse no cierra el recorrido: la factura sigue pudiendo pagarse,
            // así que el paso se intercala en vez de reemplazar el de pago.
            if ($this->invoice->status === 'vencida') {
                $steps[] = [
                    'label'     => 'Vencida',
                    'reached'   => true,
                    'dot_class' => 'bg-rose-500',
                    'hint'      => $this->invoice->due_date?->format('d/m/Y'),
                ];
            }

            $steps[] = [
                'label'     => 'Pagada',
                'reached'   => $this->invoice->status === 'pagada',
                'dot_class' => 'bg-emerald-500',
                'hint'      => $this->invoice->paid_at?->format('d/m/Y'),
            ];
        }

        // El paso actual es el último alcanzado del recorrido.
        $last_reached = null;

        foreach ($steps as $index => $step) {
            if ($step['reached']) {
                $last_reached = $index;
            }
        }

        foreach ($steps as $index => $step) {
            $steps[$index]['is_current'] = $index === $last_reached;
        }

        return $steps;
    }

    /** @return array<string, int|float> */
    private function buildItemsSummary(): array
    {
        $items = $this->invoice->items;

        return [
            'lines'    => $items->count(),
            'total'    => (float) $items->sum(fn ($item) => (float) $item->quantity),
            'complete' => (float) $items->sum(fn ($item) => (float) $item->quantity_complete),
            'canceled' => (float) $items->sum(fn ($item) => (float) $item->quantity_canceled),
        ];
    }

    /**
     * Situación del vencimiento, solo relevante mientras la factura no esté paga.
     *
     * @return array<string, mixed>
     */
    private function buildDueState(): array
    {
        $due = $this->invoice->due_date;
        $settled = in_array($this->invoice->status, ['pagada', 'anulada'], true);

        if (! $due || $settled) {
            return ['label' => $due?->format('d/m/Y'), 'note' => null, 'is_overdue' => false];
        }

        $days = (int) now()->startOfDay()->diffInDays($due->copy()->startOfDay(), absolute: false);

        return [
            'label'      => $due->format('d/m/Y'),
            'note'       => match (true) {
                $days < 0 => 'Vencida hace '.abs($days).' '.(abs($days) === 1 ? 'día' : 'días'),
                $days === 0 => 'Vence hoy',
                $days === 1 => 'Vence mañana',
                default => 'Vence en '.$days.' días',
            },
            'is_overdue' => $days < 0,
        ];
    }

    /** ¿Esta factura pertenece a un negocio que factura electrónicamente? */
    private function dianApplies(): bool
    {
        if ($this->invoice->electronicInvoice !== null) {
            return true;
        }

        // Se pregunta varias veces por render; la consulta se hace una sola vez.
        return $this->dian_applies ??= BusinessDianSetting::query()
            ->where('business_id', $this->invoice->business_id)
            ->exists();
    }
}
