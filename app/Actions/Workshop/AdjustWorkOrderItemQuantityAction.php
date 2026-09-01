<?php

namespace App\Actions\Workshop;

use App\Actions\LogUserHistoricalAction;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class AdjustWorkOrderItemQuantityAction
{
    use AsAction;

    public const ACTION_COMPLETE = 'complete';

    public const ACTION_CANCEL = 'cancel';

    public function handle(int $work_order_id, int $item_id, string $action): WorkOrderItem
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.edit'), 403);

        $work_order = WorkOrder::query()->forAuthUser()->findOrFail($work_order_id);

        $user = auth()->user();
        if (! $user->hasRole('superAdmin')) {
            abort_unless((int) $work_order->business_id === (int) $user->business_id, 403);
        }

        if (! $work_order->isEditable()) {
            throw ValidationException::withMessages([
                'item' => 'La OT está finalizada o cancelada y no admite cambios.',
            ]);
        }

        $item = WorkOrderItem::query()
            ->where('work_order_id', $work_order->id)
            ->whereKey($item_id)
            ->firstOrFail();

        $quantity = (float) $item->quantity;
        $complete = (float) $item->quantity_complete;
        $canceled = (float) $item->quantity_canceled;

        if ($action === self::ACTION_COMPLETE) {
            if ($complete >= $quantity) {
                return $item;
            }

            $complete = min($complete + 1, $quantity);
            if ($complete + $canceled > $quantity) {
                $canceled = max(0, $quantity - $complete);
            }
        } elseif ($action === self::ACTION_CANCEL) {
            if ($canceled >= $quantity) {
                return $item;
            }

            $canceled = min($canceled + 1, $quantity);
            if ($complete + $canceled > $quantity) {
                $complete = max(0, $quantity - $canceled);
            }
        } else {
            abort(400, 'Acción de cantidad no válida.');
        }

        $item->update([
            'quantity_complete' => $complete,
            'quantity_canceled' => $canceled,
        ]);

        SyncWorkOrderStatusFromItemsAction::run($work_order->id);

        $item = $item->fresh();
        $work_order->loadMissing('client:id,name');

        $action_label = $action === self::ACTION_COMPLETE ? 'completó' : 'canceló';
        LogUserHistoricalAction::run(
            action: $action === self::ACTION_COMPLETE ? 'item_completed' : 'item_canceled',
            module: 'workshop.work-orders',
            description: "Se {$action_label} cantidad del ítem «{$item->description}» en la OT {$work_order->reference}",
            subject: $work_order,
            subject_label: $work_order->reference,
            properties: [
                'item_id' => $item->id,
                'item_description' => $item->description,
                'quantity' => $item->quantity,
                'quantity_complete' => $item->quantity_complete,
                'quantity_canceled' => $item->quantity_canceled,
                'adjust_action' => $action,
            ],
            business_id: (int) $work_order->business_id,
        );

        return $item;
    }
}
