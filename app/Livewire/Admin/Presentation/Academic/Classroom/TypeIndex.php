<?php

namespace App\Livewire\Admin\Presentation\Academic\Classroom;

use App\Support\Presentation\AcademicDemoData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Presentación — Actividades')]
class TypeIndex extends Component
{
    public string $subject_id = '';

    public string $activity_type = '';

    public function mount(string $subject, string $type): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
        abort_unless(AcademicDemoData::subject($subject) !== null, 404);
        abort_unless(array_key_exists($type, AcademicDemoData::activityTypes()), 404);

        $this->subject_id = $subject;
        $this->activity_type = $type;
    }

    public function render()
    {
        $types = AcademicDemoData::activityTypes();

        return view('livewire.admin.presentation.academic.classroom.type-index', [
            'subject' => AcademicDemoData::subject($this->subject_id),
            'activities' => AcademicDemoData::activitiesForSubject($this->subject_id, $this->activity_type),
            'type_label' => $types[$this->activity_type],
        ]);
    }
}
