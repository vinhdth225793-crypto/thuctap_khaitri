<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportQuestionDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'khoa_hoc_id' => ['required', 'integer', 'exists:khoa_hoc,id'],
            'module_hoc_id' => ['nullable', 'integer', 'exists:module_hoc,id'],
            'file_import' => ['required', 'file', 'mimes:docx,pdf,xlsx,csv,txt', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'khoa_hoc_id.required' => 'Vui long chon khoa hoc.',
            'khoa_hoc_id.exists' => 'Khoa hoc da chon khong ton tai.',
            'module_hoc_id.exists' => 'Module da chon khong ton tai.',
            'file_import.required' => 'Vui long chon file de phan tich.',
            'file_import.mimes' => 'Vui long dung file .docx, .pdf, .xlsx, .csv hoac .txt.',
            'file_import.max' => 'File import khong duoc lon hon 10MB.',
        ];
    }
}
