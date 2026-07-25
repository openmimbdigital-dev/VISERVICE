<?php

namespace App\Actions\Events;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Lorisleiva\Actions\Concerns\AsAction;

class CalculatePeriodicEventDatesAction
{
    use AsAction;

    /**
     * Calcula las fechas (Y-m-d) de los días de la semana dentro del rango de meses de un año.
     *
     * @param  list<int>  $weekdays  ISO: 1 = lunes … 7 = domingo
     * @return list<string>
     */
    public function handle(int $year, int $start_month, int $end_month, array $weekdays): array
    {
        abort_unless($year >= 2000 && $year <= 2100, 422);
        abort_unless($start_month >= 1 && $start_month <= 12, 422);
        abort_unless($end_month >= 1 && $end_month <= 12, 422);
        abort_unless($start_month <= $end_month, 422);

        $weekdays = array_values(array_unique(array_map('intval', $weekdays)));
        $weekdays = array_values(array_filter(
            $weekdays,
            fn (int $day) => $day >= 1 && $day <= 7
        ));

        abort_unless($weekdays !== [], 422);

        $start = Carbon::create($year, $start_month, 1)->startOfDay();
        $end = Carbon::create($year, $end_month, 1)->endOfMonth()->startOfDay();

        $dates = [];

        foreach (CarbonPeriod::create($start, $end) as $day) {
            if (in_array($day->isoWeekday(), $weekdays, true)) {
                $dates[] = $day->toDateString();
            }
        }

        return $dates;
    }
}
