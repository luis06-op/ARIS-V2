<?php

/*
|--------------------------------------------------------------------------
| Límites conservadores de evidencia
|--------------------------------------------------------------------------
|
| Valores iniciales tomados de docs/architecture/ALMACENAMIENTO-PRIVADO.md.
| PI-010 sigue pendiente: mientras no se aprueben cuotas formales se aplican
| estos límites conservadores y el vídeo permanece deshabilitado.
|
*/

return [
    'files' => [
        'max_bytes' => env('ARIS_FILE_MAX_BYTES', 10 * 1024 * 1024),
        'max_per_version' => env('ARIS_FILES_PER_VERSION', 5),
        'video_enabled' => false,
        'formats' => [
            'pdf' => ['application/pdf'],
            'docx' => [
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/zip',
            ],
            'xlsx' => [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/zip',
            ],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
        ],
    ],
];
