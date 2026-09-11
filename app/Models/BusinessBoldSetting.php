<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Llaves de Bold con las que un negocio cobra sus facturas.
 *
 * Se guardan cifradas: son credenciales de cobro, y con la de identidad se puede
 * crear links a nombre del negocio.
 */
class BusinessBoldSetting extends Model
{
    protected $fillable = [
        'business_id',
        'identity_key',
        'secret_key',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'identity_key' => 'encrypted',
            'secret_key'   => 'encrypted',
            'active'       => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /** ¿Sirve para cobrar por su cuenta? */
    public function isUsable(): bool
    {
        return $this->active && filled($this->identity_key);
    }
}
