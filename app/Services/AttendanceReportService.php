<?php

namespace App\Services;

use App\Models\DiemDanh;
use App\Models\GiangVien;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AttendanceReportService
{
    public function teacherAttendanceReport(array $filters = []): LengthAwarePaginator
    {
        return LichHoc::query()
            ->with([
                'khoaHoc',
                'moduleHoc.phanCongGiangViens.giangVien.nguoiDung',
                'giangVien.nguoiDung',
                'teacherAttendanceLogs.giangVien.nguoiDung',
            ])
            ->where('hinh_thuc', 'online')
            ->when(filled($filters['khoa_hoc_id'] ?? null), fn ($query) => $query->where('khoa_hoc_id', (int) $filters['khoa_hoc_id']))
            ->when(filled($filters['giang_vien_id'] ?? null), function ($query) use ($filters) {
                $teacherId = (int) $filters['giang_vien_id'];
                $query->where(function ($builder) use ($teacherId) {
                    $builder
                        ->where('giang_vien_id', $teacherId)
                        ->orWhereHas('teacherAttendanceLogs', fn ($attendanceQuery) => $attendanceQuery->where('giang_vien_id', $teacherId));
                });
            })
            ->when(filled($filters['ngay_hoc'] ?? null), fn ($query) => $query->whereDate('ngay_hoc', $filters['ngay_hoc']))
            ->when(filled($filters['trang_thai'] ?? null), function ($query) use ($filters) {
                $status = (string) $filters['trang_thai'];

                if ($status === 'chua_bat_dau') {
                    $query->whereDoesntHave('teacherAttendanceLogs');

                    return;
                }

                $query->whereHas('teacherAttendanceLogs', fn ($attendanceQuery) => $attendanceQuery->where('trang_thai', $status));
            })
            ->orderByDesc('ngay_hoc')
            ->orderByDesc('gio_bat_dau')
            ->paginate(12)
            ->withQueryString();
    }

    public function studentAttendanceReport(array $filters = []): LengthAwarePaginator
    {
        return DiemDanh::query()
            ->with([
                'hocVien',
                'lichHoc.khoaHoc',
                'lichHoc.giangVien.nguoiDung',
                'lichHoc.moduleHoc.phanCongGiangViens.giangVien.nguoiDung',
            ])
            ->when(filled($filters['khoa_hoc_id'] ?? null), fn ($query) => $query->whereHas('lichHoc', fn ($scheduleQuery) => $scheduleQuery->where('khoa_hoc_id', (int) $filters['khoa_hoc_id'])))
            ->when(filled($filters['lich_hoc_id'] ?? null), fn ($query) => $query->where('lich_hoc_id', (int) $filters['lich_hoc_id']))
            ->when(filled($filters['ngay_hoc'] ?? null), fn ($query) => $query->whereHas('lichHoc', fn ($scheduleQuery) => $scheduleQuery->whereDate('ngay_hoc', $filters['ngay_hoc'])))
            ->when(filled($filters['trang_thai'] ?? null), fn ($query) => $query->where('trang_thai', (string) $filters['trang_thai']))
            ->orderByDesc(
                LichHoc::query()
                    ->select('ngay_hoc')
                    ->whereColumn('lich_hoc.id', 'diem_danh.lich_hoc_id')
                    ->limit(1)
            )
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();
    }

    public function teacherAttendanceDetail(LichHoc $lichHoc, GiangVien $giangVien): LichHoc
    {
        return LichHoc::query()
            ->with([
                'khoaHoc',
                'moduleHoc.phanCongGiangViens.giangVien.nguoiDung',
                'giangVien.nguoiDung',
                'teacherAttendanceLogs' => fn ($query) => $query
                    ->where('giang_vien_id', $giangVien->id)
                    ->with('giangVien.nguoiDung'),
                'baiGiangs.phongHocLive.nguoiThamGia.nguoiDung',
            ])
            ->findOrFail($lichHoc->id);
    }

    public function filterOptions(): array
    {
        return [
            'courses' => KhoaHoc::query()
                ->orderBy('ten_khoa_hoc')
                ->get(['id', 'ten_khoa_hoc', 'ma_khoa_hoc']),
            'teachers' => GiangVien::query()
                ->with('nguoiDung')
                ->whereHas('nguoiDung')
                ->orderBy('id')
                ->get(),
        ];
    }
}
