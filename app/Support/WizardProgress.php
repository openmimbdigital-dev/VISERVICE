<?php

namespace App\Support;

class WizardProgress
{
    /**
     * Pasos ya persistidos. El paso visible de la UI no cuenta hasta guardarlo.
     */
    public static function completedSteps(int $persisted_step, int $total_steps, bool $is_complete): int
    {
        $total = max(1, $total_steps);

        if ($is_complete) {
            return $total;
        }

        return max(0, min($persisted_step - 1, $total));
    }

    public static function percent(int $completed_steps, int $total_steps): int
    {
        $total = max(1, $total_steps);

        return (int) min(100, round(($completed_steps / $total) * 100));
    }

    /** Paso persistido en el que quedó el flujo (1…total). */
    public static function currentStep(int $persisted_step, int $total_steps): int
    {
        $total = max(1, $total_steps);

        return max(1, min(max(0, $persisted_step), $total));
    }

    public static function datatableBadge(int $persisted_step, int $total_steps, bool $is_complete): string
    {
        $total   = max(1, $total_steps);
        $step    = self::currentStep($persisted_step, $total);
        $percent = self::percent(
            self::completedSteps($persisted_step, $total, $is_complete),
            $total
        );

        if ($is_complete) {
            $label = 'Completo';
            $class = 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20';
        } else {
            $label = 'Paso '.$step.'/'.$total;
            $class = 'bg-amber-50 text-amber-800 ring-1 ring-amber-600/20';
        }

        return '<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium '.$class.'">'
            .e($label)
            .' · '.$percent.'%</span>';
    }

    /**
     * @return array{
     *     completed_steps: int,
     *     progress: int,
     *     progress_circumference: float,
     *     progress_offset: float
     * }
     */
    public static function viewData(int $persisted_step, int $total_steps, bool $is_complete, float $radius = 30.0): array
    {
        $completed_steps = self::completedSteps($persisted_step, $total_steps, $is_complete);
        $progress        = self::percent($completed_steps, $total_steps);
        $circumference   = round(2 * M_PI * $radius, 2);

        return [
            'completed_steps'        => $completed_steps,
            'progress'               => $progress,
            'progress_circumference' => $circumference,
            'progress_offset'        => round($circumference * (1 - $progress / 100), 2),
        ];
    }
}
