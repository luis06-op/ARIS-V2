<?php

namespace App\Services\Storage;

use App\Exceptions\InvalidEvidence;
use Illuminate\Http\UploadedFile;

/**
 * Valida evidencias por contenido, nunca por la cabecera enviada por el cliente (ADR-003).
 * Los límites son los conservadores de docs/architecture/ALMACENAMIENTO-PRIVADO.md (PI-010).
 *
 * Para DOCX y XLSX la firma ZIP (PK\x03\x04) es necesaria pero NO suficiente: se valida la
 * estructura OOXML de manera acotada (partes obligatorias, tipos de contenido y relaciones)
 * sin extraer el paquete indiscriminadamente, protegiéndose de bombas de descompresión y
 * de paquetes ZIP genéricos con extensión cruzada.
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
            throw new InvalidEvidence('El archivo no tiene la firma y estructura esperadas de su formato.');
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

    private function iniciaCon(string $ruta, string $prefijo): bool
    {
        $gestor = fopen($ruta, 'rb');
        if ($gestor === false) {
            return false;
        }
        $cabecera = fread($gestor, strlen($prefijo));
        fclose($gestor);

        return $cabecera === $prefijo;
    }

    private function firmaValida(string $ruta, string $extension): bool
    {
        return match ($extension) {
            'pdf' => $this->iniciaCon($ruta, '%PDF-'),
            'docx', 'xlsx' => $this->esZipValido($ruta) && $this->estructuraOoxmlValida($ruta, $extension),
            'jpg', 'jpeg' => $this->iniciaCon($ruta, "\xFF\xD8\xFF"),
            'png' => $this->iniciaCon($ruta, "\x89PNG\r\n\x1A\n"),
            default => false,
        };
    }

    private function esZipValido(string $ruta): bool
    {
        if (! $this->iniciaCon($ruta, "PK\x03\x04")) {
            return false;
        }

        $zip = new \ZipArchive;
        if ($zip->open($ruta) !== true) {
            return false;
        }
        $zip->close();

        return true;
    }

    /**
     * Valida la estructura OOXML de un paquete DOCX o XLSX sin extraerlo a disco.
     *
     * Requisitos: partes obligatorias ([Content_Types].xml, _rels/.rels y la parte
     * principal del documento), tipo de contenido coherente con la extensión y relación
     * de empaquetado que apunte a la parte principal. Límites defensivos contra paquetes
     * con muchas entradas o descompresión desbordada.
     */
    private function estructuraOoxmlValida(string $ruta, string $extension): bool
    {
        $zip = new \ZipArchive;
        if ($zip->open($ruta) !== true) {
            return false;
        }

        try {
            $totalEntradas = $zip->numFiles;
            $totalDescomprimido = 0;
            $partesNecesarias = $this->partesObligatorias($extension);
            $partesEncontradas = [];

            for ($i = 0; $i < $totalEntradas; $i++) {
                $estadistica = $zip->statIndex($i);
                if ($estadistica === false) {
                    return false;
                }

                // Límites defensivos: evitar bombas de descompresión y paquetes desmesurados.
                $totalDescomprimido += (int) $estadistica['size'];
                if ($totalDescomprimido > 60 * 1024 * 1024) {
                    return false; // límite total de contenido descomprimido
                }
                if ((int) $estadistica['size'] > 20 * 1024 * 1024) {
                    return false; // una sola entrada no puede ser tan grande
                }

                $nombre = (string) $estadistica['name'];
                if (in_array($nombre, $partesNecesarias, true)) {
                    $partesEncontradas[] = $nombre;
                }
            }

            if ($totalEntradas > 3000) {
                return false; // demasiadas entradas para una evidencia de oficina
            }

            foreach ($partesNecesarias as $parte) {
                if (! in_array($parte, $partesEncontradas, true)) {
                    return false; // falta una parte obligatoria del paquete OOXML
                }
            }

            // Tipo de contenido de la parte principal, coherente con la extensión.
            if (! $this->tipoContenidoPrincipalCoherente($zip, $extension)) {
                return false;
            }

            // Relación de empaquetado que apunte a la parte principal del documento.
            if (! $this->relacionApuntaADocumentoPrincipal($zip, $extension)) {
                return false;
            }

            return true;
        } finally {
            $zip->close();
        }
    }

    /**
     * @return list<string>
     */
    private function partesObligatorias(string $extension): array
    {
        $comunes = ['[Content_Types].xml', '_rels/.rels'];

        return $extension === 'xlsx'
            ? array_merge($comunes, ['xl/workbook.xml'])
            : array_merge($comunes, ['word/document.xml']);
    }

    private function tipoContenidoPrincipalCoherente(\ZipArchive $zip, string $extension): bool
    {
        $contenido = $zip->getFromName('[Content_Types].xml');
        if ($contenido === false || $contenido === '') {
            return false;
        }

        $xml = $this->cargarXmlSeguro($contenido);
        if ($xml === null) {
            return false; // XML mal formado: se rechaza la evidencia
        }

        $partePrincipal = $extension === 'xlsx' ? '/xl/workbook.xml' : '/word/document.xml';
        $espera = $extension === 'xlsx'
            ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml'
            : 'application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml';

        foreach ($xml->Override as $override) {
            $parte = (string) $override['PartName'];
            $tipo = (string) $override['ContentType'];
            if ($parte === $partePrincipal && $tipo === $espera) {
                return true;
            }
        }

        return false;
    }

    private function relacionApuntaADocumentoPrincipal(\ZipArchive $zip, string $extension): bool
    {
        $contenido = $zip->getFromName('_rels/.rels');
        if ($contenido === false || $contenido === '') {
            return false;
        }

        $xml = $this->cargarXmlSeguro($contenido);
        if ($xml === null) {
            return false; // XML mal formado: se rechaza la evidencia
        }

        $objetivo = $extension === 'xlsx' ? 'xl/workbook.xml' : 'word/document.xml';
        $tipoEsperado = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument';

        foreach ($xml->Relationship as $relacion) {
            $tipo = (string) $relacion['Type'];
            $target = (string) $relacion['Target'];
            $targetMode = (string) $relacion['TargetMode'];
            // Solo la relación principal de documento, con tipo exacto y destino interno
            // (TargetMode ausente o "Internal"). Se rechazan relaciones externas.
            if ($target === $objetivo && $tipo === $tipoEsperado
                && ($targetMode === '' || $targetMode === 'Internal')
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Carga un string XML de forma segura, sin permitir que advertencias de libxml
     * se conviertan en ErrorException de Laravel. Devuelve null si el XML es inválido.
     */
    private function cargarXmlSeguro(string $xml): ?\SimpleXMLElement
    {
        $anterior = libxml_use_internal_errors(true);
        try {
            $resultado = simplexml_load_string($xml);
            libxml_clear_errors();

            // simplexml_load_string devuelve false en error; el tipo de retorno exige null.
            return $resultado === false ? null : $resultado;
        } finally {
            libxml_use_internal_errors($anterior);
        }
    }
}
