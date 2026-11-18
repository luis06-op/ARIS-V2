<?php

namespace App\Console\Commands;

use App\Models\FileBlob;
use App\Services\Storage\EvidenceIntegrity;
use Illuminate\Console\Command;

class VerifyEvidenceIntegrity extends Command
{
    protected $signature = 'aris:verificar-integridad';

    protected $description = 'Comprueba tamaño, SHA-256 y metadatos de evidencias disponibles, sin modificar archivos ni base';

    public function handle(EvidenceIntegrity $integrity): int
    {
        $checked = 0;
        $incidents = 0;
        foreach (FileBlob::where('status', 'disponible')->lazyById(100) as $blob) {
            $checked++;
            $stream = $integrity->openBlob($blob);
            $valid = is_resource($stream);
            if ($valid) {
                fclose($stream);
            }
            foreach ($blob->documentVersions()->lazyById(100) as $version) {
                $valid = $integrity->matchesMetadata($version, $blob) && $valid;
            }
            if (! $valid) {
                $incidents++;
                $this->error("Integridad no verificada para el blob {$blob->uuid}.");
            }
        }
        $this->info("Comprobación de solo lectura: {$checked} blobs disponibles, {$incidents} incidentes.");

        return $incidents === 0 ? self::SUCCESS : self::FAILURE;
    }
}
