<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FR-04, FR-06 — validates application form fields. The `submit` field
 * (true/false) distinguishes a final submission (all fields mandatory)
 * from a draft save (FR-07, fields optional).
 */
class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isApplicant() ?? false;
    }

    public function rules(): array
    {
        $isSubmit = $this->boolean('submit');
        $required = $isSubmit ? 'required' : 'nullable';

        return [
            'scholarship_id' => ['required', 'exists:scholarships,id'],
            'programme_of_study' => [$required, 'string', 'max:255'],
            'year_of_study' => [$required, 'integer', 'min:1', 'max:8'],
            'nationality' => [$required, 'string', 'max:100'],
            'gpa_submitted' => [$required, 'numeric', 'min:0', 'max:4'],
            'financial_need_declared' => ['sometimes', 'boolean'],
            'personal_statement' => [$required, 'string', 'max:5000'],
            'referee_name' => [$required, 'string', 'max:255'],
            'referee_email' => [$required, 'email', 'max:255'],
            'submit' => ['sometimes', 'boolean'],
        ];
    }
}
