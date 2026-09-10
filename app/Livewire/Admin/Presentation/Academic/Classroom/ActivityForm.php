<?php

namespace App\Livewire\Admin\Presentation\Academic\Classroom;

use App\Support\Presentation\AcademicDemoData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Presentación — Nueva actividad')]
class ActivityForm extends Component
{
    public string $subject_id = '';

    public string $activity_type = '';

    public string $title = '';

    public string $due_date = '';

    public string $max_score = '5';

    public string $description = '';

    public function mount(string $subject, string $type): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
        abort_unless(AcademicDemoData::subject($subject) !== null, 404);
        abort_unless(array_key_exists($type, AcademicDemoData::activityTypes()), 404);

        $this->subject_id = $subject;
        $this->activity_type = $type;
        $this->due_date = now()->addDays(7)->format('Y-m-d');
    }

    public function save(): void
    {
        $this->validate();

        AcademicDemoData::addActivity([
            'id' => 'act-custom-'.uniqid(),
            'subject_id' => $this->subject_id,
            'type' => $this->activity_type,
            'title' => trim($this->title),
            'due_date' => $this->due_date,
            'max_score' => (float) $this->max_score,
            'description' => trim($this->description) !== '' ? trim($this->description) : 'Sin descripción',
        ]);

        $this->redirectRoute(
            'admin.presentation.academic.classroom.activities.index',
            [$this->subject_id, $this->activity_type],
            navigate: true
        );
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:120',
            'due_date' => 'required|date',
            'max_score' => 'required|numeric|min:1|max:5',
            'description' => 'nullable|string|max:240',
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'due_date.required' => 'La fecha de entrega es obligatoria.',
            'due_date.date' => 'Indica una fecha válida.',
            'max_score.required' => 'La nota máxima es obligatoria.',
            'max_score.numeric' => 'La nota máxima debe ser un número.',
        ];
    }

    public function render()
    {
        $types = AcademicDemoData::activityTypes();

        return view('livewire.admin.presentation.academic.classroom.activity-form', [
            'subject' => AcademicDemoData::subject($this->subject_id),
            'type_label' => $types[$this->activity_type],
        ]);
    }
}
