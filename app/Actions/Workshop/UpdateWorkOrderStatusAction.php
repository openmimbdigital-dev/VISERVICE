<?php

namespace App\Actions\Workshop;

use App\Actions\LogEquipmentHistoricalAction;
use App\Actions\LogUserHistoricalAction;
use App\Enums\WorkOrderStatus;
use App\Models\Status;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateWorkOrderStatusAction
{
    use AsAction;

    public function handle(int $work_order_id, WorkOrderStatus $status, ?string $comment = null): WorkOrder
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.edit'), 403);

        $work_order = WorkOrder::query()->forAuthUser()->findOrFail($work_order_id);

        $user = auth()->user();
        if (! $user->hasRole('superAdmin')) {
            abort_unless((int) $work_order->business_id === (int) $user->business_id, 403);
        }

        if (! $work_order->canChangeStatus()) {
            throw ValidationException::withMessages([
                'status' => 'No se puede cambiar el estado: la OT está finalizada o cancelada.',
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
        $this->syncItemsQuantitiesFromStatus($work_order->id, $status);
        SyncWorkOrderRemissionsStatusAction::run($work_order, $status);
        $work_order = $work_order->fresh(['client:id,name', 'equipments', 'items', 'statusDefinition', 'remissions']);

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
