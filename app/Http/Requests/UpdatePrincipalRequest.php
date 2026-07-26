<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePrincipalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('principal.create') || $this->user()->can('principal.edit');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'type' => ['required', Rule::in(['principal', 'management'])],
            'country' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:60'],
            'email' => ['nullable', 'email', 'max:191'],
            'website' => ['nullable', 'string', 'max:191'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'assigned_staff_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
