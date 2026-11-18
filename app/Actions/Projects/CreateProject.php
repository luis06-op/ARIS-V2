<?php

namespace App\Actions\Projects;

use App\Models\Ficha;
use App\Models\Institution;
use App\Models\Project;
use App\Models\User;
use App\Services\Audit\RecordAuditEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CreateProject
{
    public function execute(User $actor, Ficha $ficha, array $data): Project
    {
        return DB::transaction(function () use ($actor, $ficha, $data): Project {
            $ficha = Ficha::with('especialidad')->findOrFail($ficha->id);
            Institution::whereKey($ficha->especialidad->institution_id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor)->authorize('create', [Project::class, $ficha]);

            if (Project::where('institution_id', $ficha->especialidad->institution_id)->where('code', $data['code'])->exists()) {
                throw ValidationException::withMessages(['code' => 'Este código ya existe en la institución.']);
            }

            $project = Project::create([
                'institution_id' => $ficha->especialidad->institution_id,
                'ficha_id' => $ficha->id,
                'owner_id' => $actor->id,
                'code' => $data['code'],
                'slug' => (string) Str::uuid(),
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
            ]);
            app(RecordAuditEvent::class)->execute($actor, $project->institution_id, 'project.created', 'project', $project->id);

            return $project;
        });
    }
}
