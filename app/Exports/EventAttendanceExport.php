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

class EventAttendanceExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithTitle
{
    private string $entity_label;

    private string $business_name;

    private string $generated_by;

    public function __construct(
        private readonly Event $event,
        private readonly ?User $user = null,
    ) {
        $business = $this->event->business;
        $is_church = $business?->organization_type?->label === 'iglesia';

        $this->entity_label = $is_church ? 'Iglesia' : 'Negocio';
        $this->business_name = $business?->name ?: '—';
        $this->generated_by = trim(($user?->first_name ?? '').' '.($user?->last_name ?? '')) ?: ($user?->username ?? '—');
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
            'Categoría',
            'Fecha',
            'Día',
            'Horario',
            'Tipo de asistencia',
            'Rango de edad',
            'Asistencia',
        ];
    }

    /** @return list<string|int> */
    public function map($row): array
    {
        if (is_object($row) && ! isset($row->pivot)) {
            return [
                $this->business_name,
                $this->generated_by,
                $this->event->name,
                $this->event->category?->name ?? '—',
                $this->event->date?->format('d/m/Y') ?? '—',
                $this->event->day ?: '—',
                $this->event->scheduleRangeLabel(),
                (string) ($row->name ?? '—'),
                (string) ($row->age_range ?? '—'),
                (int) ($row->attendance ?? 0),
            ];
        }

        return [
            $this->business_name,
            $this->generated_by,
            $this->event->name,
            $this->event->category?->name ?? '—',
            $this->event->date?->format('d/m/Y') ?? '—',
            $this->event->day ?: '—',
            $this->event->scheduleRangeLabel(),
            $row->name,
            $row->ageRangeLabel(),
            (int) $row->pivot->attendance,
        ];
    }

    public function title(): string
    {
        return 'Asistencia';
    }
}
