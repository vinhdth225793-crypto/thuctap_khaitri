<?php

namespace App\Services;

use App\Models\BaiGiang;
use App\Models\BaiKiemTra;
use App\Models\DiemDanh;
use App\Models\HocVienKhoaHoc;
use App\Models\KetQuaHocTap;
use App\Models\LichHoc;
use App\Models\NguoiDung;
use App\Models\TaiNguyenBuoiHoc;
use App\Models\YeuCauHocVien;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class StudentLearningDashboardService
{
    public function __construct(
        private readonly ModuleFinalScoreService $moduleFinalScoreService
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function buildFor(NguoiDung $user): array
    {
        $user->loadMissing('hocVien');

        $ghiDanhKhoaHoc = HocVienKhoaHoc::query()
            ->where('hoc_vien_id', $user->ma_nguoi_dung)
            ->whereHas('khoaHoc')
            ->with([
                'khoaHoc' => fn ($query) => $query->with('nhomNganh'),
            ])
            ->orderByRaw("
                CASE trang_thai
                    WHEN 'dang_hoc' THEN 1
                    WHEN 'hoan_thanh' THEN 2
                    WHEN 'ngung_hoc' THEN 3
                    ELSE 4
                END
            ")
            ->orderByDesc('ngay_tham_gia')
            ->orderByDesc('created_at')
            ->get();

        $tatCaKhoaHocIds = $ghiDanhKhoaHoc->pluck('khoa_hoc_id')->filter()->values();
        $khoaHocDangHocIds = $ghiDanhKhoaHoc
            ->where('trang_thai', 'dang_hoc')
            ->pluck('khoa_hoc_id')
            ->filter()
            ->values();
        $khoaHocDangHocIdSet = $khoaHocDangHocIds
            ->map(fn ($id) => (int) $id)
            ->all();

        $lichHocTheoKhoaHoc = collect();
        if ($tatCaKhoaHocIds->isNotEmpty()) {
            $lichHocTheoKhoaHoc = LichHoc::query()
                ->with([
                    'khoaHoc:id,ten_khoa_hoc,ma_khoa_hoc',
                    'moduleHoc:id,ten_module,ma_module',
                    'baiGiangs' => function ($query) {
                        $query->where('loai_bai_giang', BaiGiang::TYPE_LIVE)
                            ->where('trang_thai_duyet', BaiGiang::STATUS_DUYET_DA_DUYET)
                            ->where('trang_thai_cong_bo', BaiGiang::CONG_BO_DA_CONG_BO)
                            ->with('phongHocLive');
                    },
                ])
                ->whereIn('khoa_hoc_id', $tatCaKhoaHocIds->all())
                ->where('trang_thai', '!=', 'huy')
                ->get([
                    'id',
                    'khoa_hoc_id',
                    'module_hoc_id',
                    'buoi_so',
                    'ngay_hoc',
                    'gio_bat_dau',
                    'gio_ket_thuc',
                    'hinh_thuc',
                    'link_online',
                    'trang_thai',
                ])
                ->groupBy('khoa_hoc_id');
        }

        $homNay = now()->startOfDay();
        $hienTai = now();
        $tatCaLichHoc = $lichHocTheoKhoaHoc->flatten(1)->values();
        $lichHocCuaKhoaDangHoc = $tatCaLichHoc
            ->filter(fn (LichHoc $lichHoc) => in_array((int) $lichHoc->khoa_hoc_id, $khoaHocDangHocIdSet, true))
            ->values();
        $cacBuoiSapToi = $lichHocCuaKhoaDangHoc
            ->filter(fn (LichHoc $lichHoc) => $this->shouldShowInUpcomingList($lichHoc, $hienTai))
            ->sortBy(fn (LichHoc $lichHoc) => $lichHoc->starts_at?->getTimestamp() ?? PHP_INT_MAX)
            ->values();

        $taiLieuCongKhaiQuery = TaiNguyenBuoiHoc::query()
            ->hienThiChoHocVien()
            ->whereHas('lichHoc', function ($query) use ($tatCaKhoaHocIds) {
                $query->whereIn('khoa_hoc_id', $tatCaKhoaHocIds->all());
            });

        $diemDanhTheoKhoaHoc = collect();
        $diemDanhGanDay = collect();

        if ($tatCaKhoaHocIds->isNotEmpty()) {
            $diemDanhGanDay = DiemDanh::query()
                ->where('hoc_vien_id', $user->ma_nguoi_dung)
                ->whereHas('lichHoc', function ($query) use ($tatCaKhoaHocIds) {
                    $query->whereIn('khoa_hoc_id', $tatCaKhoaHocIds->all());
                })
                ->with([
                    'lichHoc:id,khoa_hoc_id,module_hoc_id,ngay_hoc,gio_bat_dau,trang_thai',
                    'lichHoc.khoaHoc:id,ten_khoa_hoc,ma_khoa_hoc',
                    'lichHoc.moduleHoc:id,ten_module,ma_module',
                ])
                ->orderByDesc('updated_at')
                ->orderByDesc('created_at')
                ->get();

            $diemDanhTheoKhoaHoc = $diemDanhGanDay->groupBy(fn ($item) => optional($item->lichHoc)->khoa_hoc_id);
        }

        $ketQuaHocTapTheoKhoaHoc = KetQuaHocTap::query()
            ->where('hoc_vien_id', $user->ma_nguoi_dung)
            ->whereIn('khoa_hoc_id', $tatCaKhoaHocIds->all())
            ->get()
            ->groupBy('khoa_hoc_id');

        $baiKiemTraCanChuY = collect();
        if ($tatCaKhoaHocIds->isNotEmpty()) {
            $baiKiemTraCanChuY = BaiKiemTra::query()
                ->where('trang_thai', true)
                ->where('trang_thai_duyet', 'da_duyet')
                ->where('trang_thai_phat_hanh', 'phat_hanh')
                ->whereIn('khoa_hoc_id', $tatCaKhoaHocIds->all())
                ->with([
                    'khoaHoc:id,ten_khoa_hoc,ma_khoa_hoc',
                    'moduleHoc:id,ten_module,ma_module',
                    'lichHoc:id,khoa_hoc_id,module_hoc_id,buoi_so,ngay_hoc',
                    'baiLams' => fn ($query) => $query
                        ->where('hoc_vien_id', $user->ma_nguoi_dung)
                        ->orderByDesc('lan_lam_thu'),
                ])
                ->get()
                ->filter(fn (BaiKiemTra $baiKiemTra) => in_array($baiKiemTra->access_status_key, ['dang_mo', 'sap_mo'], true))
                ->sortBy(function (BaiKiemTra $baiKiemTra) {
                    $priority = match ($baiKiemTra->access_status_key) {
                        'dang_mo' => 1,
                        'sap_mo' => 2,
                        default => 3,
                    };

                    $timestamp = $baiKiemTra->ngay_mo?->timestamp ?? 0;

                    return sprintf('%s-%s', $priority, str_pad((string) $timestamp, 12, '0', STR_PAD_LEFT));
                })
                ->values();
        }

        $soCoMat = $diemDanhGanDay->where('trang_thai', 'co_mat')->count();
        $soVaoTre = $diemDanhGanDay->where('trang_thai', 'vao_tre')->count();
        $soVangMat = $diemDanhGanDay->where('trang_thai', 'vang_mat')->count();
        $tongLanDiemDanh = $diemDanhGanDay->count();
        $tyLeChuyenCan = $tongLanDiemDanh > 0
            ? (int) round((($soCoMat + $soVaoTre) / $tongLanDiemDanh) * 100)
            : 0;

        $yeuCauThamGiaDangChoDuyet = YeuCauHocVien::query()
            ->where('hoc_vien_id', $user->ma_nguoi_dung)
            ->where('loai_yeu_cau', 'them')
            ->where('trang_thai', 'cho_duyet')
            ->count();

        $tongBuoiHoc = $tatCaLichHoc->count();
        $tongBuoiHoanThanh = $tatCaLichHoc
            ->filter(fn (LichHoc $lichHoc) => $lichHoc->is_ended)
            ->count();
        $tongBuoiDangHoc = $lichHocCuaKhoaDangHoc->count();
        $tongBuoiHoanThanhDangHoc = $lichHocCuaKhoaDangHoc
            ->filter(fn (LichHoc $lichHoc) => $lichHoc->is_ended)
            ->count();
        $buoiHocHienTaiIds = $lichHocCuaKhoaDangHoc
            ->reject(fn (LichHoc $lichHoc) => $lichHoc->is_ended)
            ->groupBy('khoa_hoc_id')
            ->map(fn (Collection $cacBuoiHoc) => $cacBuoiHoc
                ->sortBy(fn (LichHoc $lichHoc) => $lichHoc->starts_at?->getTimestamp() ?? PHP_INT_MAX)
                ->first())
            ->filter()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();
        $tongBuoiDangHocHienTai = $buoiHocHienTaiIds->count();
        $tienDoTongQuan = $tongBuoiHoc > 0
            ? (int) round(($tongBuoiHoanThanh / $tongBuoiHoc) * 100)
            : 0;

        $dashboardStats = [
            'khoa_hoc_dang_hoc' => $ghiDanhKhoaHoc->where('trang_thai', 'dang_hoc')->count(),
            'buoi_hoc_hom_nay' => $lichHocCuaKhoaDangHoc
                ->filter(fn (LichHoc $lichHoc) => $lichHoc->ngay_hoc && $lichHoc->ngay_hoc->isSameDay($homNay))
                ->count(),
            'buoi_hoc_sap_toi' => $cacBuoiSapToi->count(),
            'tai_lieu_cong_khai' => (clone $taiLieuCongKhaiQuery)->count(),
            'tai_lieu_moi_7_ngay' => (clone $taiLieuCongKhaiQuery)->where('created_at', '>=', now()->subDays(7))->count(),
            'buoi_co_tai_lieu' => (clone $taiLieuCongKhaiQuery)->distinct('lich_hoc_id')->count('lich_hoc_id'),
            'bai_kiem_tra_dang_mo' => $baiKiemTraCanChuY->where('access_status_key', 'dang_mo')->count(),
            'bai_kiem_tra_sap_mo' => $baiKiemTraCanChuY->where('access_status_key', 'sap_mo')->count(),
            'tien_do_tong_quan' => $tienDoTongQuan,
            'tong_buoi_hoc' => $tongBuoiHoc,
            'tong_buoi_hoan_thanh' => $tongBuoiHoanThanh,
            'tong_buoi_dang_hoc' => $tongBuoiDangHoc,
            'tong_buoi_hoan_thanh_dang_hoc' => $tongBuoiHoanThanhDangHoc,
            'tong_buoi_dang_hoc_hien_tai' => $tongBuoiDangHocHienTai,
            'buoi_hoc_hien_tai_ids' => $buoiHocHienTaiIds->all(),
            'tong_buoi_con_lai_dang_hoc' => max($tongBuoiDangHoc - $tongBuoiHoanThanhDangHoc - $tongBuoiDangHocHienTai, 0),
            'yeu_cau_dang_cho_duyet' => $yeuCauThamGiaDangChoDuyet,
            'tong_lan_diem_danh' => $tongLanDiemDanh,
            'ty_le_chuyen_can' => $tyLeChuyenCan,
        ];

        $tienDoKhoaHoc = $ghiDanhKhoaHoc->map(function (HocVienKhoaHoc $ghiDanh) use ($lichHocTheoKhoaHoc, $diemDanhTheoKhoaHoc, $ketQuaHocTapTheoKhoaHoc, $hienTai) {
            $cacBuoiHoc = $lichHocTheoKhoaHoc->get($ghiDanh->khoa_hoc_id, collect());
            $tongBuoi = $cacBuoiHoc->count();
            $buoiHoanThanh = $cacBuoiHoc
                ->filter(fn (LichHoc $lichHoc) => $lichHoc->is_ended)
                ->count();
            $buoiOnline = $cacBuoiHoc->where('hinh_thuc', 'online')->count();
            $tienDo = $tongBuoi > 0
                ? (int) round(($buoiHoanThanh / $tongBuoi) * 100)
                : 0;

            $diemDanhTheoKhoa = $diemDanhTheoKhoaHoc->get($ghiDanh->khoa_hoc_id, collect());
            $coMat = $diemDanhTheoKhoa->where('trang_thai', 'co_mat')->count();
            $vaoTre = $diemDanhTheoKhoa->where('trang_thai', 'vao_tre')->count();
            $vangMat = $diemDanhTheoKhoa->where('trang_thai', 'vang_mat')->count();
            $tongDiemDanh = $diemDanhTheoKhoa->count();
            $tyLeThamDu = $tongDiemDanh > 0
                ? (int) round((($coMat + $vaoTre) / $tongDiemDanh) * 100)
                : null;

            $buoiSapToi = $cacBuoiHoc
                ->filter(fn (LichHoc $lichHoc) => $this->shouldShowInUpcomingList($lichHoc, $hienTai))
                ->sortBy(fn (LichHoc $lichHoc) => $lichHoc->starts_at?->getTimestamp() ?? PHP_INT_MAX)
                ->first();

            $allKq = $ketQuaHocTapTheoKhoaHoc->get($ghiDanh->khoa_hoc_id, collect());
            $ketQuaHocTap = $allKq->whereNull('module_hoc_id')->whereNull('bai_kiem_tra_id')->first();
            $moduleKq = $allKq->whereNotNull('module_hoc_id')->whereNull('bai_kiem_tra_id');
            $examKq = $allKq->whereNotNull('bai_kiem_tra_id');

            $moduleBreakdowns = $moduleKq->map(function ($kq) use ($ghiDanh) {
                return [
                    'module_id' => $kq->module_hoc_id,
                    'module_name' => optional($kq->moduleHoc)->ten_module,
                    'is_finalized' => (bool) $kq->da_chot,
                    'final_score' => $kq->da_chot ? (float) $kq->diem_giang_vien_chot : (float) $kq->diem_tong_ket,
                    'breakdown' => $this->moduleFinalScoreService->calculateForStudent((int) $kq->module_hoc_id, (int) $ghiDanh->hoc_vien_id),
                ];
            })->values();

            return [
                'ghi_danh' => $ghiDanh,
                'khoa_hoc' => $ghiDanh->khoaHoc,
                'tong_buoi' => $tongBuoi,
                'buoi_hoan_thanh' => $buoiHoanThanh,
                'buoi_online' => $buoiOnline,
                'tien_do' => $tienDo,
                'buoi_sap_toi' => $buoiSapToi,
                'co_mat' => $coMat,
                'vao_tre' => $vaoTre,
                'vang_mat' => $vangMat,
                'tong_diem_danh' => $tongDiemDanh,
                'ty_le_tham_du' => $tyLeThamDu,
                'ket_qua_hoc_tap' => $ketQuaHocTap,
                'ket_qua_module_count' => $moduleKq->count(),
                'ket_qua_module_hoan_thanh' => $moduleKq->where('trang_thai', 'hoan_thanh')->count(),
                'bai_thi_dat_count' => $examKq->where('trang_thai', 'dat')->count(),
                'module_breakdowns' => $moduleBreakdowns,
            ];
        });

        $buoiSapToi = $cacBuoiSapToi->take(5)->values();
        $dongThoiGianBuoiHoc = $lichHocCuaKhoaDangHoc
            ->sortBy(fn (LichHoc $lichHoc) => $lichHoc->starts_at?->getTimestamp() ?? PHP_INT_MAX)
            ->values();

        $taiLieuMoi = (clone $taiLieuCongKhaiQuery)
            ->with([
                'lichHoc.khoaHoc:id,ten_khoa_hoc,ma_khoa_hoc',
                'lichHoc.moduleHoc:id,ten_module,ma_module',
            ])
            ->latest()
            ->take(6)
            ->get();

        $buoiHoanThanhGanDay = $tatCaLichHoc
            ->filter(fn (LichHoc $lichHoc) => $lichHoc->is_ended)
            ->sortByDesc(fn (LichHoc $lichHoc) => $lichHoc->ends_at?->getTimestamp() ?? ($lichHoc->starts_at?->getTimestamp() ?? 0))
            ->take(6)
            ->values();

        $hoatDongGanDay = $this->buildHoatDongGanDay($diemDanhGanDay, $taiLieuMoi, $buoiHoanThanhGanDay);

        $chuyenCanTongQuan = [
            'co_mat' => $soCoMat,
            'vao_tre' => $soVaoTre,
            'vang_mat' => $soVangMat,
            'tong' => $tongLanDiemDanh,
            'ty_le_tham_du' => $tyLeChuyenCan,
        ];

        return [
            'dashboardStats' => $dashboardStats,
            'tienDoKhoaHoc' => $tienDoKhoaHoc,
            'buoiSapToi' => $buoiSapToi,
            'dongThoiGianBuoiHoc' => $dongThoiGianBuoiHoc,
            'taiLieuMoi' => $taiLieuMoi,
            'baiKiemTraCanChuY' => $baiKiemTraCanChuY->take(5)->values(),
            'diemDanhGanDay' => $diemDanhGanDay->take(6)->values(),
            'hoatDongGanDay' => $hoatDongGanDay,
            'chuyenCanTongQuan' => $chuyenCanTongQuan,
        ];
    }

    private function shouldShowInUpcomingList(LichHoc $lichHoc, CarbonInterface $referenceTime): bool
    {
        if ($lichHoc->trang_thai === 'huy') {
            return false;
        }

        if ($lichHoc->ends_at) {
            return $lichHoc->ends_at->greaterThanOrEqualTo($referenceTime);
        }

        return $lichHoc->ngay_hoc
            && $lichHoc->ngay_hoc->greaterThanOrEqualTo($referenceTime->copy()->startOfDay());
    }

    /**
     * @param  Collection<int, \App\Models\DiemDanh>  $diemDanhGanDay
     * @param  Collection<int, \App\Models\TaiNguyenBuoiHoc>  $taiLieuMoi
     * @param  Collection<int, \App\Models\LichHoc>  $buoiHoanThanhGanDay
     * @return Collection<int, array<string, mixed>>
     */
    private function buildHoatDongGanDay(Collection $diemDanhGanDay, Collection $taiLieuMoi, Collection $buoiHoanThanhGanDay): Collection
    {
        $hoatDongDiemDanh = $diemDanhGanDay->map(function ($diemDanh) {
            $trangThai = $diemDanh->trang_thai;

            return [
                'sort_at' => $diemDanh->updated_at ?? $diemDanh->created_at,
                'icon' => match ($trangThai) {
                    'co_mat' => 'fa-check-circle',
                    'vao_tre' => 'fa-clock',
                    'vang_mat' => 'fa-times-circle',
                    default => 'fa-circle',
                },
                'color' => match ($trangThai) {
                    'co_mat' => 'success',
                    'vao_tre' => 'warning',
                    'vang_mat' => 'danger',
                    default => 'secondary',
                },
                'title' => match ($trangThai) {
                    'co_mat' => 'Điểm danh: có mặt',
                    'vao_tre' => 'Điểm danh: vào trễ',
                    'vang_mat' => 'Điểm danh: vắng mặt',
                    default => 'Cập nhật điểm danh',
                },
                'description' => trim(collect([
                    optional(optional($diemDanh->lichHoc)->khoaHoc)->ten_khoa_hoc,
                    optional(optional($diemDanh->lichHoc)->moduleHoc)->ten_module,
                ])->filter()->implode(' • ')),
                'meta' => optional(optional($diemDanh->lichHoc)->ngay_hoc)->format('d/m/Y'),
            ];
        });

        $hoatDongTaiLieu = $taiLieuMoi->map(function ($taiLieu) {
            return [
                'sort_at' => $taiLieu->created_at,
                'icon' => 'fa-folder-open',
                'color' => 'info',
                'title' => 'Tài liệu mới được công khai',
                'description' => $taiLieu->tieu_de,
                'meta' => optional(optional($taiLieu->lichHoc)->khoaHoc)->ten_khoa_hoc,
            ];
        });

        $hoatDongBuoiHoc = $buoiHoanThanhGanDay->map(function ($lichHoc) {
            $sortAt = $lichHoc->ends_at
                ?? ($lichHoc->ngay_hoc
                    ? $lichHoc->ngay_hoc->copy()->setTimeFromTimeString($lichHoc->gio_bat_dau ?: '00:00:00')
                    : $lichHoc->updated_at);

            return [
                'sort_at' => $sortAt,
                'icon' => 'fa-calendar-check',
                'color' => 'primary',
                'title' => 'Hoàn thành buổi học',
                'description' => trim(collect([
                    optional($lichHoc->khoaHoc)->ten_khoa_hoc,
                    optional($lichHoc->moduleHoc)->ten_module,
                ])->filter()->implode(' • ')),
                'meta' => optional($lichHoc->ngay_hoc)->format('d/m/Y'),
            ];
        });

        return $hoatDongDiemDanh
            ->concat($hoatDongTaiLieu)
            ->concat($hoatDongBuoiHoc)
            ->sortByDesc('sort_at')
            ->take(10)
            ->values();
    }
}
