<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\TaiNguyenBuoiHoc;
use App\Models\LichHoc;
use App\Models\PhanCongModuleGiangVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class TaiNguyenController extends Controller
{
    /**
     * Phase 4: Lưu tài nguyên buổi học (Bài giảng, tài liệu, bài tập)
     */
    public function store(Request $request, $lichHocId)
    {
        $lichHoc = LichHoc::findOrFail($lichHocId);
        $giangVien = auth()->user()->giangVien;

        // Bảo mật: Kiểm tra quyền giảng viên đối với module này
        $isAssigned = PhanCongModuleGiangVien::where('module_hoc_id', $lichHoc->module_hoc_id)
            ->where('giao_vien_id', $giangVien->id)
            ->where('trang_thai', 'da_nhan')
            ->exists();

        if (!$isAssigned) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền quản lý tài nguyên buổi học này.'], 403);
        }

        $request->validate([
            'loai_tai_nguyen' => 'required|in:bai_giang,tai_lieu,bai_tap',
            'tieu_de'         => 'required|string|max:255',
            'mo_ta'           => 'nullable|string',
            'link_ngoai'      => 'nullable|url',
            'file_dinh_kem'   => 'nullable|file|max:10240', // Tối đa 10MB
        ]);

        try {
            $data = $request->only(['loai_tai_nguyen', 'tieu_de', 'mo_ta', 'link_ngoai']);
            $data['lich_hoc_id'] = $lichHocId;

            if ($request->hasFile('file_dinh_kem')) {
                $file = $request->file('file_dinh_kem');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('tai-lieu-buoi-hoc', $filename, 'public');
                $data['duong_dan_file'] = $path;
            }

            TaiNguyenBuoiHoc::create($data);

            return back()->with('success', 'Đã đăng tài nguyên lên buổi học thành công.');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    /**
     * Xóa tài nguyên
     */
    public function destroy($id)
    {
        $taiNguyen = TaiNguyenBuoiHoc::findOrFail($id);
        $lichHoc = $taiNguyen->lichHoc;
        $giangVien = auth()->user()->giangVien;

        // Bảo mật
        $isAssigned = PhanCongModuleGiangVien::where('module_hoc_id', $lichHoc->module_hoc_id)
            ->where('giao_vien_id', $giangVien->id)
            ->exists();

        if (!$isAssigned) {
            return back()->with('error', 'Bạn không có quyền xóa tài nguyên này.');
        }

        if ($taiNguyen->duong_dan_file) {
            Storage::disk('public')->delete($taiNguyen->duong_dan_file);
        }

        $taiNguyen->delete();

        return back()->with('success', 'Đã xóa tài nguyên.');
    }
}
