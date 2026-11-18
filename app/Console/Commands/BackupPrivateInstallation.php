<?php

namespace App\Console\Commands;

use App\Models\FileBlob;
use App\Services\Delivery\BackupArchive;
use App\Services\Delivery\MySqlClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;
use ZipArchive;

class BackupPrivateInstallation extends Command
{
    protected $signature = 'aris:respaldar {destino} {--mysqldump=mysqldump : Ejecutable del cliente MySQL 8}';

    protected $description = 'Respalda MySQL y disco privado durante mantenimiento, sin sobrescribir destinos';

    public function handle(MySqlClient $client, BackupArchive $verifier): int
    {
        $destination = $this->argument('destino');
        $temporary = storage_path('framework/backup-'.bin2hex(random_bytes(8)));
        $reserved = false;
        $zip = new ZipArchive;
        $opened = false;
        try {
            if (! app()->isDownForMaintenance() || DB::connection()->getDriverName() !== 'mysql') {
                throw new RuntimeException('Se requiere MySQL y modo mantenimiento; detener también trabajadores y escrituras externas.');
            }
            $version = DB::selectOne('SELECT VERSION() AS version')->version;
            if (! str_starts_with($version, '8.') || str_contains($version, 'MariaDB')) {
                throw new RuntimeException('Este procedimiento requiere MySQL 8.x.');
            }
            $root = realpath(Storage::disk('local')->path(''));
            $parent = realpath(dirname($destination));
            if (! $root || ! $parent || $parent === $root || str_starts_with($parent, $root.DIRECTORY_SEPARATOR)
                || str_starts_with($parent.DIRECTORY_SEPARATOR, realpath(public_path()).DIRECTORY_SEPARATOR)) {
                throw new RuntimeException('El respaldo debe quedar fuera del disco privado y del directorio público.');
            }
            $handle = @fopen($destination, 'x+b');
            if ($handle === false) {
                throw new RuntimeException('El destino debe ser nuevo y su directorio debe existir.');
            }
            fclose($handle);
            chmod($destination, 0600);
            $reserved = true;
            File::makeDirectory($temporary, 0700, true);
            $client->run($this->option('mysqldump'), [
                '--single-transaction', '--skip-lock-tables', '--no-tablespaces', '--set-gtid-purged=OFF',
                '--hex-blob', '--triggers', '--result-file='.$temporary.'/database.sql',
                '--', DB::connection()->getDatabaseName(),
            ], DB::connection()->getConfig());
            if ($zip->open($destination, ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('No se pudo crear el ZIP.');
            }
            $opened = true;
            $files = ['database.sql' => $temporary.'/database.sql'];
            $copyBytes = filesize($temporary.'/database.sql');
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $entry) {
                if ($entry->isLink()) {
                    throw new RuntimeException('No se admiten enlaces en el disco privado.');
                }
            }
            foreach (File::allFiles($root, true) as $file) {
                if ($file->isLink() || ! $file->isFile()) {
                    throw new RuntimeException('No se admiten enlaces en el respaldo.');
                }
                if ($file->getFilename() === '.gitignore') {
                    continue;
                }
                $name = 'private/'.$file->getRelativePathname();
                $copyBytes += $file->getSize();
                if ($copyBytes > BackupArchive::MAX_BYTES || count($files) >= 10001) {
                    throw new RuntimeException('El almacenamiento supera los límites técnicos de respaldo.');
                }
                if (! $verifier->allowed($name)) {
                    throw new RuntimeException('Ruta privada no permitida.');
                }
                $copy = $temporary.'/'.$name;
                File::ensureDirectoryExists(dirname($copy), 0700);
                if (! copy($file->getPathname(), $copy)) {
                    throw new RuntimeException('No se pudo copiar una evidencia.');
                }
                $files[$name] = $copy;
            }
            foreach (FileBlob::where('status', 'disponible')->lazyById(100) as $blob) {
                $copy = $files['private/'.$blob->storage_key] ?? null;
                if ($blob->disk !== 'local' || ! $copy || filesize($copy) !== (int) $blob->size_bytes
                    || ! hash_equals($blob->sha256, hash_file('sha256', $copy))) {
                    throw new RuntimeException('Un blob disponible no coincide con la copia respaldada.');
                }
            }
            $manifest = ['formato' => 1, 'tipo' => 'respaldo-mysql-privado', 'mysql' => $version, 'fecha' => now()->toIso8601String(), 'archivos' => []];
            $total = 0;
            foreach ($files as $name => $path) {
                $size = filesize($path);
                $total += $size;
                if ($total > BackupArchive::MAX_BYTES || count($files) > 10001) {
                    throw new RuntimeException('Se excedieron los límites técnicos de respaldo (2 GiB / 10.000 archivos privados).');
                }
                $manifest['archivos'][] = ['ruta' => $name, 'bytes' => $size, 'sha256' => hash_file('sha256', $path)];
                if (! $zip->addFile($path, $name) || ! $zip->setExternalAttributesName($name, ZipArchive::OPSYS_UNIX, 0100600 << 16)) {
                    throw new RuntimeException('No se pudo archivar una entrada.');
                }
            }
            $zip->addFromString('MANIFIESTO.json', json_encode($manifest, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
            $zip->setExternalAttributesName('MANIFIESTO.json', ZipArchive::OPSYS_UNIX, 0100600 << 16);
            if (! $zip->close()) {
                throw new RuntimeException('No se pudo finalizar el respaldo.');
            }
            $opened = false;
            $hash = hash_file('sha256', $destination);
            $verifier->verify($destination, $hash);
            $this->info('Respaldo creado y verificado. SHA-256: '.$hash);
            $this->line('Conservar el hash por separado y custodiar el paquete como dato privado; no incluye .env ni APP_KEY.');

            return self::SUCCESS;
        } catch (Throwable $error) {
            if ($opened) {
                $zip->close();
            }
            if ($reserved) {
                @unlink($destination);
            }
            $this->error($error->getMessage());

            return self::FAILURE;
        } finally {
            File::deleteDirectory($temporary);
        }
    }
}
