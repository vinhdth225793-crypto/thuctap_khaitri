<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\KhoaHoc;
use App\Models\PhanCongModuleGiangVien;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PhanCongController extends Controller
{
    /**
     * Hiển thị lộ trình giảng dạy gom nhóm theo Khóa học
     */
    public function index()
    {
        $giangVien = auth()->user()->giangVien;
        if (!$giangVien) {
            return redirect()->route('home')
                ->with('error', 'Tài khoản chưa được liên kết với giảng viên.');
        }

        // Lấy danh sách khóa học mà GV có tham gia dạy (ít nhất 1 module)
        $khoaHocs = KhoaHoc::with(['nhomNganh', 'moduleHocs' => function($q) use ($giangVien) {
                // Chỉ lấy những module mà GV này được phân công trong khóa học đó
                $q->whereHas('phanCongGiangViens', function($q2) use ($giangVien) {
                    $q2->where('giao_vien_id', $giangVien->id);
                })->with(['phanCongGiangViens' => function($q2) use ($giangVien) {
                    $q2->where('giao_vien_id', $giangVien->id);
                }]);
            }])
            ->whereHas('moduleHocs.phanCongGiangViens', function($q) use ($giangVien) {
                $q->where('giao_vien_id', $giangVien->id);
            })
            ->orderBy('id', 'desc')
            ->get();

        // Đếm số phân công mới để hiển thị badge thông báo
        $phanCongChoXacNhan = PhanCongModuleGiangVien::where('giao_vien_id', $giangVien->id)
            ->where('trang_thai', 'cho_xac_nhan')
            ->count();

        return view('pages.giang-vien.phan-cong.index', compact('khoaHocs', 'phanCongChoXacNhan'));
    }

    /**
     * Chi tiết khóa học/module dành cho giảng viên
     */
    public function show($id)
    {
        $giangVien = auth()->user()->giangVien;
        
        $phanCong = PhanCongModuleGiangVien::with([
            'khoaHoc.nhomNganh', 
            'moduleHoc',
            'khoaHoc.hocVienKhoaHocs.hocVien'
        ])
        ->where('giao_vien_id', $giangVien->id)
        ->findOrFail($id);

        $khoaHoc = $phanCong->khoaHoc;
        
        // Lấy TOÀN BỘ lịch dạy của Module này (để GV thấy lộ trình đầy đủ)
        $lichDays = \App\Models\LichHoc::where('module_hoc_id', $phanCong->module_hoc_id)
            ->orderBy('ngay_hoc')
            ->get();

        return view('pages.giang-vien.phan-cong.show', compact('phanCong', 'khoaHoc', 'lichDays'));
    }

    public function xacNhan(Request $request, $id)
    {
        $giangVien = auth()->user()->giangVien;
        $phanCong  = PhanCongModuleGiangVien::where('id', $id)
            ->where('giao_vien_id', $giangVien->id)
            ->firstOrFail();

        if ($phanCong->trang_thai !== 'cho_xac_nhan') {
            return back()->with('error', 'Phân công này đã được xử lý rồi.');
        }

        $validated = $request->validate([
            'hanh_dong' => 'required|in:da_nhan,tu_choi',
            'ghi_chu'   => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($phanCong, $validated) {
            $phanCong->update([
                'trang_thai' => $validated['hanh_dong'],
                'ghi_chu'    => $validated['ghi_chu'] ?? null,
            ]);

            if ($validated['hanh_dong'] === 'da_nhan') {
                $khoaHoc    = $phanCong->khoaHoc()->with('moduleHocs.phanCongGiangViens')->first();
                $tongModule = $khoaHoc->moduleHocs->count();
                $daXacNhan  = $khoaHoc->moduleHocs->filter(
                    fn($m) => $m->phanCongGiangViens
                        ->where('trang_thai', 'da_nhan')->count() > 0
                )->count();

                if ($tongModule > 0 && $daXacNhan >= $tongModule) {
                    $khoaHoc->update(['trang_thai_van_hanh' => 'san_sang']);
                    ThongBaoService::guiSanSangChoAdmin($khoaHoc);
                }
            }
        });

        $msg = $validated['hanh_dong'] === 'da_nhan'
            ? 'Đã xác nhận bài dạy thành công!'
            : 'Đã từ chối phân công.';

        return back()->with('success', $msg);
    }
}
