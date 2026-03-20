<?php

namespace App\Services;

use App\Models\BaiKiemTra;
use App\Models\DiemDanh;
use App\Models\KetQuaHocTap;
use App\Models\KhoaHoc;

class KetQuaHocTapService
{
    public function refreshForCourseStudent(int $khoaHocId, int $hocVienId): KetQuaHocTap
    {
        $khoaHoc = KhoaHoc::query()
            ->with([
                'moduleHocs:id,khoa_hoc_id,ten_module',
                'lichHocs:id,khoa_hoc_id,module_hoc_id',
                'baiKiemTras' => function ($query) {
                    $query->with([
                        'baiLams' => function ($attemptQuery) {
                            $attemptQuery->whereIn('trang_thai', ['da_nop', 'cho_cham', 'da_cham'])
                                ->orderByDesc('lan_lam_thu');
                        },
                    ]);
                },
            ])
            ->findOrFail($khoaHocId);

        $diemDanh = DiemDanh::query()
            ->where('hoc_vien_id', $hocVienId)
            ->whereHas('lichHoc', fn ($query) => $query->where('khoa_hoc_id', $khoaHocId))
            ->get();

        $tongSoBuoi = $diemDanh->count();
        $soBuoiThamDu = $diemDanh->whereIn('trang_thai', ['co_mat', 'vao_tre'])->count();
        $tyLeThamDu = $tongSoBuoi > 0 ? round(($soBuoiThamDu / $tongSoBuoi) * 100, 2) : null;
        $diemDiemDanh = $tyLeThamDu !== null ? round(($tyLeThamDu / 100) * 10, 2) : null;

        [$diemKiemTra, $soBaiKiemTraHoanThanh, $chiTietBaiKiemTra] = $this->resolveExamScore($khoaHoc, $hocVienId);

        $diemTongKet = null;
        if ($diemDiemDanh !== null && $diemKiemTra !== null) {
            $diemTongKet = round(
                ($diemDiemDanh * ((float) $khoaHoc->ty_trong_diem_danh / 100))
                + ($diemKiemTra * ((float) $khoaHoc->ty_trong_kiem_tra / 100)),
                2
            );
        }

        return KetQuaHocTap::updateOrCreate(
            [
                'khoa_hoc_id' => $khoaHoc->id,
                'hoc_vien_id' => $hocVienId,
            ],
            [
                'phuong_thuc_danh_gia' => $khoaHoc->phuong_thuc_danh_gia,
                'diem_diem_danh' => $diemDiemDanh,
                'diem_kiem_tra' => $diemKiemTra,
                'diem_tong_ket' => $diemTongKet,
                'tong_so_buoi' => $tongSoBuoi,
                'so_buoi_tham_du' => $soBuoiThamDu,
                'ty_le_tham_du' => $tyLeThamDu,
                'so_bai_kiem_tra_hoan_thanh' => $soBaiKiemTraHoanThanh,
                'chi_tiet' => [
                    'ty_trong_diem_danh' => (float) $khoaHoc->ty_trong_diem_danh,
                    'ty_trong_kiem_tra' => (float) $khoaHoc->ty_trong_kiem_tra,
                    'bai_kiem_tra' => $chiTietBaiKiemTra,
                ],
                'cap_nhat_luc' => now(),
            ]
        );
    }

    /**
     * @return array{0: float|null, 1: int, 2: array<int, array<string, mixed>>}
     */
    private function resolveExamScore(KhoaHoc $khoaHoc, int $hocVienId): array
    {
        $baiKiemTras = $khoaHoc->baiKiemTras
            ->filter(fn (BaiKiemTra $baiKiemTra) => $baiKiemTra->trang_thai_duyet === 'da_duyet' && $baiKiemTra->trang_thai_phat_hanh === 'phat_hanh');

        if ($khoaHoc->phuong_thuc_danh_gia === 'theo_module') {
            $theoModule = $baiKiemTras
                ->filter(fn (BaiKiemTra $baiKiemTra) => in_array($baiKiemTra->loai_bai_kiem_tra, ['module', 'buoi_hoc'], true))
                ->groupBy(fn (BaiKiemTra $baiKiemTra) => (string) ($baiKiemTra->module_hoc_id ?? 'khac'));

            $moduleScores = [];
            $chiTiet = [];

            foreach ($theoModule as $moduleId => $items) {
                $attempts = $items
                    ->flatMap(fn (BaiKiemTra $baiKiemTra) => $baiKiemTra->baiLams->where('hoc_vien_id', $hocVienId)->where('trang_thai_cham', 'da_cham'))
                    ->sortByDesc('diem_so')
                    ->values();

                if ($attempts->isEmpty()) {
                    continue;
                }

                $bestAttempt = $attempts->first();
                $moduleScores[] = (float) $bestAttempt->diem_so;
                $chiTiet[] = [
                    'module_hoc_id' => $moduleId !== 'khac' ? (int) $moduleId : null,
                    'bai_lam_id' => $bestAttempt->id,
                    'diem' => (float) $bestAttempt->diem_so,
                ];
            }

            if ($moduleScores === []) {
                return [null, 0, $chiTiet];
            }

            return [round(array_sum($moduleScores) / count($moduleScores), 2), count($moduleScores), $chiTiet];
        }

        $cuoiKhoaAttempts = $baiKiemTras
            ->filter(fn (BaiKiemTra $baiKiemTra) => $baiKiemTra->loai_bai_kiem_tra === 'cuoi_khoa')
            ->flatMap(fn (BaiKiemTra $baiKiemTra) => $baiKiemTra->baiLams->where('hoc_vien_id', $hocVienId)->where('trang_thai_cham', 'da_cham'))
            ->sortByDesc('diem_so')
            ->values();

        if ($cuoiKhoaAttempts->isEmpty()) {
            return [null, 0, []];
        }

        $bestAttempt = $cuoiKhoaAttempts->first();

        return [
            round((float) $bestAttempt->diem_so, 2),
            1,
            [[
                'bai_lam_id' => $bestAttempt->id,
                'diem' => (float) $bestAttempt->diem_so,
                'loai' => 'cuoi_khoa',
            ]],
        ];
    }
}
