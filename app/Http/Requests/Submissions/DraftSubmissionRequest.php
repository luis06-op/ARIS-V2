<?php

namespace App\Http\Requests\Submissions;

use Illuminate\Foundation\Http\FormRequest;

class DraftSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('submit', $this->route('project'));
    }

    public function rules(): array
    {
        return [
            'activity_assignment_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:180'],
            'summary' => ['required', 'string', 'max:500'],
        ];
    }
}
