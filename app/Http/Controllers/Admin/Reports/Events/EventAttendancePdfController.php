<?php

namespace App\Http\Controllers\Admin\Reports\Events;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use App\Support\EventAttendanceReport;
use App\Support\EventsAccess;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class EventAttendancePdfController extends Controller
{
    public function __invoke(EventCategory $eventCategory, Event $event): Response
    {
        EventsAccess::authorizeExportAttendanceReport();

        $event = Event::query()
            ->forAuthUser()
            ->where('event_category_id', $eventCategory->id)
            ->with([
                'business.organization_type:id,label',
                'category:id,name,type',
                'teams:id,name',
                'attendee_types' => fn ($query) => $query->orderBy('name'),
            ])
            ->findOrFail($event->id);

        $user = auth()->user();
        $chart = EventAttendanceReport::chartForEvent($event);
        $attendance_total = array_sum($chart['values']);
        $max_attendance = max($chart['values'] ?: [0]);

        $business = $event->business;
        $is_church = $business?->organization_type?->label === 'iglesia';
        $entity_label = $is_church ? 'Iglesia' : 'Negocio';

        $filename = 'asistencia-evento-'.$event->id.'-'.$event->date?->format('Y-m-d').'.pdf';

        return Pdf::loadView('pdf.event-attendance-report', [
            'event' => $event,
            'event_category' => $eventCategory,
            'business' => $business,
            'entity_label' => $entity_label,
            'is_church' => $is_church,
            'logo_path' => $this->resolveLogoPath($business?->logo),
            'attendance_rows' => $chart['rows'],
            'chart_labels' => $chart['labels'],
            'chart_values' => $chart['values'],
            'attendance_total' => $attendance_total,
            'max_attendance' => $max_attendance > 0 ? $max_attendance : 1,
            'printed_by' => trim(($user?->first_name ?? '').' '.($user?->last_name ?? '')) ?: ($user?->username ?? '—'),
            'printed_by_roles' => $user?->getRoleNames()->implode(', ') ?: '—',
            'printed_at' => now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY h:mm a'),
            'title' => 'Informe de asistencia — '.$event->name,
        ])
            ->setPaper('letter')
            ->download($filename);
    }

    private function resolveLogoPath(?string $stored_path): ?string
    {
        if (blank($stored_path)) {
            return null;
        }

        $stored_path = str_replace('\\', '/', $stored_path);

        if (! Storage::disk('public')->exists($stored_path)) {
            return null;
        }

        $absolute_path = Storage::disk('public')->path($stored_path);
        $mime = mime_content_type($absolute_path) ?: 'image/png';
        $contents = Storage::disk('public')->get($stored_path);

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }
}
