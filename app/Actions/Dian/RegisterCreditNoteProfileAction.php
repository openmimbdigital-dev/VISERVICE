<?php

namespace App\Actions\Dian;

use App\Actions\LogUserHistoricalAction;
use App\Models\BusinessDianSetting;
use App\Services\Dian\TitanioClient;
use App\Support\DianNit;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Crea en el proveedor el perfil con el que este negocio emite notas crédito.
 *
 * Para el proveedor la nota crédito es otro tipo de documento —el 20— y cada
 * tipo tiene su propio perfil, con su propio «tr_tipo_id». El de facturas no
 * sirve: emitir una nota contra él la rechazaría por no corresponder.
 *
 * Se reutiliza la resolución del negocio cambiando el prefijo, porque el emisor y
 * su habilitación son los mismos; lo único propio de la nota es su numeración.
 */
class RegisterCreditNoteProfileAction
{
    use AsAction;

    public function handle(BusinessDianSetting $setting): BusinessDianSetting
    {
        abort_unless(auth()->user()?->can('dian_settings.edit'), 403);
        abort_unless($setting->isEditableBy(null, 'dian_settings.edit'), 403);

        $setting->loadMissing('business');
        $business = $setting->business;

        if (! $business) {
            throw ValidationException::withMessages(['dian' => 'El negocio emisor no existe.']);
        }

        if (blank($setting->credit_note_prefix)) {
            throw ValidationException::withMessages([
                'dian' => 'Define primero el prefijo de las notas crédito: es lo que las distingue de las facturas.',
            ]);
        }

        if (blank($setting->tr_tipo_id)) {
            throw ValidationException::withMessages([
                'dian' => 'Registra primero el negocio como emisor de facturas; la nota crédito se suma a esa habilitación.',
            ]);
        }

        $defaults = (array) config('dian.defaults');

        $result = TitanioClient::for($setting->environment)->createProfile(
            company_nit: DianNit::normalize($business->nit),
            profile: [
                'tipo_documento' => (string) config('dian.credit_note.provider_document_type_code', '20'),
                'correo'         => (string) ($business->email ?? ''),
                'perfil'         => 'Emisor',
                'resolucion'     => [
                    'numeroResolucion' => (string) $setting->resolution_number,
                    'prefijo'          => (string) $setting->credit_note_prefix,
                    'rangoInicial'     => (string) ($setting->range_from ?? 1),
                    'rangoFinal'       => (string) ($setting->range_to ?? 999999999),
                    'claveTecnica'     => (string) ($setting->technical_key ?? ''),
                ],
                'entrada'        => (string) $defaults['input_format'],
            ],
        );

        if (! $result['tr_tipo_id']) {
            throw ValidationException::withMessages([
                'dian' => 'El proveedor no devolvió el identificador del perfil de notas crédito.',
            ]);
        }

        $setting->forceFill([
            'credit_note_tr_tipo_id'       => $result['tr_tipo_id'],
            'credit_note_next_consecutive' => $setting->credit_note_next_consecutive ?: 1,
        ])->save();

        LogUserHistoricalAction::run(
            action: 'updated',
            module: 'business.dian_settings',
            description: "Registró el perfil de notas crédito de {$business->name}",
            subject: $setting,
            subject_label: $business->name,
            properties: [
                'credit_note_tr_tipo_id' => $setting->credit_note_tr_tipo_id,
                'credit_note_prefix'     => $setting->credit_note_prefix,
                'environment'            => $setting->environment,
            ],
            business_id: (int) $setting->business_id,
        );

        return $setting->refresh();
    }
}
