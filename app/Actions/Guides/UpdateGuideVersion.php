<?php

namespace App\Actions\Guides;

use App\Models\Activity;
use App\Models\ActivityVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Una versión publicada es inmutable (INV-011): editarla crea una versión nueva. El número se
 * calcula dentro de una transacción con bloqueo de la fila padre; el UNIQUE de la tabla es la
 * última barrera contra dos versiones concurrentes con el mismo número.
 *
 * Control de concurrencia optimista: el formulario debe traer la revisión esperada
 * (`lock_version`) y se comprueba dentro de la transacción antes de escribir. Si no coincide,
 * el formulario está obsoleto y se rechaza para no sobrescribir cambios válidos.
 */
final class UpdateGuideVersion
{
    public function execute(ActivityVersion $version, array $cambios, User $autor): ActivityVersion
    {
        return DB::transaction(function () use ($version, $cambios, $autor): ActivityVersion {
            $actividad = Activity::whereKey($version->activity_id)->lockForUpdate()->firstOrFail();
            $version = ActivityVersion::whereKey($version->id)->lockForUpdate()->firstOrFail();

            // Revisión esperada enviada por el formulario. Si difiere de la actual, el formulario
            // es obsoleto: no se acepta la escritura.
            $revisionEsperada = $cambios['lock_version'] ?? null;
            $cambios = array_intersect_key($cambios, array_flip(['title', 'content']));

            if ($revisionEsperada !== null && $version->lock_version !== (int) $revisionEsperada) {
                throw ValidationException::withMessages([
                    'lock_version' => 'La guía fue modificada por otro usuario. Recarga la página antes de guardar.',
                ]);
            }

            if ($version->status !== 'publicada') {
                $version->update([
                    'title' => $cambios['title'] ?? $version->title,
                    'content' => $cambios['content'] ?? $version->content,
                    'checksum' => hash('sha256', (string) ($cambios['content'] ?? $version->content)),
                    'lock_version' => $version->lock_version + 1,
                ]);

                return $version->fresh();
            }

            // Versión publicada inmutable: editar genera un borrador nuevo, nunca altera la
            // publicada ni incrementa su número.
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
                'lock_version' => 1,
            ]);
        });
    }
}
