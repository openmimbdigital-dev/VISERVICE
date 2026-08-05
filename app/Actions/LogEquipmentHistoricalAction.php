<?php

namespace App\Actions;

use App\Enums\QuotationStatus;
use App\Enums\WorkOrderStatus;
use App\Models\Equipment;
use App\Models\EquipmentHistorical;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use App\Support\CurrentBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class LogEquipmentHistoricalAction
{
    use AsAction;

    /**
     * Registra un evento en el historial del equipo.
     *
     * @param  array<int, array<string, mixed>>|null  $items
     * @param  array<string, mixed>|null  $properties
     */
    public function handle(
        string $action,
        string $module,
        ?string $description = null,
        ?Equipment $equipment = null,
        ?Model $subject = null,
        ?array $items = null,
        ?array $properties = null,
        ?int $business_id = null,
        ?int $user_id = null,
    ): ?EquipmentHistorical {
        $user_id ??= auth()->id();

        if ($subject instanceof Equipment && ! $equipment) {
            $equipment = $subject;
        }

        if (! $equipment && ($subject instanceof Quotation || $subject instanceof WorkOrder)) {
            $subject->loadMissing('equipment');
            $equipment = $subject->equipment;
        }

        if (! $equipment?->getKey()) {
            return null;
        }

        $equipment->loadMissing('client:id,name');

        $subject_reference = null;
        $subject_status = null;
        $subtotal = null;
        $tax_percentage = null;
        $tax_amount = null;
        $total = null;

        if ($subject instanceof Quotation) {
            $subject->loadMissing(['client:id,name', 'items']);
            $subject_reference = $subject->reference;
            $subject_status = $subject->status instanceof QuotationStatus
                ? $subject->status->value
                : (string) $subject->status;
            $subtotal = $subject->subtotal;
            $tax_percentage = $subject->tax_percentage;
            $tax_amount = $subject->tax_amount;
            $total = $subject->total;
            $items ??= $this->serializeItems($subject->items);
        }

        if ($subject instanceof WorkOrder) {
            $subject->loadMissing(['client:id,name', 'items']);
            $subject_reference = $subject->reference;
            $subject_status = $subject->status instanceof WorkOrderStatus
                ? $subject->status->value
                : (string) $subject->status;
            $subtotal = $subject->subtotal;
            $tax_percentage = $subject->tax_percentage;
            $tax_amount = $subject->tax_amount;
            $total = $subject->total;
            $items ??= $this->serializeItems($subject->items);
        }

        $client_id = $equipment->client_id ? (int) $equipment->client_id : null;
        $client_name = $equipment->client_name
            ?: $equipment->client?->name
            ?: ($subject instanceof Quotation || $subject instanceof WorkOrder ? $subject->client?->name : null);

        $resolved_business_id = $business_id
            ?? (int) $equipment->business_id
            ?? CurrentBusiness::id()
            ?? auth()->user()?->business_id
            ?? ($subject && isset($subject->business_id) ? (int) $subject->business_id : null);

        if (! $resolved_business_id) {
            return null;
        }

        return EquipmentHistorical::query()->create([
            'business_id'       => $resolved_business_id,
            'equipment_id'      => $equipment->id,
            'client_id'         => $client_id,
            'client_name'       => $client_name,
            'equipment_plate'   => $equipment->plate,
            'equipment_label'   => $equipment->select_label ?? $equipment->plate,
            'user_id'           => $user_id,
            'action'            => $action,
            'module'            => $module,
            'description'       => $description,
            'subject_type'      => $subject ? $subject::class : null,
            'subject_id'        => $subject?->getKey(),
            'subject_reference' => $subject_reference,
            'subject_status'    => $subject_status,
            'items'             => $items,
            'subtotal'          => $subtotal,
            'tax_percentage'    => $tax_percentage,
            'tax_amount'        => $tax_amount,
            'total'             => $total,
            'properties'        => $properties,
            'created_at'        => now(),
        ]);
    }

    /**
     * @param  Collection<int, QuotationItem|WorkOrderItem>|iterable<int, QuotationItem|WorkOrderItem>  $items
     * @return list<array<string, mixed>>
     */
    public function serializeItems(iterable $items): array
    {
        $rows = [];

        foreach ($items as $item) {
            $rows[] = [
                'id'                  => $item->id ?? null,
                'product_id'          => $item->product_id ?? null,
                'product_type_id'     => $item->product_type_id ?? null,
                'product_category_id' => $item->product_category_id ?? null,
                'description'         => $item->description ?? null,
                'quantity'            => isset($item->quantity) ? (float) $item->quantity : null,
                'unit_price'          => isset($item->unit_price) ? (float) $item->unit_price : null,
                'discount_percentage' => isset($item->discount_percentage) ? (float) $item->discount_percentage : null,
                'subtotal'            => isset($item->subtotal) ? (float) $item->subtotal : null,
                'status'              => $item->status ?? null,
            ];
        }

        return $rows;
    }
}
