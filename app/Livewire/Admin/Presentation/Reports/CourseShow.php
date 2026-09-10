<?php

namespace App\Livewire\Admin\Presentation\Reports;

use App\Mail\Presentation\GradeReportMail;
use App\Support\Presentation\AcademicDemoData;
use App\Support\Presentation\AcademicReportPdf;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Throwable;

#[Layout('layouts.app')]
#[Title('Presentación — Reporte por curso')]
class CourseShow extends Component
{
    public string $course_id = '';

    public bool $showEmailModal = false;

    public string $email = '';

    public string $email_note = '';

    public function mount(string $course): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
        abort_unless(AcademicDemoData::courseReport($course) !== null, 404);

        $this->course_id = $course;
    }

    public function openEmailModal(): void
    {
        $this->email = auth()->user()?->email ?? '';
        $this->email_note = '';
        $this->showEmailModal = true;
        $this->resetValidation();
    }

    public function closeEmailModal(): void
    {
        $this->showEmailModal = false;
        $this->resetValidation();
    }

    public function sendEmail(): void
    {
        $this->validate([
            'email' => 'required|email',
            'email_note' => 'nullable|string|max:240',
        ], [
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'Indica un correo válido.',
        ]);

        $report = AcademicDemoData::courseReport($this->course_id);
        abort_unless($report !== null, 404);

        try {
            Mail::to($this->email)->send(new GradeReportMail(
                report_title: 'Reporte de notas — '.$report['name'],
                recipient_name: $report['name'],
                note: trim($this->email_note),
                pdf_contents: AcademicReportPdf::output(AcademicReportPdf::course($this->course_id)),
                pdf_filename: 'reporte-curso-'.$report['code'].'.pdf',
            ));
        } catch (Throwable $exception) {
            report($exception);
            $this->dispatch('swal', [
                'title' => 'No se pudo enviar',
                'text' => 'Revisa la configuración de correo e inténtalo de nuevo.',
                'icon' => 'error',
            ]);

            return;
        }

        $this->closeEmailModal();
        $this->dispatch('swal', [
            'title' => 'Reporte enviado',
            'text' => 'Se envió el PDF a '.$this->email.'.',
            'icon' => 'success',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.presentation.reports.course-show', [
            'report' => AcademicDemoData::courseReport($this->course_id),
        ]);
    }
}
