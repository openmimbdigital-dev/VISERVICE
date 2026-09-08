<?php

namespace App\Console\Commands;

use App\Actions\Dian\DownloadElectronicInvoiceFilesAction;
use App\Support\BusinessLogoStorage;
use App\Support\PaymentProofStorage;
use App\Support\ProductImageStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Traslada al volumen de datos los archivos que quedaron dentro del proyecto,
 * conservando la ruta relativa para que los registros existentes sigan siendo válidos.
 */
class MediaMigrateFiles extends Command
{
    protected $signature = 'media:migrate
                            {--dry-run : Muestra lo que se movería sin copiar nada}
                            {--keep : Conserva el archivo original en lugar de borrarlo}';

    protected $description = 'Mueve los archivos de storage/app al volumen de datos (discos media y documents)';

    /**
     * Carpetas a trasladar: origen => [disco origen, disco destino].
     *
     * Se conserva la ruta relativa para que los registros ya guardados en base de
     * datos sigan apuntando al archivo correcto.
     *
     * @var array<string, array{0:string, 1:string}>
     */
    private const FOLDERS = [
        'products'       => ['public', ProductImageStorage::DISK],
        'business-logos' => ['public', BusinessLogoStorage::DISK],
        'logos'          => ['public', BusinessLogoStorage::DISK],
        'payment-proofs' => ['public', PaymentProofStorage::DISK],
        'dian'           => ['local', DownloadElectronicInvoiceFilesAction::DISK],
    ];

    public function handle(): int
    {
        $dry_run = (bool) $this->option('dry-run');
        $keep = (bool) $this->option('keep');
        $total = 0;

        $this->line('Entorno de destino: <comment>'.media_environment().'</comment> ('.media_root().')');
        $this->newLine();

        $total += $this->moveLegacyEnvironmentFolders($dry_run);

        foreach (self::FOLDERS as $folder => [$source_disk, $target_disk]) {
            $files = Storage::disk($source_disk)->allFiles($folder);

            if ($files === []) {
                $this->line("· {$folder}: sin archivos en «{$source_disk}»");

                continue;
            }

            $this->line("· {$folder}: ".count($files)." archivo(s) de «{$source_disk}» a «{$target_disk}»");

            foreach ($files as $file) {
                if (Storage::disk($target_disk)->exists($file)) {
                    $this->warn("    ya existía, se omite: {$file}");

                    continue;
                }

                if ($dry_run) {
                    $this->line("    [simulación] {$file}");
                    $total++;

                    continue;
                }

                $contents = Storage::disk($source_disk)->get($file);

                if ($contents === null) {
                    $this->warn("    no se pudo leer: {$file}");

                    continue;
                }

                Storage::disk($target_disk)->put($file, $contents);

                if (! $keep) {
                    Storage::disk($source_disk)->delete($file);
                }

                $this->line("    movido: {$file}");
                $total++;
            }
        }

        $this->newLine();
        $this->info($dry_run
            ? "Simulación: se moverían {$total} archivo(s)."
            : "Listo: {$total} archivo(s) en el volumen de datos.");

        return $this->finish($dry_run, $total);
    }

    /**
     * Reubica los archivos que quedaron directamente bajo el volumen, de cuando
     * todavía no se separaba por entorno, dentro de la carpeta que les corresponde.
     */
    private function moveLegacyEnvironmentFolders(bool $dry_run): int
    {
        $volume = rtrim((string) config('media.root', storage_path('app/media')), '/\\');
        $moved = 0;

        foreach (['public', 'private'] as $folder) {
            $legacy = $volume.'/'.$folder;

            if (! File::isDirectory($legacy)) {
                continue;
            }

            $target = media_root($folder);
            $files = File::allFiles($legacy);

            $this->line("· {$folder}/ (sin entorno): ".count($files).' archivo(s) a '.media_environment().'/');

            foreach ($files as $file) {
                $relative = str_replace('\\', '/', $file->getRelativePathname());
                $destination = $target.'/'.$relative;

                if ($dry_run) {
                    $this->line("    [simulación] {$folder}/{$relative}");
                    $moved++;

                    continue;
                }

                File::ensureDirectoryExists(dirname($destination));
                File::move($file->getPathname(), $destination);

                $this->line("    movido: {$folder}/{$relative}");
                $moved++;
            }

            if (! $dry_run && File::allFiles($legacy) === []) {
                File::deleteDirectory($legacy);
            }
        }

        if ($moved > 0) {
            $this->newLine();
        }

        return $moved;
    }

    private function finish(bool $dry_run, int $total): int
    {
        if (! $dry_run && $total > 0) {
            $this->line('Recuerda ejecutar «php artisan storage:link» para publicar el enlace /media.');
        }

        return self::SUCCESS;
    }
}
