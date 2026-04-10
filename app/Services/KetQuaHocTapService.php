<?php

namespace App\Services;

use App\Models\BaiKiemTra;
use App\Models\BaiLamBaiKiemTra;
use App\Models\DiemDanh;
use App\Models\HocVienKhoaHoc;
use App\Models\KetQuaHocTap;
use App\Models\KhoaHoc;
use App\Models\ModuleHoc;

class KetQuaHocTapService
{
    /**
     * Làm mới toàn bộ kết quả học tập của học viên trong một khóa học (gồm các module và bài thi)
     */
    public function refreshAllForCourseStudent(int $khoaHocId, int $hocVienId): void
    {
        $khoaHoc = KhoaHoc::with('moduleHocs')->findOrFail($khoaHocId);

        // 1. Làm mới kết quả từng module
        foreach ($khoaHoc->moduleHocs as $module) {
            $this->refreshForModuleStudent($module->id, $hocVienId);
        }

        // 2. Làm mới kết quả tổng hợp khóa học
        $this->refreshForCourseStudent($khoaHocId, $hocVienId);
    }

    /**
     * Làm mới kết quả tổng hợp cấp Khóa học
     */
    public function refreshForCourseStudent(int $khoaHocId, int $hocVienId): KetQuaHocTap
    {
        $khoaHoc = KhoaHoc::findOrFail($khoaHocId);

        // Lấy điểm danh tổng quát của khóa
        $diemDanh = DiemDanh::query()
            ->where('hoc_vien_id', $hocVienId)
            ->whereHas('lichHoc', fn ($query) => $query->where('khoa_hoc_id', $khoaHocId))
            ->get();

        $tongSoBuoi = $diemDanh->count();
        $soBuoiThamDu = $diemDanh->whereIn('trang_thai', ['co_mat', 'vao_tre'])->count();
        $tyLeThamDu = $tongSoBuoi > 0 ? round(($soBuoiThamDu / $tongSoBuoi) * 100, 2) : null;
        $diemDiemDanh = $tyLeThamDu !== null ? round(($tyLeThamDu / 100) * 10, 2) : null;

        // Lấy kết quả từ các module hoặc bài thi cuối khóa tùy theo phương thức đánh giá
        $diemKiemTra = null;
        $soBaiHoanThanh = 0;
        $chiTiet = [];

        if ($khoaHoc->phuong_thuc_danh_gia === 'theo_module') {
            $kqModules = KetQuaHocTap::where('khoa_hoc_id', $khoaHocId)
                ->where('hoc_vien_id', $hocVienId)
                ->whereNotNull('module_hoc_id')
                ->whereNull('bai_kiem_tra_id')
                ->get();

            if ($kqModules->isNotEmpty()) {
                $diemKiemTra = round($kqModules->avg('diem_tong_ket'), 2);
                $soBaiHoanThanh = $kqModules->where('trang_thai', 'hoan_thanh')->count();
                foreach ($kqModules as $kqm) {
                    $chiTiet['modules'][] = [
                        'module_id' => $kqm->module_hoc_id,
                        'diem' => $kqm->diem_tong_ket,
                        'trang_thai' => $kqm->trang_thai
                    ];
                }
            }
        } else {
            // Theo bài kiểm tra cuối khóa
            $kqExams = KetQuaHocTap::where('khoa_hoc_id', $khoaHocId)
                ->where('hoc_vien_id', $hocVienId)
                ->whereNotNull('bai_kiem_tra_id')
                ->whereHas('baiKiemTra', fn($q) => $q->where('loai_bai_kiem_tra', 'cuoi_khoa'))
                ->get();
            
            if ($kqExams->isNotEmpty()) {
                $diemKiemTra = round($kqExams->max('diem_kiem_tra'), 2);
                $soBaiHoanThanh = $kqExams->count();
                foreach ($kqExams as $kqe) {
                    $chiTiet['exams'][] = [
                        'exam_id' => $kqe->bai_kiem_tra_id,
                        'diem' => $kqe->diem_kiem_tra,
                        'trang_thai' => $kqe->trang_thai
                    ];
                }
            }
        }

        $diemTongKet = null;
        if ($diemDiemDanh !== null && $diemKiemTra !== null) {
            $diemTongKet = round(
                ($diemDiemDanh * ((float) $khoaHoc->ty_trong_diem_danh / 100))
                + ($diemKiemTra * ((float) $khoaHoc->ty_trong_kiem_tra / 100)),
                2
            );
        }

        $trangThai = 'dang_hoc';
        if ($diemTongKet !== null) {
            $trangThai = $diemTongKet >= 5.0 ? 'dat' : 'khong_dat';
        }

        // Tự động chốt trạng thái hoàn thành khóa học cho học viên nếu ĐẠT
        if ($trangThai === 'dat') {
            HocVienKhoaHoc::where('khoa_hoc_id', $khoaHocId)
                ->where('hoc_vien_id', $hocVienId)
                ->where('trang_thai', 'dang_hoc')
                ->update(['trang_thai' => 'hoan_thanh']);
        }

        return KetQuaHocTap::updateOrCreate(
            [
                'khoa_hoc_id' => $khoaHocId,
                'hoc_vien_id' => $hocVienId,
                'module_hoc_id' => null,
                'bai_kiem_tra_id' => null,
            ],
            [
                'phuong_thuc_danh_gia' => $khoaHoc->phuong_thuc_danh_gia,
                'diem_diem_danh' => $diemDiemDanh,
                'diem_kiem_tra' => $diemKiemTra,
                'diem_tong_ket' => $diemTongKet,
                'tong_so_buoi' => $tongSoBuoi,
                'so_buoi_tham_du' => $soBuoiThamDu,
                'ty_le_tham_du' => $tyLeThamDu,
                'so_bai_kiem_tra_hoan_thanh' => $soBaiHoanThanh,
                'trang_thai' => $trangThai,
                'chi_tiet' => $chiTiet,
                'cap_nhat_luc' => now(),
            ]
        );
    }

    /**
     * Làm mới kết quả tổng hợp cấp Module
     */
    public function refreshForModuleStudent(int $moduleHocId, int $hocVienId): KetQuaHocTap
    {
        $module = ModuleHoc::with('khoaHoc')->findOrFail($moduleHocId);
        
        // 1. Refresh các bài thi thuộc module này trước
        $baiKiemTras = BaiKiemTra::where('module_hoc_id', $moduleHocId)
            ->where('trang_thai_duyet', 'da_duyet')
            ->where('trang_thai_phat_hanh', 'phat_hanh')
            ->get();

        foreach ($baiKiemTras as $bkt) {
            $this->refreshForExamStudent($bkt->id, $hocVienId);
        }

        // 2. Tổng hợp điểm từ các bài thi
        $kqExams = KetQuaHocTap::where('module_hoc_id', $moduleHocId)
            ->where('hoc_vien_id', $hocVienId)
            ->whereNotNull('bai_kiem_tra_id')
            ->get();

        $diemKiemTra = $kqExams->isNotEmpty() ? round($kqExams->avg('diem_kiem_tra'), 2) : null;
        
        // 3. Lấy điểm danh module
        $diemDanh = DiemDanh::query()
            ->where('hoc_vien_id', $hocVienId)
            ->whereHas('lichHoc', fn ($query) => $query->where('module_hoc_id', $moduleHocId))
            ->get();

        $tongSoBuoi = $diemDanh->count();
        $soBuoiThamDu = $diemDanh->whereIn('trang_thai', ['co_mat', 'vao_tre'])->count();
        $tyLeThamDu = $tongSoBuoi > 0 ? round(($soBuoiThamDu / $tongSoBuoi) * 100, 2) : null;
        $diemDiemDanh = $tyLeThamDu !== null ? round(($tyLeThamDu / 100) * 10, 2) : null;

        // 4. Tính điểm tổng kết module (tạm thời dùng tỷ trọng của khóa học)
        $diemTongKet = null;
        if ($diemDiemDanh !== null && $diemKiemTra !== null) {
            $diemTongKet = round(
                ($diemDiemDanh * ((float) $module->khoaHoc->ty_trong_diem_danh / 100))
                + ($diemKiemTra * ((float) $module->khoaHoc->ty_trong_kiem_tra / 100)),
                2
            );
        } elseif ($diemKiemTra !== null) {
            $diemTongKet = $diemKiemTra;
        }

        $trangThai = 'dang_hoc';
        if ($diemTongKet !== null) {
            $trangThai = $diemTongKet >= 5.0 ? 'hoan_thanh' : 'dang_hoc';
        }

        return KetQuaHocTap::updateOrCreate(
            [
                'khoa_hoc_id' => $module->khoa_hoc_id,
                'hoc_vien_id' => $hocVienId,
                'module_hoc_id' => $moduleHocId,
                'bai_kiem_tra_id' => null,
            ],
            [
                'diem_diem_danh' => $diemDiemDanh,
                'diem_kiem_tra' => $diemKiemTra,
                'diem_tong_ket' => $diemTongKet,
                'tong_so_buoi' => $tongSoBuoi,
                'so_buoi_tham_du' => $soBuoiThamDu,
                'ty_le_tham_du' => $tyLeThamDu,
                'so_bai_kiem_tra_hoan_thanh' => $kqExams->count(),
                'trang_thai' => $trangThai,
                'cap_nhat_luc' => now(),
            ]
        );
    }

    /**
     * Làm mới kết quả cho một bài kiểm tra cụ thể (lấy điểm cao nhất)
     */
    public function refreshForExamStudent(int $baiKiemTraId, int $hocVienId): ?KetQuaHocTap
    {
        $baiKiemTra = BaiKiemTra::findOrFail($baiKiemTraId);
        
        $bestAttempt = BaiLamBaiKiemTra::where('bai_kiem_tra_id', $baiKiemTraId)
            ->where('hoc_vien_id', $hocVienId)
            ->where('trang_thai_cham', 'da_cham')
            ->orderByDesc('diem_so')
            ->first();

        if (!$bestAttempt) {
            return null;
        }

        $trangThai = $bestAttempt->diem_so >= ($baiKiemTra->tong_diem * 0.5) ? 'dat' : 'khong_dat';

        return KetQuaHocTap::updateOrCreate(
            [
                'khoa_hoc_id' => $baiKiemTra->khoa_hoc_id,
                'hoc_vien_id' => $hocVienId,
                'module_hoc_id' => $baiKiemTra->module_hoc_id,
                'bai_kiem_tra_id' => $baiKiemTraId,
            ],
            [
                'diem_kiem_tra' => $bestAttempt->diem_so,
                'diem_tong_ket' => $bestAttempt->diem_so,
                'trang_thai' => $trangThai,
                'chi_tiet' => [
                    'bai_lam_id' => $bestAttempt->id,
                    'lan_lam_thu' => $bestAttempt->lan_lam_thu,
                    'nop_luc' => $bestAttempt->nop_luc,
                ],
                'cap_nhat_luc' => now(),
            ]
        );
    }
}
