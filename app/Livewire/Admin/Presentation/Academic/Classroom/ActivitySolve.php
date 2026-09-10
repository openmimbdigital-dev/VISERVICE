<?php

namespace App\Livewire\Admin\Presentation\Academic\Classroom;

use App\Support\Presentation\AcademicDemoData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Presentación — Resolver actividad')]
class ActivitySolve extends Component
{
    public string $subject_id = '';

    public string $activity_type = '';

    public string $activity_id = '';

    /** @var array<string, string> */
    public array $answers = [];

    public string $file_name = '';

    public function mount(string $subject, string $type, string $activity): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);

        $record = AcademicDemoData::activity($activity);
        abort_unless($record !== null, 404);
        abort_unless($record['subject_id'] === $subject && $record['type'] === $type, 404);

        $this->subject_id = $subject;
        $this->activity_type = $type;
        $this->activity_id = $activity;
        $this->hydrateAnswers($record);
    }

    public function save(): void
    {
        $record = $this->activityRecord();
        $this->validate($this->rulesFor($record), $this->messagesFor($record));

        $submission = [
            'answers' => $this->answers,
            'file_name' => trim($this->file_name),
            'submitted_at' => now()->toDateTimeString(),
            'score' => $this->scoreFor($record),
            'status' => $record['type'] === 'homework' ? 'pending_review' : 'graded',
        ];

        AcademicDemoData::saveSubmission($this->activity_id, $submission);
        $this->dispatch('swal', ['title' => 'Actividad enviada', 'icon' => 'success']);
    }

    public function retry(): void
    {
        AcademicDemoData::clearSubmission($this->activity_id);
        $this->hydrateAnswers($this->activityRecord());
        $this->file_name = '';
        $this->resetValidation();
    }

    public function setFileName(string $name): void
    {
        $this->file_name = $name;
    }

    public function render()
    {
        $record = $this->activityRecord();
        $subject = AcademicDemoData::subject($this->subject_id);

        return view('livewire.admin.presentation.academic.classroom.activity-solve', [
            'subject' => $subject,
            'activity_record' => $record,
            'submission' => $record['submission'],
        ]);
    }

    /** @return array<string, mixed> */
    private function activityRecord(): array
    {
        $record = AcademicDemoData::activity($this->activity_id);
        abort_unless($record !== null, 404);

        return $record;
    }

    /** @param array<string, mixed> $record */
    private function hydrateAnswers(array $record): void
    {
        $submitted = $record['submission']['answers'] ?? [];
        $answers = [];

        foreach ($record['items'] as $item) {
            $answers[$item['id']] = (string) ($submitted[$item['id']] ?? '');
        }

        $this->answers = $answers;
        $this->file_name = (string) ($record['submission']['file_name'] ?? '');
    }

    /**
     * @param  array<string, mixed>  $record
     * @return array<string, string>
     */
    private function rulesFor(array $record): array
    {
        $rules = [];

        foreach ($record['items'] as $item) {
            $rules['answers.'.$item['id']] = 'required|string|max:4000';
        }

        if ($record['type'] === 'homework') {
            $rules['file_name'] = 'nullable|string|max:120';
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $record
     * @return array<string, string>
     */
    private function messagesFor(array $record): array
    {
        $messages = [
            'file_name.max' => 'El nombre del archivo es demasiado largo.',
        ];

        foreach ($record['items'] as $item) {
            $messages['answers.'.$item['id'].'.required'] = $item['kind'] === 'choice'
                ? 'Selecciona una opción.'
                : 'Completa esta respuesta.';
        }

        return $messages;
    }

    /** @param array<string, mixed> $record */
    private function scoreFor(array $record): ?float
    {
        $max = (float) $record['max_score'];

        if ($record['type'] === 'homework') {
            return null;
        }

        if ($record['type'] === 'evaluation') {
            $total = count($record['items']);
            if ($total === 0) {
                return $max;
            }

            $correct = 0;
            foreach ($record['items'] as $item) {
                if (($this->answers[$item['id']] ?? '') === ($item['correct'] ?? '')) {
                    $correct++;
                }
            }

            return round(($correct / $total) * $max, 1);
        }

        $total = count($record['items']);
        if ($total === 0) {
            return $max;
        }

        $filled = collect($record['items'])
            ->filter(fn (array $item) => trim((string) ($this->answers[$item['id']] ?? '')) !== '')
            ->count();

        return round(($filled / $total) * $max, 1);
    }
}
