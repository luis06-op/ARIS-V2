<?php

namespace Tests\Feature\Submissions;

use App\Actions\Identity\ConfigureEvaluatorScope;
use App\Actions\Projects\ManageProjectTutor;
use App\Actions\Submissions\ReopenSubmission;
use App\Models\AuditEvent;
use App\Models\DocumentVersion;
use App\Models\Ficha;
use App\Models\Institution;
use App\Models\ProjectMembership;
use App\Models\ReviewRound;
use App\Models\Submission;
use App\Models\SubmissionVersion;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Support\AcademicScenario;
use Tests\TestCase;

class AcademicWorkflowTest extends TestCase
{
    use AcademicScenario, RefreshDatabase;

    private Institution $institution;

    private User $admin;

    private User $instructor;

    private User $student;

    private Ficha $ficha;

    private SubmissionVersion $version;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->institution = Institution::create(['name' => 'Institución de prueba']);
        $this->admin = $this->member($this->institution, 'administrador');
        $this->instructor = $this->member($this->institution, 'instructor');
        $this->student = $this->member($this->institution, 'aprendiz');
        $this->ficha = $this->ficha($this->institution);
        $project = $this->project($this->ficha, $this->admin);
        app(ManageProjectTutor::class)->execute($this->admin, $project, $this->instructor, true);
        ProjectMembership::create(['project_id' => $project->id, 'user_id' => $this->student->id, 'function' => 'aprendiz', 'assigned_at' => today()]);
        $this->actingAs($this->admin)->post('/proyectos/'.$project->id.'/entregas/excepcion', [
            'title' => 'Entrega de prueba', 'summary' => 'Primer intento', 'user_id' => $this->student->id,
            'motivo_excepcion' => 'Excepción sintética autorizada para probar el recorrido.',
        ])->assertRedirect();
        $this->version = SubmissionVersion::firstOrFail();
        $this->actingAs($this->student)->post($this->url('evidencias'), ['archivo' => UploadedFile::fake()->image('original.png')])->assertRedirect();
    }

    public function test_send_freezes_content_and_correction_preserves_original_review_and_file(): void
    {
        $file = DocumentVersion::firstOrFail();
        $this->post($this->url('enviar'))->assertRedirect();
        $snapshot = $this->version->fresh()->getAttributes();
        $this->post($this->url('enviar'))->assertForbidden();
        $this->post($this->url('evidencias'), ['archivo' => UploadedFile::fake()->image('no.png')])->assertForbidden();
        $this->post($this->url('versiones'), ['summary' => 'No autorizado'])->assertForbidden();
        $this->post($this->url('revision/iniciar'))->assertForbidden();
        $this->actingAs($this->instructor)->post($this->url('revision/iniciar'))->assertRedirect();
        $this->post($this->url('revision/iniciar'))->assertForbidden();
        $this->post($this->url('revision/completar'), ['resultado' => 'requiere_ajustes', 'retroalimentacion' => 'Precisar la evidencia.'])->assertRedirect();
        $this->post($this->url('revision/completar'), ['resultado' => 'cumple', 'retroalimentacion' => 'Sobrescritura'])->assertForbidden();
        $review = ReviewRound::firstOrFail()->toArray();
        $this->actingAs($this->student)->get($this->url())->assertOk()->assertSee('Requiere Ajustes')->assertSee('Precisar la evidencia.');
        $this->post($this->url('evidencias'), ['archivo' => UploadedFile::fake()->image('no.png')])->assertForbidden();
        $this->post($this->url('versiones'), ['summary' => 'Corrección'])->assertRedirect();
        $next = SubmissionVersion::orderByDesc('id')->firstOrFail();
        $this->assertSame(2, $next->version_number);
        $this->assertSame('borrador', $next->submission->status);
        $this->assertSame(0, $next->documentLinks()->count());
        $this->assertSame($snapshot, $this->version->fresh()->getAttributes());
        $this->assertSame($review, ReviewRound::firstOrFail()->toArray());
        $this->assertSame($file->sha256, $file->fresh()->sha256);
        $this->get('/entregas/versiones/'.$next->id)->assertOk()->assertDontSee('Precisar la evidencia.');
        $this->get('/evidencias/versiones/'.$file->id.'/descargar')->assertOk();
        $this->post($this->url('versiones'), ['summary' => 'Intento duplicado'])->assertForbidden();
        $this->post('/entregas/versiones/'.$next->id.'/evidencias', ['archivo' => UploadedFile::fake()->image('corregido.png')])->assertRedirect();
        $this->post('/entregas/versiones/'.$next->id.'/enviar')->assertRedirect();
        $this->actingAs($this->instructor)->post($this->url('revision/iniciar'))->assertForbidden();
        $this->post('/entregas/versiones/'.$next->id.'/revision/iniciar')->assertRedirect();
        $this->post('/entregas/versiones/'.$next->id.'/revision/completar', ['resultado' => 'cumple', 'retroalimentacion' => 'Corrección verificada.'])->assertRedirect();
        $this->assertSame($review, ReviewRound::findOrFail($review['id'])->toArray());
        $this->assertDatabaseHas('review_rounds', ['submission_version_id' => $next->id, 'decision' => 'aprobada']);
    }

    public function test_review_requires_qualitative_result_feedback_and_current_reviewer(): void
    {
        $this->post($this->url('enviar'))->assertRedirect();
        $this->actingAs($this->instructor)->post($this->url('revision/completar'), ['resultado' => 'cumple', 'retroalimentacion' => 'Prematura'])->assertForbidden();
        $this->post($this->url('revision/iniciar'))->assertRedirect();
        $this->post($this->url('revision/completar'), ['resultado' => 'cumple', 'retroalimentacion' => '   '])->assertSessionHasErrors('retroalimentacion');
        $this->post($this->url('revision/completar'), ['resultado' => '5.0', 'retroalimentacion' => 'Texto'])->assertSessionHasErrors('resultado');
        $this->assertDatabaseCount('review_comments', 0);
        $this->actingAs($this->admin)->post($this->url('revision/completar'), ['resultado' => 'cumple', 'retroalimentacion' => 'Otro revisor'])->assertForbidden();
        $this->actingAs($this->instructor)->post($this->url('revision/completar'), ['resultado' => 'cumple', 'retroalimentacion' => '<script>alert(1)</script>'])->assertRedirect();
        $this->actingAs($this->student)->get($this->url())->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->post($this->url('versiones'), ['summary' => 'Sin reapertura'])->assertForbidden();
    }

    public function test_reopening_requires_reason_and_records_server_actor_ip_and_frozen_version(): void
    {
        $this->complete();
        $snapshot = $this->version->fresh()->getAttributes();
        $this->post($this->url('reabrir'), ['motivo' => '   '])->assertSessionHasErrors('motivo');
        $this->assertSame('revisada', $this->version->submission->fresh()->status);
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.45'])->post($this->url('reabrir'), [
            'motivo' => 'Aportar una aclaración.', 'user_id' => $this->student->id, 'ip_address' => '203.0.113.1',
        ])->assertRedirect();
        $event = AuditEvent::where('action', 'REAPERTURA_ENTREGA')->firstOrFail();
        $this->assertSame($this->instructor->id, $event->actor_id);
        $this->assertSame((string) $this->version->submission_id, $event->resource_id);
        $this->assertSame($this->version->submission_id, $event->metadata['entrega_id']);
        $this->assertSame('Aportar una aclaración.', $event->metadata['motivo']);
        $this->assertSame('192.0.2.45', $event->metadata['ip_address']);
        $this->assertNotNull($event->occurred_at);
        $this->assertNotNull($event->request_id);
        $this->assertSame($snapshot, $this->version->fresh()->getAttributes());
        $this->post($this->url('reabrir'), ['motivo' => 'Duplicado'])->assertForbidden();
        $this->actingAs($this->student)->post($this->url('versiones'), ['summary' => 'Nuevo intento autorizado'])->assertRedirect();
    }

    public function test_evaluator_scope_and_revocation_apply_to_review_and_reopening(): void
    {
        $this->post($this->url('enviar'))->assertRedirect();
        $evaluator = $this->member($this->institution, 'evaluador');
        $membership = $evaluator->institutionMemberships()->firstOrFail();
        $this->actingAs($evaluator)->post($this->url('revision/iniciar'))->assertForbidden();
        app(ConfigureEvaluatorScope::class)->execute($this->admin, $membership, $this->ficha, true);
        $this->post($this->url('revision/iniciar'))->assertRedirect();
        app(ConfigureEvaluatorScope::class)->execute($this->admin, $membership, $this->ficha, false);
        $this->post($this->url('revision/completar'), ['resultado' => 'cumple', 'retroalimentacion' => 'Cumple los criterios.'])->assertForbidden();
        app(ConfigureEvaluatorScope::class)->execute($this->admin, $membership, $this->ficha, true);
        $this->post($this->url('revision/completar'), ['resultado' => 'cumple', 'retroalimentacion' => 'Cumple los criterios.'])->assertRedirect();
        app(ConfigureEvaluatorScope::class)->execute($this->admin, $membership, $this->ficha, false);
        $this->post($this->url('reabrir'), ['motivo' => 'Aclaración'])->assertForbidden();
        app(ConfigureEvaluatorScope::class)->execute($this->admin, $membership, $this->ficha, true);
        $this->post($this->url('reabrir'), ['motivo' => 'Aclaración'])->assertRedirect();
    }

    public function test_reopening_rejects_student_unassigned_staff_and_other_institution_admin(): void
    {
        $this->complete();
        foreach ([$this->student, $this->member($this->institution, 'instructor'), $this->member(Institution::create(['name' => 'Otra institución']), 'administrador')] as $actor) {
            $this->actingAs($actor)->post($this->url('reabrir'), ['motivo' => 'No autorizado'])->assertForbidden();
        }
        $this->actingAs($this->admin)->post($this->url('reabrir'), ['motivo' => 'Reapertura administrativa'])->assertRedirect();
    }

    public function test_nullable_deadlines_do_not_block_sending_or_correction(): void
    {
        $this->assertNull($this->version->submission->fecha_limite_entrega);
        $this->assertNull($this->version->submission->fecha_limite_correccion);
        DB::table('submissions')->where('id', $this->version->submission_id)->update([
            'fecha_limite_entrega' => now()->subDays(5), 'fecha_limite_correccion' => now()->subDays(2),
        ]);
        $this->post($this->url('enviar'))->assertRedirect();
        $this->actingAs($this->instructor)->post($this->url('revision/iniciar'))->assertRedirect();
        $this->post($this->url('revision/completar'), ['resultado' => 'requiere_ajustes', 'retroalimentacion' => 'Precisar evidencia.'])->assertRedirect();
        $this->actingAs($this->student)->post($this->url('versiones'), ['summary' => 'Corrección fuera del plazo diferido'])->assertRedirect();
    }

    public function test_audit_events_reject_direct_sql_updates(): void
    {
        $this->complete();
        $this->post($this->url('reabrir'), ['motivo' => 'Aclaración'])->assertRedirect();
        $this->expectException(QueryException::class);
        DB::table('audit_events')->where('action', 'REAPERTURA_ENTREGA')->update(['metadata' => '{}']);
    }

    public function test_audit_events_reject_direct_sql_deletion(): void
    {
        $this->expectException(QueryException::class);
        DB::table('audit_events')->delete();
    }

    public function test_audit_failure_rolls_back_reopening(): void
    {
        $this->complete();
        $reject = true;
        DB::listen(function ($query) use (&$reject): void {
            if ($reject && in_array('REAPERTURA_ENTREGA', $query->bindings, true)) {
                throw new \RuntimeException('Fallo sintético de auditoría.');
            }
        });
        try {
            app(ReopenSubmission::class)->execute($this->instructor, $this->version, 'Aclaración', '192.0.2.1');
            $this->fail('La reapertura debe fallar si no se registra auditoría.');
        } catch (\RuntimeException) {
            $reject = false;
            $this->assertSame('revisada', Submission::findOrFail($this->version->submission_id)->status);
            $this->assertDatabaseMissing('audit_events', ['action' => 'REAPERTURA_ENTREGA']);
        } finally {
            $reject = false;
        }
    }

    private function complete(): void
    {
        $this->actingAs($this->student)->post($this->url('enviar'))->assertRedirect();
        $this->actingAs($this->instructor)->post($this->url('revision/iniciar'))->assertRedirect();
        $this->post($this->url('revision/completar'), ['resultado' => 'cumple', 'retroalimentacion' => 'Evidencia suficiente.'])->assertRedirect();
        $this->assertSame('revisada', Submission::findOrFail($this->version->submission_id)->status);
    }

    private function url(string $suffix = ''): string
    {
        return '/entregas/versiones/'.$this->version->id.($suffix === '' ? '' : '/'.$suffix);
    }
}
