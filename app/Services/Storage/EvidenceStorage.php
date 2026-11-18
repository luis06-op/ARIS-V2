<?php

namespace App\Services\Storage;

use App\Exceptions\InvalidEvidence;
use App\Models\AuditEvent;
use App\Models\Document;
use App\Models\DocumentLink;
use App\Models\DocumentVersion;
use App\Models\FileBlob;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * Guarda evidencias fuera del DocumentRoot y sirve solo versiones autorizadas (ADR-003).
 * EvidenceController autoriza la descarga sobre DocumentVersion; AttachEvidence autoriza
 * y bloquea la versión de entrega antes de invocar este servicio interno.
 */
final class EvidenceStorage
{
    public function __construct(private readonly EvidenceValidator $validator) {}

    /**
     * @param  array<string, mixed>  $vinculo
     */
    public function attach(
        Document $documento,
        UploadedFile $archivo,
        User $autor,
        array $vinculo = [],
    ): DocumentVersion {
        $datos = $this->validator->validate($archivo);
        $vinculo = array_intersect_key($vinculo, array_flip([
            'activity_version_id', 'submission_version_id', 'review_round_id',
        ]));

        if ($vinculo !== [] && count($vinculo) !== 1) {
            throw new InvalidEvidence('El vínculo debe indicar un solo destino.');
        }

        $uuid = (string) Str::uuid();
        $nombreFisico = $uuid.'.'.$datos['extension'];
        $rutaCuarentena = 'quarantine/'.$nombreFisico;

        Storage::disk('local')->putFileAs('quarantine', $archivo, $nombreFisico);

        $rutaReal = Storage::disk('local')->path($rutaCuarentena);

        if (! is_file($rutaReal) || hash_file('sha256', $rutaReal) !== $datos['sha256']) {
            Storage::disk('local')->delete($rutaCuarentena);

            throw new InvalidEvidence('No se pudo verificar la copia del archivo en cuarentena.');
        }

        try {
            $version = DB::transaction(function () use ($documento, $autor, $datos, $uuid, $rutaCuarentena, $vinculo): DocumentVersion {
                $padre = Document::whereKey($documento->id)->lockForUpdate()->firstOrFail();
                $numero = (int) $padre->versions()->max('version_number') + 1;

                $blob = FileBlob::create([
                    'uuid' => $uuid,
                    'disk' => 'local',
                    'storage_key' => $rutaCuarentena,
                    'size_bytes' => $datos['size'],
                    'sha256' => $datos['sha256'],
                    'mime' => $datos['mime'],
                    'extension' => $datos['extension'],
                    'status' => 'cuarentena',
                ]);

                $version = DocumentVersion::create([
                    'document_id' => $padre->id,
                    'version_number' => $numero,
                    'blob_id' => $blob->id,
                    'author_id' => $autor->id,
                    'original_name' => $datos['original_name'],
                    'mime' => $datos['mime'],
                    'extension' => $datos['extension'],
                    'size_bytes' => $datos['size'],
                    'sha256' => $datos['sha256'],
                ]);

                if ($vinculo !== []) {
                    DocumentLink::create(array_merge([
                        'document_id' => $padre->id,
                        'document_version_id' => $version->id,
                    ], $vinculo));
                }

                AuditEvent::create([
                    'institution_id' => $padre->institution_id,
                    'actor_id' => $autor->id,
                    'action' => 'document.upload',
                    'resource_type' => 'document_version',
                    'resource_id' => (string) $version->id,
                    'result' => 'exito',
                    'metadata' => [
                        'version' => $numero,
                        'extension' => $datos['extension'],
                    ],
                ]);

                return $version;
            });
        } catch (Throwable $error) {
            Storage::disk('local')->delete($rutaCuarentena);

            throw $error;
        }

        $destino = 'documents/'.$nombreFisico;

        if (! Storage::disk('local')->move($rutaCuarentena, $destino)) {
            $version->blob()->update(['status' => 'cuarentena']);

            throw new InvalidEvidence('No se pudo almacenar el archivo de forma definitiva.');
        }

        $version->blob()->update([
            'storage_key' => $destino,
            'status' => 'disponible',
        ]);

        return $version->fresh();
    }

    public function download(DocumentVersion $version): StreamedResponse
    {
        $blob = $version->blob;

        if ($blob->status !== 'disponible' || ! Storage::disk('local')->exists($blob->storage_key)) {
            abort(404);
        }

        $ruta = Storage::disk('local')->path($blob->storage_key);
        $nombre = str_replace('"', '', $this->validator->sanitizarNombre($version->original_name));

        return response()->stream(function () use ($ruta): void {
            $gestor = fopen($ruta, 'rb');

            if ($gestor === false) {
                return;
            }

            fpassthru($gestor);
            fclose($gestor);
        }, 200, [
            'Content-Type' => $blob->mime,
            'Content-Disposition' => 'attachment; filename="'.$nombre.'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
