<?php

namespace App\Services;

use App\Models\BaiKiemTra;
use Illuminate\Validation\ValidationException;

class ExamConfigurationService
{
    public const MIN_QUESTION_SCORE = 0.25;

    public function __construct(
        private readonly ExamScoringPackageService $scoringPackageService,
    ) {
    }

    /**
     * @param  array<string, mixed>  $validated
     * @param  array<int, int>  $questionIds
     * @return array<int, float>
     */
    public function resolveQuestionScores(array $validated, array $questionIds): array
    {
        if (($validated['che_do_tinh_diem'] ?? 'thu_cong') === 'goi_diem') {
            return $this->resolvePackageScores($validated, $questionIds);
        }

        return $this->resolveManualScores($validated, $questionIds);
    }

    public function ensureReadyForApproval(BaiKiemTra $baiKiemTra): void
    {
        $baiKiemTra->loadMissing(['chiTietCauHois.cauHoi']);

        $errors = [];

        if (blank($baiKiemTra->tieu_de)) {
            $errors['tieu_de'] = 'Vui lòng nhập tiêu đề bài kiểm tra trước khi gửi duyệt.';
        }

        if ((int) $baiKiemTra->thoi_gian_lam_bai < 1) {
            $errors['thoi_gian_lam_bai'] = 'Thời gian làm bài phải lớn hơn 0 phút.';
        }

        if ((int) $baiKiemTra->so_lan_duoc_lam < 1) {
            $errors['so_lan_duoc_lam'] = 'Số lần được làm phải từ 1 trở lên.';
        }

        if ($baiKiemTra->ngay_mo && $baiKiemTra->ngay_dong && $baiKiemTra->ngay_dong->lte($baiKiemTra->ngay_mo)) {
            $errors['ngay_dong'] = 'Ngày đóng đề phải sau ngày mở đề.';
        }

        if ($baiKiemTra->chiTietCauHois->isEmpty() && blank($baiKiemTra->mo_ta)) {
            $errors['mo_ta'] = 'Hãy thêm câu hỏi hoặc mô tả đề bài trước khi gửi duyệt.';
        }

        if ($baiKiemTra->chiTietCauHois->isNotEmpty()) {
            if ($baiKiemTra->chiTietCauHois->contains(fn ($detail) => $detail->cauHoi === null)) {
                $errors['question_ids'] = 'Đề đang chứa câu hỏi không còn tồn tại trong ngân hàng.';
            }

            if ($baiKiemTra->chiTietCauHois->contains(fn ($detail) => (float) $detail->diem_so < self::MIN_QUESTION_SCORE)) {
                $errors['question_scores'] = 'Mỗi câu hỏi phải có điểm số hợp lệ từ ' . number_format(self::MIN_QUESTION_SCORE, 2) . ' trở lên.';
            }
        }

        if ($baiKiemTra->che_do_tinh_diem === 'goi_diem') {
            if ($baiKiemTra->chiTietCauHois->isEmpty()) {
                $errors['question_ids'] = 'Chế độ gói điểm yêu cầu chọn đủ câu hỏi trước khi gửi duyệt.';
            }

            if ((int) $baiKiemTra->so_cau_goi_diem < 1) {
                $errors['so_cau_goi_diem'] = 'Vui lòng cấu hình số câu cho gói điểm.';
            }

            if ($baiKiemTra->chiTietCauHois->isNotEmpty() && (int) $baiKiemTra->so_cau_goi_diem !== $baiKiemTra->chiTietCauHois->count()) {
                $errors['question_ids'] = 'Số câu hỏi hiện tại chưa khớp với số câu trong gói điểm.';
            }
        }

        if ($baiKiemTra->chiTietCauHois->isNotEmpty()) {
            $tongDiemChiTiet = round($baiKiemTra->chiTietCauHois->sum(fn ($detail) => (float) $detail->diem_so), 2);
            $tongDiemDe = round((float) $baiKiemTra->tong_diem, 2);

            if ($tongDiemChiTiet <= 0) {
                $errors['tong_diem'] = 'Tổng điểm của đề phải lớn hơn 0.';
            } elseif (abs($tongDiemDe - $tongDiemChiTiet) > 0.009) {
                $errors['tong_diem'] = 'Tổng điểm bài kiểm tra chưa khớp với tổng điểm các câu hỏi.';
            }
        }

        if ($baiKiemTra->co_giam_sat) {
            if ((int) $baiKiemTra->so_lan_vi_pham_toi_da < 1) {
                $errors['so_lan_vi_pham_toi_da'] = 'Ngưỡng vi phạm tối đa phải lớn hơn hoặc bằng 1.';
            }

            if ($baiKiemTra->bat_buoc_camera && (int) $baiKiemTra->chu_ky_snapshot_giay < ExamSurveillanceService::MIN_SNAPSHOT_INTERVAL) {
                $errors['chu_ky_snapshot_giay'] = 'Chu kỳ snapshot phải từ ' . ExamSurveillanceService::MIN_SNAPSHOT_INTERVAL . ' giây trở lên.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * @param  array<string, mixed>  $validated
     * @param  array<int, int>  $questionIds
     * @return array<int, float>
     */
    private function resolvePackageScores(array $validated, array $questionIds): array
    {
        $soCau = (int) ($validated['so_cau_goi_diem'] ?? 0);
        $tongDiem = round((float) ($validated['tong_diem_goi_diem'] ?? 0), 2);

        if ($soCau < 1) {
            throw ValidationException::withMessages([
                'so_cau_goi_diem' => 'Vui lòng nhập số câu cho gói điểm.',
            ]);
        }

        if (count($questionIds) !== $soCau) {
            throw ValidationException::withMessages([
                'question_ids' => 'Số câu hỏi đã chọn (' . count($questionIds) . ') phải bằng số câu trong gói điểm (' . $soCau . ').',
            ]);
        }

        $tongDiemToiThieu = round($soCau * self::MIN_QUESTION_SCORE, 2);
        if ($tongDiem < $tongDiemToiThieu) {
            throw ValidationException::withMessages([
                'tong_diem_goi_diem' => 'Tổng điểm tối thiểu cho ' . $soCau . ' câu là ' . number_format($tongDiemToiThieu, 2) . '.',
            ]);
        }

        $points = $this->scoringPackageService->splitPoints($tongDiem, $soCau);

        if ($points === [] || collect($points)->contains(fn ($point) => (float) $point < self::MIN_QUESTION_SCORE)) {
            throw ValidationException::withMessages([
                'tong_diem_goi_diem' => 'Không thể chia điểm tự động hợp lệ với cấu hình hiện tại.',
            ]);
        }

        $scores = [];
        foreach ($questionIds as $index => $questionId) {
            $scores[$questionId] = round((float) ($points[$index] ?? 0), 2);
        }

        return $scores;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @param  array<int, int>  $questionIds
     * @return array<int, float>
     */
    private function resolveManualScores(array $validated, array $questionIds): array
    {
        if ($questionIds === []) {
            return [];
        }

        $rawScores = is_array($validated['question_scores'] ?? null)
            ? $validated['question_scores']
            : [];

        $scores = [];
        $errors = [];

        foreach ($questionIds as $index => $questionId) {
            $rawScore = $rawScores[$questionId] ?? $rawScores[$index] ?? null;

            if ($rawScore === null || $rawScore === '') {
                $errors['question_scores.' . $questionId] = 'Vui lòng nhập điểm cho từng câu hỏi đã chọn.';
                continue;
            }

            if (!is_numeric($rawScore) || (float) $rawScore < self::MIN_QUESTION_SCORE) {
                $errors['question_scores.' . $questionId] = 'Điểm mỗi câu phải từ ' . number_format(self::MIN_QUESTION_SCORE, 2) . ' trở lên.';
                continue;
            }

            $scores[$questionId] = round((float) $rawScore, 2);
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        if (round(array_sum($scores), 2) <= 0) {
            throw ValidationException::withMessages([
                'question_scores' => 'Tổng điểm của đề phải lớn hơn 0.',
            ]);
        }

        return $scores;
    }
}
