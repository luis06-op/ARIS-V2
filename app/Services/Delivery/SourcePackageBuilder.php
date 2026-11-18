<?php

namespace App\Services\Delivery;

use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;
use ZipArchive;

final class SourcePackageBuilder
{
    public function __construct(private SourcePackageVerifier $verifier) {}

    public function build(string $repository, string $destination): array
    {
        if (trim($this->git($repository, ['status', '--porcelain=v1', '--untracked-files=normal'])) !== '') {
            throw new RuntimeException('El árbol Git debe estar limpio antes de empaquetar.');
        }
        $commit = trim($this->git($repository, ['rev-parse', 'HEAD']));
        $timestamp = max(315532800, (int) trim($this->git($repository, ['show', '-s', '--format=%ct', $commit])));
        $entries = [];
        $total = 0;
        foreach (explode("\0", $this->git($repository, ['ls-tree', '-r', '-z', '--long', $commit])) as $line) {
            if ($line === '') {
                continue;
            }
            [$metadata, $name] = explode("\t", $line, 2);
            if (! SourcePackageLayout::allows($name)) {
                continue;
            }
            [$mode, $type, $object, $size] = preg_split('/\s+/', $metadata);
            if ($type !== 'blob' || ! in_array($mode, ['100644', '100755'], true)) {
                throw new RuntimeException('No se admiten enlaces simbólicos ni submódulos en el paquete.');
            }
            $size = (int) $size;
            $total += $size;
            if ($size > SourcePackageLayout::MAX_FILE_BYTES || $total > SourcePackageLayout::MAX_TOTAL_BYTES) {
                throw new RuntimeException('Las fuentes superan los límites técnicos del paquete.');
            }
            $entries[$name] = ['object' => $object, 'bytes' => $size, 'mode' => $mode];
        }
        if (count($entries) > SourcePackageLayout::MAX_FILES || array_diff(SourcePackageLayout::REQUIRED, array_keys($entries))) {
            throw new RuntimeException('El repositorio no contiene todos los archivos obligatorios del paquete.');
        }
        ksort($entries, SORT_STRING);
        $reserved = @fopen($destination, 'x+b');
        if ($reserved === false) {
            throw new RuntimeException('El destino debe ser nuevo y su directorio debe existir. No se sobrescriben paquetes.');
        }
        fclose($reserved);
        $zip = new ZipArchive;
        $opened = false;
        try {
            if ($zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('No se pudo crear el paquete ZIP.');
            }
            $opened = true;
            $manifest = ['formato' => 1, 'tipo' => 'fuentes-sin-dependencias', 'commit' => $commit, 'archivos' => []];
            foreach ($entries as $name => $entry) {
                $bytes = $this->git($repository, ['cat-file', 'blob', $entry['object']]);
                $this->add($zip, $name, $bytes, $timestamp, $entry['mode'] === '100755' ? 0100755 : 0100644);
                $manifest['archivos'][] = ['ruta' => $name, 'bytes' => strlen($bytes), 'sha256' => hash('sha256', $bytes)];
            }
            $this->add($zip, SourcePackageLayout::MANIFEST, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n", $timestamp, 0100644);
            if (! $zip->close()) {
                throw new RuntimeException('No se pudo finalizar el paquete ZIP.');
            }
            $opened = false;
            $this->verifier->verify($destination);

            return $manifest;
        } catch (Throwable $error) {
            if ($opened) {
                $zip->close();
            }
            unlink($destination);
            throw $error;
        }
    }

    private function git(string $repository, array $arguments): string
    {
        return (new Process(['git', ...$arguments], $repository))->setTimeout(120)->mustRun()->getOutput();
    }

    private function add(ZipArchive $zip, string $name, string $bytes, int $timestamp, int $mode): void
    {
        if (! $zip->addFromString($name, $bytes) || ! $zip->setCompressionName($name, ZipArchive::CM_STORE)
            || ! $zip->setMtimeName($name, $timestamp)
            || ! $zip->setExternalAttributesName($name, ZipArchive::OPSYS_UNIX, $mode << 16)) {
            throw new RuntimeException('No se pudo escribir una entrada del paquete.');
        }
    }
}
