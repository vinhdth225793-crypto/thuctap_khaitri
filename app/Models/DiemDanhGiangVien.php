<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiemDanhGiangVien extends Model
{
    use HasFactory;

    public const STATUS_CHUA_DIEM_DANH = 'chua_diem_danh';
    public const STATUS_DA_CHECKIN = 'da_checkin';
    public const STATUS_DA_CHECKOUT = 'da_checkout';
    public const STATUS_HOAN_THANH = 'hoan_thanh';
    public const STATUS_LEGACY_DANG_DAY = 'dang_day';
    public const STATUS_LEGACY_DA_KET_THUC = 'da_ket_thuc';

    protected $table = 'diem_danh_giang_vien';

    protected $fillable = [
        'lich_hoc_id',
        'khoa_hoc_id',
        'module_hoc_id',
        'giang_vien_id',
        'hinh_thuc_hoc',
        'thoi_gian_bat_dau_day',
        'thoi_gian_ket_thuc_day',
        'thoi_gian_mo_live',
        'thoi_gian_tat_live',
        'tong_thoi_luong_day_phut',
        'trang_thai',
        'ghi_chu',
        'nguoi_tao_id',
    ];

    protected $casts = [
        'thoi_gian_bat_dau_day' => 'datetime',
        'thoi_gian_ket_thuc_day' => 'datetime',
        'thoi_gian_mo_live' => 'datetime',
        'thoi_gian_tat_live' => 'datetime',
        'tong_thoi_luong_day_phut' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function lichHoc(): BelongsTo
    {
        return $this->belongsTo(LichHoc::class, 'lich_hoc_id');
    }

    public function khoaHoc(): BelongsTo
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    public function moduleHoc(): BelongsTo
    {
        return $this->belongsTo(ModuleHoc::class, 'module_hoc_id');
    }

    public function giangVien(): BelongsTo
    {
        return $this->belongsTo(GiangVien::class, 'giang_vien_id');
    }

    public function nguoiTao(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_tao_id', 'ma_nguoi_dung');
    }

    public function getCheckInAtAttribute()
    {
        return $this->thoi_gian_bat_dau_day;
    }

    public function getCheckOutAtAttribute()
    {
        return $this->thoi_gian_ket_thuc_day;
    }

    public function getHasCheckedInAttribute(): bool
    {
        return $this->thoi_gian_bat_dau_day !== null;
    }

    public function getHasCheckedOutAttribute(): bool
    {
        return $this->thoi_gian_ket_thuc_day !== null;
    }

    public function getDisplayStatusAttribute(): string
    {
        if ($this->trang_thai === self::STATUS_HOAN_THANH) {
            return self::STATUS_HOAN_THANH;
        }

        if ($this->trang_thai === self::STATUS_DA_CHECKOUT) {
            return self::STATUS_DA_CHECKOUT;
        }

        if ($this->thoi_gian_ket_thuc_day !== null) {
            return self::STATUS_DA_CHECKOUT;
        }

        if ($this->thoi_gian_bat_dau_day !== null) {
            return self::STATUS_DA_CHECKIN;
        }

        return self::STATUS_CHUA_DIEM_DANH;
    }

    public function getTrangThaiLabelAttribute(): string
    {
        return match ($this->display_status) {
            self::STATUS_DA_CHECKIN => 'Đã check-in',
            self::STATUS_DA_CHECKOUT => 'Đã check-out',
            self::STATUS_HOAN_THANH => 'Hoàn thành',
            default => 'Chưa điểm danh',
        };
    }

    public function getTrangThaiColorAttribute(): string
    {
        return match ($this->display_status) {
            self::STATUS_DA_CHECKIN => 'warning',
            self::STATUS_DA_CHECKOUT => 'primary',
            self::STATUS_HOAN_THANH => 'success',
            default => 'secondary',
        };
    }

    public function getStatusHintAttribute(): string
    {
        return match ($this->display_status) {
            self::STATUS_DA_CHECKIN => 'Giảng viên đã check-in và đang trong phiên dạy.',
            self::STATUS_DA_CHECKOUT => 'Giảng viên đã check-out, hệ thống đã ghi nhận đủ giờ vào và giờ ra.',
            self::STATUS_HOAN_THANH => 'Attendance giảng viên của buổi học này đã hoàn tất.',
            default => 'Giảng viên chưa thực hiện attendance cho buổi học này.',
        };
    }
}
