<?php

namespace App\Actions\Workshop;

use App\Enums\WorkOrderStatus;
use App\Models\WorkOrder;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncWorkOrderStatusFromItemsAction
{
    use AsAction;

    public function handle(int $work_order_id): ?WorkOrderStatus
    {
        $work_order = WorkOrder::query()->forAuthUser()->findOrFail($work_order_id);

        if (! $work_order->isEditable()) {
            return null;
        }

        $items = $work_order->items()->get([
            'id',
            'quantity',
            'quantity_complete',
            'quantity_canceled',
        ]);

        if ($items->isEmpty()) {
            return null;
        }

        if ($items->sum(fn ($item) => (float) $item->quantity) <= 0) {
            return null;
        }

        $all_canceled = $items->every(
            fn ($item) => round((float) $item->quantity_canceled, 2) === round((float) $item->quantity, 2)
        );

        if ($all_canceled) {
            UpdateWorkOrderStatusAction::run(
                $work_order->id,
                WorkOrderStatus::Cancelled,
                'Se cancelan los items'
            );

            return WorkOrderStatus::Cancelled;
        }

        $all_complete = $items->every(
            fn ($item) => round((float) $item->quantity_complete, 2) === round((float) $item->quantity, 2)
        );

        if ($all_complete) {
            UpdateWorkOrderStatusAction::run(
                $work_order->id,
                WorkOrderStatus::Completed
            );

            return WorkOrderStatus::Completed;
        }

        $has_completed_quantity = $items->contains(
            fn ($item) => (float) $item->quantity_complete > 0
        );

        if ($has_completed_quantity && $work_order->status === WorkOrderStatus::Created) {
            UpdateWorkOrderStatusAction::run(
                $work_order->id,
                WorkOrderStatus::InProgress
            );

            return WorkOrderStatus::InProgress;
        }

        return null;
    }
}
