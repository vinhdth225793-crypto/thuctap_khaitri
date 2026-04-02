<?php

namespace App\Services;

use App\Models\BaiGiang;
use App\Models\BaiKiemTra;
use App\Models\HocVienKhoaHoc;
use App\Models\KetQuaHocTap;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use App\Models\NguoiDung;
use App\Models\PhongHocLiveBanGhi;
use Illuminate\Support\Collection;

class StudentScheduleViewService
{
    /**
     * @return array<string, mixed>|null
     */
    public function buildCourseDetail(NguoiDung $user, int $courseId): ?array
    {
        $ghiDanh = $this->findAccessibleEnrollment($user->ma_nguoi_dung, $courseId);
        if (!$ghiDanh) {
            return null;
        }

        $khoaHoc = KhoaHoc::with([
            'nhomNganh',
            'moduleHocs' => function ($query) use ($courseId) {
                $query->orderBy('thu_tu_module')
                    ->with([
                        'lichHocs' => function ($lichHocQuery) use ($courseId) {
                            $lichHocQuery->where('khoa_hoc_id', $courseId)
                                ->with([
                                    'giangVien.nguoiDung',
                                    'taiNguyen' => fn ($taiNguyenQuery) => $taiNguyenQuery
                                        ->hienThiChoHocVien()
                                        ->orderBy('thu_tu_hien_thi')
                                        ->orderBy('created_at'),
                                    'baiGiangs' => fn ($bgQuery) => $bgQuery
                                        ->hienThiChoHocVien()
                                        ->with(['taiNguyenChinh', 'taiNguyenPhu', 'phongHocLive'])
                                        ->orderBy('thu_tu_hien_thi')
                                        ->orderBy('created_at'),
                                ])
                                ->orderBy('ngay_hoc')
                                ->orderBy('gio_bat_dau');
                        },
                    ]);
            },
        ])->findOrFail($courseId);

        $publishedExams = $this->queryPublishedExamsForStudent($user->ma_nguoi_dung)
            ->where('khoa_hoc_id', $courseId)
            ->get()
            ->pipe(fn (Collection $items) => $this->sortPublishedExams($items));

        $courseSchedules = $khoaHoc->moduleHocs
            ->flatMap(fn ($module) => $module->lichHocs)
            ->sortBy(fn (LichHoc $lichHoc) => sprintf(
                '%s-%s',
                $lichHoc->ngay_hoc?->format('Ymd') ?? '99999999',
                substr((string) $lichHoc->gio_bat_dau, 0, 5) ?: '99:99'
            ))
            ->values();

        $publishedLectures = $courseSchedules
            ->flatMap(fn (LichHoc $lichHoc) => $lichHoc->baiGiangs)
            ->sortBy([
                fn (BaiGiang $baiGiang) => optional($baiGiang->lichHoc?->ngay_hoc)->format('Ymd') ?? '99999999',
                fn (BaiGiang $baiGiang) => $baiGiang->thu_tu_hien_thi ?? 0,
            ])
            ->values();

        $publishedResources = $courseSchedules
            ->flatMap(fn (LichHoc $lichHoc) => $lichHoc->taiNguyen)
            ->sortBy([
                fn ($taiNguyen) => optional($taiNguyen->lichHoc?->ngay_hoc)->format('Ymd') ?? '99999999',
                fn ($taiNguyen) => $taiNguyen->thu_tu_hien_thi ?? 0,
            ])
            ->values();

        $ketQuaHocTap = KetQuaHocTap::query()
            ->where('hoc_vien_id', $user->ma_nguoi_dung)
            ->where('khoa_hoc_id', $courseId)
            ->first();

        $stats = [
            'tong_module' => $khoaHoc->moduleHocs->count(),
            'module_hoan_thanh' => $khoaHoc->so_module_hoan_thanh,
            'module_co_lich' => $khoaHoc->moduleHocs->filter(fn ($module) => $module->lichHocs->isNotEmpty())->count(),
            'tong_buoi_hoc' => $courseSchedules->count(),
            'buoi_hoan_thanh' => $courseSchedules->where('is_ended', true)->count(),
            'buoi_online' => $courseSchedules->where('hinh_thuc', 'online')->count(),
            'tai_nguyen_cong_khai' => $publishedResources->count(),
            'bai_giang_cong_khai' => $publishedLectures->count(),
            'bai_kiem_tra_cong_khai' => $publishedExams->count(),
        ];

        $buoiSapToi = $courseSchedules
            ->filter(fn (LichHoc $lichHoc) => $lichHoc->ends_at?->greaterThanOrEqualTo(now()))
            ->sortBy(fn (LichHoc $lichHoc) => $lichHoc->starts_at?->getTimestamp() ?? PHP_INT_MAX)
            ->first();

        return [
            'ghiDanh' => $ghiDanh,
            'khoaHoc' => $khoaHoc,
            'stats' => $stats,
            'courseSchedules' => $courseSchedules,
            'publishedLectures' => $publishedLectures,
            'publishedResources' => $publishedResources,
            'publishedExams' => $publishedExams,
            'ketQuaHocTap' => $ketQuaHocTap,
            'buoiSapToi' => $buoiSapToi,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buildSessionDetail(NguoiDung $user, int $scheduleId): ?array
    {
        $lichHoc = LichHoc::with([
            'khoaHoc.nhomNganh',
            'moduleHoc',
            'giangVien.nguoiDung',
            'taiNguyen' => fn ($query) => $query
                ->hienThiChoHocVien()
                ->orderBy('thu_tu_hien_thi')
                ->orderBy('created_at'),
            'baiGiangs' => fn ($query) => $query
                ->hienThiChoHocVien()
                ->with([
                    'taiNguyenChinh',
                    'taiNguyenPhu',
                    'phongHocLive.banGhis',
                ])
                ->orderBy('thu_tu_hien_thi')
                ->orderBy('created_at'),
        ])->findOrFail($scheduleId);

        $ghiDanh = $this->findAccessibleEnrollment($user->ma_nguoi_dung, (int) $lichHoc->khoa_hoc_id);
        if (!$ghiDanh) {
            return null;
        }

        $relatedExams = $this->queryPublishedExamsForStudent($user->ma_nguoi_dung)
            ->where('khoa_hoc_id', $lichHoc->khoa_hoc_id)
            ->where('lich_hoc_id', $lichHoc->id)
            ->get()
            ->pipe(fn (Collection $items) => $this->sortPublishedExams($items));

        $courseSchedules = LichHoc::query()
            ->where('khoa_hoc_id', $lichHoc->khoa_hoc_id)
            ->where('trang_thai', '!=', 'huy')
            ->with('moduleHoc:id,ten_module')
            ->orderBy('ngay_hoc')
            ->orderBy('gio_bat_dau')
            ->get([
                'id',
                'khoa_hoc_id',
                'module_hoc_id',
                'ngay_hoc',
                'gio_bat_dau',
                'gio_ket_thuc',
                'buoi_so',
                'trang_thai',
            ]);

        $currentIndex = $courseSchedules->search(fn (LichHoc $item) => (int) $item->id === (int) $lichHoc->id);
        $previousSession = $currentIndex !== false && $currentIndex > 0
            ? $courseSchedules->get($currentIndex - 1)
            : null;
        $nextSession = $currentIndex !== false && $currentIndex < ($courseSchedules->count() - 1)
            ? $courseSchedules->get($currentIndex + 1)
            : null;

        $relatedLectures = $lichHoc->baiGiangs->values();
        $liveLecture = $relatedLectures->first(fn (BaiGiang $baiGiang) => $baiGiang->isLive() && $baiGiang->phongHocLive);
        $recordings = $relatedLectures
            ->filter(fn (BaiGiang $baiGiang) => $baiGiang->isLive() && $baiGiang->phongHocLive)
            ->flatMap(fn (BaiGiang $baiGiang) => $baiGiang->phongHocLive->banGhis)
            ->filter(fn (PhongHocLiveBanGhi $recording) => $recording->trang_thai !== false)
            ->values();

        return [
            'ghiDanh' => $ghiDanh,
            'lichHoc' => $lichHoc,
            'relatedLectures' => $relatedLectures,
            'relatedExams' => $relatedExams,
            'courseSchedules' => $courseSchedules,
            'previousSession' => $previousSession,
            'nextSession' => $nextSession,
            'liveLecture' => $liveLecture,
            'recordings' => $recordings,
        ];
    }

    private function findAccessibleEnrollment(int $hocVienId, int $courseId): ?HocVienKhoaHoc
    {
        return HocVienKhoaHoc::query()
            ->where('khoa_hoc_id', $courseId)
            ->where('hoc_vien_id', $hocVienId)
            ->whereIn('trang_thai', ['dang_hoc', 'hoan_thanh'])
            ->first();
    }

    private function queryPublishedExamsForStudent(int $hocVienId)
    {
        return BaiKiemTra::query()
            ->where('trang_thai', true)
            ->where('trang_thai_duyet', 'da_duyet')
            ->where('trang_thai_phat_hanh', 'phat_hanh')
            ->with([
                'khoaHoc:id,ten_khoa_hoc,ma_khoa_hoc',
                'moduleHoc:id,ten_module,ma_module',
                'lichHoc:id,khoa_hoc_id,module_hoc_id,buoi_so,ngay_hoc',
                'chiTietCauHois',
                'baiLams' => fn ($query) => $query
                    ->where('hoc_vien_id', $hocVienId)
                    ->orderByDesc('lan_lam_thu'),
            ]);
    }

    /**
     * @param  Collection<int, BaiKiemTra>  $items
     * @return Collection<int, BaiKiemTra>
     */
    private function sortPublishedExams(Collection $items): Collection
    {
        return $items
            ->sortBy(function (BaiKiemTra $baiKiemTra) {
                $priority = match ($baiKiemTra->access_status_key) {
                    'dang_mo' => 1,
                    'sap_mo' => 2,
                    'da_dong' => 3,
                    default => 4,
                };

                $timestamp = $baiKiemTra->ngay_mo?->timestamp ?? 0;

                return sprintf('%s-%s', $priority, str_pad((string) $timestamp, 12, '0', STR_PAD_LEFT));
            })
            ->values();
    }
}
