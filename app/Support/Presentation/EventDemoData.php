<?php

namespace App\Support\Presentation;

class EventDemoData
{
    public const SESSION_KEY = 'presentation.events.created';

    /** @return list<array<string, mixed>> */
    public static function defaultEvents(): array
    {
        return [
            [
                'id' => 'evt-1',
                'title' => 'Reunión de padres 10°',
                'date' => '2026-09-12',
                'start_time' => '07:00',
                'end_time' => '08:30',
                'location' => 'Auditorio principal',
                'description' => 'Encuentro con acudientes del grado décimo para revisar el plan académico del primer periodo, asistencia y acuerdos de convivencia.',
            ],
            [
                'id' => 'evt-2',
                'title' => 'Izada de bandera',
                'date' => '2026-09-15',
                'start_time' => '06:45',
                'end_time' => '07:30',
                'location' => 'Patio central',
                'description' => 'Acto cívico con honores a la bandera e izada a cargo del grado 9° A. Participan los cursos de la jornada de la mañana.',
            ],
            [
                'id' => 'evt-3',
                'title' => 'Feria de ciencias',
                'date' => '2026-09-18',
                'start_time' => '09:00',
                'end_time' => '13:00',
                'location' => 'Canchas y laboratorios',
                'description' => 'Exposición de proyectos de Ciencias Naturales. Cada grupo presenta un experimento y una ficha de resultados. Abierta a familias.',
            ],
            [
                'id' => 'evt-4',
                'title' => 'Consejo académico',
                'date' => '2026-09-22',
                'start_time' => '14:00',
                'end_time' => '16:00',
                'location' => 'Sala de profesores',
                'description' => 'Revisión de planes de mejoramiento, cortes de notas y calendario de evaluaciones del segundo mes.',
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function events(): array
    {
        $created = session(self::SESSION_KEY, []);

        if (! is_array($created)) {
            $created = [];
        }

        $items = array_values(array_merge(self::defaultEvents(), $created));

        usort($items, fn (array $a, array $b) => strcmp($a['date'].$a['start_time'], $b['date'].$b['start_time']));

        return $items;
    }

    /** @return array<string, mixed>|null */
    public static function event(string $id): ?array
    {
        foreach (self::events() as $event) {
            if ($event['id'] === $id) {
                return $event;
            }
        }

        return null;
    }

    /** @param array<string, mixed> $event */
    public static function addEvent(array $event): void
    {
        $created = session(self::SESSION_KEY, []);

        if (! is_array($created)) {
            $created = [];
        }

        $created[] = $event;
        session([self::SESSION_KEY => $created]);
    }

    /** @return array<string, list<array<string, mixed>>> */
    public static function eventsByDate(): array
    {
        $grouped = [];

        foreach (self::events() as $event) {
            $grouped[$event['date']][] = $event;
        }

        return $grouped;
    }
}
