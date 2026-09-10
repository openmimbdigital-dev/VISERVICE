<?php

namespace App\Livewire\Admin\Presentation\Enrollment;

use App\Support\Presentation\AcademicDemoData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Presentación — Inscripción')]
class Show extends Component
{
    public string $enrollment_id = '';

    public function mount(string $enrollment): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
        abort_unless(AcademicDemoData::enrollment($enrollment) !== null, 404);

        $this->enrollment_id = $enrollment;
    }

    public function render()
    {
        return view('livewire.admin.presentation.enrollment.show', [
            'enrollment' => AcademicDemoData::enrollment($this->enrollment_id),
        ]);
    }
}
