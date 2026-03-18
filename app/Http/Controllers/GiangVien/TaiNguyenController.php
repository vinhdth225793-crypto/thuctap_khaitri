<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\TaiNguyenBuoiHoc;
use App\Models\LichHoc;
use App\Models\PhanCongModuleGiangVien;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTaiNguyenRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class TaiNguyenController extends Controller
{
    /**
     * Phase 4: Lưu tài nguyên buổi học (Bài giảng, tài liệu, bài tập)
     */
    public function store(StoreTaiNguyenRequest $request, $lichHocId)
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
            'loai_tai_nguyen' => 'required|in:bai_giang,tai_lieu,bai_tap,link_ngoai',
            'tieu_de'         => 'required|string|max:255',
            'mo_ta'           => 'nullable|string',
            'link_ngoai'      => 'nullable|url',
            'file_dinh_kem'   => 'nullable|file|mimes:doc,docx,ppt,pptx,pdf,xls,xlsx,txt,zip,rar|max:20480', // Max 20MB, more extensions
            'trang_thai_hien_thi' => 'nullable|in:an,hien',
            'thu_tu_hien_thi' => 'nullable|integer|min:0',
        ]);

        try {
            $data = $request->validated();
            $data = Arr::only($data, ['loai_tai_nguyen', 'tieu_de', 'mo_ta', 'link_ngoai', 'trang_thai_hien_thi', 'thu_tu_hien_thi']);
            $data['lich_hoc_id'] = $lichHocId;

            if ($request->hasFile('file_dinh_kem')) {
                $file = $request->file('file_dinh_kem');

                // Chuẩn hóa tên file an toàn
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', $file->getClientOriginalName());

                // Lưu vào disk 'public' dưới thư mục tai-lieu-buoi-hoc và đảm bảo visibility public
                $path = "tai-lieu-buoi-hoc/giang-vien/khoa-hoc/{$lichHoc->khoa_hoc_id}/buoi-{$lichHoc->id}";
                \Illuminate\Support\Facades\Storage::disk('public')->putFileAs($path, $file, $filename, ['visibility' => 'public']);

                $data['duong_dan_file'] = $path . '/' . $filename;
            }

            TaiNguyenBuoiHoc::create($data);

            return back()->with('success', 'Đã lưu bài giảng/tài liệu buổi học thành công.');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi upload: ' . $e->getMessage());
        }
    }

    /**
     * Cập nhật tài nguyên (Phase 4)
     */
    public function update(Request $request, $id)
    {
        $taiNguyen = TaiNguyenBuoiHoc::findOrFail($id);
        $lichHoc = $taiNguyen->lichHoc;
        $giangVien = auth()->user()->giangVien;

        // Bảo mật
        $isAssigned = PhanCongModuleGiangVien::where('module_hoc_id', $lichHoc->module_hoc_id)
            ->where('giao_vien_id', $giangVien->id)
            ->exists();

        if (!$isAssigned) {
            return back()->with('error', 'Bạn không có quyền chỉnh sửa tài nguyên này.');
        }

        $request->validate([
            'loai_tai_nguyen' => 'required|in:bai_giang,tai_lieu,bai_tap,link_ngoai',
            'tieu_de'         => 'required|string|max:255',
            'mo_ta'           => 'nullable|string',
            'link_ngoai'      => 'nullable|url',
            'file_dinh_kem'   => 'nullable|file|mimes:doc,docx,ppt,pptx,pdf,xls,xlsx,txt,zip,rar|max:20480',
            'trang_thai_hien_thi' => 'nullable|in:an,hien',
            'thu_tu_hien_thi' => 'nullable|integer|min:0',
        ]);

        try {
            $data = $request->only(['loai_tai_nguyen', 'tieu_de', 'mo_ta', 'link_ngoai', 'trang_thai_hien_thi', 'thu_tu_hien_thi']);

            if ($request->hasFile('file_dinh_kem')) {
                // Xóa file cũ nếu tồn tại
                if ($taiNguyen->duong_dan_file && Storage::disk('public')->exists($taiNguyen->duong_dan_file)) {
                    Storage::disk('public')->delete($taiNguyen->duong_dan_file);
                }

                $file = $request->file('file_dinh_kem');
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', $file->getClientOriginalName());
                $path = "giang-vien/khoa-hoc/{$lichHoc->khoa_hoc_id}/buoi-{$lichHoc->id}";
                
                $fullPath = $file->storeAs($path, $filename, 'public');
                $data['duong_dan_file'] = $fullPath;
            }

            $taiNguyen->update($data);

            return back()->with('success', 'Đã cập nhật bài giảng/tài liệu thành công.');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi cập nhật: ' . $e->getMessage());
        }
    }

    /**
     * Phase 6: Nhanh chóng bật/tắt hiển thị tài nguyên cho học viên
     */
    public function toggleHienThi($id)
    {
        $taiNguyen = TaiNguyenBuoiHoc::findOrFail($id);
        $lichHoc = $taiNguyen->lichHoc;
        $giangVien = auth()->user()->giangVien;

        // Bảo mật
        $isAssigned = PhanCongModuleGiangVien::where('module_hoc_id', $lichHoc->module_hoc_id)
            ->where('giao_vien_id', $giangVien->id)
            ->exists();

        if (!$isAssigned) {
            return back()->with('error', 'Bạn không có quyền thao tác trên tài nguyên này.');
        }

        $taiNguyen->trang_thai_hien_thi = ($taiNguyen->trang_thai_hien_thi === 'hien') ? 'an' : 'hien';
        $taiNguyen->save();

        $msg = $taiNguyen->trang_thai_hien_thi === 'hien' ? 'Đã công khai tài liệu cho học viên.' : 'Đã ẩn tài liệu với học viên.';
        return back()->with('success', $msg);
    }

    /**
     * Xóa tài nguyên (Cập nhật Phase 2: Xóa file vật lý triệt để)
     */
    public function destroy($id)
    {
        $taiNguyen = TaiNguyenBuoiHoc::findOrFail($id);
        $lichHoc = $taiNguyen->lichHoc;
        $giangVien = auth()->user()->giangVien;

        // Bảo mật: Kiểm tra quyền giảng viên
        $isAssigned = PhanCongModuleGiangVien::where('module_hoc_id', $lichHoc->module_hoc_id)
            ->where('giao_vien_id', $giangVien->id)
            ->exists();

        if (!$isAssigned) {
            return back()->with('error', 'Bạn không có quyền xóa tài nguyên này.');
        }

        try {
            if ($taiNguyen->duong_dan_file && Storage::disk('public')->exists($taiNguyen->duong_dan_file)) {
                Storage::disk('public')->delete($taiNguyen->duong_dan_file);
            }

            $taiNguyen->delete();

            return back()->with('success', 'Đã xóa tài nguyên và file đính kèm.');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi xóa tài nguyên: ' . $e->getMessage());
        }
    }
}
