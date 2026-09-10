<?php

namespace App\Livewire\Admin\Presentation\Academic\Classroom;

use App\Support\Presentation\AcademicDemoData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Presentación — Mi aula')]
class Index extends Component
{
    public string $filter_shift = '';

    public string $filter_section = '';

    public string $filter_teacher = '';

    public bool $showModal = false;

    public string $new_name = '';

    public string $new_shift = 'morning';

    public string $new_section = 'A';

    public string $new_teacher = '';

    public string $new_schedule = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
    }

    public function openCreate(): void
    {
        $this->resetSubjectForm();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetSubjectForm();
    }

    public function saveSubject(): void
    {
        $this->validate();

        $colors = AcademicDemoData::subjectColors();
        $index = count(AcademicDemoData::subjects());

        AcademicDemoData::addSubject([
            'id' => 'sub-custom-'.uniqid(),
            'name' => trim($this->new_name),
            'shift' => $this->new_shift,
            'section' => $this->new_section,
            'teacher' => trim($this->new_teacher),
            'schedule' => trim($this->new_schedule) !== '' ? trim($this->new_schedule) : 'Por definir',
            'color' => $colors[$index % count($colors)],
        ]);

        $this->closeModal();
        $this->dispatch('swal', [
            'title' => 'Asignatura creada',
            'text' => 'Se añadió al aula (solo en esta sesión, no se guarda en la base de datos).',
            'icon' => 'success',
        ]);
    }

    public function resetFilters(): void
    {
        $this->filter_shift = '';
        $this->filter_section = '';
        $this->filter_teacher = '';
    }

    protected function rules(): array
    {
        return [
            'new_name' => 'required|string|max:80',
            'new_shift' => 'required|in:morning,afternoon',
            'new_section' => 'required|in:A,B,C',
            'new_teacher' => 'required|string|max:80',
            'new_schedule' => 'nullable|string|max:80',
        ];
    }

    protected function messages(): array
    {
        return [
            'new_name.required' => 'El nombre de la asignatura es obligatorio.',
            'new_shift.required' => 'La jornada es obligatoria.',
            'new_shift.in' => 'Selecciona una jornada válida.',
            'new_section.required' => 'La sección es obligatoria.',
            'new_section.in' => 'Selecciona una sección válida.',
            'new_teacher.required' => 'La miss es obligatoria.',
        ];
    }

    public function render()
    {
        $subjects = collect(AcademicDemoData::subjects())
            ->when($this->filter_shift !== '', fn ($items) => $items->where('shift', $this->filter_shift))
            ->when($this->filter_section !== '', fn ($items) => $items->where('section', $this->filter_section))
            ->when($this->filter_teacher !== '', fn ($items) => $items->where('teacher', $this->filter_teacher))
            ->values()
            ->all();

        $shifts = AcademicDemoData::shifts();

        $subjects = array_map(function (array $subject) use ($shifts) {
            $subject['shift_label'] = $shifts[$subject['shift']] ?? $subject['shift'];
            $subject['activities_count'] = AcademicDemoData::activitiesCount($subject['id']);

            return $subject;
        }, $subjects);

        return view('livewire.admin.presentation.academic.classroom.index', [
            'subjects' => $subjects,
            'shifts' => $shifts,
            'sections' => AcademicDemoData::sections(),
            'teachers' => AcademicDemoData::teachers(),
            'active_filters' => (int) ($this->filter_shift !== '') + (int) ($this->filter_section !== '') + (int) ($this->filter_teacher !== ''),
        ]);
    }

    private function resetSubjectForm(): void
    {
        $this->new_name = '';
        $this->new_shift = 'morning';
        $this->new_section = 'A';
        $this->new_teacher = AcademicDemoData::teachers()[0] ?? '';
        $this->new_schedule = '';
        $this->resetValidation();
    }
}
