<?php

namespace App\Actions\Workshop;

use App\Models\WorkOrderInvoice;
use App\Models\WorkOrderInvoiceStatusHistory;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Agrega un paso a la línea de tiempo de una factura de OT.
 *
 * La permanencia se mide contra el último registro del mismo eje: el reloj del
 * cobro y el de la emisión electrónica corren por separado.
 */
class RecordInvoiceStatusHistoryAction
{
    use AsAction;

    /** @param  array<string, mixed>  $metadata */
    public function handle(
        WorkOrderInvoice $invoice,
        string $kind,
        string $to_status,
        ?string $from_status = null,
        ?string $comment = null,
        array $metadata = [],
    ): WorkOrderInvoiceStatusHistory {
        $user = auth()->user();
        $changed_at = now();

        $previous = WorkOrderInvoiceStatusHistory::query()
            ->where('work_order_invoice_id', $invoice->id)
            ->where('kind', $kind)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        $comment = $comment !== null ? trim($comment) : null;

        return WorkOrderInvoiceStatusHistory::query()->create([
            'business_id'           => (int) $invoice->business_id,
            'work_order_invoice_id' => (int) $invoice->id,
            'kind'                  => $kind,
            'from_status'           => $from_status,
            'to_status'             => $to_status,
            'comment'               => $comment !== '' ? $comment : null,
            'user_id'               => $user?->id,
            'user_name'             => $this->nameOf($user),
            'duration_seconds'      => $previous?->created_at
                ? max(0, (int) $previous->created_at->diffInSeconds($changed_at))
                : null,
            'metadata'              => $metadata !== [] ? $metadata : null,
            'created_at'            => $changed_at,
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
