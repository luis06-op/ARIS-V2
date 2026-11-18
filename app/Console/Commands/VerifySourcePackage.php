<?php

namespace App\Console\Commands;

use App\Services\Delivery\SourcePackageVerifier;
use Illuminate\Console\Command;
use RuntimeException;

class VerifySourcePackage extends Command
{
    protected $signature = 'aris:verificar-paquete {archivo : Ruta del ZIP que se comprobará sin extraerlo}';

    protected $description = 'Verifica inventario, rutas y SHA-256 de un paquete de fuentes sin extraerlo';

    public function handle(SourcePackageVerifier $verifier): int
    {
        try {
            $manifest = $verifier->verify($this->argument('archivo'));
        } catch (RuntimeException $error) {
            $this->error($error->getMessage());

            return self::FAILURE;
        }
        $this->info('Paquete íntegro: '.count($manifest['archivos']).' archivos; commit '.$manifest['commit']);

        return self::SUCCESS;
    }
}
