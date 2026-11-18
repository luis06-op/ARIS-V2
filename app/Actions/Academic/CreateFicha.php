<?php

namespace App\Actions\Academic;

use App\Models\Especialidad;
use App\Models\Ficha;
use App\Models\User;
use App\Services\Audit\RecordAuditEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class CreateFicha
{
    public function execute(User $actor, Especialidad $specialty, array $data): void
    {
        DB::transaction(function () use ($actor, $specialty, $data): void {
            $specialty = Especialidad::whereKey($specialty->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor)->authorize('manageRoles', $specialty->institution);
            if (Ficha::where('especialidad_id', $specialty->id)->where('numero', $data['numero'])->exists()) {
                throw ValidationException::withMessages(['numero' => 'Esta ficha ya está registrada en la especialidad.']);
            }
            $ficha = Ficha::create(['especialidad_id' => $specialty->id, 'numero' => $data['numero'], 'cohorte' => $data['cohorte']]);
            app(RecordAuditEvent::class)->execute($actor, $specialty->institution_id, 'academic.ficha_created', 'ficha', $ficha->id);
        });
    }
}
