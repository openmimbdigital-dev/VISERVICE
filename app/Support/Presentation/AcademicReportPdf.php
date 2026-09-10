<?php

namespace App\Support\Presentation;

use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocument;

class AcademicReportPdf
{
    public static function courses(): PdfDocument
    {
        return self::make('pdf.presentation.course-reports', [
            'title' => 'Reporte por curso',
            'rows' => AcademicDemoData::courseReports(),
        ]);
    }

    public static function course(string $course_id): PdfDocument
    {
        $report = AcademicDemoData::courseReport($course_id);

        abort_unless($report !== null, 404);

        return self::make('pdf.presentation.course-report', [
            'title' => 'Reporte de notas — '.$report['name'],
            'report' => $report,
        ]);
    }

    public static function students(?string $course_id = null): PdfDocument
    {
        $rows = collect(AcademicDemoData::studentReports())
            ->when($course_id, fn ($items) => $items->where('course_id', $course_id))
            ->values()
            ->all();

        $course_name = null;

        if ($course_id) {
            $course_name = AcademicDemoData::course($course_id)['name'] ?? null;
        }

        return self::make('pdf.presentation.student-reports', [
            'title' => $course_name ? 'Reporte por estudiantes — '.$course_name : 'Reporte por estudiantes',
            'rows' => $rows,
            'course_name' => $course_name,
        ]);
    }

    public static function student(int $student_id): PdfDocument
    {
        $report = AcademicDemoData::studentReport($student_id);

        abort_unless($report !== null, 404);

        return self::make('pdf.presentation.student-report', [
            'title' => 'Reporte de notas — '.$report['name'],
            'report' => $report,
        ]);
    }

    public static function output(PdfDocument $pdf): string
    {
        return $pdf->output();
    }

    /** @param  array<string, mixed>  $data */
    private static function make(string $view, array $data): PdfDocument
    {
        $user = auth()->user();

        return Pdf::loadView($view, array_merge($data, [
            'printed_by' => trim(($user?->first_name ?? '').' '.($user?->last_name ?? '')) ?: ($user?->username ?? '—'),
            'printed_at' => now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY h:mm a'),
        ]))->setPaper('letter');
    }
}
