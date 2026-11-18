<?php

namespace App\Actions\Academic;

use App\Models\Especialidad;
use App\Models\Institution;
use App\Models\User;
use App\Services\Audit\RecordAuditEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class CreateSpecialty
{
    public function execute(User $actor, Institution $institution, array $data): void
    {
        DB::transaction(function () use ($actor, $institution, $data): void {
            $institution = Institution::whereKey($institution->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor)->authorize('manageRoles', $institution);
            if (Especialidad::where('institution_id', $institution->id)->where('codigo', $data['codigo'])->exists()) {
                throw ValidationException::withMessages(['codigo' => 'Este código de especialidad ya está registrado.']);
            }
            $specialty = Especialidad::create(['institution_id' => $institution->id, 'codigo' => $data['codigo'], 'nombre' => $data['nombre']]);
            app(RecordAuditEvent::class)->execute($actor, $institution->id, 'academic.specialty_created', 'especialidad', $specialty->id);
        });
    }
}
