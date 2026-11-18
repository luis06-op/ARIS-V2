<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manageRoles', $this->route('institution'));
    }

    public function rules(): array
    {
        return [
            'operation' => ['required', Rule::in(['especialidad', 'ficha'])],
            'codigo' => ['required_if:operation,especialidad', 'nullable', 'string', 'max:80'],
            'nombre' => ['required_if:operation,especialidad', 'nullable', 'string', 'max:255'],
            'especialidad_id' => ['required_if:operation,ficha', 'nullable', 'integer', Rule::exists('especialidades', 'id')->where('institution_id', $this->route('institution')->id)],
            'numero' => ['required_if:operation,ficha', 'nullable', 'string', 'max:80'],
            'cohorte' => ['required_if:operation,ficha', 'nullable', 'string', 'max:120'],
        ];
    }
}
