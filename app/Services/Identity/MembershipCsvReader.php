<?php

namespace App\Services\Identity;

use RuntimeException;
use SplFileObject;

final class MembershipCsvReader
{
    public const MAX_ROWS = 1000;

    public const MAX_BYTES = 1024 * 1024;

    /** @return array{validos: array<int, string>, invalidos: array<int, string>, duplicados: array<int, string>} */
    public function read(string $path): array
    {
        if (! is_file($path) || filesize($path) > self::MAX_BYTES) {
            throw new RuntimeException('El archivo debe existir y no superar 1 MB.');
        }
        $file = new SplFileObject($path, 'rb');
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY);
        $header = $file->fgetcsv();
        if (! is_array($header) || ! in_array('email', array_map(fn ($cell) => is_string($cell) ? mb_strtolower(trim($cell)) : '', $header), true)) {
            throw new RuntimeException('El CSV debe tener una columna «email».');
        }
        $index = array_search('email', array_map(fn ($cell) => is_string($cell) ? mb_strtolower(trim($cell)) : '', $header), true);
        $validos = [];
        $invalidos = [];
        $duplicados = [];
        $seen = [];
        $rows = 0;
        while (! $file->eof()) {
            $row = $file->fgetcsv();
            if (! is_array($row) || ($row[0] === null && count($row) <= 1)) {
                continue;
            }
            $rows++;
            if ($rows > self::MAX_ROWS) {
                throw new RuntimeException('El archivo supera el límite de 1000 filas.');
            }
            $email = mb_strtolower(trim((string) ($row[$index] ?? '')));
            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $invalidos[] = $email === '' ? 'fila '.$rows.': correo vacío' : 'fila '.$rows.': correo no válido';

                continue;
            }
            if (isset($seen[$email])) {
                $duplicados[] = $email;

                continue;
            }
            $seen[$email] = true;
            $validos[] = $email;
        }

        return ['validos' => $validos, 'invalidos' => $invalidos, 'duplicados' => $duplicados];
    }
}
