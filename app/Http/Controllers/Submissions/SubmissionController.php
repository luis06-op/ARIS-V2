<?php

namespace App\Http\Controllers\Submissions;

use App\Actions\Submissions\CreateDraftSubmission;
use App\Actions\Submissions\CreateSubmissionVersion;
use App\Http\Controllers\Controller;
use App\Http\Requests\Submissions\DraftSubmissionRequest;
use App\Http\Requests\Submissions\SubmissionVersionRequest;
use App\Models\Project;
use App\Models\SubmissionVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SubmissionController extends Controller
{
    public function store(DraftSubmissionRequest $request, Project $project, CreateDraftSubmission $action): RedirectResponse
    {
        $version = $action->execute($request->user(), $project, $request->validated());

        return to_route('entregas.show', $version)->with('status', 'Borrador de entrega creado contra la versión exacta de la guía.');
    }

    public function show(SubmissionVersion $version): View
    {
        Gate::authorize('view', $version);

        return view('entregas.ver', [
            'version' => $version->load('submission.project', 'submission.assignment.activityVersion', 'documentLinks.documentVersion', 'reviewRounds.comments'),
            'versiones' => $version->submission->versions()->orderByDesc('version_number')->get(),
        ]);
    }

    public function version(SubmissionVersionRequest $request, SubmissionVersion $version, CreateSubmissionVersion $action): RedirectResponse
    {
        $created = $action->execute($request->user(), $version, $request->validated('summary'));

        return to_route('entregas.show', $created)->with('status', 'Nueva versión creada. Los archivos de versiones anteriores se conservan sin cambios.');
    }
}
