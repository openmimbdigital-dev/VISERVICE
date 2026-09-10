<?php

namespace App\Livewire\Admin\Presentation\Enrollment;

use App\Support\Presentation\AcademicDemoData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Presentación — Inscripción')]
class Index extends Component
{
    public string $filter_status = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
    }

    public function render()
    {
        $all = AcademicDemoData::enrollments();

        $enrollments = collect($all)
            ->when($this->filter_status !== '', fn ($items) => $items->where('status', $this->filter_status))
            ->values()
            ->all();

        return view('livewire.admin.presentation.enrollment.index', [
            'enrollments' => $enrollments,
            'statuses' => AcademicDemoData::enrollmentStatuses(),
            'stats' => [
                'completed' => collect($all)->where('status', 'completed')->count(),
                'documents_pending' => collect($all)->where('status', 'documents_pending')->count(),
                'pending_payment' => collect($all)->where('status', 'pending_payment')->count(),
            ],
        ]);
    }
}
