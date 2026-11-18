<?php

namespace App\Console\Commands;

use App\Services\Delivery\BackupArchive;
use App\Services\Delivery\MySqlClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Throwable;
use ZipArchive;

class RestorePrivateInstallation extends Command
{
    protected $signature = 'aris:restaurar-respaldo {archivo} {base : Base nueva con prefijo aris_restore_} {destino : Directorio privado nuevo} {--sha256= : Hash confiable del ZIP} {--mysql=mysql : Cliente MySQL 8}';

    protected $description = 'Restaura un respaldo verificado en base y directorio nuevos; no modifica la instalación actual';

    public function handle(BackupArchive $verifier, MySqlClient $client): int
    {
        $base = $this->argument('base');
        $destination = $this->argument('destino');
        $createdDirectory = false;
        $createdDatabase = false;
        $temporary = storage_path('framework/restore-'.bin2hex(random_bytes(8)));
        try {
            if (DB::connection()->getDriverName() !== 'mysql' || ! preg_match('/\Aaris_restore_[a-z0-9_]{1,40}\z/', $base)) {
                throw new RuntimeException('Se requiere MySQL y una base nueva aris_restore_*.');
            }
            $parent = realpath(dirname($destination));
            if (! $parent || file_exists($destination) || is_link($destination)
                || str_starts_with($parent.DIRECTORY_SEPARATOR, realpath(public_path()).DIRECTORY_SEPARATOR)
                || str_starts_with($parent.DIRECTORY_SEPARATOR, realpath(storage_path()).DIRECTORY_SEPARATOR)) {
                throw new RuntimeException('El destino debe ser nuevo, privado y externo al almacenamiento actual.');
            }
            File::makeDirectory($temporary, 0700, true);
            $snapshot = $temporary.'/respaldo.zip';
            if (! copy($this->argument('archivo'), $snapshot)) {
                throw new RuntimeException('No se pudo copiar el respaldo para verificarlo.');
            }
            // Se verifica y lee la misma copia privada, no una ruta sustituible por el llamante.
            $manifest = $verifier->verify($snapshot, (string) $this->option('sha256'));
            if (! mkdir($destination, 0700)) {
                throw new RuntimeException('No se pudo reservar el destino.');
            }
            $createdDirectory = true;
            $zip = new ZipArchive;
            if ($zip->open($snapshot, ZipArchive::RDONLY) !== true) {
                throw new RuntimeException('No se pudo leer el respaldo verificado.');
            }
            try {
                foreach ($manifest['archivos'] as $entry) {
                    $target = $entry['ruta'] === 'database.sql' ? $temporary.'/database.sql' : $destination.'/'.substr($entry['ruta'], 8);
                    File::ensureDirectoryExists(dirname($target), 0700);
                    $source = $zip->getStream($entry['ruta']);
                    $output = fopen($target, 'xb');
                    try {
                        if ($source === false || $output === false || stream_copy_to_stream($source, $output, $entry['bytes'] + 1) !== $entry['bytes']) {
                            throw new RuntimeException('No se pudo restaurar una entrada.');
                        }
                    } finally {
                        if (is_resource($source)) {
                            fclose($source);
                        }
                        if (is_resource($output)) {
                            fclose($output);
                        }
                    }
                    chmod($target, 0600);
                }
            } finally {
                $zip->close();
            }
            DB::statement('CREATE DATABASE `'.$base.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            $createdDatabase = true;
            $config = DB::connection()->getConfig();
            $client->run($this->option('mysql'), ['--binary-mode', '--database='.$base], $config, $temporary.'/database.sql');
            config(['database.connections.aris_restore_check' => array_replace($config, ['database' => $base, 'url' => null])]);
            DB::purge('aris_restore_check');
            foreach (DB::connection('aris_restore_check')->table('file_blobs')->where('status', 'disponible')->orderBy('id')->lazyById(100) as $blob) {
                $name = 'private/'.$blob->storage_key;
                if ($blob->disk !== 'local' || ! $verifier->allowed($name)) {
                    throw new RuntimeException('El blob restaurado declara una ruta o disco inválidos.');
                }
                $path = $destination.'/'.$blob->storage_key;
                if (! is_file($path) || filesize($path) !== (int) $blob->size_bytes || ! hash_equals($blob->sha256, hash_file('sha256', $path))) {
                    throw new RuntimeException('La integridad de un blob restaurado no coincide.');
                }
            }
            $this->info('Restauración aislada completada; base '.$base.'. Integridad de blobs disponibles verificada.');
            $this->line('Falta configurar una copia de la aplicación con su APP_KEY custodiada y comprobar el recorrido HTTP.');

            return self::SUCCESS;
        } catch (Throwable $error) {
            if ($createdDatabase) {
                DB::statement('DROP DATABASE `'.$base.'`');
            }
            if ($createdDirectory) {
                File::deleteDirectory($destination);
            }
            $this->error($error->getMessage());

            return self::FAILURE;
        } finally {
            DB::purge('aris_restore_check');
            File::deleteDirectory($temporary);
        }
    }
}
