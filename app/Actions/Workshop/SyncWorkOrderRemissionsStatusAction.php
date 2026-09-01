<?php

namespace App\Actions\Workshop;

use App\Actions\LogUserHistoricalAction;
use App\Enums\WorkOrderStatus;
use App\Models\Remission;
use App\Models\WorkOrder;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncWorkOrderRemissionsStatusAction
{
    use AsAction;

    public function handle(WorkOrder $work_order, WorkOrderStatus $status): void
    {
        $work_order->remissions()
            ->get()
            ->each(function (Remission $remission) use ($status, $work_order) {
                $previous = $remission->status instanceof WorkOrderStatus
                    ? $remission->status->value
                    : (string) $remission->status;

                $payload = ['status' => $status->value];

                if ($status === WorkOrderStatus::InProgress) {
                    if (empty($remission->issue_date)) {
                        $payload['issue_date'] = now()->toDateString();
                    }
                    if (empty($remission->issued_at)) {
                        $payload['issued_at'] = now();
                    }
                }

                if ($status === WorkOrderStatus::Completed) {
                    $payload['delivered_at'] = $remission->delivered_at ?? now();
                    $payload['issued_at'] = $remission->issued_at ?? now();
                    $payload['issue_date'] = $remission->issue_date?->toDateString()
                        ?? now()->toDateString();
                }

                $remission->update($payload);

                if ($previous === $status->value) {
                    return;
                }

                LogUserHistoricalAction::run(
                    action: 'status_changed',
                    module: 'workshop.remissions',
                    description: "Actualizó el estado de la remisión {$remission->reference} a {$status->label()} (sincronizado desde OT)",
                    subject: $remission,
                    subject_label: $remission->reference,
                    properties: [
                        'previous_status' => $previous,
                        'status' => $status->value,
                        'work_order_id' => $work_order->id,
                        'work_order_reference' => $work_order->reference,
                        'synced_from' => 'work_order',
                    ],
                    business_id: (int) $work_order->business_id,
                );
            });
    }
}
