<?php

namespace Tests\Feature\Auth;

use App\Actions\Identity\InitializeAdministration;
use App\Models\AuditEvent;
use App\Models\Institution;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InitialAdministrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_interactive_setup_creates_only_explicit_authority_and_never_prints_password(): void
    {
        $this->artisan('aris:inicializar-administracion')
            ->expectsConfirmation('¿Confirmas que esta instalación y la creación de su administrador inicial están autorizadas?', 'yes')
            ->expectsQuestion('Nombre oficial de la institución', 'Institución de prueba')
            ->expectsQuestion('Código oficial de la institución', 'PRUEBA')
            ->expectsQuestion('Nombre de la autoridad designada', 'Autoridad de prueba')
            ->expectsQuestion('Correo de la autoridad designada', 'autoridad@example.test')
            ->expectsQuestion('Contraseña inicial', 'ClavePrueba2026')
            ->expectsQuestion('Confirma la contraseña inicial', 'ClavePrueba2026')
            ->doesntExpectOutput('ClavePrueba2026')
            ->assertSuccessful();

        $user = User::firstOrFail();
        $this->assertTrue(Hash::check('ClavePrueba2026', $user->password));
        $this->assertTrue($user->can('manageRoles', Institution::firstOrFail()));
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('projects', 0);
        $this->assertDatabaseCount('especialidades', 0);
        $this->assertDatabaseHas('audit_events', ['action' => 'identity.bootstrap', 'actor_id' => $user->id]);
        $this->assertStringNotContainsString('ClavePrueba2026', AuditEvent::firstOrFail()->toJson());
    }

    public function test_setup_cancellation_creates_nothing(): void
    {
        $this->artisan('aris:inicializar-administracion')
            ->expectsConfirmation('¿Confirmas que esta instalación y la creación de su administrador inicial están autorizadas?', 'no')
            ->assertFailed();
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('institutions', 0);
    }

    public function test_setup_cannot_be_repeated_even_after_administrator_role_is_removed(): void
    {
        $action = app(InitializeAdministration::class);
        $data = [
            'institution_name' => 'Institución de prueba', 'institution_code' => 'PRUEBA',
            'name' => 'Autoridad de prueba', 'email' => 'autoridad@example.test',
            'password' => 'ClavePrueba2026', 'password_confirmation' => 'ClavePrueba2026',
        ];
        $action->execute($data);
        Role::where('code', 'administrador')->firstOrFail()->institutionMemberships()->detach();
        try {
            $action->execute(array_replace($data, ['institution_code' => 'OTRA', 'email' => 'otra@example.test']));
            $this->fail('El instalador no debe poder reutilizarse.');
        } catch (ValidationException) {
            $this->assertDatabaseCount('users', 1);
            $this->assertDatabaseCount('institutions', 1);
        }
    }
}
