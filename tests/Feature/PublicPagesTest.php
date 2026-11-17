<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_portada_publica_muestra_la_marca_y_el_estado_real_del_repositorio(): void
    {
        $respuesta = $this->get('/');

        $respuesta->assertOk();
        $respuesta->assertSee('ARIS');
        $respuesta->assertSee('Proyectos publicados');
        $respuesta->assertSee('Todavía no hay proyectos publicados');
        $respuesta->assertSee(route('registro.create'), false);
        $respuesta->assertSee(route('sesion.create'), false);
    }

    public function test_la_pantalla_de_acceso_esta_disponible_para_visitantes(): void
    {
        $this->get('/ingresar')->assertOk()->assertSee('Bienvenido de nuevo');
        $this->get('/registro')->assertOk()->assertSee('Crea tu cuenta');
    }

    public function test_la_portada_ofrece_el_menu_de_accesibilidad(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('data-a11y-toggle', false)
            ->assertSee('data-a11y-panel', false)
            ->assertSee('Opciones de accesibilidad');
    }

    public function test_los_campos_de_contrasena_tienen_boton_para_mostrarla(): void
    {
        $this->get('/ingresar')
            ->assertOk()
            ->assertSee('data-password-toggle', false)
            ->assertSee('aria-controls="password"', false)
            ->assertSee('Mostrar contraseña');

        $registro = $this->get('/registro')->assertOk();

        $registro->assertSee('aria-controls="password"', false);
        $registro->assertSee('aria-controls="password_confirmation"', false);
        $registro->assertSee('Mostrar contraseña');
    }

    public function test_un_visitante_no_entra_a_mi_cuenta(): void
    {
        $this->get('/cuenta')->assertRedirect(route('sesion.create'));
    }

    public function test_una_cuenta_autenticada_ve_sus_datos_reales_sin_roles_inventados(): void
    {
        $usuario = User::factory()->create([
            'name' => 'Yuleimis Baltazar',
            'email' => 'yuleimis@example.com',
        ]);

        $respuesta = $this->actingAs($usuario)->get('/cuenta');

        $respuesta->assertOk();
        $respuesta->assertSee('Bienvenido, Yuleimis');
        $respuesta->assertSee('yuleimis@example.com');
        $respuesta->assertSee('Sin rol institucional');
        $respuesta->assertDontSee('Proyectos publicados</h2>', false);
    }
}
