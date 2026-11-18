<?php

namespace App\Policies;

use App\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class DocumentVersionPolicy
{
    public function download(User $user, DocumentVersion $version): bool
    {
        $document = $version->document;
        if ($document->status !== 'activo' || ! $document->submission_id) {
            return false;
        }

        foreach ($version->documentLinks()->where('document_id', $document->id)->whereNotNull('submission_version_id')->with('submissionVersion.submission.project')->get() as $link) {
            $submission = $link->submissionVersion->submission;
            if ($submission->id === $document->submission_id && $submission->project_id === $document->project_id
                && $submission->project->institution_id === $document->institution_id
                && Gate::forUser($user)->allows('view', $link->submissionVersion)) {
                return true;
            }
        }

        return false;
    }
}
