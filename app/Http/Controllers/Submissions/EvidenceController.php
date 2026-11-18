<?php

namespace App\Http\Controllers\Submissions;

use App\Actions\Submissions\AttachEvidence;
use App\Exceptions\InvalidEvidence;
use App\Http\Controllers\Controller;
use App\Http\Requests\Submissions\EvidenceRequest;
use App\Models\DocumentVersion;
use App\Models\SubmissionVersion;
use App\Services\Storage\EvidenceStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EvidenceController extends Controller
{
    public function store(EvidenceRequest $request, SubmissionVersion $version, AttachEvidence $action): RedirectResponse
    {
        try {
            $action->execute($request->user(), $version, $request->file('archivo'));
        } catch (InvalidEvidence $error) {
            throw ValidationException::withMessages(['archivo' => $error->getMessage()]);
        }

        return to_route('entregas.show', $version)->with('status', 'Evidencia almacenada de forma privada.');
    }

    public function download(DocumentVersion $documentVersion, EvidenceStorage $storage): StreamedResponse
    {
        Gate::authorize('download', $documentVersion);

        return $storage->download($documentVersion);
    }
}
