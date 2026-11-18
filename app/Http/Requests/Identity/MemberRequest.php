<?php

namespace App\Http\Requests\Identity;

use Illuminate\Foundation\Http\FormRequest;

class MemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manageRoles', $this->route('institution'));
    }

    public function rules(): array
    {
        return ['email' => ['required', 'string', 'email', 'max:255']];
    }
}
