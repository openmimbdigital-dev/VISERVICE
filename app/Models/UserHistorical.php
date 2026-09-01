<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserHistorical extends Model
{
    public $timestamps = false;

    protected $table = 'user_historical';

    protected $fillable = [
        'business_id',
        'user_id',
        'client_id',
        'client_name',
        'action',
        'module',
        'description',
        'subject_type',
        'subject_id',
        'subject_label',
        'properties',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function booted(): void
    {
        static::creating(function (UserHistorical $log) {
            if ($log->created_at === null) {
                $log->created_at = now();
            }
        });
    }
}
