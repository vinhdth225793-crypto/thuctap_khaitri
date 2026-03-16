<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhanCongModuleGiangVien;
use App\Models\ModuleHoc;
use App\Models\GiangVien;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PhanCongController extends Controller
{
    /**
     * Phân công giảng viên cho module
     */
    public function assign(Request $request, $moduleId)
    {
        $request->validate([
            'giao_vien_id' => 'required|exists:giang_vien,id',
            'ghi_chu'      => 'nullable|string|max:500',
        ], [
            'giao_vien_id.required' => 'Vui lòng chọn giảng viên.',
            'giao_vien_id.exists'   => 'Giảng viên không hợp lệ.',
        ]);

        $moduleHoc = ModuleHoc::findOrFail($moduleId);

        DB::beginTransaction();
        try {
            $phanCong = PhanCongModuleGiangVien::updateOrCreate(
                [
                    'module_hoc_id' => $moduleId,
                    'giao_vien_id'  => $request->giao_vien_id
                ],
                [
                    'khoa_hoc_id'    => $moduleHoc->khoa_hoc_id,
                    'ngay_phan_cong' => now(),
                    'trang_thai'     => 'cho_xac_nhan',
                    'ghi_chu'        => $request->ghi_chu,
                    'created_by'     => Auth::user()->ma_nguoi_dung,
                ]
            );

            // Gửi thông báo cho GV
            $gv = GiangVien::find($request->giao_vien_id);
            ThongBaoService::guiPhanCongGV($gv, $moduleHoc, $moduleHoc->khoaHoc);

            DB::commit();
            return back()->with('success', 'Đã gửi yêu cầu phân công cho giảng viên.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    /**
     * Hủy phân công (chỉ khi đang chờ xác nhận)
     */
    public function huy($id)
    {
        $phanCong = PhanCongModuleGiangVien::findOrFail($id);

        if ($phanCong->trang_thai !== 'cho_xac_nhan') {
            return back()->with('error', 'Không thể hủy phân công đã được giảng viên xử lý.');
        }

        $phanCong->delete(); // Xóa hẳn record nếu chưa xác nhận để có thể gán người khác sạch sẽ

        return back()->with('success', 'Đã hủy yêu cầu phân công.');
    }

    /**
     * Thay đổi giảng viên cho module (Thay thế nhanh)
     */
    public function replace(Request $request, $id)
    {
        $request->validate([
            'giao_vien_id' => 'required|exists:giang_vien,id',
            'ghi_chu'      => 'nullable|string|max:500',
        ]);

        $phanCongCu = PhanCongModuleGiangVien::findOrFail($id);
        $moduleId = $phanCongCu->module_hoc_id;

        DB::beginTransaction();
        try {
            // Xóa phân công cũ
            $phanCongCu->delete();

            // Tạo phân công mới
            $moduleHoc = ModuleHoc::findOrFail($moduleId);
            $newPc = PhanCongModuleGiangVien::create([
                'khoa_hoc_id'    => $moduleHoc->khoa_hoc_id,
                'module_hoc_id'  => $moduleId,
                'giao_vien_id'   => $request->giao_vien_id,
                'ngay_phan_cong' => now(),
                'trang_thai'     => 'cho_xac_nhan',
                'ghi_chu'        => $request->ghi_chu,
                'created_by'     => Auth::user()->ma_nguoi_dung,
            ]);

            // Gửi thông báo cho GV mới
            $gv = GiangVien::find($request->giao_vien_id);
            ThongBaoService::guiPhanCongGV($gv, $moduleHoc, $moduleHoc->khoaHoc);

            DB::commit();
            return back()->with('success', 'Đã thay đổi giảng viên và gửi thông báo mới.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }
}
