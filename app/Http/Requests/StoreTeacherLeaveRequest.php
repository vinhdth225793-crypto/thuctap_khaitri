<?php

namespace App\Http\Requests;

use App\Models\LichHoc;
use App\Services\Scheduling\TeacherScheduleRuleService;
use App\Support\Scheduling\TeachingPeriodCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isGiangVien();
    }

    protected function prepareForValidation(): void
    {
        $selectedRange = TeachingPeriodCatalog::normalizeSelectedPeriods((array) $this->input('selected_tiets', []));
        $explicitRange = ($this->filled('tiet_bat_dau') && $this->filled('tiet_ket_thuc'))
            ? TeachingPeriodCatalog::normalizeRange(
                (int) $this->input('tiet_bat_dau'),
                (int) $this->input('tiet_ket_thuc'),
            )
            : null;
        $sessionRange = $selectedRange === null && $explicitRange === null && filled($this->input('buoi_hoc'))
            ? TeachingPeriodCatalog::normalizeRange(null, null, $this->input('buoi_hoc'))
            : null;
        $range = $selectedRange ?? $explicitRange ?? $sessionRange;

        if ($range !== null) {
            $this->merge([
                'tiet_bat_dau' => $range['start'],
                'tiet_ket_thuc' => $range['end'],
                'buoi_hoc' => $range['session'],
            ]);
        }

        if ($this->filled('lich_hoc_id') && !$this->filled('ngay_xin_nghi')) {
            $schedule = LichHoc::find($this->input('lich_hoc_id'));
            if ($schedule) {
                $this->merge([
                    'ngay_xin_nghi' => $schedule->ngay_hoc?->toDateString(),
                    'tiet_bat_dau' => $this->input('tiet_bat_dau') ?: $schedule->tiet_bat_dau,
                    'tiet_ket_thuc' => $this->input('tiet_ket_thuc') ?: $schedule->tiet_ket_thuc,
                    'buoi_hoc' => $this->input('buoi_hoc') ?: $schedule->buoi_hoc,
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'lich_hoc_id' => ['nullable', 'integer', 'exists:lich_hoc,id'],
            'ngay_xin_nghi' => ['required_without:lich_hoc_id', 'nullable', 'date', 'after_or_equal:today'],
            'selected_tiets' => ['nullable', 'array', 'min:1'],
            'selected_tiets.*' => ['integer', 'between:1,12'],
            'tiet_bat_dau' => ['nullable', 'integer', 'between:1,12'],
            'tiet_ket_thuc' => ['nullable', 'integer', 'between:1,12', 'gte:tiet_bat_dau'],
            'buoi_hoc' => ['nullable', Rule::in(array_keys(TeachingPeriodCatalog::sessions()))],
            'ly_do' => ['required', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $teacher = auth()->user()?->giangVien;
            if (!$teacher) {
                $validator->errors()->add('lich_hoc_id', 'Không tìm thấy hồ sơ giảng viên.');
                return;
            }

            if ($this->filled('lich_hoc_id')) {
                $schedule = LichHoc::query()
                    ->where('giang_vien_id', $teacher->id)
                    ->find($this->input('lich_hoc_id'));

                if (!$schedule) {
                    $validator->errors()->add('lich_hoc_id', 'Bạn chỉ được gửi đơn cho buổi học của chính mình.');
                }

                return;
            }

            if (!$this->filled('tiet_bat_dau') || !$this->filled('tiet_ket_thuc')) {
                $validator->errors()->add('selected_tiets', 'Cần chọn tiết học hoặc buổi học cho đơn xin nghỉ.');
                return;
            }

            $times = TeachingPeriodCatalog::timeRangeFromPeriods((int) $this->input('tiet_bat_dau'), (int) $this->input('tiet_ket_thuc'));
            $ruleCheck = app(TeacherScheduleRuleService::class)->inspect(
                (string) $this->input('ngay_xin_nghi'),
                $times['start_time'],
                $times['end_time'],
            );

            if (!$ruleCheck['ok']) {
                $validator->errors()->add('ngay_xin_nghi', $ruleCheck['message']);
            }
        });
    }
}
