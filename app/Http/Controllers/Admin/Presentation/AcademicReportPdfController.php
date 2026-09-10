<?php

namespace App\Http\Controllers\Admin\Presentation;

use App\Http\Controllers\Controller;
use App\Support\Presentation\AcademicDemoData;
use App\Support\Presentation\AcademicReportPdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AcademicReportPdfController extends Controller
{
    public function courses(): Response
    {
        $this->authorizeSuperAdmin();

        return AcademicReportPdf::courses()->download('reporte-por-curso.pdf');
    }

    public function course(string $course): Response
    {
        $this->authorizeSuperAdmin();
        abort_unless(AcademicDemoData::courseReport($course) !== null, 404);

        $report = AcademicDemoData::courseReport($course);

        return AcademicReportPdf::course($course)->download('reporte-curso-'.$report['code'].'.pdf');
    }

    public function students(Request $request): Response
    {
        $this->authorizeSuperAdmin();

        $course_id = (string) $request->query('course', '');
        $course_id = $course_id !== '' ? $course_id : null;

        $suffix = $course_id ? '-'.$course_id : '';

        return AcademicReportPdf::students($course_id)->download('reporte-estudiantes'.$suffix.'.pdf');
    }

    public function student(int $student): Response
    {
        $this->authorizeSuperAdmin();

        $report = AcademicDemoData::studentReport($student);
        abort_unless($report !== null, 404);

        return AcademicReportPdf::student($student)->download('reporte-notas-'.$report['document'].'.pdf');
    }

    private function authorizeSuperAdmin(): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
    }
}
