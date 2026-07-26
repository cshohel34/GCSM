<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCrewProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('crew.create');
    }

    public function rules(): array
    {
        $id = $this->route('crew')?->id;

        return [
            'admission_id' => ['nullable', 'string', 'max:120', Rule::unique('crew_profiles', 'admission_id')->ignore($id)],
            'name' => ['required', 'string', 'max:191'],
            'name_chinese' => ['nullable', 'string', 'max:191'],
            'father_name' => ['nullable', 'string', 'max:191'],
            'mother_name' => ['nullable', 'string', 'max:191'],
            'sid_no' => ['nullable', 'string', 'max:120'],
            'nid_no' => ['nullable', 'string', 'max:120'],
            'birth_registration_no' => ['nullable', 'string', 'max:120'],
            'current_rank_id' => ['nullable', 'exists:ranks,id'],
            'rank_applied_id' => ['nullable', 'exists:ranks,id'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(['Male', 'Female'])],
            'marital_status' => ['nullable', Rule::in(['Single','Married','Widowed','Separated','Divorced','Not specified'])],
            'mobile' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:191'],
            'cdc_no' => ['nullable', 'string', 'max:120'],
            'passport_no' => ['nullable', 'string', 'max:120'],
            'coc_no' => ['nullable', 'string', 'max:120'],
            'place_of_birth' => ['nullable', 'string', 'max:191'],
            'nationality' => ['nullable', 'string', 'max:120'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'emergency_contact' => ['nullable', 'string', 'max:60'],
            'present_address' => ['nullable', 'string', 'max:500'],
            'availability' => ['nullable', Rule::in(['available', 'not_available'])],
            'job_urgency' => ['nullable', Rule::in(['normal', 'high', 'urgent'])],
            'photo' => ['nullable', 'image', 'max:4096'],
        ];
    }
}
