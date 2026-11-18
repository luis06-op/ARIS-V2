<?php

namespace Tests\Feature\Database;

use App\Models\Ficha;
use App\Models\Institution;
use App\Models\JurisdiccionEvaluador;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Support\AcademicScenario;
use Tests\TestCase;

class JurisdictionIntegrityTest extends TestCase
{
    use AcademicScenario, RefreshDatabase;

    public function test_scope_cannot_be_duplicated(): void
    {
        $institution = Institution::create(['name' => 'Institución de prueba']);
        $evaluator = $this->member($institution, 'evaluador');
        $ficha = $this->ficha($institution);
        $data = ['institution_membership_id' => $evaluator->institutionMemberships()->firstOrFail()->id, 'ficha_id' => $ficha->id, 'assigned_by' => $evaluator->id];
        JurisdiccionEvaluador::create($data);
        $this->expectException(QueryException::class);
        JurisdiccionEvaluador::create($data);
    }

    public function test_ficha_cannot_be_deleted_when_linked_to_project(): void
    {
        $institution = Institution::create(['name' => 'Institución de prueba']);
        $admin = $this->member($institution, 'administrador');
        $ficha = $this->ficha($institution);
        $this->project($ficha, $admin);
        $this->expectException(QueryException::class);
        Ficha::whereKey($ficha->id)->delete();
    }

    public function test_new_migrations_rollback_and_reapply_in_isolated_database(): void
    {
        try {
            $this->artisan('migrate:rollback', ['--step' => 7, '--force' => true])->assertSuccessful();
            $this->assertFalse(Schema::hasColumn('submissions', 'fecha_limite_entrega'));
            $this->assertFalse(Schema::hasColumn('projects', 'ficha_id'));
            $this->assertFalse(Schema::hasTable('especialidades'));
            $this->assertFalse(Schema::hasTable('jurisdicciones_evaluador'));
        } finally {
            $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        }
        $this->assertTrue(Schema::hasColumn('projects', 'ficha_id'));
        $this->assertTrue(Schema::hasColumn('submissions', 'fecha_limite_entrega'));
        $this->assertTrue(Schema::hasColumn('submissions', 'fecha_limite_correccion'));
        $this->assertTrue(Schema::hasTable('vinculos_especialidad'));
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
    }
}
