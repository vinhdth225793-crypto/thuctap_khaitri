<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\PhanCongModuleGiangVien;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PhanCongController extends Controller
{
    public function index()
    {
        $giangVien = auth()->user()->giangVien;
        if (!$giangVien) {
            return redirect()->route('home')
                ->with('error', 'Tài khoản chưa được liên kết với giảng viên.');
        }

        $phanCongs = PhanCongModuleGiangVien::with([
                'moduleHoc.khoaHoc.monHoc',
            ])
            ->where('giao_vien_id', $giangVien->id)
            ->orderByRaw("FIELD(trang_thai, 'cho_xac_nhan', 'da_nhan', 'tu_choi')")
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.giang-vien.phan-cong.index', compact('phanCongs'));
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
        ], [
            'hanh_dong.required' => 'Vui lòng chọn hành động.',
            'hanh_dong.in'       => 'Hành động không hợp lệ.',
        ]);

        DB::transaction(function () use ($phanCong, $validated) {
            $phanCong->update([
                'trang_thai' => $validated['hanh_dong'],
                'ghi_chu'    => $validated['ghi_chu'],
            ]);

            if ($validated['hanh_dong'] === 'da_nhan') {
                // Kiểm tra xem TẤT CẢ module của KH đã có GV xác nhận chưa
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
            ? 'Đã xác nhận dạy module thành công!'
            : 'Đã từ chối phân công.';

        return back()->with('success', $msg);
    }
}
