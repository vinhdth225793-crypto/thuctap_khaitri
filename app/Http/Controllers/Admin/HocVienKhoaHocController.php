<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KhoaHoc;
use App\Models\HocVienKhoaHoc;
use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class HocVienKhoaHocController extends Controller
{
    /**
     * Phase 2: Xem danh sách học viên trong khóa học
     */
    public function index(int $khoaHocId)
    {
        $khoaHoc = KhoaHoc::with(['nhomNganh'])->findOrFail($khoaHocId);

        // Lấy danh sách ghi danh kèm thông tin người dùng
        $hocViens = HocVienKhoaHoc::with(['hocVien'])
            ->where('khoa_hoc_id', $khoaHocId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Thống kê nhanh
        $stats = [
            'tong' => $hocViens->total(),
            'dang_hoc' => HocVienKhoaHoc::where('khoa_hoc_id', $khoaHocId)->where('trang_thai', 'dang_hoc')->count(),
            'hoan_thanh' => HocVienKhoaHoc::where('khoa_hoc_id', $khoaHocId)->where('trang_thai', 'hoan_thanh')->count(),
        ];

        // Lấy danh sách học viên toàn hệ thống (phục vụ Phase 3 - thêm mới)
        // Chỉ lấy những người có vai trò là hoc_vien và chưa có trong khóa học này
        $availableStudents = NguoiDung::where('vai_tro', 'hoc_vien')
            ->where('trang_thai', 1)
            ->whereDoesntHave('khoaHocs', function($q) use ($khoaHocId) {
                $q->where('khoa_hoc_id', $khoaHocId);
            })
            ->orderBy('ho_ten')
            ->get();

        return view('pages.admin.hoc-vien-khoa-hoc.index', compact('khoaHoc', 'hocViens', 'stats', 'availableStudents'));
    }

    /**
     * Phase 3: Thêm học viên vào khóa học
     */
    public function store(Request $request, int $khoaHocId)
    {
        $request->validate([
            'hoc_vien_ids' => 'required|array|min:1',
            'hoc_vien_ids.*' => 'exists:nguoi_dung,ma_nguoi_dung',
            'ngay_tham_gia' => 'nullable|date',
            'ghi_chu' => 'nullable|string|max:500',
        ]);

        $khoaHoc = KhoaHoc::findOrFail($khoaHocId);
        
        DB::beginTransaction();
        try {
            $count = 0;
            foreach ($request->hoc_vien_ids as $hvId) {
                // Kiểm tra lại lần nữa để tránh trùng (mặc dù view đã lọc)
                $exists = HocVienKhoaHoc::where('khoa_hoc_id', $khoaHocId)
                    ->where('hoc_vien_id', $hvId)
                    ->exists();

                if (!$exists) {
                    HocVienKhoaHoc::create([
                        'khoa_hoc_id' => $khoaHocId,
                        'hoc_vien_id' => $hvId,
                        'ngay_tham_gia' => $request->ngay_tham_gia ?? now(),
                        'trang_thai' => 'dang_hoc',
                        'ghi_chu' => $request->ghi_chu,
                        'created_by' => Auth::user()->ma_nguoi_dung,
                    ]);
                    $count++;
                }
            }
            DB::commit();
            return back()->with('success', "Đã thêm thành công {$count} học viên vào khóa học.");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Phase 5: Cập nhật thông tin ghi danh
     */
    public function update(Request $request, int $khoaHocId, int $id)
    {
        $request->validate([
            'ngay_tham_gia' => 'required|date',
            'trang_thai' => 'required|in:dang_hoc,hoan_thanh,ngung_hoc',
            'ghi_chu' => 'nullable|string|max:500',
        ]);

        $enrollment = HocVienKhoaHoc::where('khoa_hoc_id', $khoaHocId)->findOrFail($id);
        $enrollment->update($request->only(['ngay_tham_gia', 'trang_thai', 'ghi_chu']));

        return back()->with('success', 'Cập nhật thông tin ghi danh thành công.');
    }

    /**
     * Phase 4: Xóa học viên khỏi khóa học
     */
    public function destroy(int $khoaHocId, int $id)
    {
        $enrollment = HocVienKhoaHoc::where('khoa_hoc_id', $khoaHocId)->findOrFail($id);
        
        // Tùy chọn: Xóa vĩnh viễn bản ghi bảng trung gian
        $enrollment->delete();

        return back()->with('success', 'Đã xóa học viên khỏi khóa học.');
    }
}
