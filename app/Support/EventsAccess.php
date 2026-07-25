<?php

namespace App\Support;

use App\Models\Event;
use App\Models\User;

class EventsAccess
{
    public static function canViewEvents(?User $user = null): bool
    {
        $user ??= auth()->user();

        return ChurchEventsAccess::allowed($user) && (bool) $user?->can('events.events.view');
    }

    public static function canCreateEvents(?User $user = null): bool
    {
        $user ??= auth()->user();

        return ChurchEventsAccess::allowed($user) && (bool) $user?->can('events.events.create');
    }

    public static function canViewSchedule(?User $user = null): bool
    {
        $user ??= auth()->user();

        return ChurchEventsAccess::allowed($user) && (bool) $user?->can('events.schedule.view');
    }

    public static function canEditEvent(Event $event, ?User $user = null): bool
    {
        $user ??= auth()->user();

        return ChurchEventsAccess::allowed($user)
            && (bool) $user?->can('events.events.edit')
            && static::belongsToUserBusiness($event, $user);
    }

    public static function canDeleteEvent(Event $event, ?User $user = null): bool
    {
        $user ??= auth()->user();

        return ChurchEventsAccess::allowed($user)
            && (bool) $user?->can('events.events.delete')
            && static::belongsToUserBusiness($event, $user);
    }

    public static function canManageEvent(Event $event, ?User $user = null): bool
    {
        $user ??= auth()->user();

        return static::canViewEvents($user)
            && $event->event_category_id !== null
            && static::belongsToUserBusiness($event, $user);
    }

    public static function authorizeViewEvents(?User $user = null): void
    {
        abort_unless(static::canViewEvents($user), 403);
    }

    public static function authorizeCreateEvents(?User $user = null): void
    {
        abort_unless(static::canCreateEvents($user), 403);
    }

    public static function authorizeViewSchedule(?User $user = null): void
    {
        abort_unless(static::canViewSchedule($user), 403);
    }

    public static function authorizeEditEvent(Event $event, ?User $user = null): void
    {
        abort_unless(static::canEditEvent($event, $user), 403);
    }

    public static function authorizeDeleteEvent(Event $event, ?User $user = null): void
    {
        abort_unless(static::canDeleteEvent($event, $user), 403);
    }

    public static function canStartAttendance(Event $event, ?User $user = null): bool
    {
        $user ??= auth()->user();

        return ChurchEventsAccess::allowed($user)
            && (bool) $user?->can('events.attendance.start')
            && static::belongsToUserBusiness($event, $user);
    }

    public static function authorizeStartAttendance(Event $event, ?User $user = null): void
    {
        abort_unless(static::canStartAttendance($event, $user), 403);
    }

    public static function canCloseAttendance(Event $event, ?User $user = null): bool
    {
        $user ??= auth()->user();

        return ChurchEventsAccess::allowed($user)
            && (bool) $user?->can('events.attendance.close')
            && static::belongsToUserBusiness($event, $user);
    }

    public static function authorizeCloseAttendance(Event $event, ?User $user = null): void
    {
        abort_unless(static::canCloseAttendance($event, $user), 403);
    }

    public static function canViewAttendanceReport(?User $user = null): bool
    {
        $user ??= auth()->user();

        return ChurchEventsAccess::allowed($user)
            && (bool) $user?->can('events.reports.attendance.view');
    }

    public static function authorizeViewAttendanceReport(?User $user = null): void
    {
        abort_unless(static::canViewAttendanceReport($user), 403);
    }

    public static function canExportAttendanceReport(?User $user = null): bool
    {
        $user ??= auth()->user();

        return static::canViewAttendanceReport($user)
            && (bool) $user?->can('reports.export');
    }

    public static function authorizeExportAttendanceReport(?User $user = null): void
    {
        abort_unless(static::canExportAttendanceReport($user), 403);
    }

    public static function belongsToBusiness(int $business_id, ?User $user = null): bool
    {
        $user ??= auth()->user();

        if (! $user) {
            return false;
        }

        return $user->hasRole('superAdmin') || $user->belongsToBusiness($business_id);
    }

    private static function belongsToUserBusiness(Event $event, ?User $user): bool
    {
        return static::belongsToBusiness((int) $event->business_id, $user);
    }
}
