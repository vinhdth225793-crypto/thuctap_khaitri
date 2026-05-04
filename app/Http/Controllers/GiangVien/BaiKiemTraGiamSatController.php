<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Http\Controllers\GiangVien\Concerns\XacThucGiangVienBaiKiemTra;
use App\Models\BaiKiemTra;
use App\Services\ExamSurveillanceService;
use Illuminate\Http\Request;

class BaiKiemTraGiamSatController extends Controller
{
    use XacThucGiangVienBaiKiemTra;

    public function __construct(
        private readonly ExamSurveillanceService $surveillanceService,
    ) {}

    public function editSurveillance(int $id)
    {
        $baiKiemTra = BaiKiemTra::with([
            'khoaHoc:id,ma_khoa_hoc,ten_khoa_hoc',
            'moduleHoc:id,ma_module,ten_module',
            'lichHoc:id,buoi_so,ngay_hoc',
        ])->withCount([
            'chiTietCauHois',
            'baiLams',
            'baiLams as bai_lams_dang_lam_count' => fn ($query) => $query->where('trang_thai', 'dang_lam'),
        ])->findOrFail($id);

        $this->authorizeTeacherForExam(auth()->user()?->giangVien, $baiKiemTra);

        return view('pages.hoc-vien.bai-kiem-tra.teacher-surveillance-settings', compact('baiKiemTra'));
    }

    public function updateSurveillanceSettings(Request $request, int $id)
    {
        $baiKiemTra = BaiKiemTra::withCount([
            'baiLams as bai_lams_dang_lam_count' => fn ($query) => $query->where('trang_thai', 'dang_lam'),
        ])->findOrFail($id);

        $this->authorizeTeacherForExam(auth()->user()?->giangVien, $baiKiemTra);

        if ((int) $baiKiemTra->bai_lams_dang_lam_count > 0) {
            return back()->with('error', 'Không thể thay đổi cấu hình giám sát khi đang có học viên làm bài.');
        }

        $validated = $request->validate([
            'co_giam_sat' => 'nullable|boolean',
            'bat_buoc_fullscreen' => 'nullable|boolean',
            'bat_buoc_camera' => 'nullable|boolean',
            'so_lan_vi_pham_toi_da' => 'nullable|integer|min:1|max:20',
            'chu_ky_snapshot_giay' => 'nullable|integer|min:10|max:300',
            'tu_dong_nop_khi_vi_pham' => 'nullable|boolean',
            'chan_copy_paste' => 'nullable|boolean',
            'chan_chuot_phai' => 'nullable|boolean',
        ]);

        $baiKiemTra->update($this->surveillanceService->normalizeExamConfig($validated, $request));

        return redirect()
            ->route('giang-vien.bai-kiem-tra.surveillance.edit', $baiKiemTra->id)
            ->with('success', 'Đã cập nhật cấu hình giám sát cho bài kiểm tra.');
    }
}
