<?php

namespace App\Services\Storage;

use App\Exceptions\InvalidEvidence;
use Illuminate\Http\UploadedFile;

/**
 * Valida evidencias por contenido, nunca por la cabecera enviada por el cliente (ADR-003).
 * Los límites son los conservadores de docs/architecture/ALMACENAMIENTO-PRIVADO.md (PI-010).
 */
final class EvidenceValidator
{
    /**
     * @return array{original_name: string, extension: string, mime: string, size: int, sha256: string}
     */
    public function validate(UploadedFile $archivo): array
    {
        $original = $this->sanitizarNombre($archivo->getClientOriginalName());
        $extension = strtolower($archivo->getClientOriginalExtension());
        $formatos = config('aris.files.formats', []);

        if (! isset($formatos[$extension])) {
            throw new InvalidEvidence('El formato del archivo no está permitido.');
        }

        $tamanio = $archivo->getSize();

        if ($tamanio <= 0) {
            throw new InvalidEvidence('El archivo está vacío.');
        }

        if ($tamanio > (int) config('aris.files.max_bytes')) {
            throw new InvalidEvidence('El archivo supera el tamaño máximo permitido.');
        }

        $ruta = $archivo->getRealPath();
        $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $ruta) ?: 'application/octet-stream';

        if (! in_array($mime, $formatos[$extension], true)) {
            throw new InvalidEvidence('El contenido del archivo no coincide con su extensión.');
        }

        if (! $this->firmaValida($ruta, $extension)) {
            throw new InvalidEvidence('El archivo no tiene la firma esperada de su formato.');
        }

        return [
            'original_name' => $original,
            'extension' => $extension,
            'mime' => $mime,
            'size' => $tamanio,
            'sha256' => hash_file('sha256', $ruta),
        ];
    }

    public function sanitizarNombre(string $nombre): string
    {
        $nombre = str_replace('\\', '/', $nombre);
        $nombre = basename($nombre);
        $nombre = preg_replace('/[[:cntrl:]\/\\\\]/', '', $nombre) ?? '';
        $nombre = trim(str_replace('..', '', $nombre));

        return mb_substr($nombre === '' ? 'archivo' : $nombre, 0, 255);
    }

    private function firmaValida(string $ruta, string $extension): bool
    {
        $cabecera = (string) file_get_contents($ruta, false, null, 0, 8);

        return match ($extension) {
            'pdf' => str_starts_with($cabecera, '%PDF-'),
            'docx', 'xlsx' => str_starts_with($cabecera, "PK\x03\x04"),
            'jpg', 'jpeg' => str_starts_with($cabecera, "\xFF\xD8\xFF"),
            'png' => str_starts_with($cabecera, "\x89PNG\r\n\x1A\n"),
            default => false,
        };
    }
}
