<?php

namespace App\Http\Controllers\Projects;

use App\Actions\Projects\ManageProjectLearner;
use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\LearnerRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class LearnerController extends Controller
{
    public function __invoke(LearnerRequest $request, Project $project, ManageProjectLearner $action): RedirectResponse
    {
        $action->execute($request->user(), $project, User::findOrFail($request->validated('user_id')), $request->boolean('active'));

        return to_route('proyectos.show', $project)->with('status', 'Vinculación de aprendiz actualizada.');
    }
}
