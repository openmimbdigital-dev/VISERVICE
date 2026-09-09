<?php

namespace App\Livewire\Concerns;

use App\Support\WizardProgress;

trait TracksWizardProgress
{
    /** Último paso persistido en BD. Cero si el registro aún no existe. */
    public int $persisted_step = 0;

    public bool $saved_complete = false;

    protected function syncWizardProgress(object $record): void
    {
        $this->persisted_step = (int) $record->step;
        $this->saved_complete = method_exists($record, 'isComplete') && $record->isComplete();
    }

    /**
     * @return array{
     *     completed_steps: int,
     *     progress: int,
     *     progress_circumference: float,
     *     progress_offset: float
     * }
     */
    protected function wizardProgressViewData(int $total_steps): array
    {
        return WizardProgress::viewData(
            $this->persisted_step,
            $total_steps,
            $this->saved_complete
        );
    }
}
