<?php

namespace App\Http\Requests\Submissions;

use Illuminate\Foundation\Http\FormRequest;

class SubmissionVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('upload', $this->route('version'));
    }

    public function rules(): array
    {
        return ['summary' => ['required', 'string', 'max:500']];
    }
}
