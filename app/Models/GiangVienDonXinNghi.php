<?php

namespace App\Models;

use App\Support\Scheduling\TeachingPeriodCatalog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiangVienDonXinNghi extends Model
{
    use HasFactory;

    public const TRANG_THAI_CHO_DUYET = 'cho_duyet';
    public const TRANG_THAI_DA_DUYET = 'da_duyet';
    public const TRANG_THAI_TU_CHOI = 'tu_choi';

    protected $table = 'giang_vien_don_xin_nghi';

    protected $fillable = [
        'giang_vien_id',
        'khoa_hoc_id',
        'module_hoc_id',
        'lich_hoc_id',
        'ngay_xin_nghi',
        'buoi_hoc',
        'tiet_bat_dau',
        'tiet_ket_thuc',
        'ly_do',
        'ghi_chu_phan_hoi',
        'trang_thai',
        'nguoi_duyet_id',
        'ngay_duyet',
    ];

    protected $casts = [
        'ngay_xin_nghi' => 'date',
        'ngay_duyet' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function giangVien(): BelongsTo
    {
        return $this->belongsTo(GiangVien::class, 'giang_vien_id');
    }

    public function khoaHoc(): BelongsTo
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    public function moduleHoc(): BelongsTo
    {
        return $this->belongsTo(ModuleHoc::class, 'module_hoc_id');
    }

    public function lichHoc(): BelongsTo
    {
        return $this->belongsTo(LichHoc::class, 'lich_hoc_id');
    }

    public function nguoiDuyet(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_duyet_id', 'ma_nguoi_dung');
    }

    public function scopeDangXuLy($query)
    {
        return $query->whereIn('trang_thai', [
            self::TRANG_THAI_CHO_DUYET,
            self::TRANG_THAI_DA_DUYET,
        ]);
    }

    public function getTrangThaiLabelAttribute(): string
    {
        return match ($this->trang_thai) {
            self::TRANG_THAI_CHO_DUYET => 'Chờ duyệt',
            self::TRANG_THAI_DA_DUYET => 'Đã duyệt',
            self::TRANG_THAI_TU_CHOI => 'Từ chối',
            default => '-',
        };
    }

    public function getTrangThaiColorAttribute(): string
    {
        return match ($this->trang_thai) {
            self::TRANG_THAI_CHO_DUYET => 'warning',
            self::TRANG_THAI_DA_DUYET => 'success',
            self::TRANG_THAI_TU_CHOI => 'danger',
            default => 'secondary',
        };
    }

    public function getTietRangeLabelAttribute(): string
    {
        return TeachingPeriodCatalog::rangeLabel($this->tiet_bat_dau, $this->tiet_ket_thuc);
    }

    public function getBuoiHocLabelAttribute(): ?string
    {
        return TeachingPeriodCatalog::sessionLabel($this->buoi_hoc);
    }

    public function getScheduleRangeLabelAttribute(): string
    {
        if ($this->buoi_hoc_label !== null) {
            return $this->buoi_hoc_label . ' (' . $this->tiet_range_label . ')';
        }

        return $this->tiet_range_label;
    }
}
