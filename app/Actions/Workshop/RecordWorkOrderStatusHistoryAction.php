<?php

namespace App\Actions\Workshop;

use App\Enums\WorkOrderStatus;
use App\Models\WorkOrder;
use App\Models\WorkOrderStatusHistory;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Agrega un paso a la línea de tiempo de estados de una orden de trabajo.
 *
 * Calcula solo el tiempo de permanencia en el estado anterior; el resto de la
 * información llega desde quien hace el cambio.
 */
class RecordWorkOrderStatusHistoryAction
{
    use AsAction;

    /** @param  array<string, mixed>  $metadata */
    public function handle(
        WorkOrder $work_order,
        WorkOrderStatus $to_status,
        ?WorkOrderStatus $from_status = null,
        ?string $comment = null,
        array $metadata = [],
    ): WorkOrderStatusHistory {
        $user = auth()->user();
        $changed_at = now();

        $previous = WorkOrderStatusHistory::query()
            ->where('work_order_id', $work_order->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        $comment = $comment !== null ? trim($comment) : null;

        return WorkOrderStatusHistory::query()->create([
            'business_id' => (int) $work_order->business_id,
            'work_order_id' => (int) $work_order->id,
            'from_status' => $from_status?->value,
            'to_status' => $to_status->value,
            'comment' => $comment !== '' ? $comment : null,
            'user_id' => $user?->id,
            'user_name' => $this->nameOf($user),
            'duration_seconds' => $previous?->created_at
                ? max(0, (int) $previous->created_at->diffInSeconds($changed_at))
                : null,
            'metadata' => $metadata !== [] ? $metadata : null,
            'created_at' => $changed_at,
        ]);
    }

    private function nameOf(mixed $user): ?string
    {
        if (! $user) {
            return null;
        }

        $name = trim(($user->first_name ?? '').' '.($user->last_name ?? ''));

        return $name !== '' ? $name : ($user->username ?? null);
    }
}
