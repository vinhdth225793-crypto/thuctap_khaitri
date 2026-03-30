<?php

namespace App\Models;

use App\Support\Scheduling\TeachingPeriodCatalog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class GiangVienLichRanh extends Model
{
    use HasFactory;

    public const LOAI_THEO_TUAN = 'theo_tuan';
    public const LOAI_THEO_NGAY = 'theo_ngay';
    public const TRANG_THAI_HOAT_DONG = 'hoat_dong';
    public const TRANG_THAI_TAM_AN = 'tam_an';

    protected $table = 'giang_vien_lich_ranh';

    protected $fillable = [
        'giang_vien_id',
        'loai_lich_ranh',
        'thu_trong_tuan',
        'ngay_cu_the',
        'tiet_bat_dau',
        'tiet_ket_thuc',
        'buoi_hoc',
        'gio_bat_dau',
        'gio_ket_thuc',
        'ca_day',
        'ghi_chu',
        'trang_thai',
    ];

    protected $casts = [
        'ngay_cu_the' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $availability) {
            $session = $availability->buoi_hoc ?: $availability->ca_day;
            $hasExplicitPeriods = $availability->tiet_bat_dau !== null && $availability->tiet_ket_thuc !== null;
            $shouldCanonicalizeTimes = $hasExplicitPeriods || blank($availability->gio_bat_dau) || blank($availability->gio_ket_thuc);

            $range = $hasExplicitPeriods
                ? TeachingPeriodCatalog::normalizeRange((int) $availability->tiet_bat_dau, (int) $availability->tiet_ket_thuc)
                : null;

            if ($range === null && filled($session)) {
                $range = TeachingPeriodCatalog::normalizeRange(null, null, $session);
            }

            if ($range === null) {
                $range = TeachingPeriodCatalog::periodsFromTimes(
                    substr((string) $availability->gio_bat_dau, 0, 5) ?: null,
                    substr((string) $availability->gio_ket_thuc, 0, 5) ?: null,
                );
            }

            if ($range !== null) {
                $availability->tiet_bat_dau = $range['start'];
                $availability->tiet_ket_thuc = $range['end'];
                $availability->buoi_hoc = $range['session'];
                $availability->ca_day = $range['session'];

                if ($shouldCanonicalizeTimes) {
                    $times = TeachingPeriodCatalog::timeRangeFromPeriods($range['start'], $range['end']);
                    $availability->gio_bat_dau = $times['start_time'];
                    $availability->gio_ket_thuc = $times['end_time'];
                }
            }

            if ($availability->ngay_cu_the instanceof Carbon) {
                $availability->thu_trong_tuan = $availability->ngay_cu_the->dayOfWeek === Carbon::SUNDAY
                    ? 8
                    : ($availability->ngay_cu_the->dayOfWeek + 1);
            } elseif (filled($availability->ngay_cu_the)) {
                $date = Carbon::parse((string) $availability->ngay_cu_the);
                $availability->thu_trong_tuan = $date->dayOfWeek === Carbon::SUNDAY ? 8 : ($date->dayOfWeek + 1);
            }
        });
    }

    public function giangVien(): BelongsTo
    {
        return $this->belongsTo(GiangVien::class, 'giang_vien_id');
    }

    public function scopeHoatDong($query)
    {
        return $query->where('trang_thai', self::TRANG_THAI_HOAT_DONG);
    }

    public function getLoaiLabelAttribute(): string
    {
        return $this->loai_lich_ranh === self::LOAI_THEO_NGAY ? 'Theo ngay' : 'Theo tuan';
    }

    public function getTrangThaiLabelAttribute(): string
    {
        return $this->trang_thai === self::TRANG_THAI_TAM_AN ? 'Tam an' : 'Hoat dong';
    }

    public function getThuLabelAttribute(): string
    {
        return LichHoc::$thuLabels[$this->thu_trong_tuan] ?? '-';
    }

    public function getDisplayDateOrDayAttribute(): string
    {
        if ($this->loai_lich_ranh === self::LOAI_THEO_NGAY) {
            return $this->ngay_cu_the?->format('d/m/Y') ?? '-';
        }

        return $this->thu_label;
    }

    public function getCaDayLabelAttribute(): ?string
    {
        $value = $this->buoi_hoc ?: $this->ca_day;

        return match ($value) {
            'sang' => 'Ca sang',
            'chieu' => 'Ca chieu',
            'toi' => 'Ca toi',
            'ca_ngay' => 'Ca ngay',
            default => $value,
        };
    }

    public function getTimeRangeAttribute(): string
    {
        return substr((string) $this->gio_bat_dau, 0, 5) . ' - ' . substr((string) $this->gio_ket_thuc, 0, 5);
    }

    public function getTietRangeLabelAttribute(): string
    {
        return TeachingPeriodCatalog::rangeLabel($this->tiet_bat_dau, $this->tiet_ket_thuc);
    }

    public function getScheduleRangeLabelAttribute(): string
    {
        $sessionLabel = $this->ca_day_label;
        $periodLabel = $this->tiet_range_label;

        if ($sessionLabel !== null) {
            return $sessionLabel . ' (' . $periodLabel . ')';
        }

        return $periodLabel;
    }
}
