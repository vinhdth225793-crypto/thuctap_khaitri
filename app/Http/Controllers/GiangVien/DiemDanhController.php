<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\DiemDanh;
use App\Models\HocVienKhoaHoc;
use App\Models\LichHoc;
use App\Models\PhanCongModuleGiangVien;
use App\Services\KetQuaHocTapService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DiemDanhController extends Controller
{
    public function __construct(
        private readonly KetQuaHocTapService $ketQuaHocTapService,
    ) {
    }

    public function redirectToSession(Request $request)
    {
        $lichHoc = LichHoc::findOrFail((int) $request->query('lich_hoc_id'));

        $this->authorizeGiangVienForLichHoc($lichHoc);

        return redirect()->to(
            route('giang-vien.khoa-hoc.show', [
                'id' => $lichHoc->khoa_hoc_id,
                'focus_lich_hoc_id' => $lichHoc->id,
                'quick_action' => 'attendance',
            ]) . '#session-' . $lichHoc->id
        );
    }

    /**
     * Lay danh sach hoc vien de hien thi trong modal diem danh.
     */
    public function show(Request $request, $lichHocId)
    {
        $lichHoc = LichHoc::findOrFail($lichHocId);

        if ($response = $this->authorizeGiangVienForLichHoc($lichHoc, true)) {
            return $response;
        }

        // Lấy danh sách học viên trong khóa học (bao gồm cả những người đã hoàn thành)
        $hocViens = HocVienKhoaHoc::with(['hocVien.nguoiDung'])
            ->where('khoa_hoc_id', $lichHoc->khoa_hoc_id)
            ->whereIn('trang_thai', ['dang_hoc', 'hoan_thanh'])
            ->get()
            ->sortBy(fn($item) => $item->hocVien?->nguoiDung?->ho_ten ?? '');

        // Lấy dữ liệu điểm danh đã có của buổi học này
        $diemDanhs = DiemDanh::where('lich_hoc_id', $lichHocId)
            ->get()
            ->keyBy('hoc_vien_id');

        // Ánh xạ dữ liệu trả về cho modal
        $data = $hocViens->map(function ($item) use ($diemDanhs) {
            $existing = $diemDanhs->get($item->hoc_vien_id);
            $user = $item->hocVien?->nguoiDung;

            return [
                'id' => $item->hoc_vien_id,
                'ma_nguoi_dung' => $item->hoc_vien_id,
                'ho_ten' => $user ? $user->ho_ten : 'N/A (Học viên không tồn tại)',
                'trang_thai' => $existing ? $existing->trang_thai : null,
                'ghi_chu' => $existing ? $existing->ghi_chu : '',
            ];
        })->values();

        $summary = [
            'total_students' => $hocViens->count(),
            'marked_students' => $diemDanhs->count(),
            'co_mat' => $diemDanhs->where('trang_thai', 'co_mat')->count(),
            'vang_mat' => $diemDanhs->where('trang_thai', 'vang_mat')->count(),
            'vao_tre' => $diemDanhs->where('trang_thai', 'vao_tre')->count(),
            'co_phep' => $diemDanhs->where('trang_thai', 'co_phep')->count(),
            'is_finalized' => in_array($lichHoc->trang_thai_bao_cao, ['da_bao_cao', 'da_bao_cao_muon'], true),
        ];

        return response()->json([
            'success' => true,
            'ngay' => $lichHoc->ngay_hoc->format('d/m/Y'),
            'bao_cao' => $lichHoc->bao_cao_giang_vien,
            'trang_thai_bao_cao' => $lichHoc->trang_thai_bao_cao,
            'summary' => $summary,
            'data' => $data,
        ]);
    }

    /**
     * Luu hoac cap nhat du lieu diem danh.
     */
    public function store(Request $request, $lichHocId)
    {
        $lichHoc = LichHoc::findOrFail($lichHocId);
        $this->authorizeGiangVienForLichHoc($lichHoc);

        $hocVienIds = HocVienKhoaHoc::query()
            ->where('khoa_hoc_id', $lichHoc->khoa_hoc_id)
            ->whereIn('trang_thai', ['dang_hoc', 'hoan_thanh'])
            ->pluck('hoc_vien_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($hocVienIds === []) {
            return back()->with('info', 'Khóa học này hiện không có học viên đang học để điểm danh.');
        }

        $attendanceData = collect($request->attendance)
            ->filter(fn ($item) => !empty($item['trang_thai']))
            ->values()
            ->all();

        if (empty($attendanceData)) {
            return back()->with('info', 'Bạn chưa chọn trạng thái điểm danh cho học viên nào.');
        }

        // Thay thế input attendance bằng dữ liệu đã lọc để pass qua validate
        $request->merge(['attendance' => $attendanceData]);

        $request->validate([
            'attendance' => 'required|array',
            'attendance.*.hoc_vien_id' => ['required', 'integer', Rule::in($hocVienIds)],
            'attendance.*.trang_thai' => 'required|in:co_mat,vang_mat,vao_tre,co_phep',
            'attendance.*.ghi_chu' => 'nullable|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($request, $lichHoc, $lichHocId): void {
                $updatedHocVienIds = [];

                foreach ($request->attendance as $item) {
                    DiemDanh::updateOrCreate(
                        [
                            'lich_hoc_id' => $lichHocId,
                            'hoc_vien_id' => $item['hoc_vien_id'],
                        ],
                        [
                            'trang_thai' => $item['trang_thai'],
                            'ghi_chu' => $item['ghi_chu'] ?? null,
                        ]
                    );

                    $updatedHocVienIds[] = (int) $item['hoc_vien_id'];
                }

                foreach (array_values(array_unique($updatedHocVienIds)) as $hocVienId) {
                    $this->ketQuaHocTapService->refreshAllForCourseStudent((int) $lichHoc->khoa_hoc_id, $hocVienId);
                }
            });

            return back()->with('success', 'Đã lưu dữ liệu điểm danh học viên thành công.');
        } catch (\Throwable $exception) {
            report($exception);
            
            $errorMessage = 'Không thể lưu điểm danh lúc này.';
            if (config('app.debug')) {
                $errorMessage .= ' Lỗi: ' . $exception->getMessage();
            }

            return back()->with('error', $errorMessage . ' Dữ liệu chưa được ghi nhận, vui lòng thử lại.');
        }
    }

    /**
     * Gui bao cao diem danh cho Admin.
     */
    public function report(Request $request, $lichHocId)
    {
        $lichHoc = LichHoc::findOrFail($lichHocId);
        $this->authorizeGiangVienForLichHoc($lichHoc);

        $request->validate([
            'bao_cao_giang_vien' => 'required|string|max:1000',
        ]);

        try {
            $now = now();
            $trangThai = 'da_bao_cao';
            
            // Phase 9: Kiểm tra nộp muộn
            if ($lichHoc->attendance_deadline_at && $now->gt($lichHoc->attendance_deadline_at)) {
                $trangThai = 'da_bao_cao_muon';
            }

            $lichHoc->update([
                'bao_cao_giang_vien' => $request->bao_cao_giang_vien,
                'thoi_gian_bao_cao' => $now,
                'trang_thai_bao_cao' => $trangThai,
            ]);

            $msg = $trangThai === 'da_bao_cao_muon' 
                ? 'Đã chốt điểm danh (Ghi nhận nộp muộn).' 
                : 'Đã chốt điểm danh và gửi báo cáo cho admin thành công.';

            return back()->with('success', $msg);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('error', 'Không thể gửi báo cáo điểm danh lúc này. Vui lòng thử lại.');
        }
    }

    private function authorizeGiangVienForLichHoc(LichHoc $lichHoc, bool $jsonResponse = false)
    {
        $giangVien = auth()->user()?->giangVien;

        $isAssigned = $giangVien && PhanCongModuleGiangVien::query()
            ->where('module_hoc_id', $lichHoc->module_hoc_id)
            ->where('giang_vien_id', $giangVien->id)
            ->where('trang_thai', 'da_nhan')
            ->exists();

        if ($isAssigned) {
            return null;
        }

        if ($jsonResponse) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không được phân công dạy buổi học này.',
            ], 403);
        }

        abort(403, 'Bạn không được phân công dạy buổi học này.');
    }
}
