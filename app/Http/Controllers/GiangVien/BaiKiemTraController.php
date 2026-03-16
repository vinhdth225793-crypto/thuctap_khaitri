<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\BaiKiemTra;
use App\Models\LichHoc;
use App\Models\PhanCongModuleGiangVien;
use Illuminate\Http\Request;

class BaiKiemTraController extends Controller
{
    /**
     * Phase 8: Lưu bài kiểm tra mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'khoa_hoc_id' => 'required|exists:khoa_hoc,id',
            'tieu_de' => 'required|string|max:255',
            'pham_vi' => 'required|in:module,buoi_hoc',
            'thoi_gian_lam_bai' => 'required|integer|min:1',
            'module_hoc_id' => 'required_if:pham_vi,module|nullable|exists:module_hoc,id',
            'lich_hoc_id' => 'required_if:pham_vi,buoi_hoc|nullable|exists:lich_hoc,id',
        ]);

        $giangVien = auth()->user()->giangVien;

        // Bảo mật: GV chỉ được tạo test cho module mình dạy
        $moduleId = $request->pham_vi === 'module' 
            ? $request->module_hoc_id 
            : LichHoc::find($request->lich_hoc_id)->module_hoc_id;

        $isAssigned = PhanCongModuleGiangVien::where('module_hoc_id', $moduleId)
            ->where('giao_vien_id', $giangVien->id)
            ->exists();

        if (!$isAssigned) {
            return back()->with('error', 'Bạn không có quyền tạo bài kiểm tra cho phần này.');
        }

        BaiKiemTra::create($request->all());

        return back()->with('success', 'Đã tạo bài kiểm tra thành công. Hãy tiếp tục thiết lập bộ câu hỏi.');
    }

    /**
     * Xóa bài kiểm tra
     */
    public function destroy($id)
    {
        $test = BaiKiemTra::findOrFail($id);
        $test->delete();
        return back()->with('success', 'Đã xóa bài kiểm tra.');
    }
}
