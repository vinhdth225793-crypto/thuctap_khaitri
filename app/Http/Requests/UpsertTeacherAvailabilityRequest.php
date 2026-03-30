<?php

namespace App\Http\Requests;

use App\Models\GiangVienLichRanh;
use App\Support\Scheduling\TeachingPeriodCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpsertTeacherAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isGiangVien();
    }

    protected function prepareForValidation(): void
    {
        $type = $this->input('loai_lich_ranh');
        $dates = $this->input('ngay_ap_dung');

        if (is_string($dates)) {
            $dates = collect(explode(',', $dates))
                ->map(fn ($value) => trim($value))
                ->filter()
                ->values()
                ->all();
        }

        if (!is_array($dates)) {
            $dates = [];
        }

        if ($dates === [] && filled($this->input('ngay_cu_the'))) {
            $dates = [$this->input('ngay_cu_the')];
        }

        $selectedRange = TeachingPeriodCatalog::normalizeSelectedPeriods((array) $this->input('selected_tiets', []));
        $explicitRange = ($this->filled('tiet_bat_dau') && $this->filled('tiet_ket_thuc'))
            ? TeachingPeriodCatalog::normalizeRange(
                (int) $this->input('tiet_bat_dau'),
                (int) $this->input('tiet_ket_thuc'),
            )
            : null;
        $sessionRange = $selectedRange === null && $explicitRange === null && filled($this->input('buoi_hoc') ?: $this->input('ca_day'))
            ? TeachingPeriodCatalog::normalizeRange(
                null,
                null,
                $this->input('buoi_hoc') ?: $this->input('ca_day'),
            )
            : null;
        $normalizedRange = $selectedRange ?? $explicitRange ?? $sessionRange;

        $shouldMergeExplicitPeriodFields = $selectedRange !== null
            || $explicitRange !== null
            || ($sessionRange !== null && blank($this->input('gio_bat_dau')) && blank($this->input('gio_ket_thuc')));

        if ($normalizedRange !== null && $shouldMergeExplicitPeriodFields) {
            $times = TeachingPeriodCatalog::timeRangeFromPeriods($normalizedRange['start'], $normalizedRange['end']);

            $this->merge([
                'tiet_bat_dau' => $normalizedRange['start'],
                'tiet_ket_thuc' => $normalizedRange['end'],
                'buoi_hoc' => $normalizedRange['session'],
                'ca_day' => $normalizedRange['session'],
                'gio_bat_dau' => $this->input('gio_bat_dau') ?: $times['start_time'],
                'gio_ket_thuc' => $this->input('gio_ket_thuc') ?: $times['end_time'],
            ]);
        } elseif ($sessionRange !== null) {
            $this->merge([
                'buoi_hoc' => $sessionRange['session'],
                'ca_day' => $sessionRange['session'],
            ]);
        }

        if (blank($type) && $dates !== []) {
            $type = GiangVienLichRanh::LOAI_THEO_NGAY;
        }

        if ($type === GiangVienLichRanh::LOAI_THEO_TUAN) {
            $this->merge([
                'ngay_cu_the' => null,
                'ngay_ap_dung' => [],
                'loai_lich_ranh' => $type,
            ]);
        }

        if ($type === GiangVienLichRanh::LOAI_THEO_NGAY) {
            $this->merge([
                'thu_trong_tuan' => null,
                'ngay_ap_dung' => $dates,
                'ngay_cu_the' => $this->input('ngay_cu_the') ?: ($dates[0] ?? null),
                'loai_lich_ranh' => $type,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'loai_lich_ranh' => ['required', Rule::in([
                GiangVienLichRanh::LOAI_THEO_TUAN,
                GiangVienLichRanh::LOAI_THEO_NGAY,
            ])],
            'thu_trong_tuan' => ['nullable', 'integer', 'between:2,8', 'required_if:loai_lich_ranh,' . GiangVienLichRanh::LOAI_THEO_TUAN],
            'ngay_cu_the' => ['nullable', 'date', 'after_or_equal:today', 'required_if:loai_lich_ranh,' . GiangVienLichRanh::LOAI_THEO_NGAY],
            'ngay_ap_dung' => ['nullable', 'array'],
            'ngay_ap_dung.*' => ['date', 'after_or_equal:today'],
            'selected_tiets' => ['nullable', 'array', 'min:1'],
            'selected_tiets.*' => ['integer', 'between:1,12'],
            'tiet_bat_dau' => ['nullable', 'integer', 'between:1,12'],
            'tiet_ket_thuc' => ['nullable', 'integer', 'between:1,12', 'gte:tiet_bat_dau'],
            'buoi_hoc' => ['nullable', Rule::in(array_keys(TeachingPeriodCatalog::sessions()))],
            'gio_bat_dau' => ['nullable', 'date_format:H:i'],
            'gio_ket_thuc' => ['nullable', 'date_format:H:i', 'after:gio_bat_dau'],
            'ca_day' => ['nullable', 'string', 'max:20'],
            'ghi_chu' => ['nullable', 'string', 'max:1000'],
            'trang_thai' => ['nullable', Rule::in([
                GiangVienLichRanh::TRANG_THAI_HOAT_DONG,
                GiangVienLichRanh::TRANG_THAI_TAM_AN,
            ])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $hasScheduleRange = $this->filled('gio_bat_dau')
                || $this->filled('gio_ket_thuc')
                || $this->filled('tiet_bat_dau')
                || $this->filled('tiet_ket_thuc')
                || $this->filled('buoi_hoc')
                || !empty($this->input('selected_tiets', []));

            if (!$hasScheduleRange) {
                $validator->errors()->add('selected_tiets', 'Vui long chon it nhat 1 tiet hoc hoac 1 buoi hoc.');
            }

            if (!empty($this->input('selected_tiets', []))
                && TeachingPeriodCatalog::normalizeSelectedPeriods((array) $this->input('selected_tiets')) === null) {
                $validator->errors()->add('selected_tiets', 'Cac tiet da chon phai lien tiep de tao thanh 1 khung day hop le.');
            }

            if ($this->input('loai_lich_ranh') === GiangVienLichRanh::LOAI_THEO_NGAY) {
                $dates = (array) $this->input('ngay_ap_dung', []);
                if ($dates === [] && !$this->filled('ngay_cu_the')) {
                    $validator->errors()->add('ngay_ap_dung', 'Vui long chon it nhat 1 ngay ap dung.');
                }

                if (!$this->isMethod('post') && count($dates) > 1) {
                    $validator->errors()->add('ngay_ap_dung', 'Ban chi co the cap nhat tung ngay dang ky mot.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'loai_lich_ranh.required' => 'Vui long chon loai lich ranh.',
            'loai_lich_ranh.in' => 'Loai lich ranh khong hop le.',
            'thu_trong_tuan.required_if' => 'Vui long chon thu trong tuan.',
            'thu_trong_tuan.between' => 'Thu trong tuan khong hop le.',
            'ngay_cu_the.required_if' => 'Vui long chon ngay cu the.',
            'ngay_cu_the.after_or_equal' => 'Ngay cu the phai tu hom nay tro di.',
            'gio_bat_dau.date_format' => 'Gio bat dau phai theo dinh dang HH:MM.',
            'gio_ket_thuc.date_format' => 'Gio ket thuc phai theo dinh dang HH:MM.',
            'gio_ket_thuc.after' => 'Gio ket thuc phai lon hon gio bat dau.',
            'tiet_bat_dau.between' => 'Tiet bat dau phai nam trong khoang 1 den 12.',
            'tiet_ket_thuc.between' => 'Tiet ket thuc phai nam trong khoang 1 den 12.',
            'tiet_ket_thuc.gte' => 'Tiet ket thuc phai lon hon hoac bang tiet bat dau.',
        ];
    }
}
