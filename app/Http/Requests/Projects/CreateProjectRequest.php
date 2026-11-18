<?php

namespace App\Http\Requests\Projects;

use App\Models\Ficha;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class CreateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ficha = filter_var($this->input('ficha_id'), FILTER_VALIDATE_INT)
            ? Ficha::find($this->input('ficha_id')) : null;

        return $ficha && $this->user()->can('create', [Project::class, $ficha]);
    }

    public function rules(): array
    {
        return [
            'ficha_id' => ['required', 'integer'],
            'code' => ['required', 'string', 'max:40'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
