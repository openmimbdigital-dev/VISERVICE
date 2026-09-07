<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusinessTenant;
use App\Support\DianNit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessDianSetting extends Model
{
    use BelongsToBusinessTenant;
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'provider',
        'environment',
        'tr_tipo_id',
        'cfg_lote_id',
        'profile_name',
        'resolution_number',
        'prefix',
        'range_from',
        'range_to',
        'valid_from',
        'valid_to',
        'technical_key',
        'next_consecutive',
        'software_id',
        'software_pin',
        'notify_customer',
        'include_pdf',
        'include_xml',
        'include_attachments',
        'active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'tr_tipo_id'          => 'integer',
            'cfg_lote_id'         => 'integer',
            'range_from'          => 'integer',
            'range_to'            => 'integer',
            'next_consecutive'    => 'integer',
            'valid_from'          => 'date',
            'valid_to'            => 'date',
            'technical_key'       => 'encrypted',
            'software_pin'        => 'encrypted',
            'notify_customer'     => 'boolean',
            'include_pdf'         => 'boolean',
            'include_xml'         => 'boolean',
            'include_attachments' => 'boolean',
            'active'              => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function isProduction(): bool
    {
        return $this->environment === 'production';
    }

    /** Número que se asignaría al próximo documento emitido. */
    public function upcomingConsecutive(): int
    {
        return (int) ($this->next_consecutive ?: $this->range_from);
    }

    public function hasAvailableRange(): bool
    {
        $next = $this->upcomingConsecutive();

        return $next > 0 && $this->range_to !== null && $next <= (int) $this->range_to;
    }

    public function isResolutionExpired(): bool
    {
        return $this->valid_to !== null && $this->valid_to->isPast();
    }

    /**
     * Requisitos que impiden emitir. Vacío significa que el negocio está listo.
     *
     * @return list<string>
     */
    public function missingRequirements(): array
    {
        $missing = [];

        if (! $this->active) {
            $missing[] = 'La facturación electrónica está desactivada para este negocio.';
        }

        if (! $this->tr_tipo_id) {
            $missing[] = 'Falta el identificador de perfil (tr_tipo_id) entregado por el proveedor.';
        }

        if (! $this->resolution_number) {
            $missing[] = 'Falta el número de resolución DIAN.';
        }

        if (! $this->prefix) {
            $missing[] = 'Falta el prefijo de la resolución.';
        }

        if (! $this->range_from || ! $this->range_to) {
            $missing[] = 'Falta el rango de numeración autorizado.';
        } elseif (! $this->hasAvailableRange()) {
            $missing[] = 'Se agotó el rango de numeración de la resolución.';
        }

        if ($this->isResolutionExpired()) {
            $missing[] = 'La resolución DIAN está vencida.';
        }

        $missing = array_merge($missing, $this->missingBusinessRequirements());

        return array_values($missing);
    }

    public function isReadyToEmit(): bool
    {
        return $this->missingRequirements() === [];
    }

    /**
     * Datos fiscales del negocio emisor que exige el documento electrónico.
     *
     * @return list<string>
     */
    private function missingBusinessRequirements(): array
    {
        $business = $this->business;
        $missing = [];

        if (! $business) {
            return ['El negocio emisor no existe.'];
        }

        if (DianNit::normalize($business->nit) === '') {
            $missing[] = 'El negocio no tiene NIT registrado.';
        } elseif (filled($business->verification_digit)) {
            $expected = DianNit::verificationDigit($business->nit);

            if ($expected !== null && $expected !== (string) $business->verification_digit) {
                $missing[] = "El dígito de verificación del NIT no corresponde: debería ser {$expected}.";
            }
        }

        if (blank($business->address)) {
            $missing[] = 'El negocio no tiene dirección registrada.';
        }

        if (blank($business->email)) {
            $missing[] = 'El negocio no tiene correo electrónico registrado.';
        }

        $city = $business->city;

        if (! $city) {
            $missing[] = 'El negocio no tiene ciudad registrada.';
        } elseif (blank($city->dane_code)) {
            $missing[] = "La ciudad «{$city->name}» no tiene código DANE configurado.";
        }

        return $missing;
    }
}
