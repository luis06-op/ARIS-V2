<?php

namespace App\Http\Requests\Projects;

use Illuminate\Foundation\Http\FormRequest;

class TutorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manageTutors', $this->route('project'));
    }

    public function rules(): array
    {
        return ['user_id' => ['required', 'integer', 'exists:users,id'], 'active' => ['required', 'boolean']];
    }
}
