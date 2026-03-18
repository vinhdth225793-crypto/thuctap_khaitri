<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaiNguyenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'loai_tai_nguyen' => 'required|in:bai_giang,tai_lieu,bai_tap,link_ngoai',
            'tieu_de' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'link_ngoai' => 'nullable|url',
            'file_dinh_kem' => 'nullable|file|mimes:doc,docx,ppt,pptx,pdf,xls,xlsx,txt,zip,rar|max:10240',
            'trang_thai_hien_thi' => 'nullable|in:an,hien',
            'thu_tu_hien_thi' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'file_dinh_kem.max' => 'Kích thước tập tin tối đa 10MB.',
        ];
    }
}
