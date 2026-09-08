<?php

namespace App\Actions\Dian;

use App\Actions\LogUserHistoricalAction;
use App\Models\BusinessDianSetting;
use App\Services\Dian\TitanioClient;
use App\Support\DianNit;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Trae del proveedor la resolución de numeración del negocio y la guarda.
 *
 * Evita que alguien copie a mano seis campos del portal de la DIAN, entre ellos
 * la clave técnica, que es larga y con la que un error tipográfico se traduce en
 * un rechazo FAD06 imposible de diagnosticar desde la aplicación.
 *
 * El proveedor solo devuelve resoluciones de producción; en habilitación
 * responde que no encontró rangos.
 */
class FetchResolutionFromProviderAction
{
    use AsAction;

    /** @return array{fields: list<string>, prefix: string} */
    public function handle(BusinessDianSetting $setting): array
    {
        abort_unless(auth()->user()?->can('dian_settings.edit'), 403);
        abort_unless($setting->isEditableBy(null, 'dian_settings.edit'), 403);

        $setting->loadMissing('business');
        $nit = DianNit::normalize((string) $setting->business?->nit);

        if ($nit === '') {
            throw ValidationException::withMessages([
                'dian' => 'El negocio no tiene NIT registrado.',
            ]);
        }

        $response = TitanioClient::for($setting->environment)->queryResolution($nit);
        $ranges = (array) ($response['rangos'] ?? []);

        if ($ranges === []) {
            throw ValidationException::withMessages([
                'dian' => (string) ($response['operationDescription']
                    ?? 'El proveedor no encontró rangos de numeración para este NIT.'),
            ]);
        }

        $range = $this->pickRange($ranges, (string) $setting->prefix);
        $changed = $this->applyRange($setting, $range);

        LogUserHistoricalAction::run(
            action: 'updated',
            module: 'business.dian_settings',
            description: "Trajo del proveedor la resolución de {$setting->business?->name}",
            subject: $setting,
            subject_label: (string) $setting->business?->name,
            properties: [
                'prefix'         => $setting->prefix,
                'updated_fields' => $changed,
            ],
            business_id: (int) $setting->business_id,
        );

        return ['fields' => $changed, 'prefix' => (string) $setting->prefix];
    }

    /**
     * Si ya hay un prefijo configurado se respeta, porque un negocio puede tener
     * varias resoluciones y solo una corresponde a esta configuración.
     *
     * @param  list<array<string, mixed>>  $ranges
     * @return array<string, mixed>
     */
    private function pickRange(array $ranges, string $current_prefix): array
    {
        if ($current_prefix !== '') {
            foreach ($ranges as $range) {
                if (strcasecmp((string) ($range['prefix'] ?? ''), $current_prefix) === 0) {
                    return $range;
                }
            }
        }

        return $ranges[0];
    }

    /**
     * @param  array<string, mixed>  $range
     * @return list<string>  campos que efectivamente cambiaron
     */
    private function applyRange(BusinessDianSetting $setting, array $range): array
    {
        $values = array_filter([
            'prefix'        => filled($range['prefix'] ?? null) ? mb_strtoupper((string) $range['prefix']) : null,
            'range_from'    => filled($range['fromNumber'] ?? null) ? (int) $range['fromNumber'] : null,
            'range_to'      => filled($range['toNumber'] ?? null) ? (int) $range['toNumber'] : null,
            'valid_from'    => $range['validDateNumberFrom'] ?? null,
            'valid_to'      => $range['validDateNumberTo'] ?? null,
            'technical_key' => $range['technicalKey'] ?? null,
            // El número de resolución no siempre viene en la respuesta.
            'resolution_number' => $range['resolutionNumber'] ?? $range['numeroResolucion'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        $changed = [];

        foreach ($values as $field => $value) {
            // Las fechas vienen casteadas a Carbon: se comparan por día, no por
            // su representación completa, que nunca coincidiría con «2026-04-15».
            $current = $setting->{$field} instanceof \DateTimeInterface
                ? $setting->{$field}->format('Y-m-d')
                : (string) $setting->{$field};

            if ($current !== (string) $value) {
                $changed[] = $field;
            }
        }

        $setting->forceFill($values)->save();

        return $changed;
    }
}
