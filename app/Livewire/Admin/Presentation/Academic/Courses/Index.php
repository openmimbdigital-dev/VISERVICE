<?php

namespace App\Livewire\Admin\Presentation\Academic\Courses;

use App\Support\Presentation\AcademicDemoData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Presentación — Cursos')]
class Index extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
    }

    public function render()
    {
        return view('livewire.admin.presentation.academic.courses.index', [
            'courses' => AcademicDemoData::coursesWithCounts(),
        ]);
    }
}
