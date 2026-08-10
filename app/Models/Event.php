<?php

namespace App\Models;

use App\Support\EventsAccess;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Event extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'event_category_id',
        'parent_id',
        'name',
        'description',
        'date_start',
        'date_end',
        'day',
        'start_time',
        'end_time',
        'active',
        'multi_day',
        'attendance_enabled',
        'participation_enabled',
        'attendance_closed',
        'participation_closed',

    ];

    protected function casts(): array
    {
        return [
            'date_start' => 'date',
            'date_end' => 'date',
            'attendance_enabled' => 'boolean',
            'participation_enabled' => 'boolean',
            'attendance_closed' => 'boolean',
            'participation_closed' => 'boolean',
            'multi_day' => 'boolean',
            'active' => 'boolean',

        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function attendee_types(): BelongsToMany
    {
        return $this->belongsToMany(AttendeeType::class, 'event_attendee_type')
            ->withPivot('attendance')
            ->withTimestamps();
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(EventTeam::class, 'event_event_team')
            ->withTimestamps();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(EventAttendance::class);
    }

    public function attending_users(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'attendable', 'event_attendances')
            ->withPivot(['date_event', 'attendance_hour', 'attendance'])
            ->wherePivotNull('deleted_at')
            ->withTimestamps();
    }

    public function attending_participants(): MorphToMany
    {
        return $this->morphedByMany(Participant::class, 'attendable', 'event_attendances')
            ->withPivot(['date_event', 'attendance_hour', 'attendance'])
            ->wherePivotNull('deleted_at')
            ->withTimestamps();
    }

    public function scopeForAuthUser(Builder $query, ?User $user = null): Builder
    {
        $user ??= auth()->user();

        if ($user?->hasRole('superAdmin')) {
            return $query;
        }

        $business_ids = $user?->businessIds() ?? [];

        if ($business_ids === []) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereIn('events.business_id', $business_ids);
    }

    public function canEdit(?User $user = null): bool
    {
        return EventsAccess::canEditEvent($this, $user);
    }

    public function canDelete(?User $user = null): bool
    {
        return EventsAccess::canDeleteEvent($this, $user);
    }

    /**
     * La toma de asistencia ya inició si existe al menos un registro en event_attendee_type
     * (en el propio evento o en alguno de sus días hijos).
     */
    public function hasStartedAttendance(): bool
    {
        if ($this->attendee_types()->exists()) {
            return true;
        }

        if ($this->parent_id !== null) {
            return false;
        }

        return $this->children()
            ->whereHas('attendee_types')
            ->exists();
    }

    public function isWithinSchedule(?CarbonInterface $now = null): bool
    {
        $now = $this->resolvedNow($now);

        if ($this->date_start === null || $this->date_end === null) {
            return false;
        }

        return $now->betweenIncluded(
            $this->scheduleStartAt($now),
            $this->scheduleEndAt($now)
        );
    }

    /**
     * @return array{available: bool, message: string|null}
     */
    public function attendanceCaptureState(?CarbonInterface $now = null): array
    {
        return $this->captureState(
            enabled: (bool) $this->attendance_enabled,
            subject: 'asistencia',
            now: $now
        );
    }

    /**
     * @return array{available: bool, message: string|null}
     */
    public function participationCaptureState(?CarbonInterface $now = null): array
    {
        return $this->captureState(
            enabled: (bool) $this->participation_enabled,
            subject: 'participación',
            now: $now
        );
    }

    /**
     * @return array{available: bool, message: string|null}
     */
    private function captureState(bool $enabled, string $subject, ?CarbonInterface $now = null): array
    {
        $now = $this->resolvedNow($now);
        $date_label = $this->dateRangeLabel();
        $start_label = $this->timeLabel($this->start_time);
        $end_label = $this->timeLabel($this->end_time);

        if (! $enabled) {
            return [
                'available' => false,
                'message' => "La toma de {$subject} no está habilitada para este evento.",
            ];
        }

        if ($this->date_start === null || $this->date_end === null) {
            return [
                'available' => false,
                'message' => "No podemos abrir la toma de {$subject} porque el evento no tiene fecha definida.",
            ];
        }

        if ($now->lt($this->date_start->copy()->startOfDay())) {
            return [
                'available' => false,
                'message' => "La toma de {$subject} estará disponible durante el evento ({$date_label}), entre {$start_label} y {$end_label}.",
            ];
        }

        if ($now->gt($this->date_end->copy()->endOfDay())) {
            return [
                'available' => false,
                'message' => "La toma de {$subject} solo estuvo disponible durante el evento ({$date_label}).",
            ];
        }

        if ($now->lt($this->scheduleStartAt($now))) {
            return [
                'available' => false,
                'message' => "Aún no inicia el horario del evento. Podrás tomar {$subject} a partir de las {$start_label}.",
            ];
        }

        if ($now->gt($this->scheduleEndAt($now))) {
            return [
                'available' => false,
                'message' => "El horario del evento ya finalizó. La toma de {$subject} solo estuvo disponible entre {$start_label} y {$end_label}.",
            ];
        }

        return [
            'available' => true,
            'message' => null,
        ];
    }

    public function startTimeLabel(): string
    {
        return $this->timeLabel($this->start_time);
    }

    public function endTimeLabel(): string
    {
        return $this->timeLabel($this->end_time);
    }

    public function scheduleRangeLabel(): string
    {
        return $this->startTimeLabel().' – '.$this->endTimeLabel();
    }

    public function dateRangeLabel(): string
    {
        if ($this->date_start === null) {
            return '—';
        }

        $start_label = $this->date_start->format('d/m/Y');

        if ($this->date_end === null || $this->date_start->isSameDay($this->date_end)) {
            return $start_label;
        }

        return $start_label.' – '.$this->date_end->format('d/m/Y');
    }

    public function isMultiDayChild(): bool
    {
        return $this->parent_id !== null;
    }

    public function multiDayContextLabel(): ?string
    {
        if (! $this->isMultiDayChild()) {
            return null;
        }

        $parent = $this->relationLoaded('parent')
            ? $this->parent
            : $this->parent()->first();

        if (! $parent) {
            return 'Este registro pertenece a un evento de varios días.';
        }

        return 'Día '.$this->dateRangeLabel().' del evento multi-día «'.$parent->name.'» ('.$parent->dateRangeLabel().').';
    }

    private function scheduleStartAt(CarbonInterface $now): Carbon
    {
        return $this->date_start
            ->copy()
            ->setTimezone($now->getTimezone())
            ->setTimeFromTimeString($this->normalizedTime($this->start_time));
    }

    private function scheduleEndAt(CarbonInterface $now): Carbon
    {
        return $this->date_end
            ->copy()
            ->setTimezone($now->getTimezone())
            ->setTimeFromTimeString($this->normalizedTime($this->end_time));
    }

    private function resolvedNow(?CarbonInterface $now): Carbon
    {
        return Carbon::instance($now ?? now());
    }

    private function normalizedTime(mixed $time): string
    {
        $value = substr((string) $time, 0, 8);

        return strlen($value) === 5 ? $value.':00' : $value;
    }

    private function timeLabel(mixed $time): string
    {
        $normalized = $this->normalizedTime($time);

        if ($normalized === '' || $normalized === ':00') {
            return '—';
        }

        return Carbon::createFromFormat('H:i:s', $normalized)
            ->locale('es')
            ->isoFormat('h:mm a');
    }
}
