<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_las_pantallas_de_recuperacion_estan_disponibles(): void
    {
        $this->get('/olvide-password')
            ->assertOk()
            ->assertSee('Recupera tu contraseña')
            ->assertSee('Volver a iniciar sesión', false);

        $this->get('/restablecer-password/token-de-prueba?email=usuario%40example.test')
            ->assertOk()
            ->assertSee('Nueva contraseña');
    }

    public function test_un_usuario_recibe_el_enlace_y_cambia_su_contraseña(): void
    {
        Notification::fake();

        $usuario = User::factory()->create([
            'email' => 'recuperacion@example.test',
            'password' => Hash::make('ClaveOriginal1'),
        ]);

        $this->post('/olvide-password', ['email' => 'RECUPERACION@example.test'])
            ->assertSessionHas('status', __('passwords.sent'));

        $token = null;

        Notification::assertSentTo($usuario, ResetPassword::class, function (ResetPassword $notificacion) use (&$token): bool {
            $token = $notificacion->token;

            return true;
        });

        $this->assertNotNull($token);

        $this->post('/restablecer-password', [
            'token' => $token,
            'email' => $usuario->email,
            'password' => 'NuevaClave123',
            'password_confirmation' => 'NuevaClave123',
        ])->assertRedirect(route('sesion.create'));

        $this->assertTrue(Hash::check('NuevaClave123', $usuario->fresh()->password));

        $this->post('/ingresar', [
            'email' => $usuario->email,
            'password' => 'NuevaClave123',
        ])->assertRedirect(route('cuenta'));
    }

    public function test_un_correo_no_registrado_no_revela_si_la_cuenta_existe(): void
    {
        Notification::fake();

        $respuesta = $this->post('/olvide-password', ['email' => 'ausente@example.test']);

        $respuesta->assertSessionHas('status', __('passwords.sent'));
        $respuesta->assertSessionHasNoErrors();
        Notification::assertNothingSent();
    }

    public function test_un_token_invalido_se_rechaza(): void
    {
        $usuario = User::factory()->create(['email' => 'usuario@example.test']);

        $this->from('/restablecer-password/invalido')
            ->post('/restablecer-password', [
                'token' => 'token-invalido',
                'email' => $usuario->email,
                'password' => 'NuevaClave123',
                'password_confirmation' => 'NuevaClave123',
            ])
            ->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('password', $usuario->fresh()->password));
    }

    public function test_una_contrasena_debil_no_se_acepta_al_restablecer(): void
    {
        $usuario = User::factory()->create(['email' => 'usuario@example.test']);

        $this->from('/restablecer-password/token')
            ->post('/restablecer-password', [
                'token' => 'token',
                'email' => $usuario->email,
                'password' => 'corta',
                'password_confirmation' => 'corta',
            ])
            ->assertSessionHasErrors('password');
    }
}
