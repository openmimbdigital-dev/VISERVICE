<?php

namespace App\Console\Commands;

use App\Services\Dian\DianRequestException;
use App\Services\Dian\TitanioClient;
use Illuminate\Console\Command;

class DianTestConnection extends Command
{
    protected $signature = 'dian:test-connection {--env= : Entorno a probar (test|production)}';

    protected $description = 'Verifica las credenciales de facturación electrónica contra el proveedor';

    public function handle(): int
    {
        $environment = $this->option('env') ?: (string) config('dian.environment', 'test');
        $client = TitanioClient::for($environment);

        $this->line('Entorno: <comment>'.$environment.'</comment>');
        $this->line('URL:     <comment>'.$client->baseUrl().'</comment>');
        $this->line('NIT:     <comment>'.config('dian.titanio.nit').'</comment>');
        $this->line('Usuario: <comment>'.config('dian.titanio.user').'</comment>');
        $this->newLine();

        try {
            $token = $client->token(force_refresh: true);
        } catch (DianRequestException $exception) {
            $this->error('No se pudo autenticar: '.$exception->getMessage());

            if ($exception->errorId !== null) {
                $this->line('error_id: '.$exception->errorId);
            }

            if ($exception->response !== []) {
                $this->line(json_encode($exception->response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }

            return self::FAILURE;
        }

        $this->info('Autenticación correcta.');
        $this->line('Token: '.substr($token, 0, 40).'...');

        return self::SUCCESS;
    }
}
