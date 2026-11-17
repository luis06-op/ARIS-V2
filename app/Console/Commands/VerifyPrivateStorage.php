<?php

namespace App\Console\Commands;

use App\Models\FileBlob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Compara la base con el disco privado. Nunca borra archivos ni blobs: solo marca incidentes
 * y mueve a cuarentena los archivos sin metadato (docs/architecture/ALMACENAMIENTO-PRIVADO.md §6).
 */
class VerifyPrivateStorage extends Command
{
    protected $signature = 'aris:verificar-storage {--dry-run : Solo informa, sin modificar base ni archivos}';

    protected $description = 'Verifica la coherencia entre los blobs de evidencia y storage/app/private';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $sinArchivo = 0;
        $sinBlob = 0;

        foreach (FileBlob::where('status', 'disponible')->get() as $blob) {
            if (Storage::disk('local')->exists($blob->storage_key)) {
                continue;
            }

            $sinArchivo++;
            $this->error("Blob {$blob->uuid} sin archivo: {$blob->storage_key}");

            if (! $dryRun) {
                $blob->update(['status' => 'huerfano']);
            }
        }

        foreach (Storage::disk('local')->files('documents') as $ruta) {
            if (FileBlob::where('storage_key', $ruta)->exists()) {
                continue;
            }

            $sinBlob++;
            $this->warn("Archivo sin blob, se mueve a cuarentena: {$ruta}");

            if (! $dryRun) {
                Storage::disk('local')->move($ruta, 'quarantine/'.basename($ruta));
            }
        }

        $huerfanos = FileBlob::where('status', 'huerfano')->count();

        $this->info(sprintf(
            'Revisión terminada: %d sin archivo, %d sin blob, %d huérfanos pendientes de decisión.%s',
            $sinArchivo,
            $sinBlob,
            $huerfanos,
            $dryRun ? ' (dry-run: sin cambios)' : ''
        ));

        return self::SUCCESS;
    }
}
