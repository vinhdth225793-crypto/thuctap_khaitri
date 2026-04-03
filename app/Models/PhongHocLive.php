<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class PhongHocLive extends Model
{
    use HasFactory, SoftDeletes;

    public const PLATFORM_ZOOM = 'zoom';
    public const PLATFORM_GOOGLE_MEET = 'google_meet';

    public const TYPE_MEETING = 'meeting';
    public const TYPE_CLASS = 'class';
    public const TYPE_WEBINAR = 'webinar';

    public const ROOM_STATE_CHUA_MO = 'chua_mo';
    public const ROOM_STATE_SAP_DIEN_RA = 'sap_dien_ra';
    public const ROOM_STATE_DANG_DIEN_RA = 'dang_dien_ra';
    public const ROOM_STATE_DA_KET_THUC = 'da_ket_thuc';
    public const ROOM_STATE_DA_HUY = 'da_huy';

    public const APPROVAL_NHAP = 'nhap';
    public const APPROVAL_CHO_DUYET = 'cho_duyet';
    public const APPROVAL_DA_DUYET = 'da_duyet';
    public const APPROVAL_CAN_CHINH_SUA = 'can_chinh_sua';
    public const APPROVAL_TU_CHOI = 'tu_choi';

    public const PUBLISH_AN = 'an';
    public const PUBLISH_DA_CONG_BO = 'da_cong_bo';

    protected $table = 'phong_hoc_live';

    protected $fillable = [
        'bai_giang_id',
        'nen_tang_live',
        'loai_live',
        'tieu_de',
        'mo_ta',
        'moderator_id',
        'tro_giang_id',
        'thoi_gian_bat_dau',
        'thoi_luong_phut',
        'mo_phong_truoc_phut',
        'nhac_truoc_phut',
        'suc_chua_toi_da',
        'cho_phep_chat',
        'cho_phep_thao_luan',
        'cho_phep_chia_se_man_hinh',
        'tat_mic_khi_vao',
        'tat_camera_khi_vao',
        'cho_phep_ghi_hinh',
        'chi_admin_duoc_ghi_hinh',
        'tu_dong_gan_ban_ghi',
        'khoa_copy_noi_dung_mo_ta',
        'trang_thai_duyet',
        'trang_thai_cong_bo',
        'trang_thai_phong',
        'du_lieu_nen_tang_json',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'thoi_gian_bat_dau' => 'datetime',
        'cho_phep_chat' => 'boolean',
        'cho_phep_thao_luan' => 'boolean',
        'cho_phep_chia_se_man_hinh' => 'boolean',
        'tat_mic_khi_vao' => 'boolean',
        'tat_camera_khi_vao' => 'boolean',
        'cho_phep_ghi_hinh' => 'boolean',
        'chi_admin_duoc_ghi_hinh' => 'boolean',
        'tu_dong_gan_ban_ghi' => 'boolean',
        'khoa_copy_noi_dung_mo_ta' => 'boolean',
        'du_lieu_nen_tang_json' => 'array',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function baiGiang()
    {
        return $this->belongsTo(BaiGiang::class, 'bai_giang_id');
    }

    public function moderator()
    {
        return $this->belongsTo(NguoiDung::class, 'moderator_id', 'ma_nguoi_dung');
    }

    public function troGiang()
    {
        return $this->belongsTo(NguoiDung::class, 'tro_giang_id', 'ma_nguoi_dung');
    }

    public function creator()
    {
        return $this->belongsTo(NguoiDung::class, 'created_by', 'ma_nguoi_dung');
    }

    public function approver()
    {
        return $this->belongsTo(NguoiDung::class, 'approved_by', 'ma_nguoi_dung');
    }

    public function nguoiThamGia()
    {
        return $this->hasMany(PhongHocLiveNguoiThamGia::class, 'phong_hoc_live_id');
    }

    public function banGhis()
    {
        return $this->hasMany(PhongHocLiveBanGhi::class, 'phong_hoc_live_id');
    }

    public function getPlatformLabelAttribute(): string
    {
        return app(\App\Services\LiveRoomPlatformService::class)->platformLabel($this->nen_tang_live);
    }

    public function getJoinUrlAttribute(): ?string
    {
        return app(\App\Services\LiveRoomPlatformService::class)->getJoinUrl($this);
    }

    public function getStartUrlAttribute(): ?string
    {
        return app(\App\Services\LiveRoomPlatformService::class)->getStartUrl($this);
    }

    public function getEmbedUrlAttribute(): ?string
    {
        return app(\App\Services\LiveRoomPlatformService::class)->getEmbedUrl($this);
    }

    public function getJoinOpensAtAttribute(): Carbon
    {
        return $this->thoi_gian_bat_dau->copy()->subMinutes($this->mo_phong_truoc_phut);
    }

    public function getEndsAtAttribute(): Carbon
    {
        return $this->thoi_gian_bat_dau->copy()->addMinutes($this->thoi_luong_phut);
    }

    public function getTimelineTrangThaiAttribute(): string
    {
        if ($this->trang_thai_phong === self::ROOM_STATE_DA_HUY) {
            return self::ROOM_STATE_DA_HUY;
        }

        if ($this->trang_thai_phong === self::ROOM_STATE_DA_KET_THUC) {
            return self::ROOM_STATE_DA_KET_THUC;
        }

        $now = now();

        if ($now->lt($this->join_opens_at)) {
            return 'chua_den_gio';
        }

        if ($now->betweenIncluded($this->join_opens_at, $this->thoi_gian_bat_dau)) {
            return 'sap_bat_dau';
        }

        if ($now->betweenIncluded($this->thoi_gian_bat_dau, $this->ends_at)) {
            return $this->trang_thai_phong === self::ROOM_STATE_DANG_DIEN_RA
                ? self::ROOM_STATE_DANG_DIEN_RA
                : 'cho_moderator';
        }

        return self::ROOM_STATE_DA_KET_THUC;
    }

    public function getTimelineTrangThaiLabelAttribute(): string
    {
        return match ($this->timeline_trang_thai) {
            'chua_den_gio' => 'Chua den gio',
            'sap_bat_dau' => 'Sap bat dau',
            'cho_moderator' => 'Cho moderator bat dau',
            self::ROOM_STATE_DANG_DIEN_RA => 'Dang dien ra',
            self::ROOM_STATE_DA_KET_THUC => 'Da ket thuc',
            self::ROOM_STATE_DA_HUY => 'Da huy',
            default => 'Chua xac dinh',
        };
    }

    public function getTimelineTrangThaiColorAttribute(): string
    {
        return match ($this->timeline_trang_thai) {
            'chua_den_gio' => 'secondary',
            'sap_bat_dau' => 'warning',
            'cho_moderator' => 'info',
            self::ROOM_STATE_DANG_DIEN_RA => 'success',
            self::ROOM_STATE_DA_KET_THUC => 'dark',
            self::ROOM_STATE_DA_HUY => 'danger',
            default => 'secondary',
        };
    }

    public function getParticipantCountAttribute(): int
    {
        return $this->nguoiThamGia()->count();
    }

    public function getCanModeratorStartAttribute(): bool
    {
        // Cho phép moderator bắt đầu bất cứ lúc nào miễn là chưa kết thúc hoặc hủy
        return !in_array($this->trang_thai_phong, [self::ROOM_STATE_DA_KET_THUC, self::ROOM_STATE_DA_HUY], true)
            && $this->trang_thai_phong !== self::ROOM_STATE_DANG_DIEN_RA;
    }

    public function getCanStudentJoinAttribute(): bool
    {
        if ($this->trang_thai_duyet !== self::APPROVAL_DA_DUYET || $this->trang_thai_cong_bo !== self::PUBLISH_DA_CONG_BO) {
            return false;
        }

        if (config('live_room.defaults.student_join_requires_moderator_started', true)) {
            return $this->timeline_trang_thai === self::ROOM_STATE_DANG_DIEN_RA && filled($this->join_url);
        }

        return in_array($this->timeline_trang_thai, ['sap_bat_dau', self::ROOM_STATE_DANG_DIEN_RA], true) && filled($this->join_url);
    }

    public function getStatusHintAttribute(): string
    {
        return match ($this->timeline_trang_thai) {
            'chua_den_gio' => 'Phong hoc live chua mo. Ban co the vao som truoc gio bat dau theo cau hinh.',
            'sap_bat_dau' => 'Phong hoc sap mo. Ban co the chuan bi tham gia.',
            'cho_moderator' => 'Moderator chua bat dau buoi hoc. Vui long doi.',
            self::ROOM_STATE_DANG_DIEN_RA => 'Buoi hoc dang dien ra. Ban co the vao phong ngay bay gio.',
            self::ROOM_STATE_DA_KET_THUC => 'Buoi hoc da ket thuc. Xem ban ghi neu co.',
            self::ROOM_STATE_DA_HUY => 'Buoi hoc da bi huy.',
            default => 'Thong tin phong hoc dang duoc cap nhat.',
        };
    }

    public function isDangDienRa(): bool
    {
        return $this->trang_thai_phong === self::ROOM_STATE_DANG_DIEN_RA;
    }
}
