<?php

namespace App\Actions\Submissions;

use App\Exceptions\InvalidEvidence;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Submission;
use App\Models\SubmissionVersion;
use App\Models\User;
use App\Services\Storage\EvidenceStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class AttachEvidence
{
    public function execute(User $actor, SubmissionVersion $version, UploadedFile $file): DocumentVersion
    {
        return DB::transaction(function () use ($actor, $version, $file): DocumentVersion {
            $submission = Submission::whereKey($version->submission_id)->lockForUpdate()->firstOrFail();
            $version = SubmissionVersion::whereKey($version->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor)->authorize('upload', $version);
            if ($version->documentLinks()->count() >= (int) config('aris.files.max_per_version')) {
                throw new InvalidEvidence('Esta versión alcanzó el límite de archivos permitido.');
            }
            $document = Document::create([
                'institution_id' => $submission->project->institution_id,
                'project_id' => $submission->project_id, 'submission_id' => $submission->id, 'created_by' => $actor->id,
            ]);

            return app(EvidenceStorage::class)->attach($document, $file, $actor, ['submission_version_id' => $version->id]);
        });
    }
}
