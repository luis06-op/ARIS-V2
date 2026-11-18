<?php

namespace App\Http\Requests\Identity;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manageRoles', $this->route('institution'));
    }

    public function rules(): array
    {
        return [
            'operation' => ['required', Rule::in(['rol', 'instructor', 'evaluador'])],
            'membership_id' => ['required', 'integer', Rule::exists('institution_memberships', 'id')->where('institution_id', $this->route('institution')->id)],
            'active' => ['required', 'boolean'],
            'role' => ['required_if:operation,rol', 'nullable', Rule::in(['administrador', 'evaluador', 'instructor', 'aprendiz'])],
            'especialidad_id' => ['required_if:operation,instructor', 'nullable', 'integer', Rule::exists('especialidades', 'id')->where('institution_id', $this->route('institution')->id)],
            'ficha_id' => ['required_if:operation,evaluador', 'nullable', 'integer', 'exists:fichas,id'],
        ];
    }
}
