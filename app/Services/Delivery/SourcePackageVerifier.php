<?php

namespace App\Services\Delivery;

use JsonException;
use RuntimeException;
use ZipArchive;

final class SourcePackageVerifier
{
    public function verify(string $path): array
    {
        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::RDONLY) !== true) {
            throw new RuntimeException('No se pudo abrir el paquete ZIP.');
        }
        try {
            if ($zip->numFiles > SourcePackageLayout::MAX_FILES + 1) {
                throw new RuntimeException('El paquete supera el límite técnico de entradas.');
            }
            $stat = $zip->statName(SourcePackageLayout::MANIFEST);
            if (! $stat || $stat['size'] > 1024 * 1024) {
                throw new RuntimeException('Falta el manifiesto o supera el límite técnico.');
            }
            $json = $zip->getFromName(SourcePackageLayout::MANIFEST, 1024 * 1024);
            if ($json === false) {
                throw new RuntimeException('No se pudo leer el manifiesto.');
            }
            $manifest = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($manifest) || ($manifest['formato'] ?? null) !== 1 || ($manifest['tipo'] ?? null) !== 'fuentes-sin-dependencias'
                || ! is_string($manifest['commit'] ?? null) || ! preg_match('/\A(?:[a-f0-9]{40}|[a-f0-9]{64})\z/', $manifest['commit'])
                || ! is_array($manifest['archivos'] ?? null) || ! array_is_list($manifest['archivos'])) {
                throw new RuntimeException('El formato del manifiesto no es válido.');
            }
            $expected = [];
            $total = 0;
            foreach ($manifest['archivos'] as $entry) {
                if (! is_array($entry) || ! is_string($entry['ruta'] ?? null) || ! SourcePackageLayout::allows($entry['ruta'])
                    || isset($expected[$entry['ruta']]) || ! is_int($entry['bytes'] ?? null) || $entry['bytes'] < 0
                    || $entry['bytes'] > SourcePackageLayout::MAX_FILE_BYTES || ! is_string($entry['sha256'] ?? null)
                    || ! preg_match('/\A[a-f0-9]{64}\z/', $entry['sha256'])) {
                    throw new RuntimeException('El manifiesto contiene una entrada no permitida.');
                }
                $total += $entry['bytes'];
                $expected[$entry['ruta']] = $entry;
            }
            if ($total > SourcePackageLayout::MAX_TOTAL_BYTES || count($expected) + 1 !== $zip->numFiles
                || array_diff(SourcePackageLayout::REQUIRED, array_keys($expected))) {
                throw new RuntimeException('El inventario está incompleto o supera los límites técnicos.');
            }
            $seen = [];
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $stat = $zip->statIndex($index);
                $name = $stat['name'];
                $zip->getExternalAttributesIndex($index, $system, $attributes);
                if (isset($seen[$name]) || ($system === ZipArchive::OPSYS_UNIX && (($attributes >> 16) & 0170000) !== 0100000)) {
                    throw new RuntimeException('El paquete contiene duplicados o entradas que no son archivos regulares.');
                }
                $seen[$name] = true;
                if ($name === SourcePackageLayout::MANIFEST) {
                    continue;
                }
                if (! isset($expected[$name]) || $stat['size'] !== $expected[$name]['bytes']) {
                    throw new RuntimeException('Hay archivos ausentes, adicionales o con tamaño incorrecto.');
                }
                $stream = $zip->getStream($name);
                if ($stream === false) {
                    throw new RuntimeException('No se pudo leer una entrada del paquete.');
                }
                try {
                    $hash = hash_init('sha256');
                    $bytes = hash_update_stream($hash, $stream, $expected[$name]['bytes'] + 1);
                    if ($bytes !== $expected[$name]['bytes'] || ! hash_equals($expected[$name]['sha256'], hash_final($hash))) {
                        throw new RuntimeException('La integridad SHA-256 del paquete no coincide con el manifiesto.');
                    }
                } finally {
                    fclose($stream);
                }
            }

            return $manifest;
        } catch (JsonException) {
            throw new RuntimeException('El manifiesto no contiene JSON válido.');
        } finally {
            $zip->close();
        }
    }
}
