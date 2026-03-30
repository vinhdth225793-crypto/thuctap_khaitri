<?php

namespace App\Http\Requests;

use App\Services\Scheduling\TeacherScheduleRuleService;
use App\Support\Scheduling\TeachingPeriodCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
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

        $shouldMergeExplicitPeriodFields = $selectedRange !== null
            || $explicitRange !== null
            || ($sessionRange !== null && blank($this->input('gio_bat_dau')) && blank($this->input('gio_ket_thuc')));

        if ($range !== null && $shouldMergeExplicitPeriodFields) {
            $times = TeachingPeriodCatalog::timeRangeFromPeriods($range['start'], $range['end']);

            $this->merge([
                'tiet_bat_dau' => $range['start'],
                'tiet_ket_thuc' => $range['end'],
                'buoi_hoc' => $range['session'],
                'gio_bat_dau' => $this->input('gio_bat_dau') ?: $times['start_time'],
                'gio_ket_thuc' => $this->input('gio_ket_thuc') ?: $times['end_time'],
            ]);
        } elseif ($sessionRange !== null) {
            $this->merge([
                'buoi_hoc' => $sessionRange['session'],
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'ngay_hoc' => ['required', 'date'],
            'selected_tiets' => ['nullable', 'array', 'min:1'],
            'selected_tiets.*' => ['integer', 'between:1,12'],
            'tiet_bat_dau' => ['nullable', 'integer', 'between:1,12'],
            'tiet_ket_thuc' => ['nullable', 'integer', 'between:1,12', 'gte:tiet_bat_dau'],
            'buoi_hoc' => ['nullable', Rule::in(array_keys(TeachingPeriodCatalog::sessions()))],
            'gio_bat_dau' => ['required', 'date_format:H:i'],
            'gio_ket_thuc' => ['required', 'date_format:H:i', 'after:gio_bat_dau'],
            'trang_thai' => ['required', Rule::in(['cho', 'dang_hoc', 'hoan_thanh', 'huy'])],
            'hinh_thuc' => ['required', Rule::in(['truc_tiep', 'online'])],
            'phong_hoc' => ['nullable', 'string', 'max:500'],
            'giang_vien_id' => ['nullable', 'integer', 'exists:giang_vien,id', 'required_unless:trang_thai,huy'],
            'ghi_chu' => ['nullable', 'string'],
            'apply_to_all_online' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty() || (string) $this->input('trang_thai') === 'huy') {
                return;
            }

            $ruleCheck = app(TeacherScheduleRuleService::class)->inspect(
                (string) $this->input('ngay_hoc'),
                (string) $this->input('gio_bat_dau'),
                (string) $this->input('gio_ket_thuc'),
            );

            if (!$ruleCheck['ok']) {
                $validator->errors()->add('ngay_hoc', $ruleCheck['message']);
            }
        });
    }
}
