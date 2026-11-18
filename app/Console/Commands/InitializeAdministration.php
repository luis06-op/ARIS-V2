<?php

namespace App\Console\Commands;

use App\Actions\Identity\InitializeAdministration as InitializeAdministrationAction;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

class InitializeAdministration extends Command
{
    protected $signature = 'aris:inicializar-administracion';

    protected $description = 'Crea la autoridad inicial de una instalación autorizada, sin credenciales predeterminadas';

    public function handle(InitializeAdministrationAction $action): int
    {
        $this->line('Conexión configurada: '.config('database.default'));
        if (! $this->confirm('¿Confirmas que esta instalación y la creación de su administrador inicial están autorizadas?')) {
            return self::FAILURE;
        }

        $data = [
            'institution_name' => $this->ask('Nombre oficial de la institución'),
            'institution_code' => $this->ask('Código oficial de la institución'),
            'name' => $this->ask('Nombre de la autoridad designada'),
            'email' => $this->ask('Correo de la autoridad designada'),
            'password' => $this->secret('Contraseña inicial'),
            'password_confirmation' => $this->secret('Confirma la contraseña inicial'),
        ];

        try {
            $action->execute($data);
        } catch (ValidationException $error) {
            foreach ($error->validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $this->info('Autoridad inicial creada y auditada. Entrega las credenciales a la persona designada por el canal institucional.');

        return self::SUCCESS;
    }
}
