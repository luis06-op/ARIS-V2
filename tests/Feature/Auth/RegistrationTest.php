<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_una_persona_puede_crear_su_cuenta_y_queda_autenticada(): void
    {
        $respuesta = $this->post('/registro', [
            'name' => 'Ana Pérez',
            'email' => 'Ana.Perez@Example.com',
            'password' => 'Clave1234',
            'password_confirmation' => 'Clave1234',
        ]);

        $respuesta->assertRedirect(route('cuenta'));
        $this->assertAuthenticated();

        $usuario = User::where('email', 'ana.perez@example.com')->firstOrFail();

        $this->assertSame('Ana Pérez', $usuario->name);
        $this->assertSame('activo', $usuario->status);
        $this->assertTrue(Hash::check('Clave1234', $usuario->password));
        $this->assertNull($usuario->institutionMemberships()->first());
    }

    public function test_no_se_puede_registrar_dos_veces_el_mismo_correo(): void
    {
        User::factory()->create(['email' => 'existente@example.com']);

        $respuesta = $this->from('/registro')->post('/registro', [
            'name' => 'Otra Persona',
            'email' => 'EXISTENTE@example.com',
            'password' => 'Clave1234',
            'password_confirmation' => 'Clave1234',
        ]);

        $respuesta->assertRedirect('/registro');
        $respuesta->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertSame(1, User::where('email', 'existente@example.com')->count());
    }

    public function test_una_contrasena_debil_se_rechaza(): void
    {
        $respuesta = $this->from('/registro')->post('/registro', [
            'name' => 'Ana Pérez',
            'email' => 'ana@example.com',
            'password' => 'corta',
            'password_confirmation' => 'corta',
        ]);

        $respuesta->assertRedirect('/registro');
        $respuesta->assertSessionHasErrors('password');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'ana@example.com']);
    }

    public function test_una_cuenta_autenticada_no_ve_el_formulario_de_registro(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($usuario)->get('/registro')->assertRedirect(route('cuenta'));
    }
}
