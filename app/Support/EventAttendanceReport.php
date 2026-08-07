<?php

namespace App\Support;

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EventAttendanceReport
{
    /**
     * Totales generales de asistencia por tipo (todos los eventos visibles al usuario).
     *
     * @return array{labels: list<string>, values: list<int>}
     */
    public static function generalChartByAttendeeType(?User $user = null): array
    {
        $user ??= auth()->user();

        $event_ids = Event::query()
            ->forAuthUser($user)
            ->where('multi_day', false)
            ->pluck('id');

        if ($event_ids->isEmpty()) {
            return ['labels' => [], 'values' => []];
        }

        $rows = DB::table('event_attendee_type')
            ->join('attendee_types', 'attendee_types.id', '=', 'event_attendee_type.attendee_type_id')
            ->whereIn('event_attendee_type.event_id', $event_ids)
            ->whereNull('attendee_types.deleted_at')
            ->groupBy('attendee_types.id', 'attendee_types.name')
            ->orderBy('attendee_types.name')
            ->select([
                'attendee_types.name',
                DB::raw('COALESCE(SUM(event_attendee_type.attendance), 0) as total_attendance'),
            ])
            ->get();

        return [
            'labels' => $rows->pluck('name')->map(fn ($name) => (string) $name)->all(),
            'values' => $rows->map(fn ($row) => (int) $row->total_attendance)->all(),
        ];
    }

    /**
     * Datos de gráfica para un evento concreto.
     *
     * @return array{labels: list<string>, values: list<int>, rows: Collection}
     */
    public static function chartForEvent(Event $event): array
    {
        $rows = $event->attendee_types
            ->sortBy('name')
            ->values();

        return [
            'labels' => $rows->pluck('name')->map(fn ($name) => (string) $name)->all(),
            'values' => $rows->map(fn ($type) => (int) $type->pivot->attendance)->all(),
            'rows' => $rows,
        ];
    }
}
