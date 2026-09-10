<?php

namespace App\Livewire\Admin\Presentation\Academic\Classroom;

use App\Support\Presentation\AcademicDemoData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Presentación — Actividades')]
class Show extends Component
{
    public string $subject_id = '';

    public function mount(string $subject): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
        abort_unless(AcademicDemoData::subject($subject) !== null, 404);

        $this->subject_id = $subject;
    }

    public function render()
    {
        $types = AcademicDemoData::activityTypes();
        $counts = [];

        foreach (array_keys($types) as $type) {
            $counts[$type] = count(AcademicDemoData::activitiesForSubject($this->subject_id, $type));
        }

        return view('livewire.admin.presentation.academic.classroom.show', [
            'subject' => AcademicDemoData::subject($this->subject_id),
            'types' => $types,
            'counts' => $counts,
            'activities' => AcademicDemoData::activitiesForSubject($this->subject_id),
        ]);
    }
}
