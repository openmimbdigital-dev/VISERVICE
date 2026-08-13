<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class GeneralConfig extends Model
{
    public const KEY_ASSOCIATE_DOCUMENT_OT = 'asociate_document_ot';

    public const KEY_PUBLIC_PARTICIPANTS_PORTAL_PIN = 'participants_portal_pin';

    public const LABEL_PUBLIC_PARTICIPANTS_PORTAL_PIN = 'portal_access_pin';

    protected $fillable = [
        'business_id',
        'configurable_type',
        'configurable_id',
        'key',
        'label',
        'value',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function configurable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForAuthUser($query)
    {
        $user = auth()->user();

        if ($user?->hasRole('superAdmin')) {
            return $query;
        }

        $business_ids = $user->businessIds();

        if ($business_ids === []) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereIn($query->getModel()->getTable() . '.business_id', $business_ids);
    }

    public function scopeForKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    public function scopeAssociatedDocumentsOt($query)
    {
        return $query->where('key', self::KEY_ASSOCIATE_DOCUMENT_OT);
    }

    public function scopeParticipantsPortalPin($query)
    {
        return $query
            ->where('key', self::KEY_PUBLIC_PARTICIPANTS_PORTAL_PIN)
            ->where('label', self::LABEL_PUBLIC_PARTICIPANTS_PORTAL_PIN);
    }

    public static function makeLabelFromValue(string $value): string
    {
        return (string) Str::of($value)->trim()->lower()->replaceMatches('/\s+/', '_');
    }
}
