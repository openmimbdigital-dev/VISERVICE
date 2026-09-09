<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessDianSetting;
use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        $this->resolveNextConsecutive($setting);

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

    /**
     * Decide con qué número seguirá la numeración autorizada.
     *
     * El contador vive solo aquí, pero los números los quema el proveedor: apenas
     * crea la transacción el número queda usado, aunque la DIAN después lo rechace,
     * y reutilizarlo devuelve «Documento duplicado». Como saltarse números no
     * cuesta nada y repetirlos sí, se toma el mayor de todo lo que se sabe:
     *
     *   1. el contador guardado, si la fila sobrevivió;
     *   2. el consecutivo más alto ya emitido, por si el contador se perdió pero
     *      quedaron facturas (una restauración parcial, un refresh a medias);
     *   3. DIAN_NEXT_CONSECUTIVE del .env, que es lo único que queda tras un
     *      migrate:fresh y por eso se avisa fuerte.
     */
    private function resolveNextConsecutive(BusinessDianSetting $setting): void
    {
        $stored = filled($setting->next_consecutive) ? (int) $setting->next_consecutive : null;
        $emitted = $this->highestEmittedConsecutive($setting->business_id);
        $from_env = filled(env('DIAN_NEXT_CONSECUTIVE')) ? (int) env('DIAN_NEXT_CONSECUTIVE') : null;

        $candidates = array_filter([
            'contador guardado'      => $stored,
            'facturas ya emitidas'   => $emitted !== null ? $emitted + 1 : null,
            'DIAN_NEXT_CONSECUTIVE'  => $from_env,
        ], fn ($value) => $value !== null && $value > 0);

        if ($candidates === []) {
            $setting->next_consecutive = (int) $setting->range_from;

            $this->warnAboutBlindStart($setting, 'no había ningún dato del cual deducirlo');

            return;
        }

        $next = max($candidates);
        $source = array_search($next, $candidates, true);
        $setting->next_consecutive = $next;

        // Si el contador se quedó atrás de lo ya emitido, hay que decirlo: significa
        // que alguien restauró una base vieja encima de facturas más nuevas.
        if ($stored !== null && $emitted !== null && $stored <= $emitted) {
            $this->command?->warn(
                "  El contador decía {$stored} pero ya hay documentos emitidos hasta el {$emitted}. "
                ."Se continúa en {$next} para no repetir numeración."
            );
        }

        // El único caso realmente peligroso: no quedó rastro local y el número sale
        // del .env, que nadie garantiza que esté al día.
        if ($source === 'DIAN_NEXT_CONSECUTIVE' && $stored === null && $emitted === null) {
            $this->warnAboutBlindStart($setting, 'se tomó de DIAN_NEXT_CONSECUTIVE en el .env');
        }

        if ($setting->range_to !== null && $next > (int) $setting->range_to) {
            $this->command?->error(
                "  El consecutivo {$next} supera el rango autorizado ({$setting->range_from}-{$setting->range_to}). "
                .'Hay que pedir una resolución nueva antes de emitir.'
            );
        }
    }

    /** Consecutivo más alto que ya se le pidió al proveedor para este negocio. */
    private function highestEmittedConsecutive(int $business_id): ?int
    {
        if (! Schema::hasTable('electronic_invoices')) {
            return null;
        }

        $highest = DB::table('electronic_invoices')
            ->where('business_id', $business_id)
            ->max('consecutive');

        return $highest !== null ? (int) $highest : null;
    }

    /**
     * Aviso visible cuando el punto de partida no se pudo deducir de datos propios.
     */
    private function warnAboutBlindStart(BusinessDianSetting $setting, string $reason): void
    {
        $next = $setting->next_consecutive;
        $document = $setting->prefix.$next;

        $this->command?->warn(str_repeat('─', 72));
        $this->command?->warn('  ATENCIÓN — numeración de facturación electrónica');
        $this->command?->warn(str_repeat('─', 72));
        $this->command?->warn("  La próxima factura saldrá como {$document} porque {$reason}.");
        $this->command?->warn('');
        $this->command?->warn('  El proveedor y la DIAN llevan su propio registro de números usados y ese');
        $this->command?->warn('  no se borra al refrescar la base. Si este número ya se emitió alguna vez,');
        $this->command?->warn('  el envío fallará con «Documento duplicado».');
        $this->command?->warn('');
        $this->command?->warn('  Antes de emitir, confirma que sea correcto y ajústalo si hace falta en');
        $this->command?->warn('  DIAN_NEXT_CONSECUTIVE o desde /admin/dian-settings. Saltarse números no');
        $this->command?->warn('  cuesta nada; repetirlos sí.');
        $this->command?->warn(str_repeat('─', 72));
    }
}
