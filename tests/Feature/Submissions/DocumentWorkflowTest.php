<?php

namespace Tests\Feature\Submissions;

use App\Actions\Identity\ConfigureEvaluatorScope;
use App\Actions\Projects\ManageProjectTutor;
use App\Models\ActivityAssignment;
use App\Models\ActivityVersion;
use App\Models\AuditEvent;
use App\Models\DocumentLink;
use App\Models\DocumentVersion;
use App\Models\Ficha;
use App\Models\Institution;
use App\Models\Project;
use App\Models\ProjectMembership;
use App\Models\Submission;
use App\Models\SubmissionVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\AcademicScenario;
use Tests\TestCase;

class DocumentWorkflowTest extends TestCase
{
    use AcademicScenario, RefreshDatabase;

    private Institution $institution;

    private User $admin;

    private User $instructor;

    private User $student;

    private Ficha $ficha;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->institution = Institution::create(['name' => 'Institución de prueba']);
        $this->admin = $this->member($this->institution, 'administrador');
        $this->instructor = $this->member($this->institution, 'instructor');
        $this->student = $this->member($this->institution, 'aprendiz');
        $this->ficha = $this->ficha($this->institution);
        $this->project = $this->project($this->ficha, $this->admin);
        app(ManageProjectTutor::class)->execute($this->admin, $this->project, $this->instructor, true);
        ProjectMembership::create(['project_id' => $this->project->id, 'user_id' => $this->student->id, 'function' => 'aprendiz', 'assigned_at' => today()]);
    }

    public function test_real_http_draft_workflow_preserves_exact_guide_and_evidence_versions(): void
    {
        $guide = $this->publishGuide();
        $submissionVersion = $this->createDraft($guide);
        $file = UploadedFile::fake()->image('evidencia.png');
        $expected = file_get_contents($file->getRealPath());
        $this->post('/entregas/versiones/'.$submissionVersion->id.'/evidencias', ['archivo' => $file])->assertRedirect();
        $documentVersion = DocumentVersion::firstOrFail();
        $this->get('/entregas/versiones/'.$submissionVersion->id)->assertOk()->assertSee('evidencia.png');
        $download = $this->get('/evidencias/versiones/'.$documentVersion->id.'/descargar');
        $download->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertSame($expected, $download->streamedContent());
        $this->assertSame(hash('sha256', $expected), $documentVersion->sha256);

        $this->post('/entregas/versiones/'.$submissionVersion->id.'/versiones', ['summary' => 'Segunda versión'])->assertRedirect();
        $next = SubmissionVersion::orderByDesc('id')->firstOrFail();
        $this->assertSame(2, $next->version_number);
        $this->assertSame(0, $next->documentLinks()->count());
        $this->post('/entregas/versiones/'.$submissionVersion->id.'/evidencias', ['archivo' => UploadedFile::fake()->image('no.png')])->assertForbidden();
        $this->post('/entregas/versiones/'.$submissionVersion->id.'/versiones')->assertForbidden();
        $this->get('/evidencias/versiones/'.$documentVersion->id.'/descargar')->assertOk();
        $this->assertSame($guide->id, $submissionVersion->submission->assignment->activity_version_id);
        $this->assertDatabaseCount('document_versions', 1);
    }

    public function test_unpublished_guide_is_hidden_and_editing_published_guide_creates_new_unassigned_version(): void
    {
        $guide = $this->publishGuide();
        $original = $guide->content;
        $this->actingAs($this->instructor)->patch('/guias/versiones/'.$guide->id, ['title' => 'Nueva guía privada', 'content' => 'Nuevo contenido'])->assertRedirect();
        $new = ActivityVersion::orderByDesc('id')->firstOrFail();
        $this->assertSame(2, $new->version_number);
        $this->assertSame('borrador', $new->status);
        $this->assertSame($original, $guide->fresh()->content);
        $this->assertSame(0, $new->assignments()->count());
        $this->actingAs($this->student)->get('/guias/versiones/'.$new->id)->assertForbidden();
        $this->get('/proyectos/'.$this->project->id)->assertDontSee('Nueva guía privada');
        $this->get('/guias/versiones/'.$guide->id)->assertOk();
    }

    public function test_other_student_same_project_cannot_read_or_mutate_exact_submission_or_file(): void
    {
        $version = $this->createDraft($this->publishGuide());
        $this->post('/entregas/versiones/'.$version->id.'/evidencias', ['archivo' => UploadedFile::fake()->image('privado.png')])->assertRedirect();
        $other = $this->member($this->institution, 'aprendiz');
        ProjectMembership::create(['project_id' => $this->project->id, 'user_id' => $other->id, 'function' => 'aprendiz', 'assigned_at' => today()]);
        $this->actingAs($other)->get('/entregas/versiones/'.$version->id)->assertForbidden();
        $this->get('/evidencias/versiones/'.DocumentVersion::firstOrFail()->id.'/descargar')->assertForbidden();
        $this->post('/entregas/versiones/'.$version->id.'/versiones')->assertForbidden();
        $this->post('/entregas/versiones/'.$version->id.'/evidencias', ['archivo' => UploadedFile::fake()->image('ajeno.png')])->assertForbidden();
        $this->get('/proyectos/'.$this->project->id)->assertDontSee('Entrega privada de prueba');
    }

    public function test_staff_requires_assignment_or_scope_for_exact_versions_and_cannot_upload_for_student(): void
    {
        $version = $this->createDraft($this->publishGuide());
        $this->post('/entregas/versiones/'.$version->id.'/evidencias', ['archivo' => UploadedFile::fake()->image('privado.png')])->assertRedirect();
        $document = DocumentVersion::firstOrFail();
        $unassigned = $this->member($this->institution, 'instructor');
        $this->actingAs($unassigned)->get('/evidencias/versiones/'.$document->id.'/descargar')->assertForbidden();
        $evaluator = $this->member($this->institution, 'evaluador');
        $this->actingAs($evaluator)->get('/entregas/versiones/'.$version->id)->assertForbidden();
        app(ConfigureEvaluatorScope::class)->execute($this->admin, $evaluator->institutionMemberships()->firstOrFail(), $this->ficha, true);
        $this->get('/entregas/versiones/'.$version->id)->assertOk();
        $this->get('/evidencias/versiones/'.$document->id.'/descargar')->assertOk();
        $this->post('/entregas/versiones/'.$version->id.'/evidencias', ['archivo' => UploadedFile::fake()->image('no.png')])->assertForbidden();
        app(ConfigureEvaluatorScope::class)->execute($this->admin, $evaluator->institutionMemberships()->firstOrFail(), $this->ficha, false);
        $this->get('/evidencias/versiones/'.$document->id.'/descargar')->assertForbidden();
        $this->actingAs($this->instructor)->get('/evidencias/versiones/'.$document->id.'/descargar')->assertOk();
    }

    public function test_exact_document_version_without_link_cannot_inherit_other_versions_permission(): void
    {
        $version = $this->createDraft($this->publishGuide());
        $this->post('/entregas/versiones/'.$version->id.'/evidencias', ['archivo' => UploadedFile::fake()->image('privado.png')])->assertRedirect();
        $original = DocumentVersion::firstOrFail();
        $unlinked = $original->replicate();
        $unlinked->version_number = 2;
        $unlinked->save();
        $this->get('/evidencias/versiones/'.$original->id.'/descargar')->assertOk();
        $this->get('/evidencias/versiones/'.$unlinked->id.'/descargar')->assertForbidden();
        DocumentLink::query()->delete();
        $this->get('/evidencias/versiones/'.$original->id.'/descargar')->assertForbidden();
    }

    public function test_invalid_files_and_file_limit_leave_no_extra_database_records(): void
    {
        $version = $this->createDraft($this->publishGuide());
        $url = '/entregas/versiones/'.$version->id.'/evidencias';
        $this->post($url, ['archivo' => UploadedFile::fake()->createWithContent('falso.pdf', 'no es un pdf')])->assertSessionHasErrors('archivo');
        $this->assertDatabaseCount('documents', 0);
        config(['aris.files.max_per_version' => 1]);
        $this->post($url, ['archivo' => UploadedFile::fake()->image('primero.png')])->assertRedirect();
        $this->post($url, ['archivo' => UploadedFile::fake()->image('segundo.png')])->assertSessionHasErrors('archivo');
        $this->assertDatabaseCount('documents', 1);
        $this->assertDatabaseCount('file_blobs', 1);
    }

    public function test_assignment_replacement_revocation_and_author_injection_are_rejected_or_ignored(): void
    {
        $guide = $this->publishGuide();
        $assignment = $guide->assignments()->firstOrFail();
        $this->actingAs($this->student)->post('/proyectos/'.$this->project->id.'/entregas', [
            'title' => 'Autor real', 'activity_assignment_id' => $assignment->id, 'summary' => 'Resumen de prueba',
            'author_id' => $this->admin->id, 'status' => 'entregada', 'motivo_excepcion' => 'No saltar la asignación',
        ])->assertRedirect();
        $this->assertSame($this->student->id, Submission::firstOrFail()->author_id);
        $this->assertSame('borrador', Submission::firstOrFail()->status);
        $assignment->update(['status' => 'revocada']);
        $this->post('/proyectos/'.$this->project->id.'/entregas', ['title' => 'Rechazada', 'summary' => 'Resumen de prueba', 'activity_assignment_id' => $assignment->id])->assertSessionHasErrors('activity_assignment_id');
        $assignment->update(['status' => 'activa', 'target_type' => 'usuario', 'target_ref' => (string) $this->admin->id, 'target_user_id' => $this->admin->id]);
        $this->post('/proyectos/'.$this->project->id.'/entregas', ['title' => 'Rechazada', 'summary' => 'Resumen de prueba', 'activity_assignment_id' => $assignment->id])->assertSessionHasErrors('activity_assignment_id');
        $this->assertDatabaseCount('submissions', 1);
    }

    public function test_anonymous_download_and_missing_blob_are_not_served(): void
    {
        $version = $this->createDraft($this->publishGuide());
        $this->post('/entregas/versiones/'.$version->id.'/evidencias', ['archivo' => UploadedFile::fake()->image('privado.png')])->assertRedirect();
        $document = DocumentVersion::firstOrFail();
        Storage::disk('local')->delete($document->blob->storage_key);
        $this->get('/evidencias/versiones/'.$document->id.'/descargar')->assertNotFound();
        auth()->logout();
        $this->get('/evidencias/versiones/'.$document->id.'/descargar')->assertRedirect('/ingresar');
    }

    private function publishGuide(): ActivityVersion
    {
        $this->actingAs($this->instructor)->post('/proyectos/'.$this->project->id.'/guias', [
            'code' => 'GUIA', 'title' => 'Guía de prueba', 'content' => 'Contenido académico de prueba',
        ])->assertRedirect();
        $guide = ActivityVersion::firstOrFail();
        $this->actingAs($this->student)->get('/guias/versiones/'.$guide->id)->assertForbidden();
        $this->post('/guias/versiones/'.$guide->id.'/publicar')->assertForbidden();
        $this->actingAs($this->instructor)->post('/guias/versiones/'.$guide->id.'/publicar')->assertRedirect();
        $this->assertDatabaseHas('activity_assignments', ['activity_version_id' => $guide->id, 'project_id' => $this->project->id]);

        return $guide->fresh();
    }

    public function test_unlinked_exception_requires_scope_and_complete_server_audit(): void
    {
        $url = '/proyectos/'.$this->project->id.'/entregas/excepcion';
        $data = [
            'title' => 'Entrega excepcional', 'summary' => 'Contingencia documentada',
            'user_id' => $this->student->id, 'motivo_excepcion' => 'Contingencia técnica justificada por la institución.',
            'ip' => '203.0.113.10', 'actor_id' => $this->student->id,
        ];
        $this->actingAs($this->student)->post($url, $data)->assertForbidden();
        $evaluator = $this->member($this->institution, 'evaluador');
        $this->actingAs($evaluator)->post($url, $data)->assertForbidden();
        app(ConfigureEvaluatorScope::class)->execute($this->admin, $evaluator->institutionMemberships()->firstOrFail(), $this->ficha, true);
        $this->post($url, array_replace($data, ['motivo_excepcion' => 'Corto']))->assertSessionHasErrors('motivo_excepcion');
        $this->assertDatabaseCount('submissions', 0);
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.20'])->post($url, $data)->assertRedirect();
        $event = AuditEvent::where('action', 'submission.unlinked_exception')->firstOrFail();
        $this->assertSame($evaluator->id, $event->actor_id);
        $this->assertSame($this->student->id, $event->metadata['autor']);
        $this->assertSame($this->project->id, $event->metadata['proyecto']);
        $this->assertSame('192.0.2.20', $event->metadata['ip']);
        $this->assertSame($data['motivo_excepcion'], $event->metadata['motivo']);
        $this->assertNotNull($event->request_id);
        $this->assertNotNull($event->occurred_at);
        $this->assertNull(Submission::firstOrFail()->activity_assignment_id);
        app(ConfigureEvaluatorScope::class)->execute($this->admin, $evaluator->institutionMemberships()->firstOrFail(), $this->ficha, false);
        $this->post($url, $data)->assertForbidden();
        $this->assertDatabaseCount('submissions', 1);
    }

    public function test_finalized_version_rejects_mutation_while_academic_transitions_are_pending(): void
    {
        $version = $this->createDraft($this->publishGuide());
        $version->update(['status' => 'finalizada', 'finalized_at' => now()]);
        $this->post('/entregas/versiones/'.$version->id.'/evidencias', ['archivo' => UploadedFile::fake()->image('no.png')])->assertForbidden();
        $this->post('/entregas/versiones/'.$version->id.'/versiones', ['summary' => 'Corrección no autorizada'])->assertForbidden();
        $this->assertDatabaseCount('document_versions', 0);
        $this->assertDatabaseCount('submission_versions', 1);
        $this->assertSame('finalizada', $version->fresh()->status);
    }

    public function test_authorized_download_rejects_corrupted_bytes_without_returning_them(): void
    {
        $version = $this->createDraft($this->publishGuide());
        $this->post('/entregas/versiones/'.$version->id.'/evidencias', ['archivo' => UploadedFile::fake()->image('original.png')])->assertRedirect();
        $document = DocumentVersion::firstOrFail();
        Storage::disk('local')->put($document->blob->storage_key, str_repeat('X', (int) $document->size_bytes));
        $this->get('/evidencias/versiones/'.$document->id.'/descargar')->assertNotFound()->assertDontSee('original.png');
    }

    private function createDraft(ActivityVersion $guide): SubmissionVersion
    {
        $assignment = ActivityAssignment::where('activity_version_id', $guide->id)->firstOrFail();
        $this->actingAs($this->student)->post('/proyectos/'.$this->project->id.'/entregas', [
            'title' => 'Entrega privada de prueba', 'activity_assignment_id' => $assignment->id, 'summary' => 'Resumen de prueba',
        ])->assertRedirect();

        return SubmissionVersion::firstOrFail();
    }
}
