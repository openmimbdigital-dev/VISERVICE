<?php

namespace App\Models\Concerns;

use App\Support\WizardProgress;

trait HasWizardProgress
{
    public function progressPercent(): int
    {
        $total = max(1, (int) $this->final_step);

        return WizardProgress::percent(
            WizardProgress::completedSteps(
                (int) $this->step,
                $total,
                $this->isComplete()
            ),
            $total
        );
    }
}
