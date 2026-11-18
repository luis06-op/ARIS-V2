<?php

namespace App\Http\Requests\Submissions;

use Illuminate\Foundation\Http\FormRequest;

class UnlinkedSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('authorizeUnlinkedException', $this->route('project'));
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:180'],
            'summary' => ['required', 'string', 'max:500'],
            'motivo_excepcion' => ['required', 'string', 'min:20', 'max:2000'],
        ];
    }
}
