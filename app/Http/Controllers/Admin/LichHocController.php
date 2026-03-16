<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KhoaHoc;
use App\Models\ModuleHoc;
use App\Models\LichHoc;
use App\Models\GiangVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LichHocController extends Controller
{
    /**
     * Hiển thị toàn bộ lịch học của khóa học (nhóm theo module)
     */
    public function index(int $khoaHocId)
    {
        $khoaHoc = KhoaHoc::with([
            'nhomNganh',
            'moduleHocs.lichHocs.giangVien.nguoiDung',
            'moduleHocs.phanCongGiangViens.giangVien.nguoiDung'
        ])->findOrFail($khoaHocId);

        $giangViens = GiangVien::with('nguoiDung')
            ->whereHas('nguoiDung', fn($q) => $q->where('trang_thai', true))
            ->get();

        return view('pages.admin.lich-hoc.index', compact('khoaHoc', 'giangViens'));
    }

    /**
     * Cập nhật số buổi của module
     */
    public function updateSoBuoiModule(Request $request, int $khoaHocId, int $moduleId)
    {
        $request->validate([
            'so_buoi' => 'required|integer|min:1|max:100'
        ]);

        $module = ModuleHoc::where('khoa_hoc_id', $khoaHocId)->findOrFail($moduleId);
        $module->update(['so_buoi' => $request->so_buoi]);

        return back()->with('success', "Đã cập nhật số buổi cho module {$module->ten_module}.");
    }

    /**
     * Tạo 1 buổi lẻ
     */
    public function store(Request $request, int $khoaHocId)
    {
        $request->validate([
            'module_hoc_id'  => 'required|exists:module_hoc,id',
            'ngay_hoc'       => 'required|date|after_or_equal:today',
            'gio_bat_dau'    => 'required|date_format:H:i',
            'gio_ket_thuc'   => 'required|date_format:H:i|after:gio_bat_dau',
            'phong_hoc'      => 'nullable|string|max:100',
            'hinh_thuc'      => 'required|in:truc_tiep,online',
            'link_online'    => 'required_if:hinh_thuc,online|nullable|url|max:500',
            'giang_vien_id'  => 'nullable|exists:giang_vien,id',
        ]);

        $module = ModuleHoc::findOrFail($request->module_hoc_id);
        $buoiSo = $module->lichHocs()->count() + 1;

        // Tự động tính toán thứ trong tuần dựa trên ngày học (International standard mapping to 2-8)
        $date = Carbon::parse($request->ngay_hoc);
        $carbonDay = $date->dayOfWeek; // 0 (Sun) to 6 (Sat)
        $dbDay = ($carbonDay === 0) ? 8 : ($carbonDay + 1);

        LichHoc::create(array_merge($request->all(), [
            'khoa_hoc_id'    => $khoaHocId,
            'thu_trong_tuan' => $dbDay,
            'buoi_so'        => $buoiSo,
            'trang_thai'     => 'cho'
        ]));

        return back()->with('success', 'Đã thêm buổi học mới thành công.');
    }

    /**
     * Sinh lịch tự động
     */
    public function storeAuto(Request $request, int $khoaHocId)
    {
        $request->validate([
            'module_hoc_id'  => 'required|exists:module_hoc,id',
            'ngay_bat_dau'   => 'required|date|after_or_equal:today',
            'gio_bat_dau'    => 'required|date_format:H:i',
            'gio_ket_thuc'   => 'required|date_format:H:i|after:gio_bat_dau',
            'thu_trong_tuan' => 'required|array|min:1',
            'thu_trong_tuan.*' => 'integer|between:2,8',
            'phong_hoc'      => 'nullable|string|max:100',
            'hinh_thuc'      => 'required|in:truc_tiep,online',
        ]);

        $module = ModuleHoc::findOrFail($request->module_hoc_id);
        $soBuoiQuyDinh = $module->so_buoi;
        
        DB::beginTransaction();
        try {
            // Chỉ xóa các buổi học có trạng thái 'cho' (chưa bắt đầu)
            $module->lichHocs()->where('trang_thai', 'cho')->delete();

            // Tính số buổi đã hoàn thành hoặc đang học để bắt đầu đếm tiếp buoi_so
            $soBuoiDaCo = $module->lichHocs()->count();
            $soBuoiCanTao = max(0, $soBuoiQuyDinh - $soBuoiDaCo);

            if ($soBuoiCanTao <= 0) {
                return back()->with('info', 'Số buổi hiện tại đã đủ hoặc vượt mức quy định. Không cần sinh thêm.');
            }

            $currentDate = Carbon::parse($request->ngay_bat_dau);
            $createdCount = 0;
            $daysOfWeek = array_map('intval', $request->thu_trong_tuan);

            // Giới hạn vòng lặp để tránh treo server nếu logic có vấn đề (tối đa 2 năm)
            $stopDate = $currentDate->copy()->addYears(2);

            while ($createdCount < $soBuoiCanTao && $currentDate->lessThan($stopDate)) {
                // Carbon: 0 (Sun) to 6 (Sat)
                // DB Standard: 2 (Mon), 3 (Tue), ..., 7 (Sat), 8 (Sun)
                $carbonDay = $currentDate->dayOfWeek;
                $dbDay = ($carbonDay === 0) ? 8 : ($carbonDay + 1);

                if (in_array($dbDay, $daysOfWeek)) {
                    LichHoc::create([
                        'khoa_hoc_id'    => $khoaHocId,
                        'module_hoc_id'  => $module->id,
                        'ngay_hoc'       => $currentDate->toDateString(),
                        'gio_bat_dau'    => $request->gio_bat_dau,
                        'gio_ket_thuc'   => $request->gio_ket_thuc,
                        'thu_trong_tuan' => $dbDay,
                        'buoi_so'        => $soBuoiDaCo + $createdCount + 1,
                        'phong_hoc'      => $request->phong_hoc,
                        'hinh_thuc'      => $request->hinh_thuc,
                        'trang_thai'     => 'cho',
                    ]);
                    $createdCount++;
                }
                $currentDate->addDay();
            }

            DB::commit();
            return back()->with('success', "Đã tự động sinh {$createdCount} buổi học mới cho module.");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    public function edit(int $khoaHocId, int $id)
    {
        $lichHoc = LichHoc::with('moduleHoc', 'khoaHoc')->findOrFail($id);
        $giangViens = GiangVien::with('nguoiDung')->get();
        return view('pages.admin.lich-hoc.edit', compact('lichHoc', 'giangViens'));
    }

    public function update(Request $request, int $khoaHocId, int $id)
    {
        $request->validate([
            'ngay_hoc'     => 'required|date',
            'gio_bat_dau'  => 'required',
            'gio_ket_thuc' => 'required|after:gio_bat_dau',
            'trang_thai'   => 'required|in:cho,dang_hoc,hoan_thanh,huy',
        ]);

        $lichHoc = LichHoc::findOrFail($id);
        $lichHoc->update($request->all());

        return redirect()->route('admin.khoa-hoc.lich-hoc.index', $khoaHocId)
                         ->with('success', 'Đã cập nhật lịch học.');
    }

    public function destroy(int $khoaHocId, int $id)
    {
        LichHoc::destroy($id);
        return back()->with('success', 'Đã xóa buổi học.');
    }

    /**
     * Xóa tất cả lịch học của 1 module (chỉ xóa trạng thái 'cho')
     */
    public function destroyModuleSchedules(Request $request, int $khoaHocId, int $moduleId)
    {
        $deleted = LichHoc::where('khoa_hoc_id', $khoaHocId)
            ->where('module_hoc_id', $moduleId)
            ->where('trang_thai', 'cho')
            ->delete();

        return back()->with('success', "Đã xóa {$deleted} buổi học của module.");
    }

    /**
     * Xóa các buổi học được chọn (Bulk delete)
     */
    public function destroyBulk(Request $request, int $khoaHocId)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một buổi học để xóa.');
        }

        $deleted = LichHoc::whereIn('id', $ids)
            ->where('khoa_hoc_id', $khoaHocId)
            ->where('trang_thai', 'cho')
            ->delete();

        return back()->with('success', "Đã xóa {$deleted} buổi học đã chọn.");
    }
}
