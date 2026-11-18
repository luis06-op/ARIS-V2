<?php

namespace App\Http\Requests\Guides;

use Illuminate\Foundation\Http\FormRequest;

class GuideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('version') ?? $this->route('project'));
    }

    public function rules(): array
    {
        return [
            'code' => [$this->route('version') ? 'sometimes' : 'required', 'string', 'max:40'],
            'title' => ['required', 'string', 'max:180'],
            'content' => ['required', 'string', 'max:50000'],
        ];
    }
}
