<?php

namespace App\Services\Scheduling;

use App\Models\GiangVien;
use App\Models\GiangVienDonXinNghi;
use App\Models\LichHoc;
use App\Models\NguoiDung;
use App\Support\Scheduling\TeachingPeriodCatalog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class TeacherLeaveRequestService
{
    public function __construct(
        private readonly TeacherScheduleRuleService $ruleService,
    ) {
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function createForTeacher(GiangVien $teacher, array $payload): GiangVienDonXinNghi
    {
        $schedule = null;
        if (!empty($payload['lich_hoc_id'])) {
            $schedule = LichHoc::query()
                ->where('giang_vien_id', $teacher->id)
                ->findOrFail((int) $payload['lich_hoc_id']);
        }

        $attributes = $this->normalizePayload($teacher, $payload, $schedule);
        $duplicate = $this->findOverlappingRequests(
            $teacher->id,
            $attributes['ngay_xin_nghi'],
            $attributes['tiet_bat_dau'],
            $attributes['tiet_ket_thuc'],
        )->first();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'ngay_xin_nghi' => 'Da ton tai mot don xin nghi dang xu ly hoac da duyet trung khung nay.',
            ]);
        }

        return GiangVienDonXinNghi::create($attributes);
    }

    public function approve(GiangVienDonXinNghi $leaveRequest, NguoiDung $reviewer, ?string $feedback = null): GiangVienDonXinNghi
    {
        $leaveRequest->update([
            'trang_thai' => GiangVienDonXinNghi::TRANG_THAI_DA_DUYET,
            'ghi_chu_phan_hoi' => $feedback,
            'nguoi_duyet_id' => $reviewer->ma_nguoi_dung,
            'ngay_duyet' => now(),
        ]);

        return $leaveRequest->refresh();
    }

    public function reject(GiangVienDonXinNghi $leaveRequest, NguoiDung $reviewer, ?string $feedback = null): GiangVienDonXinNghi
    {
        $leaveRequest->update([
            'trang_thai' => GiangVienDonXinNghi::TRANG_THAI_TU_CHOI,
            'ghi_chu_phan_hoi' => $feedback,
            'nguoi_duyet_id' => $reviewer->ma_nguoi_dung,
            'ngay_duyet' => now(),
        ]);

        return $leaveRequest->refresh();
    }

    /**
     * @return Collection<int, GiangVienDonXinNghi>
     */
    public function findOverlappingRequests(
        int $teacherId,
        Carbon|string $date,
        ?int $startPeriod,
        ?int $endPeriod,
        ?int $ignoreLeaveRequestId = null,
        array $statuses = [
            GiangVienDonXinNghi::TRANG_THAI_CHO_DUYET,
            GiangVienDonXinNghi::TRANG_THAI_DA_DUYET,
        ],
    ): Collection {
        if ($startPeriod === null || $endPeriod === null) {
            return collect();
        }

        $date = $date instanceof Carbon ? $date->toDateString() : Carbon::parse($date)->toDateString();

        $query = GiangVienDonXinNghi::query()
            ->where('giang_vien_id', $teacherId)
            ->whereDate('ngay_xin_nghi', $date)
            ->whereIn('trang_thai', $statuses)
            ->where(function ($builder) use ($startPeriod, $endPeriod) {
                $builder->where(function ($periodQuery) use ($startPeriod, $endPeriod) {
                    $periodQuery
                        ->whereNotNull('tiet_bat_dau')
                        ->whereNotNull('tiet_ket_thuc')
                        ->where('tiet_bat_dau', '<=', $endPeriod)
                        ->where('tiet_ket_thuc', '>=', $startPeriod);
                });
            })
            ->orderByDesc('created_at');

        if ($ignoreLeaveRequestId !== null) {
            $query->where('id', '!=', $ignoreLeaveRequestId);
        }

        return $query->get();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function normalizePayload(GiangVien $teacher, array $payload, ?LichHoc $schedule = null): array
    {
        if ($schedule) {
            $periodRange = TeachingPeriodCatalog::normalizeRange($schedule->tiet_bat_dau, $schedule->tiet_ket_thuc)
                ?? TeachingPeriodCatalog::periodsFromTimes(
                    substr((string) $schedule->gio_bat_dau, 0, 5),
                    substr((string) $schedule->gio_ket_thuc, 0, 5),
                );

            return [
                'giang_vien_id' => $teacher->id,
                'khoa_hoc_id' => $schedule->khoa_hoc_id,
                'module_hoc_id' => $schedule->module_hoc_id,
                'lich_hoc_id' => $schedule->id,
                'ngay_xin_nghi' => $schedule->ngay_hoc?->toDateString(),
                'buoi_hoc' => $periodRange['session'] ?? $schedule->buoi_hoc,
                'tiet_bat_dau' => $periodRange['start'] ?? $schedule->tiet_bat_dau,
                'tiet_ket_thuc' => $periodRange['end'] ?? $schedule->tiet_ket_thuc,
                'ly_do' => $payload['ly_do'],
                'ghi_chu_phan_hoi' => null,
                'trang_thai' => GiangVienDonXinNghi::TRANG_THAI_CHO_DUYET,
            ];
        }

        $date = Carbon::parse((string) $payload['ngay_xin_nghi']);
        $range = TeachingPeriodCatalog::normalizeSelectedPeriods((array) ($payload['selected_tiets'] ?? []))
            ?? TeachingPeriodCatalog::normalizeRange(
                isset($payload['tiet_bat_dau']) ? (int) $payload['tiet_bat_dau'] : null,
                isset($payload['tiet_ket_thuc']) ? (int) $payload['tiet_ket_thuc'] : null,
                $payload['buoi_hoc'] ?? null,
            );

        if ($range === null) {
            throw ValidationException::withMessages([
                'selected_tiets' => 'Can chon tiet hoc hoac buoi hoc cho don xin nghi.',
            ]);
        }

        $times = TeachingPeriodCatalog::timeRangeFromPeriods($range['start'], $range['end']);
        $ruleCheck = $this->ruleService->inspect($date, $times['start_time'], $times['end_time']);

        if (!$ruleCheck['ok']) {
            throw ValidationException::withMessages([
                'ngay_xin_nghi' => $ruleCheck['message'],
            ]);
        }

        return [
            'giang_vien_id' => $teacher->id,
            'khoa_hoc_id' => $payload['khoa_hoc_id'] ?? null,
            'module_hoc_id' => $payload['module_hoc_id'] ?? null,
            'lich_hoc_id' => null,
            'ngay_xin_nghi' => $date->toDateString(),
            'buoi_hoc' => $range['session'],
            'tiet_bat_dau' => $range['start'],
            'tiet_ket_thuc' => $range['end'],
            'ly_do' => $payload['ly_do'],
            'ghi_chu_phan_hoi' => null,
            'trang_thai' => GiangVienDonXinNghi::TRANG_THAI_CHO_DUYET,
        ];
    }
}

