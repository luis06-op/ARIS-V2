<?php

namespace App\Http\Requests\Submissions;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AcademicWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = match ($this->route()->getName()) {
            'entregas.enviar' => 'send',
            'entregas.revision.iniciar' => 'startReview',
            'entregas.revision.completar' => 'completeReview',
            'entregas.reabrir' => 'reopen',
        };

        return $this->user()->can($ability, $this->route('version'));
    }

    public function rules(): array
    {
        return match ($this->route()->getName()) {
            'entregas.revision.completar' => [
                'resultado' => ['required', Rule::in(['cumple', 'requiere_ajustes'])],
                'retroalimentacion' => ['required', 'string', 'max:10000'],
            ],
            'entregas.reabrir' => ['motivo' => ['required', 'string', 'max:5000']],
            default => [],
        };
    }
}
