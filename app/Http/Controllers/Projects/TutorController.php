<?php

namespace App\Http\Controllers\Projects;

use App\Actions\Projects\ManageProjectTutor;
use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\TutorRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class TutorController extends Controller
{
    public function __invoke(TutorRequest $request, Project $project, ManageProjectTutor $action): RedirectResponse
    {
        $action->execute($request->user(), $project, User::findOrFail($request->validated('user_id')), $request->boolean('active'));

        return to_route('proyectos.show', $project)->with('status', 'Designación de tutor actualizada.');
    }
}
