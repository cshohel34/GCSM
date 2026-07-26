<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequisitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('selection.create') || $this->user()->can('selection.edit');
    }

    public function rules(): array
    {
        return [
            'principal_id' => ['required', 'exists:principals,id'],
            'principal_contact_id' => ['nullable', 'exists:principal_contacts,id'],
            'reference' => ['nullable', 'string', 'max:120'],
            'requirement_date' => ['nullable', 'date'],
            'deadline' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
