<?php

namespace App\Actions\Workshop;

use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
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

        abort_unless($work_order->isEditable(), 403, 'La OT está finalizada o cancelada y no admite cambios.');

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

        return $item->fresh();
    }
}
