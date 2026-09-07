<?php

namespace App\Actions\Business;

use App\Actions\LogUserHistoricalAction;
use App\Models\BusinessDianSetting;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateDianSettingAction
{
    use AsAction;

    /** @param array<string, mixed> $data */
    public function handle(?int $dian_setting_id, array $data): BusinessDianSetting
    {
        $user = auth()->user();

        abort_unless(
            $user?->can($dian_setting_id ? 'dian_settings.edit' : 'dian_settings.create'),
            403
        );

        $business_id = (int) ($data['business_id'] ?? 0);
        abort_unless($business_id > 0, 422);

        if (! $user->hasRole('superAdmin')) {
            abort_unless($user->belongsToBusiness($business_id), 403);
        }

        return DB::transaction(function () use ($dian_setting_id, $data, $business_id) {
            if ($dian_setting_id) {
                $setting = BusinessDianSetting::query()->forAuthUser()->findOrFail($dian_setting_id);
                abort_unless($setting->isEditableBy(null, 'dian_settings.edit'), 403);

                // La clave técnica y el PIN solo se reemplazan si se envían con valor.
                foreach (['technical_key', 'software_pin'] as $secret) {
                    if (blank($data[$secret] ?? null)) {
                        unset($data[$secret]);
                    }
                }

                $setting->update($data);
                $action = 'updated';
            } else {
                $setting = BusinessDianSetting::query()->create($data);
                $action = 'created';
            }

            $setting->loadMissing('business');

            LogUserHistoricalAction::run(
                action: $action,
                module: 'business.dian_settings',
                description: ($action === 'created' ? 'Configuró' : 'Actualizó')
                    ." la facturación electrónica de {$setting->business?->name}",
                subject: $setting,
                subject_label: (string) $setting->business?->name,
                properties: [
                    'environment' => $setting->environment,
                    'prefix'      => $setting->prefix,
                    'tr_tipo_id'  => $setting->tr_tipo_id,
                    'active'      => $setting->active,
                ],
                business_id: $business_id,
            );

            return $setting->refresh();
        });
    }
}
