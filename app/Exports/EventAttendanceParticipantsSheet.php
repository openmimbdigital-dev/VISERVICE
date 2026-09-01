<?php

namespace App\Exports;

use App\Models\Event;
use App\Models\User;
use App\Support\EventAttendanceReport;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class EventAttendanceParticipantsSheet implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithTitle
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
        $rows = EventAttendanceReport::attendedParticipantsForEvent($this->event);

        if ($rows->isEmpty()) {
            return collect([(object) [
                'empty' => true,
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
            'Fecha del evento',
            'Participante',
            'Documento',
            'Correo',
            'Hora de registro',
            'Estado',
        ];
    }

    /** @return list<string> */
    public function map($row): array
    {
        if (is_object($row) && isset($row->empty)) {
            return [
                $this->business_name,
                $this->generated_by,
                $this->event->name,
                $this->event->dateRangeLabel(),
                'Sin participantes registrados',
                '—',
                '—',
                '—',
                '—',
            ];
        }

        $participant = $row->attendable;
        $hour = $row->attendance_hour
            ? Carbon::parse($row->attendance_hour)->format('h:i a')
            : '—';

        return [
            $this->business_name,
            $this->generated_by,
            $this->event->name,
            $this->event->dateRangeLabel(),
            $participant?->full_name ?: '—',
            $participant?->document_number ?: '—',
            $participant?->email ?: '—',
            $hour,
            'Asistió',
        ];
    }

    public function title(): string
    {
        return 'Participantes';
    }
}
