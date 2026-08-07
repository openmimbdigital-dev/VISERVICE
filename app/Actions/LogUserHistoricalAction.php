<?php

namespace App\Actions;

use App\Models\Client;
use App\Models\Equipment;
use App\Models\Quotation;
use App\Models\Remission;
use App\Models\UserHistorical;
use App\Models\WorkOrder;
use App\Models\WorkOrderPayment;
use App\Support\CurrentBusiness;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;

class LogUserHistoricalAction
{
    use AsAction;

    /**
     * Registra una acción del usuario autenticado en el historial.
     *
     * @param  array<string, mixed>|null  $properties
     */
    public function handle(
        string $action,
        string $module,
        ?string $description = null,
        ?Model $subject = null,
        ?string $subject_label = null,
        ?int $client_id = null,
        ?string $client_name = null,
        ?array $properties = null,
        ?int $business_id = null,
        ?int $user_id = null,
    ): ?UserHistorical {
        $user_id ??= auth()->id();

        if (! $user_id) {
            return null;
        }

        if ($subject instanceof Client) {
            $client_id ??= (int) $subject->id;
            $client_name ??= $subject->name;
        }

        if ($subject instanceof Equipment) {
            $client_id ??= $subject->client_id ? (int) $subject->client_id : null;
            $client_name ??= $subject->client_name;
        }

        if ($subject instanceof Quotation || $subject instanceof WorkOrder || $subject instanceof Remission) {
            $client_id ??= $subject->client_id ? (int) $subject->client_id : null;
            if ($client_name === null && $client_id) {
                $subject->loadMissing('client:id,name');
                $client_name = $subject->client?->name;
            }
        }

        if ($subject instanceof WorkOrderPayment) {
            $subject->loadMissing('workOrder.client:id,name');
            $client_id ??= $subject->workOrder?->client_id
                ? (int) $subject->workOrder->client_id
                : null;
            $client_name ??= $subject->workOrder?->client?->name;
            $subject_label ??= $subject->workOrder?->reference
                ? $subject->workOrder->reference.' · abono #'.$subject->id
                : 'Abono #'.$subject->id;
        }

        $resolved_business_id = $business_id
            ?? CurrentBusiness::id()
            ?? auth()->user()?->business_id
            ?? ($subject && isset($subject->business_id) ? (int) $subject->business_id : null);

        return UserHistorical::query()->create([
            'business_id'   => $resolved_business_id,
            'user_id'       => $user_id,
            'client_id'     => $client_id,
            'client_name'   => $client_name,
            'action'        => $action,
            'module'        => $module,
            'description'   => $description,
            'subject_type'  => $subject ? $subject::class : null,
            'subject_id'    => $subject?->getKey(),
            'subject_label' => $subject_label,
            'properties'    => $properties,
            'created_at'    => now(),
        ]);
    }
}
