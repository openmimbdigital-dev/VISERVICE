<?php

namespace App\Console\Commands;

use App\Actions\Dian\SyncConsecutiveFromProviderAction;
use App\Models\BusinessDianSetting;
use Illuminate\Console\Command;
use Throwable;

/**
 * Recupera el punto de partida de la numeración preguntándole al proveedor.
 *
 * Pensado sobre todo para después de rehacer la base —un «migrate:fresh» en
 * pruebas—: ahí el contador local vuelve a empezar, pero los documentos que ya se
 * emitieron siguen existiendo en el proveedor y los siguientes envíos chocarían
 * contra ellos.
 *
 * Solo adelanta la numeración, nunca la retrocede.
 */
class DianSyncConsecutive extends Command
{
    protected $signature = 'dian:sync-consecutive
        {--business= : Solo el negocio con este id}';

    protected $description = 'Consulta en el proveedor el último consecutivo emitido y adelanta la numeración local';

    public function handle(): int
    {
        $settings = BusinessDianSetting::query()
            ->withoutGlobalScopes()
            ->with('business')
            ->when($this->option('business'), fn ($query, $id) => $query->where('business_id', $id))
            ->get();

        if ($settings->isEmpty()) {
            $this->info('No hay negocios con facturación electrónica configurada.');

            return self::SUCCESS;
        }

        $rows = [];

        foreach ($settings as $setting) {
            $rows[] = $this->syncOne($setting);
        }

        $this->table(['Negocio', 'Prefijo', 'Antes', 'En el proveedor', 'Ahora', 'Resultado'], $rows);

        return self::SUCCESS;
    }

    /** @return list<string> */
    private function syncOne(BusinessDianSetting $setting): array
    {
        $name = $setting->business?->name ?? "negocio #{$setting->business_id}";

        try {
            $result = SyncConsecutiveFromProviderAction::run($setting);
        } catch (Throwable $exception) {
            return [$name, (string) $setting->prefix, (string) $setting->upcomingConsecutive(), '—', '—', 'error: '.$exception->getMessage()];
        }

        if ($result['found'] === null) {
            return [
                $name,
                (string) $setting->prefix,
                (string) $result['previous'],
                'sin documentos',
                (string) $result['next'],
                'El proveedor no tiene documentos con este prefijo; se deja como estaba.',
            ];
        }

        return [
            $name,
            (string) $setting->prefix,
            (string) $result['previous'],
            (string) $result['found'],
            (string) $result['next'],
            $result['changed']
                ? 'Adelantado: el contador venía atrasado.'
                : 'Ya estaba al día.',
        ];
    }
}
