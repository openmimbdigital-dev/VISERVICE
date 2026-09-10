<?php

namespace App\Livewire\Admin\Presentation\Reports;

use App\Support\Presentation\AcademicDemoData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Presentación — Reportes')]
class Index extends Component
{
    #[Url]
    public string $section = 'courses';

    public string $filter_course = '';

    public string $search = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);

        if (! in_array($this->section, ['courses', 'students'], true)) {
            $this->section = 'courses';
        }
    }

    public function setSection(string $section): void
    {
        if (! in_array($section, ['courses', 'students'], true)) {
            return;
        }

        $this->section = $section;
        $this->search = '';
        $this->filter_course = '';
    }

    public function render()
    {
        $course_reports = AcademicDemoData::courseReports();

        $student_reports = collect(AcademicDemoData::studentReports())
            ->when($this->filter_course !== '', fn ($items) => $items->where('course_id', $this->filter_course))
            ->when($this->search !== '', function ($items) {
                $term = mb_strtolower($this->search);

                return $items->filter(function (array $row) use ($term) {
                    return str_contains(mb_strtolower($row['name']), $term)
                        || str_contains($row['document'], $term);
                });
            })
            ->values()
            ->all();

        return view('livewire.admin.presentation.reports.index', [
            'course_reports' => $course_reports,
            'student_reports' => $student_reports,
            'courses' => AcademicDemoData::courses(),
        ]);
    }
}
