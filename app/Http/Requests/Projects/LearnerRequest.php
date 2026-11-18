<?php

namespace App\Http\Requests\Projects;

use Illuminate\Foundation\Http\FormRequest;

class LearnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manageLearners', $this->route('project'));
    }

    public function rules(): array
    {
        return ['user_id' => ['required', 'integer', 'exists:users,id'], 'active' => ['required', 'boolean']];
    }
}
