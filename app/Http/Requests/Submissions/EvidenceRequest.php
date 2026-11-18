<?php

namespace App\Http\Requests\Submissions;

use Illuminate\Foundation\Http\FormRequest;

class EvidenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('upload', $this->route('version'));
    }

    public function rules(): array
    {
        return ['archivo' => ['required', 'file', 'max:'.(int) ceil(config('aris.files.max_bytes') / 1024)]];
    }
}
