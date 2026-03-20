<?php

namespace App\Services;

use App\Models\BaiLamBaiKiemTra;
use App\Models\ChiTietBaiLamBaiKiemTra;
use App\Models\GiangVien;

class BaiKiemTraScoringService
{
    public function autoGrade(BaiLamBaiKiemTra $baiLam): BaiLamBaiKiemTra
    {
        $baiLam->loadMissing([
            'baiKiemTra.chiTietCauHois.cauHoi.dapAns',
            'chiTietTraLois.chiTietBaiKiemTra.cauHoi.dapAns',
            'chiTietTraLois.dapAn',
        ]);

        $tongDiemTracNghiem = 0.0;
        $tongDiemTuLuan = 0.0;
        $conCauHoiTuLuanChuaCham = false;

        foreach ($baiLam->chiTietTraLois as $chiTietTraLoi) {
            $loaiCauHoi = $chiTietTraLoi->cauHoi?->loai_cau_hoi;
            $diemToiDa = (float) ($chiTietTraLoi->chiTietBaiKiemTra?->diem_so ?? $chiTietTraLoi->cauHoi?->diem_mac_dinh ?? 0);

            if ($loaiCauHoi === 'trac_nghiem') {
                $isDung = (bool) ($chiTietTraLoi->dapAn?->is_dap_an_dung ?? false);
                $diem = $isDung ? $diemToiDa : 0;

                $chiTietTraLoi->forceFill([
                    'is_dung' => $isDung,
                    'diem_tu_dong' => $diem,
                ])->save();

                $tongDiemTracNghiem += $diem;

                continue;
            }

            $tongDiemTuLuan += (float) ($chiTietTraLoi->diem_tu_luan ?? 0);
            $conCauHoiTuLuanChuaCham = $conCauHoiTuLuanChuaCham || $chiTietTraLoi->diem_tu_luan === null;
        }

        $trangThaiCham = 'da_cham';
        if ($baiLam->chiTietTraLois->isNotEmpty() && $conCauHoiTuLuanChuaCham) {
            $trangThaiCham = 'cho_cham';
        } elseif ($baiLam->chiTietTraLois->isEmpty()) {
            $trangThaiCham = 'chua_cham';
        }

        $baiLam->forceFill([
            'tong_diem_trac_nghiem' => $tongDiemTracNghiem,
            'tong_diem_tu_luan' => $tongDiemTuLuan > 0 || $this->hasEssayAnswers($baiLam) ? $tongDiemTuLuan : null,
            'diem_so' => $tongDiemTracNghiem + $tongDiemTuLuan,
            'trang_thai_cham' => $trangThaiCham,
            'auto_graded_at' => now(),
            'manual_graded_at' => $trangThaiCham === 'da_cham' && $this->hasEssayAnswers($baiLam) ? now() : $baiLam->manual_graded_at,
            'trang_thai' => $trangThaiCham === 'da_cham' ? 'da_cham' : $baiLam->trang_thai,
        ])->save();

        return $baiLam->fresh(['chiTietTraLois.cauHoi', 'chiTietTraLois.dapAn', 'baiKiemTra']);
    }

    /**
     * @param  array<int, array{diem_tu_luan?: mixed, nhan_xet?: mixed}>  $gradesByDetailId
     */
    public function applyManualGrades(BaiLamBaiKiemTra $baiLam, array $gradesByDetailId, ?GiangVien $giangVien = null): BaiLamBaiKiemTra
    {
        $baiLam->loadMissing(['chiTietTraLois.chiTietBaiKiemTra']);

        foreach ($baiLam->chiTietTraLois as $chiTietTraLoi) {
            $grade = $gradesByDetailId[$chiTietTraLoi->id] ?? null;

            if ($grade === null) {
                continue;
            }

            $diemToiDa = (float) ($chiTietTraLoi->chiTietBaiKiemTra?->diem_so ?? 0);
            $diemTuLuan = $grade['diem_tu_luan'] ?? null;
            $diemTuLuan = $diemTuLuan === '' || $diemTuLuan === null ? null : max(0, min($diemToiDa, (float) $diemTuLuan));

            $chiTietTraLoi->forceFill([
                'diem_tu_luan' => $diemTuLuan,
                'nhan_xet' => $grade['nhan_xet'] ?? $chiTietTraLoi->nhan_xet,
            ])->save();
        }

        $this->autoGrade($baiLam->fresh());

        $baiLam->forceFill([
            'nguoi_cham_id' => $giangVien?->nguoi_dung_id,
            'manual_graded_at' => now(),
        ])->save();

        return $this->autoGrade($baiLam->fresh());
    }

    private function hasEssayAnswers(BaiLamBaiKiemTra $baiLam): bool
    {
        return $baiLam->relationLoaded('chiTietTraLois')
            ? $baiLam->chiTietTraLois->contains(fn (ChiTietBaiLamBaiKiemTra $chiTiet) => $chiTiet->cauHoi?->loai_cau_hoi === 'tu_luan')
            : $baiLam->chiTietTraLois()->whereHas('cauHoi', fn ($query) => $query->where('loai_cau_hoi', 'tu_luan'))->exists();
    }
}
