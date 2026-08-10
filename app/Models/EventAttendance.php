<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventAttendance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'attendable_type',
        'attendable_id',
        'date_event',
        'attendance_hour',
        'attendance',
    ];

    protected function casts(): array
    {
        return [
            'date_event' => 'date',
            'attendance' => 'boolean',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function attendable(): MorphTo
    {
        return $this->morphTo();
    }
}
