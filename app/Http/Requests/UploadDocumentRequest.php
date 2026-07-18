<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FR-25, FR-26 — only PDF/JPEG/PNG accepted, maximum 5 MB per file.
 * NFR-15 — executable/script file types must be rejected; restricting
 * to an explicit allow-list of MIME types achieves this.
 */
class UploadDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isApplicant() ?? false;
    }

    public function rules(): array
    {
        return [
            'documents' => ['required', 'array', 'min:1'],
            'documents.*' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'], // 5120 KB = 5 MB
        ];
    }
}
