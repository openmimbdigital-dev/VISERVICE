<?php

namespace App\Actions\Workshop;

use App\Actions\LogEquipmentHistoricalAction;
use App\Actions\LogUserHistoricalAction;
use App\Enums\WorkOrderStatus;
use App\Models\Status;
use App\Models\WorkOrder;
use App\Models\WorkOrderInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateWorkOrderStatusAction
{
    use AsAction;

    /**
     * @param  bool  $force             Salta la guarda de «ya finalizada»: lo usa la anulación
     *                                  de una factura, que cancela su OT aunque esté cerrada.
     * @param  bool  $cascade_invoices  Anula las facturas vivas al cancelar. Va en false cuando
     *                                  la llamada viene de la propia anulación de la factura,
     *                                  para que las dos no se llamen en círculo.
     */
    public function handle(
        int $work_order_id,
        WorkOrderStatus $status,
        ?string $comment = null,
        bool $force = false,
        bool $cascade_invoices = true,
    ): WorkOrder {
        abort_unless(auth()->user()?->can('workshop.work-orders.edit'), 403);

        $work_order = WorkOrder::query()->forAuthUser()->findOrFail($work_order_id);

        $user = auth()->user();
        if (! $user->hasRole('superAdmin')) {
            abort_unless((int) $work_order->business_id === (int) $user->business_id, 403);
        }

        // Cancelar se permite también desde «finalizada»: una OT ya facturada
        // puede tener que echarse atrás, y es el único camino para anular su
        // factura —que es justo el caso en el que hace falta—. Lo que sigue sin
        // poderse es mover un borrador o revivir una cancelada.
        $can_cancel_finished = $status === WorkOrderStatus::Cancelled
            && ! $work_order->isDraft()
            && $work_order->status !== WorkOrderStatus::Cancelled;

        if (! $force && ! $can_cancel_finished && ! $work_order->canChangeStatus()) {
            throw ValidationException::withMessages([
                'status' => $work_order->isDraft()
                    ? 'Completa todos los pasos de la OT antes de cambiar el estado.'
                    : 'No se puede cambiar el estado: la OT está finalizada o cancelada.',
            ]);
        }

        $status_record = Status::query()
            ->forModule('work_orders')
            ->where('name', $status->value)
            ->first();

        if (! $status_record) {
            throw ValidationException::withMessages([
                'status' => 'El estado seleccionado no es válido para órdenes de trabajo.',
            ]);
        }

        $previous_status = $work_order->status;
        $comment = $comment !== null ? trim($comment) : '';

        if ($status === WorkOrderStatus::Cancelled && $comment === '') {
            throw ValidationException::withMessages([
                'status_comment' => 'Indica el motivo de la cancelación.',
            ]);
        }

        // Antes de tocar nada: si al cancelar hay facturas que no se pueden
        // anular, más vale saberlo ahora que dejar la OT cancelada con una
        // factura viva colgando.
        $invoices_to_void = $status === WorkOrderStatus::Cancelled && $cascade_invoices
            ? $this->voidableInvoices($work_order)
            : collect();

        $payload = ['status' => $status->value];

        if ($status === WorkOrderStatus::Completed) {
            $payload['finalized_at'] = $work_order->finalized_at ?? now();
        }

        if ($comment !== '') {
            $comments = $work_order->status_comments ?? [];
            $comments[] = [
                'status' => $status->value,
                'from' => $previous_status instanceof WorkOrderStatus
                    ? $previous_status->value
                    : (string) $previous_status,
                'comment' => $comment,
                'user_id' => $user?->id,
                'user_name' => trim(($user?->first_name ?? '').' '.($user?->last_name ?? ''))
                    ?: ($user?->username ?? null),
                'changed_at' => now()->toIso8601String(),
            ];
            $payload['status_comments'] = $comments;
        }

        $work_order->update($payload);

        foreach ($invoices_to_void as $invoice) {
            CancelWorkOrderInvoiceAction::run(
                invoice: $invoice,
                reason: $comment !== '' ? $comment : 'Se canceló la orden de trabajo.',
                cascade_work_order: false,
            );
        }

        $this->syncItemsQuantitiesFromStatus($work_order->id, $status);
        SyncWorkOrderRemissionsStatusAction::run($work_order, $status);
        $work_order = $work_order->fresh(['client:id,name', 'equipments', 'items', 'statusDefinition', 'remissions']);

        RecordWorkOrderStatusHistoryAction::run(
            work_order: $work_order,
            to_status: $status,
            from_status: $previous_status instanceof WorkOrderStatus
                ? $previous_status
                : WorkOrderStatus::tryFrom((string) $previous_status),
            comment: $comment !== '' ? $comment : null,
            metadata: ['equipment_ids' => $work_order->equipments->pluck('id')->all()],
        );

        $description = "Cambió el estado de la OT {$work_order->reference} a {$status_record->label}";
        $properties = [
            'from' => $previous_status instanceof WorkOrderStatus
                ? $previous_status->value
                : $previous_status,
            'to' => $status->value,
            'comment' => $comment !== '' ? $comment : null,
            'equipment_ids' => $work_order->equipments->pluck('id')->all(),
        ];

        LogUserHistoricalAction::run(
            action: 'status_changed',
            module: 'workshop.work-orders',
            description: $description,
            subject: $work_order,
            subject_label: $work_order->reference,
            properties: $properties,
            business_id: (int) $work_order->business_id,
        );

        foreach ($work_order->equipments as $equipment) {
            LogEquipmentHistoricalAction::run(
                action: 'status_changed',
                module: 'workshop.work-orders',
                description: $description,
                equipment: $equipment,
                subject: $work_order,
                properties: $properties,
                business_id: (int) $work_order->business_id,
            );
        }

        return $work_order;
    }

    /**
     * Facturas vivas de la OT, comprobando primero que todas se puedan anular.
     *
     * @return \Illuminate\Support\Collection<int, WorkOrderInvoice>
     */
    private function voidableInvoices(WorkOrder $work_order): \Illuminate\Support\Collection
    {
        $invoices = WorkOrderInvoice::query()
            ->where('work_order_id', $work_order->id)
            ->where('status', '!=', 'anulada')
            ->get();

        if ($invoices->isEmpty()) {
            return $invoices;
        }

        if (! auth()->user()?->can('workshop.invoices.void')) {
            throw ValidationException::withMessages([
                'status' => 'Esta OT tiene facturas vivas y cancelarla las anularía, '
                    .'pero no tienes permiso para anular facturas.',
            ]);
        }

        foreach ($invoices as $invoice) {
            $reason = CancelWorkOrderInvoiceAction::blockingReason($invoice);

            if ($reason !== null) {
                throw ValidationException::withMessages([
                    'status' => "No se puede cancelar la OT: {$reason}",
                ]);
            }
        }

        return $invoices;
    }

    private function syncItemsQuantitiesFromStatus(int $work_order_id, WorkOrderStatus $status): void
    {
        if ($status === WorkOrderStatus::Completed) {
            DB::table('work_order_items')
                ->where('work_order_id', $work_order_id)
                ->update([
                    'quantity_complete' => DB::raw('quantity'),
                    'quantity_canceled' => 0,
                    'updated_at' => now(),
                ]);

            return;
        }

        if ($status === WorkOrderStatus::Cancelled) {
            DB::table('work_order_items')
                ->where('work_order_id', $work_order_id)
                ->update([
                    'quantity_canceled' => DB::raw('quantity'),
                    'quantity_complete' => 0,
                    'updated_at' => now(),
                ]);
        }
    }
}
