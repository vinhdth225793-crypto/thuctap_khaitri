<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\DiemDanh;
use App\Models\LichHoc;
use App\Models\HocVienKhoaHoc;
use App\Models\PhanCongModuleGiangVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiemDanhController extends Controller
{
    /**
     * Hiển thị danh sách điểm danh cho 1 buổi học
     */
    public function show($lichHocId)
    {
        $lichHoc = LichHoc::with(['khoaHoc.hocViens', 'diemDanhs'])->findOrFail($lichHocId);
        $giangVien = auth()->user()->giangVien;

        // Bảo mật
        $isAssigned = PhanCongModuleGiangVien::where('module_hoc_id', $lichHoc->module_hoc_id)
            ->where('giao_vien_id', $giangVien->id)
            ->exists();

        if (!$isAssigned) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền điểm danh buổi này.'], 403);
        }

        // Lấy danh sách học viên chính thức của khóa học
        $hocViens = HocVienKhoaHoc::with('hocVien')
            ->where('khoa_hoc_id', $lichHoc->khoa_hoc_id)
            ->where('trang_thai', 'dang_hoc')
            ->get()
            ->map(function($enroll) use ($lichHoc) {
                $daDiemDanh = $lichHoc->diemDanhs->where('hoc_vien_id', $enroll->hoc_vien_id)->first();
                return [
                    'ma_nguoi_dung' => $enroll->hoc_vien_id,
                    'ho_ten'        => $enroll->hocVien->ho_ten,
                    'trang_thai'    => $daDiemDanh ? $daDiemDanh->trang_thai : 'co_mat',
                    'ghi_chu'       => $daDiemDanh ? $daDiemDanh->ghi_chu : '',
                ];
            });

        return response()->json([
            'success' => true,
            'buoi_so' => $lichHoc->buoi_so,
            'ngay'    => $lichHoc->ngay_hoc->format('d/m/Y'),
            'data'    => $hocViens
        ]);
    }

    /**
     * Lưu điểm danh hàng loạt
     */
    public function store(Request $request, $lichHocId)
    {
        $request->validate([
            'attendance' => 'required|array',
            'attendance.*.hoc_vien_id' => 'required|exists:nguoi_dung,ma_nguoi_dung',
            'attendance.*.trang_thai'  => 'required|in:co_mat,vang_mat,vao_tre',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->attendance as $item) {
                DiemDanh::updateOrCreate(
                    [
                        'lich_hoc_id' => $lichHocId,
                        'hoc_vien_id' => $item['hoc_vien_id']
                    ],
                    [
                        'trang_thai' => $item['trang_thai'],
                        'ghi_chu'    => $item['ghi_chu'] ?? null
                    ]
                );
            }
            DB::commit();
            return back()->with('success', 'Đã lưu thông tin điểm danh buổi học.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }
}
