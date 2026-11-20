<?php

namespace Tests\Feature\Submissions;

use App\Models\Activity;
use App\Models\ActivityAssignment;
use App\Models\ActivityVersion;
use App\Models\Institution;
use App\Models\InstitutionMembership;
use App\Models\Project;
use App\Models\ProjectMembership;
use App\Models\Submission;
use App\Models\User;
use App\Queries\SearchSubmissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AcademicScenario;
use Tests\TestCase;

/**
 * PI-002 / P1.1: el buscador no debe mostrar entregas solo porque el usuario tenga acceso
 * al proyecto. El alcance autorizado se comparte con SubmissionVersionPolicy::view:
 * el autor ve sus propias entregas; el personal institucional (administrador, instructor,
 * evaluador con jurisdicción vigente) ve las entregas de sus proyectos.
 */
class SearchPrivacyTest extends TestCase
{
    use AcademicScenario, RefreshDatabase;

    private Institution $institution;

    private Project $project;

    private User $admin;

    private User $instructor;

    private User $author;

    private User $companero;

    private User $otraInstitucion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->institution = Institution::create(['name' => 'Institución de prueba', 'status' => 'activa']);
        $this->admin = $this->member($this->institution, 'administrador');
        $this->instructor = $this->member($this->institution, 'instructor');
        $this->author = $this->member($this->institution, 'aprendiz');
        $this->companero = $this->member($this->institution, 'aprendiz');
        $this->otraInstitucion = $this->member(
            Institution::create(['name' => 'Otra institución', 'status' => 'activa']),
            'aprendiz'
        );

        $this->project = $this->project($this->ficha($this->institution), $this->admin);
        $this->project->update(['status' => 'activo']);

        // Todos son miembros del proyecto con rol aprendiz.
        foreach ([$this->author, $this->companero] as $user) {
            ProjectMembership::create([
                'project_id' => $this->project->id,
                'user_id' => $user->id,
                'function' => 'aprendiz',
                'assigned_at' => today(),
            ]);
        }
        // El instructor es tutor del proyecto (personal institucional).
        ProjectMembership::create([
            'project_id' => $this->project->id,
            'user_id' => $this->instructor->id,
            'function' => 'instructor',
            'assigned_at' => today(),
        ]);

        $actividad = Activity::create([
            'project_id' => $this->project->id,
            'code' => 'ACT',
            'title' => 'Actividad',
            'created_by' => $this->admin->id,
        ]);
        $version = ActivityVersion::create([
            'activity_id' => $actividad->id,
            'version_number' => 1,
            'title' => 'Guía',
            'content' => 'Contenido',
            'author_id' => $this->admin->id,
            'checksum' => hash('sha256', 'Contenido'),
            'status' => 'publicada',
            'published_at' => now(),
        ]);
        ActivityAssignment::create([
            'activity_version_id' => $version->id,
            'project_id' => $this->project->id,
            'status' => 'activa',
            'target_type' => 'proyecto',
            'target_ref' => (string) $this->project->id,
            'assigned_at' => now(),
            'assigned_by' => $this->admin->id,
        ]);

        Submission::create([
            'project_id' => $this->project->id,
            'author_id' => $this->author->id,
            'title' => 'Entrega del autor',
            'status' => 'borrador',
        ]);
        Submission::create([
            'project_id' => $this->project->id,
            'author_id' => $this->companero->id,
            'title' => 'Entrega del compañero',
            'status' => 'borrador',
        ]);
    }

    private function buscar(User $user): array
    {
        return app(SearchSubmissions::class)->forUser($user, [])
            ->orderByDesc('id')
            ->get()
            ->map(fn ($entrega) => $entrega->title)
            ->toArray();
    }

    public function test_el_autor_solo_ve_su_propia_entrega(): void
    {
        $this->assertSame(['Entrega del autor'], $this->buscar($this->author));
    }

    public function test_un_aprendiz_no_ve_la_entrega_del_companero_del_mismo_proyecto(): void
    {
        $this->assertSame(['Entrega del compañero'], $this->buscar($this->companero));
        $this->assertNotContains('Entrega del autor', $this->buscar($this->companero));
    }

    public function test_el_instructor_ve_las_entregas_de_su_proyecto(): void
    {
        $resultados = $this->buscar($this->instructor);
        $this->assertContains('Entrega del autor', $resultados);
        $this->assertContains('Entrega del compañero', $resultados);
    }

    public function test_el_administrador_ve_las_entregas_de_su_institucion(): void
    {
        $resultados = $this->buscar($this->admin);
        $this->assertContains('Entrega del autor', $resultados);
        $this->assertContains('Entrega del compañero', $resultados);
    }

    public function test_un_aprendiz_de_otra_institucion_no_ve_nada(): void
    {
        $this->assertSame([], $this->buscar($this->otraInstitucion));
    }

    public function test_un_miembro_sin_rol_institucional_no_ve_entregas_ajenas(): void
    {
        $sinRol = User::factory()->create();
        InstitutionMembership::create([
            'institution_id' => $this->institution->id,
            'user_id' => $sinRol->id,
            'joined_at' => today(),
        ]);
        ProjectMembership::create([
            'project_id' => $this->project->id,
            'user_id' => $sinRol->id,
            'function' => 'aprendiz',
            'assigned_at' => today(),
        ]);

        $this->assertSame([], $this->buscar($sinRol));
    }

    public function test_autor_con_membresia_expirada_no_ve_entregas_via_http(): void
    {
        // El autor tiene una entrega en el proyecto (creada en setUp).
        // Expirar su membresía de aprendiz en el proyecto.
        ProjectMembership::where('user_id', $this->author->id)
            ->where('project_id', $this->project->id)
            ->update(['ended_at' => today()->subDay()]);

        $respuesta = $this->actingAs($this->author)->get(route('entregas.buscar'));

        $respuesta->assertOk();
        $respuesta->assertDontSee('Entrega del autor');
    }
}
