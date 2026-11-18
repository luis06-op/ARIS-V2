<?php

namespace App\Http\Controllers\Submissions;

use App\Actions\Submissions\CompleteSubmissionReview;
use App\Actions\Submissions\ReopenSubmission;
use App\Actions\Submissions\SendSubmission;
use App\Actions\Submissions\StartSubmissionReview;
use App\Http\Controllers\Controller;
use App\Http\Requests\Submissions\AcademicWorkflowRequest;
use App\Models\SubmissionVersion;
use Illuminate\Http\RedirectResponse;

class AcademicWorkflowController extends Controller
{
    public function send(AcademicWorkflowRequest $request, SubmissionVersion $version, SendSubmission $action): RedirectResponse
    {
        $action->execute($request->user(), $version, $request->ip());

        return to_route('entregas.show', $version)->with('status', 'Versión enviada. Su contenido queda congelado.');
    }

    public function start(AcademicWorkflowRequest $request, SubmissionVersion $version, StartSubmissionReview $action): RedirectResponse
    {
        $action->execute($request->user(), $version, $request->ip());

        return to_route('entregas.show', $version)->with('status', 'Revisión iniciada sobre esta versión exacta.');
    }

    public function complete(AcademicWorkflowRequest $request, SubmissionVersion $version, CompleteSubmissionReview $action): RedirectResponse
    {
        $action->execute($request->user(), $version, $request->validated('resultado'), $request->validated('retroalimentacion'), $request->ip());

        return to_route('entregas.show', $version)->with('status', 'Resultado y retroalimentación registrados.');
    }

    public function reopen(AcademicWorkflowRequest $request, SubmissionVersion $version, ReopenSubmission $action): RedirectResponse
    {
        $action->execute($request->user(), $version, $request->validated('motivo'), $request->ip());

        return to_route('entregas.show', $version)->with('status', 'Reapertura auditada. El aprendiz puede crear un nuevo intento; esta versión permanece congelada.');
    }
}
