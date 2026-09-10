<?php

namespace App\Livewire\Admin\Presentation\Academic\Courses;

use App\Support\Presentation\AcademicDemoData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Presentación — Estudiantes del curso')]
class Show extends Component
{
    public string $course_id = '';

    public function mount(string $course): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);

        abort_unless(AcademicDemoData::course($course) !== null, 404);

        $this->course_id = $course;
    }

    public function render()
    {
        return view('livewire.admin.presentation.academic.courses.show', [
            'course' => AcademicDemoData::course($this->course_id),
        ]);
    }
}
