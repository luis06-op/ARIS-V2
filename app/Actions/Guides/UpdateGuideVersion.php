<?php

namespace App\Actions\Guides;

use App\Models\Activity;
use App\Models\ActivityVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Una versión publicada es inmutable (INV-011): editarla crea una versión nueva. El número se
 * calcula dentro de una transacción con bloqueo de la fila padre; el UNIQUE de la tabla es la
 * última barrera contra dos versiones concurrentes con el mismo número.
 */
final class UpdateGuideVersion
{
    public function execute(ActivityVersion $version, array $cambios, User $autor): ActivityVersion
    {
        $cambios = array_intersect_key($cambios, array_flip(['title', 'content']));

        return DB::transaction(function () use ($version, $cambios, $autor): ActivityVersion {
            $actividad = Activity::whereKey($version->activity_id)->lockForUpdate()->firstOrFail();
            $version = ActivityVersion::whereKey($version->id)->lockForUpdate()->firstOrFail();
            if ($version->status !== 'publicada') {
                $version->update($cambios + ['checksum' => hash('sha256', (string) ($cambios['content'] ?? $version->content))]);

                return $version->fresh();
            }
            $numero = (int) $actividad->versions()->max('version_number') + 1;
            $contenido = $cambios['content'] ?? $version->content;

            return ActivityVersion::create([
                'activity_id' => $actividad->id,
                'version_number' => $numero,
                'title' => $cambios['title'] ?? $version->title,
                'content' => $contenido,
                'author_id' => $autor->id,
                'checksum' => hash('sha256', (string) $contenido),
                'status' => 'borrador',
                'published_at' => null,
            ]);
        });
    }
}
