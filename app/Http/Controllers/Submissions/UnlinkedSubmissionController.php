<?php

namespace App\Http\Controllers\Submissions;

use App\Actions\Submissions\CreateUnlinkedDraft;
use App\Http\Controllers\Controller;
use App\Http\Requests\Submissions\UnlinkedSubmissionRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class UnlinkedSubmissionController extends Controller
{
    public function __invoke(UnlinkedSubmissionRequest $request, Project $project, CreateUnlinkedDraft $action): RedirectResponse
    {
        $version = $action->execute($request->user(), $project, User::findOrFail($request->validated('user_id')), $request->validated(), $request->ip());

        return to_route('entregas.show', $version)->with('status', 'Excepción PI-001 autorizada y auditada. Se creó un borrador sin asignación de guía.');
    }
}
