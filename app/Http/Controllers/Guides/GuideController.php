<?php

namespace App\Http\Controllers\Guides;

use App\Actions\Guides\CreateGuide;
use App\Actions\Guides\PublishAndAssignGuide;
use App\Actions\Guides\SaveGuideVersion;
use App\Http\Controllers\Controller;
use App\Http\Requests\Guides\GuideRequest;
use App\Models\ActivityVersion;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class GuideController extends Controller
{
    public function store(GuideRequest $request, Project $project, CreateGuide $action): RedirectResponse
    {
        $version = $action->execute($request->user(), $project, $request->validated());

        return to_route('guias.show', $version)->with('status', 'Borrador de guía creado.');
    }

    public function show(ActivityVersion $version): View
    {
        Gate::authorize('view', $version);

        return view('guias.ver', ['version' => $version->load('activity.project')]);
    }

    public function update(GuideRequest $request, ActivityVersion $version, SaveGuideVersion $action): RedirectResponse
    {
        $updated = $action->execute($request->user(), $version, $request->validated());

        return to_route('guias.show', $updated)->with('status', 'Guía guardada. Las versiones publicadas conservan su contenido original.');
    }

    public function publish(Request $request, ActivityVersion $version, PublishAndAssignGuide $action): RedirectResponse
    {
        $action->execute($request->user(), $version);

        return to_route('guias.show', $version)->with('status', 'Versión publicada y asignada al proyecto.');
    }
}
