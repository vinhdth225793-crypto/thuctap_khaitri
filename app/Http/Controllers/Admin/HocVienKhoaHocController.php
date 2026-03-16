<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KhoaHoc;
use App\Models\HocVienKhoaHoc;
use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HocVienKhoaHocController extends Controller
{
    /**
     * Danh sách học viên trong một khóa học (trang riêng)
     * GET /admin/khoa-hoc/{khoaHocId}/hoc-vien
     */
    public function index(int $khoaHocId)
    {
        $khoaHoc = KhoaHoc::with([
            'nhomNganh',
            'hocVienKhoaHocs.hocVien',
        ])->findOrFail($khoaHocId);

        // Danh sách học viên chưa tham gia khóa này (để form thêm mới)
        $idsDaThamGia = $khoaHoc->hocVienKhoaHocs->pluck('hoc_vien_id');
        $hocVienChuaThamGia = NguoiDung::where('vai_tro','hoc_vien')
            ->where('trang_thai', true)
            ->whereNotIn('ma_nguoi_dung', $idsDaThamGia)
            ->orderBy('ho_ten')
            ->get();

        return view('pages.admin.hoc-vien-khoa-hoc.index', compact(
            'khoaHoc', 'hocVienChuaThamGia'
        ));
    }

    /**
     * Thêm học viên vào khóa học
     * POST /admin/khoa-hoc/{khoaHocId}/hoc-vien
     */
    public function store(Request $request, int $khoaHocId)
    {
        $khoaHoc = KhoaHoc::findOrFail($khoaHocId);

        if ($khoaHoc->trang_thai_van_hanh !== 'dang_day') {
            return back()->with('error', 'Chỉ có thể thêm học viên vào khóa học đang hoạt động.');
        }

        $request->validate([
            'hoc_vien_ids'   => 'required|array|min:1',
            'hoc_vien_ids.*' => 'exists:nguoi_dung,ma_nguoi_dung',
            'ghi_chu'        => 'nullable|string|max:500',
        ], [
            'hoc_vien_ids.required' => 'Vui lòng chọn ít nhất một học viên.',
            'hoc_vien_ids.*.exists' => 'Học viên không hợp lệ.',
        ]);

        DB::transaction(function () use ($request, $khoaHoc) {
            foreach ($request->hoc_vien_ids as $hocVienId) {
                // Kiểm tra vai_tro
                $nd = NguoiDung::where('ma_nguoi_dung', $hocVienId)
                               ->where('vai_tro','hoc_vien')->first();
                if (!$nd) continue;

                HocVienKhoaHoc::firstOrCreate(
                    ['khoa_hoc_id' => $khoaHoc->id, 'hoc_vien_id' => $hocVienId],
                    [
                        'ngay_tham_gia' => Carbon::today(),
                        'trang_thai'    => 'dang_hoc',
                        'ghi_chu'       => $request->ghi_chu,
                        'created_by'    => auth()->id(),
                    ]
                );
            }
        });

        return back()->with('success', 'Đã thêm học viên vào khóa học thành công.');
    }

    /**
     * Thay đổi trạng thái học viên
     * PUT /admin/khoa-hoc/{khoaHocId}/hoc-vien/{id}/trang-thai
     */
    public function updateTrangThai(Request $request, int $khoaHocId, int $id)
    {
        $bghv = HocVienKhoaHoc::where('khoa_hoc_id', $khoaHocId)->findOrFail($id);

        $request->validate([
            'trang_thai' => 'required|in:dang_hoc,hoan_thanh,ngung_hoc',
            'ghi_chu'    => 'nullable|string|max:500',
        ], [
            'trang_thai.required' => 'Vui lòng chọn trạng thái.',
            'trang_thai.in'       => 'Trạng thái không hợp lệ.',
        ]);

        DB::transaction(function () use ($request, $bghv) {
            $bghv->update([
                'trang_thai' => $request->trang_thai,
                'ghi_chu'    => $request->ghi_chu ?? $bghv->ghi_chu,
            ]);
        });

        return back()->with('success', 'Đã cập nhật trạng thái học viên.');
    }

    /**
     * Xóa học viên khỏi khóa học (đổi trạng thái → ngung_hoc)
     * DELETE /admin/khoa-hoc/{khoaHocId}/hoc-vien/{id}
     */
    public function destroy(int $khoaHocId, int $id)
    {
        $khoaHoc = KhoaHoc::findOrFail($khoaHocId);
        if ($khoaHoc->trang_thai_van_hanh !== 'dang_day') {
            return back()->with('error', 'Không thể thay đổi danh sách học viên khi khóa học không ở trạng thái đang dạy.');
        }

        $bghv = HocVienKhoaHoc::where('khoa_hoc_id', $khoaHocId)->findOrFail($id);

        DB::transaction(function () use ($bghv) {
            $bghv->update(['trang_thai' => 'ngung_hoc']);
        });

        return back()->with('success', 'Đã xóa học viên khỏi khóa học.');
    }
}
