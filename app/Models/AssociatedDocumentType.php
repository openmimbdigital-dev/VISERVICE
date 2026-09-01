<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusinessTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AssociatedDocumentType extends Model
{
    use BelongsToBusinessTenant;
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'key',
        'name',
        'active',
        'document_send',
    ];

    protected function casts(): array
    {
        return [
            'active'        => 'boolean',
            'document_send' => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function workOrderAssociatedDocuments(): HasMany
    {
        return $this->hasMany(WorkOrderAssociatedDocument::class);
    }

    public static function makeKeyFromName(string $name): string
    {
        return Str::slug($name, '_');
    }

    public function canDelete(?User $user = null): bool
    {
        $user ??= auth()->user();

        return $this->isEditableBy($user, 'workshop.work-orders.associated-documents.delete');
    }
}
