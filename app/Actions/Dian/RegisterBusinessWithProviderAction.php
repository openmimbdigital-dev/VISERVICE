<?php

namespace App\Actions\Dian;

use App\Actions\LogUserHistoricalAction;
use App\Models\BusinessDianSetting;
use App\Services\Dian\TitanioClient;
use App\Support\DianNit;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Registra el negocio como empresa emisora en la plataforma del proveedor
 * (autogestión) y guarda el tr_tipo_id devuelto, necesario para emitir.
 */
class RegisterBusinessWithProviderAction
{
    use AsAction;

    public function handle(BusinessDianSetting $setting): BusinessDianSetting
    {
        abort_unless(auth()->user()?->can('dian_settings.edit'), 403);
        abort_unless($setting->isEditableBy(null, 'dian_settings.edit'), 403);

        $setting->loadMissing('business.city.country');
        $business = $setting->business;

        if (! $business) {
            throw ValidationException::withMessages(['dian' => 'El negocio emisor no existe.']);
        }

        $nit = DianNit::normalize($business->nit);

        if ($nit === '') {
            throw ValidationException::withMessages(['dian' => 'El negocio no tiene NIT registrado.']);
        }

        foreach (['resolution_number' => 'número de resolución', 'prefix' => 'prefijo', 'range_from' => 'rango inicial', 'range_to' => 'rango final'] as $field => $label) {
            if (blank($setting->{$field})) {
                throw ValidationException::withMessages([
                    'dian' => "Debes registrar el {$label} de la resolución antes de crear la empresa ante el proveedor.",
                ]);
            }
        }

        $defaults = (array) config('dian.defaults');

        $technical_key = filled($setting->technical_key)
            ? $setting->technical_key
            : ($setting->environment === 'test' ? (string) $defaults['test_technical_key'] : '');

        $result = TitanioClient::for($setting->environment)->createCompany(
            company_nit: (string) config('dian.titanio.nit'),
            company: [
                'numero_iden' => (int) $nit,
                'nombre'      => (string) $business->name,
                'tipo'        => 6, // 6 = NIT
                'regimen'     => '2',
                'direccion'   => (string) ($business->address ?? ''),
                'ciudad'      => (string) ($business->city?->name ?? ''),
                'pais'        => (string) ($business->city?->country?->name ?? 'COLOMBIA'),
            ],
            profile: [
                'tipo_documento' => (string) $defaults['document_type_code'],
                'correo'         => (string) ($business->email ?? ''),
                'perfil'         => 'Emisor',
                'resolucion'     => [
                    'numeroResolucion' => (string) $setting->resolution_number,
                    'prefijo'          => (string) $setting->prefix,
                    'rangoInicial'     => (string) $setting->range_from,
                    'rangoFinal'       => (string) $setting->range_to,
                    'claveTecnica'     => $technical_key,
                ],
                'entrada'        => (string) $defaults['input_format'],
            ],
        );

        $setting->forceFill(array_filter([
            'tr_tipo_id'   => $result['tr_tipo_id'],
            'cfg_lote_id'  => $result['cfg_lote_id'],
            'profile_name' => $result['profile_name'],
        ], fn ($value) => $value !== null))->save();

        LogUserHistoricalAction::run(
            action: 'updated',
            module: 'business.dian_settings',
            description: "Registró el negocio {$business->name} como emisor de factura electrónica",
            subject: $setting,
            subject_label: $business->name,
            properties: [
                'tr_tipo_id'   => $setting->tr_tipo_id,
                'cfg_lote_id'  => $setting->cfg_lote_id,
                'profile_name' => $setting->profile_name,
                'environment'  => $setting->environment,
            ],
            business_id: (int) $setting->business_id,
        );

        return $setting->refresh();
    }
}
