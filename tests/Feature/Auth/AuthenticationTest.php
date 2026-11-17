<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_una_cuenta_activa_inicia_sesion_y_registra_el_acceso(): void
    {
        $usuario = User::factory()->create([
            'email' => 'instructor@example.com',
            'status' => 'activo',
            'password' => Hash::make('Clave1234'),
            'last_login_at' => null,
        ]);

        $respuesta = $this->post('/ingresar', [
            'email' => 'Instructor@Example.com',
            'password' => 'Clave1234',
        ]);

        $respuesta->assertRedirect(route('cuenta'));
        $this->assertAuthenticatedAs($usuario);
        $this->assertNotNull($usuario->fresh()->last_login_at);
    }

    public function test_una_contrasena_incorrecta_no_autentica(): void
    {
        $usuario = User::factory()->create([
            'email' => 'aprendiz@example.com',
            'password' => Hash::make('Clave1234'),
        ]);

        $respuesta = $this->from('/ingresar')->post('/ingresar', [
            'email' => 'aprendiz@example.com',
            'password' => 'incorrecta',
        ]);

        $respuesta->assertRedirect('/ingresar');
        $respuesta->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_una_cuenta_inactiva_no_puede_iniciar_sesion(): void
    {
        User::factory()->create([
            'email' => 'inactiva@example.com',
            'status' => 'inactivo',
            'password' => Hash::make('Clave1234'),
        ]);

        $respuesta = $this->from('/ingresar')->post('/ingresar', [
            'email' => 'inactiva@example.com',
            'password' => 'Clave1234',
        ]);

        $respuesta->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_cerrar_sesion_invalida_la_sesion(): void
    {
        $usuario = User::factory()->create();

        $respuesta = $this->actingAs($usuario)->post('/salir');

        $respuesta->assertRedirect(route('inicio'));
        $this->assertGuest();
    }

    public function test_el_limite_de_intentos_bloquea_la_fuerza_bruta(): void
    {
        User::factory()->create([
            'email' => 'objetivo@example.com',
            'password' => Hash::make('Clave1234'),
        ]);

        foreach (range(1, 5) as $intento) {
            $this->post('/ingresar', [
                'email' => 'objetivo@example.com',
                'password' => "fallo-{$intento}",
            ]);
        }

        $respuesta = $this->from('/ingresar')->post('/ingresar', [
            'email' => 'objetivo@example.com',
            'password' => 'Clave1234',
        ]);

        $respuesta->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'Demasiados intentos',
            (string) session('errors')?->first('email')
        );
        $this->assertGuest();
    }
}
