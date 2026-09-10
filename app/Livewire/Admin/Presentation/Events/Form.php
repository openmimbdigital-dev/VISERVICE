<?php

namespace App\Livewire\Admin\Presentation\Events;

use App\Support\Presentation\EventDemoData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Presentación — Nuevo evento')]
class Form extends Component
{
    public string $title = '';

    public string $date = '';

    public string $start_time = '08:00';

    public string $end_time = '09:00';

    public string $location = '';

    public string $description = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
        $this->date = now()->format('Y-m-d');
    }

    public function save(): void
    {
        $this->validate();

        $id = 'evt-custom-'.uniqid();

        EventDemoData::addEvent([
            'id' => $id,
            'title' => trim($this->title),
            'date' => $this->date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'location' => trim($this->location) !== '' ? trim($this->location) : 'Por definir',
            'description' => trim($this->description) !== '' ? trim($this->description) : 'Sin descripción',
        ]);

        $this->redirectRoute('admin.presentation.events.show', $id, navigate: true);
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:120',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'nullable|string|max:80',
            'description' => 'nullable|string|max:600',
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'date.required' => 'La fecha es obligatoria.',
            'start_time.required' => 'La hora de inicio es obligatoria.',
            'end_time.required' => 'La hora de fin es obligatoria.',
            'end_time.after' => 'La hora de fin debe ser posterior al inicio.',
        ];
    }

    public function render()
    {
        return view('livewire.admin.presentation.events.form');
    }
}
