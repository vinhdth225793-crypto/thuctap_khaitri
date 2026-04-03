<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BaiLamViPhamGiamSat extends Model
{
    use HasFactory;

    public const SU_KIEN_TAB_SWITCH = 'tab_switch';
    public const SU_KIEN_WINDOW_BLUR = 'window_blur';
    public const SU_KIEN_WINDOW_FOCUS = 'window_focus';
    public const SU_KIEN_FULLSCREEN_EXIT = 'fullscreen_exit';
    public const SU_KIEN_CAMERA_OFF = 'camera_off';
    public const SU_KIEN_SNAPSHOT_CAPTURED = 'snapshot_captured';
    public const SU_KIEN_SNAPSHOT_FAILED = 'snapshot_failed';
    public const SU_KIEN_WARNING_ISSUED = 'warning_issued';
    public const SU_KIEN_AUTO_SUBMIT = 'auto_submit';
    public const SU_KIEN_COPY_PASTE_BLOCKED = 'copy_paste_blocked';
    public const SU_KIEN_RIGHT_CLICK_BLOCKED = 'right_click_blocked';

    protected $table = 'bai_lam_vi_pham_giam_sat';

    protected $fillable = [
        'bai_lam_bai_kiem_tra_id',
        'loai_su_kien',
        'mo_ta',
        'la_vi_pham',
        'so_lan_vi_pham_hien_tai',
        'meta',
    ];

    protected $casts = [
        'la_vi_pham' => 'boolean',
        'so_lan_vi_pham_hien_tai' => 'integer',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function baiLam(): BelongsTo
    {
        return $this->belongsTo(BaiLamBaiKiemTra::class, 'bai_lam_bai_kiem_tra_id');
    }

    public function getLoaiSuKienLabelAttribute(): string
    {
        return match ($this->loai_su_kien) {
            self::SU_KIEN_TAB_SWITCH => 'Chuyển tab',
            self::SU_KIEN_WINDOW_BLUR => 'Rời cửa sổ',
            self::SU_KIEN_WINDOW_FOCUS => 'Quay lại cửa sổ',
            self::SU_KIEN_FULLSCREEN_EXIT => 'Thoát toàn màn hình',
            self::SU_KIEN_CAMERA_OFF => 'Camera bị tắt',
            self::SU_KIEN_SNAPSHOT_CAPTURED => 'Chụp ảnh thành công',
            self::SU_KIEN_SNAPSHOT_FAILED => 'Chụp ảnh thất bại',
            self::SU_KIEN_WARNING_ISSUED => 'Cảnh báo đã phát',
            self::SU_KIEN_AUTO_SUBMIT => 'Tự động nộp bài',
            self::SU_KIEN_COPY_PASTE_BLOCKED => 'Chặn copy/paste',
            self::SU_KIEN_RIGHT_CLICK_BLOCKED => 'Chặn chuột phải',
            default => 'Sự kiện giám sát',
        };
    }

    public function getBadgeColorAttribute(): string
    {
        if ($this->la_vi_pham) {
            return 'danger';
        }

        return match ($this->loai_su_kien) {
            self::SU_KIEN_SNAPSHOT_CAPTURED, self::SU_KIEN_WINDOW_FOCUS => 'success',
            self::SU_KIEN_SNAPSHOT_FAILED, self::SU_KIEN_WARNING_ISSUED => 'warning',
            default => 'secondary',
        };
    }
}
