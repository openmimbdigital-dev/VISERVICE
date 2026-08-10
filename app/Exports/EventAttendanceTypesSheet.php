<?php

namespace App\Exports;

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class EventAttendanceTypesSheet implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithTitle
{
    private string $entity_label;

    private string $business_name;

    private string $generated_by;

    private string $event_type_label;

    private string $parent_event_label;

    public function __construct(
        private readonly Event $event,
        private readonly ?User $user = null,
    ) {
        $business = $this->event->business;
        $is_church = $business?->organization_type?->label === 'iglesia';

        $this->entity_label = $is_church ? 'Iglesia' : 'Negocio';
        $this->business_name = $business?->name ?: '—';
        $this->generated_by = trim(($user?->first_name ?? '').' '.($user?->last_name ?? '')) ?: ($user?->username ?? '—');
        $this->event_type_label = $this->event->isMultiDayChild()
            ? 'Día de evento multi-día'
            : 'Evento de un día';
        $this->parent_event_label = $this->event->isMultiDayChild() && $this->event->parent
            ? $this->event->parent->name.' ('.$this->event->parent->dateRangeLabel().')'
            : '—';
    }

    public function collection(): Collection
    {
        $rows = $this->event->attendee_types
            ->sortBy('name')
            ->values();

        if ($rows->isEmpty()) {
            return collect([(object) [
                'name' => 'Sin tipos de asistencia registrados',
                'age_range' => '—',
                'attendance' => 0,
            ]]);
        }

        return $rows;
    }

    /** @return list<string> */
    public function headings(): array
    {
        return [
            $this->entity_label,
            'Generado por',
            'Evento',
            'Tipo de evento',
            'Evento padre',
            'Categoría',
            'Fecha',
            'Día',
            'Horario',
            'Tipo de asistencia',
            'Rango de edad',
            'Asistencia',
            'Aclaración',
        ];
    }

    /** @return list<string|int> */
    public function map($row): array
    {
        $clarification = $this->event->multiDayContextLabel()
            ?? 'Asistencia del evento en la fecha indicada.';

        if (is_object($row) && ! isset($row->pivot)) {
            return [
                $this->business_name,
                $this->generated_by,
                $this->event->name,
                $this->event_type_label,
                $this->parent_event_label,
                $this->event->category?->name ?? '—',
                $this->event->dateRangeLabel(),
                $this->event->day ?: '—',
                $this->event->scheduleRangeLabel(),
                (string) ($row->name ?? '—'),
                (string) ($row->age_range ?? '—'),
                (int) ($row->attendance ?? 0),
                $clarification,
            ];
        }

        return [
            $this->business_name,
            $this->generated_by,
            $this->event->name,
            $this->event_type_label,
            $this->parent_event_label,
            $this->event->category?->name ?? '—',
            $this->event->dateRangeLabel(),
            $this->event->day ?: '—',
            $this->event->scheduleRangeLabel(),
            $row->name,
            $row->ageRangeLabel(),
            (int) $row->pivot->attendance,
            $clarification,
        ];
    }

    public function title(): string
    {
        return 'Asistencia';
    }
}
