<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertNganHangCauHoiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $questionId = $this->route('id');

        return [
            'course_type' => ['nullable', Rule::in(['mau', 'hoat_dong'])],
            'khoa_hoc_id' => ['required', 'integer', 'exists:khoa_hoc,id'],
            'module_hoc_id' => ['nullable', 'integer', 'exists:module_hoc,id'],
            'ma_cau_hoi' => [
                'nullable',
                'string',
                'max:60',
                Rule::unique('ngan_hang_cau_hoi', 'ma_cau_hoi')->ignore($questionId),
            ],
            'noi_dung' => ['nullable', 'string'],
            'noi_dung_cau_hoi' => ['nullable', 'string'],
            'loai_cau_hoi' => ['required', Rule::in(['trac_nghiem', 'tu_luan'])],
            'kieu_dap_an' => ['nullable', Rule::in(['mot_dap_an', 'nhieu_dap_an', 'dung_sai'])],
            'muc_do' => ['required', Rule::in(['de', 'trung_binh', 'kho'])],
            'diem_mac_dinh' => ['required', 'numeric', 'min:0.25', 'max:100'],
            'goi_y_tra_loi' => ['nullable', 'string'],
            'giai_thich_dap_an' => ['nullable', 'string'],
            'trang_thai' => ['required', Rule::in(['nhap', 'san_sang', 'tam_an'])],
            'co_the_tai_su_dung' => ['nullable', 'boolean'],
            'correct_answer_key' => ['nullable', 'string'],
            'correct_answer_keys' => ['nullable', 'array'],
            'correct_answer_keys.*' => ['nullable', 'string'],
            'dap_an_dung' => ['nullable', 'string'],
            'dap_an_dung_sai' => ['nullable', Rule::in(['dung', 'sai'])],
            'dap_ans' => ['nullable', 'array'],
            'dap_ans.*.ky_hieu' => ['nullable', 'string', 'max:20'],
            'dap_ans.*.noi_dung' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'course_type.in' => 'Loại khóa học không hợp lệ.',
            'khoa_hoc_id.required' => 'Vui lòng chọn khóa học.',
            'khoa_hoc_id.exists' => 'Khóa học được chọn không hợp lệ.',
            'module_hoc_id.exists' => 'Module được chọn không hợp lệ.',
            'ma_cau_hoi.unique' => 'Mã câu hỏi đã tồn tại.',
            'loai_cau_hoi.required' => 'Vui lòng chọn loại câu hỏi.',
            'loai_cau_hoi.in' => 'Loại câu hỏi không hợp lệ.',
            'kieu_dap_an.in' => 'Kiểu đáp án không hợp lệ.',
            'muc_do.required' => 'Vui lòng chọn mức độ câu hỏi.',
            'muc_do.in' => 'Mức độ câu hỏi không hợp lệ.',
            'diem_mac_dinh.required' => 'Vui lòng nhập điểm mặc định.',
            'diem_mac_dinh.numeric' => 'Điểm mặc định phải là số.',
            'diem_mac_dinh.min' => 'Điểm mặc định phải lớn hơn hoặc bằng 0.25.',
            'diem_mac_dinh.max' => 'Điểm mặc định không được lớn hơn 100.',
            'trang_thai.required' => 'Vui lòng chọn trạng thái câu hỏi.',
            'trang_thai.in' => 'Trạng thái câu hỏi không hợp lệ.',
            'dap_ans.array' => 'Danh sách đáp án không hợp lệ.',
        ];
    }
}
