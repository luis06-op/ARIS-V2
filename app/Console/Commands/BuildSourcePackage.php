<?php

namespace App\Console\Commands;

use App\Services\Delivery\SourcePackageBuilder;
use Illuminate\Console\Command;
use RuntimeException;

class BuildSourcePackage extends Command
{
    protected $signature = 'aris:empaquetar-fuentes {destino : Ruta de un ZIP nuevo, en un directorio existente}';

    protected $description = 'Empaqueta fuentes versionadas de HEAD con manifiesto SHA-256, sin datos ni dependencias';

    public function handle(SourcePackageBuilder $builder): int
    {
        try {
            $manifest = $builder->build(base_path(), $this->argument('destino'));
        } catch (RuntimeException $error) {
            $this->error($error->getMessage());

            return self::FAILURE;
        }
        $this->info('Fuentes empaquetadas: '.count($manifest['archivos']).' archivos; commit '.$manifest['commit']);
        $this->line('SHA-256 del ZIP: '.hash_file('sha256', $this->argument('destino')));
        $this->line('Paquete de fuentes: requiere dependencias y compilación. No es un despliegue ni una certificación de producción.');

        return self::SUCCESS;
    }
}
