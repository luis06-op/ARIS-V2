<?php

namespace App\Services\Delivery;

use RuntimeException;
use ZipArchive;

final class BackupArchive
{
    public const MAX_BYTES = 2 * 1024 * 1024 * 1024;

    public function build(array $files, array $metadata = []): string
    {
        $manifest = array_merge(['formato' => 1, 'tipo' => 'respaldo-mysql-privado', 'archivos' => []], $metadata);
        $temporary = tempnam(storage_path('framework'), 'backup-test-');
        if ($temporary === false) {
            throw new RuntimeException('No se pudo reservar un archivo temporal.');
        }
        $zip = new ZipArchive;
        try {
            if ($zip->open($temporary, ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('No se pudo crear el ZIP.');
            }
            foreach ($files as $name => $bytes) {
                $manifest['archivos'][] = ['ruta' => $name, 'bytes' => strlen($bytes), 'sha256' => hash('sha256', $bytes)];
                $zip->addFromString($name, $bytes);
            }
            $zip->addFromString('MANIFIESTO.json', json_encode($manifest, JSON_THROW_ON_ERROR));
            $zip->setExternalAttributesName('MANIFIESTO.json', ZipArchive::OPSYS_UNIX, 0100600 << 16);
            foreach (array_keys($files) as $name) {
                $zip->setExternalAttributesName($name, ZipArchive::OPSYS_UNIX, 0100600 << 16);
            }
            if (! $zip->close()) {
                throw new RuntimeException('No se pudo finalizar el ZIP.');
            }

            return $temporary;
        } catch (Throwable $error) {
            @$zip->close();
            @unlink($temporary);
            throw $error;
        }
    }

    public function verify(string $path, string $expectedHash = '', bool $requireHash = true): array
    {
        if ($requireHash && (! preg_match('/\A[a-f0-9]{64}\z/', $expectedHash) || ! is_file($path)
            || ! hash_equals($expectedHash, hash_file('sha256', $path)))) {
            throw new RuntimeException('El SHA-256 del respaldo no coincide con la referencia confiable.');
        }
        if (! is_file($path)) {
            throw new RuntimeException('No se pudo abrir el respaldo.');
        }
        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::RDONLY) !== true) {
            throw new RuntimeException('No se pudo abrir el respaldo.');
        }
        try {
            $stat = $zip->statName('MANIFIESTO.json');
            if (! $stat || $stat['size'] > 4 * 1024 * 1024 || $zip->numFiles > 10002) {
                throw new RuntimeException('Manifiesto ausente o límites técnicos excedidos.');
            }
            $json = $zip->getFromName('MANIFIESTO.json');
            if ($json === false) {
                throw new RuntimeException('No se pudo leer el manifiesto.');
            }
            $manifest = json_decode($json, true, 64, JSON_THROW_ON_ERROR);
            if (! is_array($manifest) || ($manifest['tipo'] ?? null) !== 'respaldo-mysql-privado'
                || ($manifest['formato'] ?? null) !== 1 || ! is_array($manifest['archivos'] ?? null)
                || ! array_is_list($manifest['archivos'])) {
                throw new RuntimeException('Manifiesto de respaldo inválido.');
            }
            $entries = [];
            $total = 0;
            foreach ($manifest['archivos'] as $entry) {
                $name = $entry['ruta'] ?? null;
                if (! is_string($name) || ! $this->allowed($name) || isset($entries[$name])
                    || ! is_int($entry['bytes'] ?? null) || $entry['bytes'] < 0
                    || ! is_string($entry['sha256'] ?? null) || ! preg_match('/\A[a-f0-9]{64}\z/', $entry['sha256'])) {
                    throw new RuntimeException('Inventario de respaldo inválido.');
                }
                $total += $entry['bytes'];
                $entries[$name] = $entry;
            }
            if ($total > self::MAX_BYTES || ! isset($entries['database.sql']) || count($entries) + 1 !== $zip->numFiles) {
                throw new RuntimeException('Respaldo incompleto o demasiado grande.');
            }
            $seen = [];
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $stat = $zip->statIndex($index);
                $name = $stat['name'];
                $zip->getExternalAttributesIndex($index, $system, $attributes);
                if (isset($seen[$name]) || ($system === ZipArchive::OPSYS_UNIX && (($attributes >> 16) & 0170000) !== 0100000)) {
                    throw new RuntimeException('Entradas duplicadas o no regulares.');
                }
                $seen[$name] = true;
                if ($name === 'MANIFIESTO.json') {
                    continue;
                }
                if (! isset($entries[$name]) || $stat['size'] !== $entries[$name]['bytes']) {
                    throw new RuntimeException('El archivo no coincide con el inventario.');
                }
                $stream = $zip->getStream($name);
                if ($stream === false) {
                    throw new RuntimeException('No se pudo leer el respaldo.');
                }
                try {
                    $hash = hash_init('sha256');
                    $bytes = hash_update_stream($hash, $stream, $stat['size'] + 1);
                    if ($bytes !== $stat['size'] || ! hash_equals($entries[$name]['sha256'], hash_final($hash))) {
                        throw new RuntimeException('Un archivo del respaldo está corrupto.');
                    }
                } finally {
                    fclose($stream);
                }
            }

            return $manifest;
        } catch (\JsonException $error) {
            throw new RuntimeException('JSON de respaldo inválido.', previous: $error);
        } finally {
            $zip->close();
        }
    }

    public function allowed(string $name): bool
    {
        if ($name === 'database.sql') {
            return true;
        }

        return str_starts_with($name, 'private/') && ! preg_match('/[\\\\\x00-\x1f:]/', $name)
            && ! array_intersect(explode('/', $name), ['', '.', '..']);
    }
}
