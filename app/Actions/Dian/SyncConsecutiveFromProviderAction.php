<?php

namespace App\Actions\Dian;

use App\Models\BusinessDianSetting;
use App\Services\Dian\TitanioClient;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Le pregunta al proveedor por dónde va la numeración y adelanta la nuestra.
 *
 * El consecutivo lo llevamos nosotros, pero quien sabe cuáles se usaron de verdad
 * es el proveedor: ahí quedaron los documentos emitidos. Si la base local se
 * rehace —un «migrate:fresh» en pruebas, una restauración— el contador vuelve a
 * empezar y los siguientes envíos chocan con documentos que ya existen.
 *
 * Esta consulta reconstruye el punto de partida desde la única fuente que
 * sobrevive a eso.
 *
 * Dos decisiones que vale la pena tener presentes:
 *
 *  - **Solo adelanta, nunca retrocede.** Si el proveedor reporta menos de lo que
 *    tenemos, se conserva lo nuestro. Saltarse números deja un hueco en la
 *    numeración, que es explicable; repetir uno es un documento rechazado y un
 *    consecutivo quemado.
 *  - **Se identifica al negocio por su prefijo.** El proveedor no deja filtrar
 *    por perfil junto con las fechas, y la cuenta es compartida por todos
 *    nuestros negocios. Si dos llegaran a usar el mismo prefijo, lo peor que
 *    pasa es que uno quede adelantado —nunca repetido—, por la regla anterior.
 */
class SyncConsecutiveFromProviderAction
{
    use AsAction;

    /**
     * @return array{found: ?int, previous: int, next: int, changed: bool, windows: int}
     */
    public function handle(BusinessDianSetting $setting): array
    {
        $previous = $setting->upcomingConsecutive();

        [$found, $windows] = $this->highestAtProvider($setting);

        if ($found === null) {
            return [
                'found'    => null,
                'previous' => $previous,
                'next'     => $previous,
                'changed'  => false,
                'windows'  => $windows,
            ];
        }

        $next = max($previous, $found + 1);

        if ($next !== $previous) {
            $setting->forceFill(['next_consecutive' => $next])->save();
        }

        return [
            'found'    => $found,
            'previous' => $previous,
            'next'     => $next,
            'changed'  => $next !== $previous,
            'windows'  => $windows,
        ];
    }

    /**
     * Consecutivo más alto que el proveedor tiene con nuestro prefijo.
     *
     * Se recorre hacia atrás en ventanas de 30 días —el máximo que acepta— y se
     * corta en la primera que traiga documentos nuestros: como la numeración solo
     * crece con el tiempo, ninguna ventana más vieja puede tener uno mayor.
     *
     * @return array{0: ?int, 1: int} El consecutivo hallado y cuántas ventanas se miraron.
     */
    private function highestAtProvider(BusinessDianSetting $setting): array
    {
        $prefix = trim((string) $setting->prefix);

        if ($prefix === '') {
            return [null, 0];
        }

        $client = TitanioClient::for($setting->environment);

        $windows = max(1, (int) config('dian.consecutive_lookup.windows', 6));
        $max_pages = max(1, (int) config('dian.consecutive_lookup.max_pages', 20));

        $end = CarbonImmutable::now()->addDay();

        for ($window = 1; $window <= $windows; $window++) {
            $start = $end->subDays(30);
            $highest = null;

            for ($page = 1; $page <= $max_pages; $page++) {
                $rows = $client->listTransactions($start->toDateString(), $end->toDateString(), $page);

                if ($rows === []) {
                    break;
                }

                foreach ($rows as $row) {
                    $consecutive = $this->consecutiveIn((string) ($row['doc'] ?? ''), $prefix);

                    if ($consecutive !== null) {
                        $highest = max($highest ?? 0, $consecutive);
                    }
                }
            }

            if ($highest !== null) {
                return [$highest, $window];
            }

            $end = $start;
        }

        return [null, $windows];
    }

    /**
     * El número dentro de «SETT10», si el documento es de este emisor.
     *
     * Se exige que lo que sigue al prefijo sean solo dígitos: así «SETT10» cuenta
     * para el prefijo «SETT» pero «SETTX10» no, y un prefijo corto no se traga los
     * documentos de otro más largo que empiece igual.
     */
    private function consecutiveIn(string $document, string $prefix): ?int
    {
        if ($document === '' || ! str_starts_with($document, $prefix)) {
            return null;
        }

        $number = mb_substr($document, mb_strlen($prefix));

        return ctype_digit($number) && $number !== '' ? (int) $number : null;
    }
}
