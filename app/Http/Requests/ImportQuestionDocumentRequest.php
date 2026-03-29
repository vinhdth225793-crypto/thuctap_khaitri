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
            'file_import' => ['required', 'file', 'mimes:docx,pdf,xlsx,csv,txt', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'khoa_hoc_id.required' => 'Vui lòng chọn khóa học.',
            'khoa_hoc_id.exists' => 'Khóa học đã chọn không tồn tại.',
            'module_hoc_id.exists' => 'Module đã chọn không tồn tại.',
            'file_import.required' => 'Vui lòng chọn file để phân tích.',
            'file_import.mimes' => 'Vui lòng dùng file .docx, .pdf, .xlsx, .csv hoặc .txt.',
            'file_import.max' => 'File import không được lớn hơn 5MB.',
        ];
    }
}
