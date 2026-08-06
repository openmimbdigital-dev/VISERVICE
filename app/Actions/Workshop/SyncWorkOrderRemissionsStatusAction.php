<?php

namespace App\Actions\Workshop;

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
            ->each(function (Remission $remission) use ($status) {
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
            });
    }
}
