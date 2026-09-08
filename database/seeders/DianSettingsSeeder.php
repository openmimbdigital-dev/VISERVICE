<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessDianSetting;
use App\Models\City;
use Illuminate\Database\Seeder;

/**
 * Deja listo el negocio emisor de pruebas ante la DIAN.
 *
 * Son los datos del RUT y de la resolución de habilitación del NIT 1047221605,
 * con los que se hicieron las pruebas de emisión. Sin esto hay que volver a
 * capturarlos a mano cada vez que se refresca la base, y basta un dato distinto
 * al RUT para que la DIAN rechace el documento.
 *
 * La clave técnica y el PIN salen del .env: no tienen por qué quedar en el
 * repositorio, aunque sean del ambiente de habilitación.
 */
class DianSettingsSeeder extends Seeder
{
    /** NIT del emisor con el que se hicieron las pruebas. */
    private const NIT = '1047221605';

    public function run(): void
    {
        $business = Business::query()->where('nit', 'like', self::NIT.'%')->first()
            ?? Business::query()->find(1);

        if (! $business) {
            $this->command?->warn('No hay negocio al cual asociar la configuración DIAN.');

            return;
        }

        $this->alignBusinessWithRut($business);
        $this->createOrUpdateSetting($business);
    }

    /**
     * Datos tal como aparecen en el RUT. La DIAN los compara y notifica con
     * FAJ43b cuando el nombre no corresponde: para persona natural van los
     * apellidos primero.
     */
    private function alignBusinessWithRut(Business $business): void
    {
        $barranquilla = City::query()->where('dane_code', '08001')->first()
            ?? City::query()->where('name', 'Barranquilla')->first();

        $business->forceFill(array_filter([
            'name'                    => 'HURTADO YENERIS ALEX DAVID',
            'nit'                     => self::NIT.'-6',
            'verification_digit'      => '6',
            'person_type'             => 2,          // Persona natural
            'fiscal_responsibilities' => 'R-99-PN',  // 49 - No responsable de IVA
            'address'                 => 'CL 53 D 33 50 BRR LUCERO',
            'city_id'                 => $barranquilla?->id,
        ], fn ($value) => $value !== null))->save();

        $this->command?->info("Negocio «{$business->name}» alineado con el RUT.");
    }

    private function createOrUpdateSetting(Business $business): void
    {
        $technical_key = (string) env('DIAN_TECHNICAL_KEY', '');
        $software_pin  = (string) env('DIAN_SOFTWARE_PIN', '');

        $setting = BusinessDianSetting::query()->firstOrNew([
            'business_id' => $business->id,
        ]);

        $setting->fill([
            'environment'       => 'test',
            'document_format'   => 'json',
            'tr_tipo_id'        => 14121,
            'profile_name'      => 'ALEX.HURTADO_SETT_PRUE',
            // Resolución de habilitación que la DIAN asigna para pruebas.
            'resolution_number' => '18760000001',
            'prefix'            => 'SETT',
            'range_from'        => 1,
            'range_to'          => 5000000,
            'valid_from'        => '2019-01-19',
            'valid_to'          => '2030-01-19',
            'software_id'       => env('DIAN_SOFTWARE_ID', '04441877-5591-4df8-88b1-b8809435513f'),
            'active'            => true,
        ]);

        // El consecutivo no se pisa si ya se avanzó: la DIAN da un solo rango de
        // pruebas por NIT y los números gastados no se recuperan.
        if (blank($setting->next_consecutive)) {
            $setting->next_consecutive = (int) env('DIAN_NEXT_CONSECUTIVE', 1);
        }

        if ($technical_key !== '') {
            $setting->technical_key = $technical_key;
        }

        if ($software_pin !== '') {
            $setting->software_pin = $software_pin;
        }

        $setting->save();

        $this->command?->info(
            "Configuración DIAN lista: {$setting->prefix} desde el consecutivo {$setting->next_consecutive}."
        );

        if (blank($setting->technical_key)) {
            $this->command?->warn(
                'Falta la clave técnica: defínela en DIAN_TECHNICAL_KEY o cárgala desde /admin/dian-settings.'
            );
        }
    }
}
