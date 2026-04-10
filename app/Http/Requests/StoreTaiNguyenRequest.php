<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaiNguyenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isGiangVien();
    }

    public function rules(): array
    {
        return [
            'loai_tai_nguyen' => 'required|in:bai_giang,tai_lieu,bai_tap',
            'tieu_de' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'link_ngoai' => 'nullable|url|required_without:file_dinh_kem',
            'file_dinh_kem' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip,rar,jpg,jpeg,png,gif,mp4,webm,mp3,wav|max:10240|required_without:link_ngoai',
            'trang_thai_hien_thi' => 'nullable|in:an,hien',
            'thu_tu_hien_thi' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'file_dinh_kem.max' => 'Kích thước tệp tối đa là 10MB.',
            'file_dinh_kem.required_without' => 'Vui lòng tải lên tệp hoặc nhập liên kết ngoài hợp lệ.',
            'link_ngoai.required_without' => 'Vui lòng nhập liên kết ngoài hợp lệ hoặc tải lên tệp.',
            'link_ngoai.url' => 'Liên kết ngoài phải là URL hợp lệ.',
        ];
    }
}
