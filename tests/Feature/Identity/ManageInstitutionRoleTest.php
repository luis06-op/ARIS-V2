<?php

namespace Tests\Feature\Identity;

use App\Actions\Identity\ManageInstitutionRole;
use App\Models\Institution;
use App\Models\InstitutionMembership;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Support\AcademicScenario;
use Tests\TestCase;

/**
 * P1.2: no se puede retirar el último administrador vigente de una institución. La comprobación
 * adquiere un bloqueo transaccional por institución y usa InstitutionalAuthority como criterio
 * de autoridad efectiva (usuario, institución y membresía vigentes).
 */
class ManageInstitutionRoleTest extends TestCase
{
    use AcademicScenario, RefreshDatabase;

    private Institution $institution;

    private User $admin;

    private InstitutionMembership $adminMembership;

    protected function setUp(): void
    {
        parent::setUp();

        $this->institution = Institution::create(['name' => 'Institución de prueba', 'status' => 'activa']);
        $this->admin = $this->member($this->institution, 'administrador');
        $this->adminMembership = $this->admin->institutionMemberships()->firstOrFail();
    }

    private function administrador(User $user, array $overrides = []): InstitutionMembership
    {
        $membership = InstitutionMembership::create(array_merge([
            'institution_id' => $this->institution->id,
            'user_id' => $user->id,
            'joined_at' => today(),
        ], $overrides));
        $membership->roles()->attach(Role::firstOrCreate(['code' => 'administrador'], ['name' => 'Administrador']));

        return $membership;
    }

    public function test_rechaza_retirar_el_unico_administrador_vigente(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('último administrador vigente');

        (new ManageInstitutionRole)->execute($this->admin, $this->adminMembership, 'administrador', false);
    }

    public function test_permite_retirar_un_administrador_si_queda_otro_vigente(): void
    {
        $otro = User::factory()->create();
        $membresiaOtro = InstitutionMembership::create([
            'institution_id' => $this->institution->id,
            'user_id' => $otro->id,
            'joined_at' => today(),
        ]);
        $membresiaOtro->roles()->attach(Role::firstOrCreate(['code' => 'administrador'], ['name' => 'Administrador']));

        // No debe lanzar: al actor (administrador) le queda al menos otro administrador vigente.
        (new ManageInstitutionRole)->execute($this->admin, $membresiaOtro, 'administrador', false);

        $this->assertDatabaseMissing('membership_roles', [
            'institution_membership_id' => $membresiaOtro->id,
            'role_id' => Role::where('code', 'administrador')->firstOrFail()->id,
        ]);
    }

    public function test_no_cuentan_administradores_inactivos(): void
    {
        $inactivo = User::factory()->create(['status' => 'inactivo']);
        $this->administrador($inactivo);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('último administrador vigente');

        // El actor es el único administrador efectivo; el inactivo no cuenta.
        (new ManageInstitutionRole)->execute($this->admin, $this->adminMembership, 'administrador', false);
    }

    public function test_no_cuentan_membresias_vencidas(): void
    {
        $vencido = User::factory()->create();
        $this->administrador($vencido, ['left_at' => today()->subDay()]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('último administrador vigente');

        (new ManageInstitutionRole)->execute($this->admin, $this->adminMembership, 'administrador', false);
    }

    public function test_aislamiento_entre_instituciones(): void
    {
        $otra = Institution::create(['name' => 'Otra institución', 'status' => 'activa']);
        $otroAdmin = $this->member($otra, 'administrador');
        $otraMembresia = $otroAdmin->institutionMemberships()->firstOrFail();

        $this->expectException(AuthorizationException::class);
        // El actor no es administrador de la otra institución.
        (new ManageInstitutionRole)->execute($this->admin, $otraMembresia, 'administrador', false);
    }

    public function test_retirar_un_rol_que_no_es_administrador_no_desencadena_proteccion(): void
    {
        $evaluador = $this->member($this->institution, 'aprendiz');
        $membresia = $evaluador->institutionMemberships()->firstOrFail();
        $membresia->roles()->attach(Role::firstOrCreate(['code' => 'evaluador'], ['name' => 'Evaluador']));

        // Puede quitarse el rol de evaluador sin afectar al único administrador.
        (new ManageInstitutionRole)->execute($this->admin, $membresia, 'evaluador', false);

        $this->assertDatabaseMissing('membership_roles', [
            'institution_membership_id' => $membresia->id,
            'role_id' => Role::where('code', 'evaluador')->firstOrFail()->id,
        ]);
    }

    public function test_no_cuentan_administrador_con_membresia_inactiva(): void
    {
        $otroAdmin = User::factory()->create();
        $membresiaOtro = InstitutionMembership::create([
            'institution_id' => $this->institution->id,
            'user_id' => $otroAdmin->id,
            'joined_at' => today(),
            'status' => 'inactiva',
        ]);
        $membresiaOtro->roles()->attach(Role::firstOrCreate(['code' => 'administrador'], ['name' => 'Administrador']));

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('último administrador vigente');

        // El actor es el único administrador efectivo; el otro tiene membresía inactiva.
        (new ManageInstitutionRole)->execute($this->admin, $this->adminMembership, 'administrador', false);
    }
}
