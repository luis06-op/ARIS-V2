<?php

namespace Tests\Feature\Guides;

use App\Actions\Guides\SaveGuideVersion;
use App\Actions\Guides\UpdateGuideVersion;
use App\Models\Activity;
use App\Models\ActivityVersion;
use App\Models\Institution;
use App\Models\InstitutionMembership;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * P2.3: control de concurrencia optimista en la edición de guías. La revisión esperada
 * (lock_version) se comprueba dentro de la transacción; un formulario obsoleto no sobrescribe
 * cambios válidos. Una versión publicada sigue siendo inmutable: editarla genera un borrador
 * nuevo sin alterar la original.
 */
class GuideVersionConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    private User $autor;

    private ActivityVersion $version;

    protected function setUp(): void
    {
        parent::setUp();

        $this->institution = Institution::create(['name' => 'Institución', 'status' => 'activa']);
        $this->autor = User::factory()->create();
        // El autor es administrador de la institución para pasar la política de actualización de guías.
        $membresiaAutor = InstitutionMembership::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->autor->id,
            'joined_at' => today(),
            'status' => 'activa',
        ]);
        $membresiaAutor->roles()->attach(Role::firstOrCreate(['code' => 'administrador'], ['name' => 'Administrador']));
        $this->project = Project::create([
            'institution_id' => $this->institution->id,
            'code' => 'ACT-1',
            'slug' => 'act-1',
            'title' => 'Proyecto',
            'owner_id' => $this->autor->id,
        ]);
        $actividad = Activity::create([
            'project_id' => $this->project->id,
            'code' => 'ACT',
            'title' => 'Actividad',
            'created_by' => $this->autor->id,
        ]);
        $this->version = ActivityVersion::create([
            'activity_id' => $actividad->id,
            'version_number' => 1,
            'title' => 'Título original',
            'content' => 'Contenido original',
            'author_id' => $this->autor->id,
            'checksum' => hash('sha256', 'Contenido original'),
            'status' => 'borrador',
        ]);
    }

    public function test_editar_borrador_con_revision_coincidente_actualiza_e_incrementa(): void
    {
        $resultado = (new UpdateGuideVersion)->execute($this->version, [
            'lock_version' => $this->version->lock_version,
            'title' => 'Título corregido',
            'content' => 'Contenido corregido',
        ], $this->autor);

        $this->assertSame('Título corregido', $resultado->title);
        $this->assertSame('Contenido corregido', $resultado->content);
        $this->assertSame(2, $resultado->lock_version);
        $this->assertSame(hash('sha256', 'Contenido corregido'), $resultado->fresh()->checksum);
    }

    public function test_formulario_obsoleto_no_sobrescribe_cambios_validos(): void
    {
        // Primera edición válida incrementa la revisión a 2.
        (new UpdateGuideVersion)->execute($this->version, [
            'lock_version' => $this->version->lock_version,
            'content' => 'Primera edición',
        ], $this->autor);

        // Un segundo formulario que aún trae la revisión 1 está obsoleto.
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('modificada por otro usuario');

        (new UpdateGuideVersion)->execute($this->version, [
            'lock_version' => 1,
            'content' => 'Edición obsoleta que debe rechazarse',
        ], $this->autor);

        $this->assertSame('Primera edición', $this->version->fresh()->content);
    }

    public function test_editar_version_publicada_crea_borrador_nuevo_y_no_altera_la_publicada(): void
    {
        $publicada = ActivityVersion::create([
            'activity_id' => $this->version->activity_id,
            'version_number' => 2,
            'title' => 'Publicada',
            'content' => 'Contenido publicado',
            'author_id' => $this->autor->id,
            'checksum' => hash('sha256', 'Contenido publicado'),
            'status' => 'publicada',
            'published_at' => now(),
            'lock_version' => 1,
        ]);

        $nueva = (new UpdateGuideVersion)->execute($publicada, [
            'lock_version' => $publicada->lock_version,
            'title' => 'Título corregido',
            'content' => 'Contenido corregido',
        ], $this->autor);

        $this->assertNotSame($publicada->id, $nueva->id);
        $this->assertSame(3, $nueva->version_number);
        $this->assertSame('borrador', $nueva->status);
        $this->assertSame(1, $nueva->lock_version);
        // La publicada permanece intacta.
        $this->assertSame('publicada', $publicada->fresh()->status);
        $this->assertSame('Contenido publicado', $publicada->fresh()->content);
    }

    public function test_save_guide_version_con_lock_version_obsoleto_es_rechazado(): void
    {
        // Primera edición válida incrementa la revisión.
        (new SaveGuideVersion)->execute($this->autor, $this->version, [
            'lock_version' => $this->version->lock_version,
            'title' => 'Título original',
            'content' => 'Primera edición válida',
        ]);

        // Segundo intento con lock_version obsoleto (1) debe rechazarse.
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('modificada por otro usuario');

        (new SaveGuideVersion)->execute($this->autor, $this->version, [
            'lock_version' => 1,
            'title' => 'Título',
            'content' => 'Edición obsoleta que debe rechazarse',
        ]);

        $this->assertSame('Primera edición válida', $this->version->fresh()->content);
    }

    public function test_despues_de_conflicto_lock_version_formulario_muestra_revision_actual_del_servidor(): void
    {
        $this->actingAs($this->autor);

        // Primera edición válida: lock_version pasa de 1 a 2.
        $this->patch(route('guias.update', $this->version), [
            'lock_version' => $this->version->lock_version,
            'title' => 'Título original',
            'content' => 'Primera edición válida',
        ])->assertRedirect();

        // Segundo intento con lock_version obsoleto (1): conflicto de revisión.
        $respuesta = $this->patch(route('guias.update', $this->version), [
            'lock_version' => 1,
            'title' => 'Título obsoleto',
            'content' => 'Contenido obsoleto',
        ]);

        $respuesta->assertSessionHasErrors('lock_version');
        // El formulario debe flash la revisión actual del servidor (2).
        $this->assertSame(2, session('guias.lock_version'));
    }
}
