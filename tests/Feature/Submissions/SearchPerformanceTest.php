<?php

namespace Tests\Feature\Submissions;

use App\Models\Institution;
use App\Models\Project;
use App\Models\Submission;
use App\Models\SubmissionVersion;
use App\Queries\SearchSubmissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\AcademicScenario;
use Tests\TestCase;

/**
 * P2.4: el buscador elimina consultas N+1 sobre versiones y conteos. Las versiones del
 * proyecto se cargan con eager loading y la vista accede a la colección ya en memoria.
 */
class SearchPerformanceTest extends TestCase
{
    use AcademicScenario, RefreshDatabase;

    public function test_acceso_a_versiones_de_resultados_no_genera_n_plus_1(): void
    {
        $institution = Institution::create(['name' => 'Institución', 'status' => 'activa']);
        $admin = $this->member($institution, 'administrador');
        $project = Project::create([
            'institution_id' => $institution->id,
            'code' => 'PROY',
            'slug' => 'proy',
            'title' => 'Proyecto',
            'owner_id' => $admin->id,
        ]);

        $total = 8;
        for ($i = 1; $i <= $total; $i++) {
            $entrega = Submission::create([
                'project_id' => $project->id,
                'author_id' => $admin->id,
                'title' => "Entrega $i",
                'status' => 'borrador',
            ]);
            SubmissionVersion::create([
                'submission_id' => $entrega->id,
                'version_number' => 1,
                'title' => "v1-$i",
                'summary' => 'Resumen de prueba',
                'content' => 'contenido',
                'author_id' => $admin->id,
                'checksum' => hash('sha256', 'contenido'),
                'status' => 'borrador',
            ]);
            SubmissionVersion::create([
                'submission_id' => $entrega->id,
                'version_number' => 2,
                'title' => "v2-$i",
                'summary' => 'Resumen de prueba',
                'content' => 'contenido',
                'author_id' => $admin->id,
                'checksum' => hash('sha256', 'contenido'),
                'status' => 'borrador',
            ]);
        }

        DB::enableQueryLog();
        $resultados = app(SearchSubmissions::class)->forUser($admin, [])->get();
        $consultasCarga = count(DB::getQueryLog());
        DB::flushQueryLog();

        foreach ($resultados as $entrega) {
            // La vista solo necesita la última versión y el conteo, ya cargados.
            $this->assertNotNull($entrega->latestVersion);
            $this->assertSame(2, $entrega->versions_count);
            $this->assertSame(2, $entrega->latestVersion->version_number);
            $this->assertNotEmpty($entrega->project->title);
        }
        $consultasAcceso = count(DB::getQueryLog());

        // La carga usa un número acotado de consultas (no una por entrega) y el acceso a
        // relaciones precargadas no dispara consultas adicionales.
        $this->assertLessThanOrEqual(6, $consultasCarga);
        $this->assertSame(0, $consultasAcceso);
        $this->assertSame($total, $resultados->count());
    }
}
