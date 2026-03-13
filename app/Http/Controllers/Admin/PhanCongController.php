<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModuleHoc;
use App\Models\PhanCongModuleGiangVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PhanCongController extends Controller
{
    /**
     * Phân công giảng viên cho module
     */
    public function assign(Request $request, $moduleId)
    {
        $validator = Validator::make($request->all(), [
            'giao_vien_id' => 'required|exists:giang_vien,id',
            'ghi_chu' => 'nullable|string|max:500',
        ], [
            'giao_vien_id.required' => 'Vui lòng chọn giảng viên',
            'giao_vien_id.exists' => 'Giảng viên không tồn tại trong hệ thống',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $module = ModuleHoc::findOrFail($moduleId);

        // Logic: updateOrCreate để reactivate nếu đã từng phân công nhưng bị từ chối/hủy
        PhanCongModuleGiangVien::updateOrCreate(
            [
                'module_hoc_id' => $moduleId,
                'giao_vien_id' => $request->giao_vien_id,
            ],
            [
                'khoa_hoc_id' => $module->khoa_hoc_id,
                'ngay_phan_cong' => now(),
                'trang_thai' => 'cho_xac_nhan',
                'ghi_chu' => $request->ghi_chu,
                'created_by' => auth()->user()->ma_nguoi_dung,
            ]
        );

        return redirect()->back()->with('success', 'Đã gửi yêu cầu phân công cho giảng viên.');
    }

    /**
     * Hủy phân công giảng viên (chỉ khi đang chờ xác nhận)
     */
    public function huy(Request $request, $id)
    {
        $phanCong = PhanCongModuleGiangVien::findOrFail($id);

        if ($phanCong->trang_thai !== 'cho_xac_nhan') {
            return redirect()->back()->with('error', 'Không thể hủy phân công đã được giảng viên tiếp nhận.');
        }

        $phanCong->update(['trang_thai' => 'tu_choi', 'ghi_chu' => $phanCong->ghi_chu . ' (Admin đã hủy phân công)']);

        return redirect()->back()->with('success', 'Đã hủy phân công giảng viên thành công.');
    }
}
