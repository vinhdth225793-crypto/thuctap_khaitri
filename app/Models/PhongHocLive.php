<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use App\Support\OnlineMeetingUrl;

class PhongHocLive extends Model
{
    use HasFactory;

    public const PLATFORM_INTERNAL = 'internal';
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
        'lop_hoc_id',
        'bai_giang_id',
        'giang_vien_id',
        'moderator_id',
        'tro_giang_id',
        'tieu_de',
        'nen_tang',
        'nen_tang_live',
        'platform_type',
        'external_meeting_url',
        'external_meeting_code',
        'external_link_updated_at',
        'external_link_updated_by',
        'phong_id',
        'mat_khau',
        'bat_dau_du_kien',
        'ket_thuc_du_kien',
        'thoi_gian_bat_dau',
        'thoi_luong_phut',
        'bat_dau_thuc_te',
        'ket_thuc_thuc_te',
        'trang_thai',
        'trang_thai_phong',
        'trang_thai_duyet',
        'trang_thai_cong_bo',
        'du_lieu_nen_tang',
        'du_lieu_nen_tang_json',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'bat_dau_du_kien' => 'datetime',
        'ket_thuc_du_kien' => 'datetime',
        'bat_dau_thuc_te' => 'datetime',
        'ket_thuc_thuc_te' => 'datetime',
        'external_link_updated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function lopHoc()
    {
        return $this->belongsTo(LopHoc::class, 'lop_hoc_id');
    }

    public function baiGiang()
    {
        return $this->belongsTo(BaiGiang::class, 'bai_giang_id');
    }

    public function lichHoc()
    {
        return $this->hasOneThrough(
            LichHoc::class,
            BaiGiang::class,
            'id', // Khóa ngoại trên BaiGiang
            'id', // Khóa ngoại trên LichHoc
            'bai_giang_id', // Khóa nội trên PhongHocLive
            'lich_hoc_id' // Khóa nội trên BaiGiang
        );
    }

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'giang_vien_id');
    }

    public function moderator()
    {
        return $this->belongsTo(NguoiDung::class, 'moderator_id', 'ma_nguoi_dung');
    }

    public function troGiang()
    {
        return $this->belongsTo(NguoiDung::class, 'tro_giang_id', 'ma_nguoi_dung');
    }

    public function nguoiThamGia()
    {
        return $this->hasMany(PhongHocLiveNguoiThamGia::class, 'phong_hoc_live_id');
    }

    public function banGhis()
    {
        return $this->hasMany(PhongHocLiveBanGhi::class, 'phong_hoc_live_id');
    }

    public function linkHistories()
    {
        return $this->hasMany(LiveRoomLinkHistory::class, 'phong_hoc_live_id')->latest();
    }

    public function getPlatformLabelAttribute(): string
    {
        return app(\App\Services\LiveRoomPlatformService::class)->platformLabel($this->platform_type);
    }

    public function getPlatformTypeAttribute($value): string
    {
        if (filled($value)) {
            return $value;
        }

        $platform = $this->attributes['nen_tang_live'] ?? $this->attributes['nen_tang'] ?? null;

        if (filled($platform)) {
            return $platform;
        }

        $externalUrl = $this->attributes['external_meeting_url'] ?? null;
        if (OnlineMeetingUrl::isGoogleMeetUrl($externalUrl)) {
            return self::PLATFORM_GOOGLE_MEET;
        }

        return self::PLATFORM_INTERNAL;
    }

    public function setPlatformTypeAttribute($value): void
    {
        $this->attributes['platform_type'] = $value;

        if (filled($value)) {
            $this->attributes['nen_tang'] = $value;
            $this->attributes['nen_tang_live'] = $value;
        }
    }

    public function getNenTangLiveAttribute($value): ?string
    {
        return $value ?? ($this->attributes['nen_tang'] ?? null);
    }

    public function setNenTangLiveAttribute($value): void
    {
        $this->attributes['nen_tang'] = $value;
        $this->attributes['nen_tang_live'] = $value;
    }

    public function setNenTangAttribute($value): void
    {
        $this->attributes['nen_tang'] = $value;
        $this->attributes['nen_tang_live'] = $value;
    }

    public function getThoiGianBatDauAttribute($value): ?Carbon
    {
        $value = $value ?? ($this->attributes['bat_dau_du_kien'] ?? null);

        return $value ? Carbon::parse($value) : null;
    }

    public function setThoiGianBatDauAttribute($value): void
    {
        $this->attributes['bat_dau_du_kien'] = $value;
        $this->attributes['thoi_gian_bat_dau'] = $value;
    }

    public function setBatDauDuKienAttribute($value): void
    {
        $this->attributes['bat_dau_du_kien'] = $value;
        $this->attributes['thoi_gian_bat_dau'] = $value;
    }

    public function getThoiLuongPhutAttribute($value): int
    {
        if ($value !== null) {
            return (int) $value;
        }

        if ($this->bat_dau_du_kien && $this->ket_thuc_du_kien) {
            return max(1, $this->bat_dau_du_kien->diffInMinutes($this->ket_thuc_du_kien));
        }

        return 90;
    }

    public function setThoiLuongPhutAttribute($value): void
    {
        $start = $this->bat_dau_du_kien ?? now();
        $this->attributes['ket_thuc_du_kien'] = $start->copy()->addMinutes((int) $value);
        $this->attributes['thoi_luong_phut'] = (int) $value;
    }

    public function getDuLieuNenTangJsonAttribute($value): array
    {
        if (is_string($value)) {
            return json_decode($value, true) ?: [];
        }

        return $value ?? ($this->du_lieu_nen_tang ?? []);
    }

    public function setDuLieuNenTangJsonAttribute($value): void
    {
        $jsonValue = is_array($value) ? json_encode($value) : $value;

        $this->attributes['du_lieu_nen_tang_json'] = $jsonValue;
        $this->attributes['du_lieu_nen_tang'] = $jsonValue;
    }

    public function setDuLieuNenTangAttribute($value): void
    {
        $jsonValue = is_array($value) ? json_encode($value) : $value;

        $this->attributes['du_lieu_nen_tang_json'] = $jsonValue;
        $this->attributes['du_lieu_nen_tang'] = $jsonValue;
    }

    public function getTrangThaiPhongAttribute($value): ?string
    {
        if ($value !== null) {
            return $value;
        }

        return match ($this->trang_thai) {
            'cho' => self::ROOM_STATE_CHUA_MO,
            'huy' => self::ROOM_STATE_DA_HUY,
            default => $this->trang_thai,
        };
    }

    public function setTrangThaiPhongAttribute($value): void
    {
        $this->attributes['trang_thai_phong'] = $value;
        $this->attributes['trang_thai'] = match ($value) {
            self::ROOM_STATE_CHUA_MO, self::ROOM_STATE_SAP_DIEN_RA => 'cho',
            self::ROOM_STATE_DA_HUY => 'huy',
            default => $value,
        };
    }

    public function setTrangThaiAttribute($value): void
    {
        $this->attributes['trang_thai'] = $value;
        $this->attributes['trang_thai_phong'] = match ($value) {
            'cho' => self::ROOM_STATE_CHUA_MO,
            'huy' => self::ROOM_STATE_DA_HUY,
            default => $value,
        };
    }

    public function getModeratorIdAttribute($value): ?int
    {
        if ($value !== null) {
            return (int) $value;
        }

        return $this->giangVien?->nguoi_dung_id;
    }

    public function getTroGiangIdAttribute($value): ?int
    {
        return $value !== null ? (int) $value : null;
    }

    public function getCreatedByAttribute($value): ?int
    {
        if ($value !== null) {
            return (int) $value;
        }

        return $this->giangVien?->nguoi_dung_id;
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

    public function getEffectiveExternalMeetingUrlAttribute(): ?string
    {
        $payload = $this->du_lieu_nen_tang_json;

        $url = $this->attributes['external_meeting_url'] ?? null;
        $url = $url ?: ($payload['join_url'] ?? null);
        $url = $url ?: ($payload['start_url'] ?? null);

        if (! $url && $this->bai_giang_id) {
            $url = $this->baiGiang?->lichHoc?->link_online;
        }

        return OnlineMeetingUrl::normalize($url);
    }

    public function getResolvedExternalMeetingCodeAttribute(): ?string
    {
        $payload = $this->du_lieu_nen_tang_json;
        $code = $this->attributes['external_meeting_code'] ?? null;
        $code = $code ?: ($payload['meeting_code'] ?? null);
        $code = $code ?: ($payload['meeting_id'] ?? null);

        return $code ?: OnlineMeetingUrl::meetingCode($this->effective_external_meeting_url);
    }

    public function isExternalProviderRoom(): bool
    {
        return in_array($this->platform_type, [self::PLATFORM_GOOGLE_MEET, self::PLATFORM_ZOOM], true);
    }

    public function hasExternalMeetingLaunch(): bool
    {
        return filled($this->effective_external_meeting_url);
    }

    public function isGoogleMeetLaunch(): bool
    {
        return $this->platform_type === self::PLATFORM_GOOGLE_MEET
            || OnlineMeetingUrl::isGoogleMeetUrl($this->effective_external_meeting_url);
    }

    public function getJoinOpensAtAttribute(): Carbon
    {
        // Fallback if null
        $start = $this->bat_dau_du_kien ?? $this->created_at ?? now();
        return $start->copy()->subMinutes(15);
    }

    public function getEndsAtAttribute(): Carbon
    {
        $start = $this->bat_dau_du_kien ?? $this->created_at ?? now();
        return $start->copy()->addMinutes(90);
    }

    public function getTimelineTrangThaiAttribute(): string
    {
        if ($this->trang_thai === 'huy') {
            return 'da_huy';
        }

        if ($this->trang_thai === 'da_ket_thuc') {
            return 'da_ket_thuc';
        }

        $now = now();

        if ($now->lt($this->join_opens_at)) {
            return 'chua_den_gio';
        }

        if ($now->betweenIncluded($this->join_opens_at, $this->bat_dau_du_kien ?? $now)) {
            return 'sap_bat_dau';
        }

        if ($now->betweenIncluded($this->bat_dau_du_kien ?? $now, $this->ends_at)) {
            return $this->trang_thai === 'dang_dien_ra'
                ? 'dang_dien_ra'
                : 'cho_moderator';
        }

        return 'da_ket_thuc';
    }

    public function getTimelineTrangThaiLabelAttribute(): string
    {
        return match ($this->timeline_trang_thai) {
            'chua_den_gio' => 'Chưa đến giờ',
            'sap_bat_dau' => 'Sắp bắt đầu',
            'cho_moderator' => 'Chờ người điều phối bắt đầu',
            'dang_dien_ra' => 'Đang diễn ra',
            'da_ket_thuc' => 'Đã kết thúc',
            'da_huy' => 'Đã hủy',
            default => 'Chưa xác định',
        };
    }

    public function getTimelineTrangThaiColorAttribute(): string
    {
        return match ($this->timeline_trang_thai) {
            'chua_den_gio' => 'secondary',
            'sap_bat_dau' => 'warning',
            'cho_moderator' => 'info',
            'dang_dien_ra' => 'success',
            'da_ket_thuc' => 'dark',
            'da_huy' => 'danger',
            default => 'secondary',
        };
    }

    public function getParticipantCountAttribute(): int
    {
        if (! Schema::hasTable('phong_hoc_live_nguoi_tham_gia')) {
            return $this->relationLoaded('nguoiThamGia') ? $this->nguoiThamGia->count() : 0;
        }

        return $this->nguoiThamGia()->count();
    }

    public function getCanModeratorStartAttribute(): bool
    {
        return !in_array($this->trang_thai, ['da_ket_thuc', 'huy'], true)
            && $this->trang_thai !== 'dang_dien_ra';
    }

    public function getCanStudentJoinAttribute(): bool
    {
        if ($this->hasExternalMeetingLaunch() && $this->isGoogleMeetLaunch()) {
            return in_array($this->timeline_trang_thai, ['sap_bat_dau', 'cho_moderator', 'dang_dien_ra'], true);
        }

        if (config('live_room.defaults.student_join_requires_moderator_started', true)) {
            return $this->timeline_trang_thai === 'dang_dien_ra' && filled($this->join_url);
        }

        return in_array($this->timeline_trang_thai, ['sap_bat_dau', 'dang_dien_ra'], true) && filled($this->join_url);
    }

    public function getStatusHintAttribute(): string
    {
        return match ($this->timeline_trang_thai) {
            'chua_den_gio' => 'Phòng học trực tuyến chưa mở.',
            'sap_bat_dau' => 'Phòng học sắp mở.',
            'cho_moderator' => 'Người điều phối chưa bắt đầu buổi học.',
            'dang_dien_ra' => 'Buổi học đang diễn ra.',
            'da_ket_thuc' => 'Buổi học đã kết thúc.',
            'da_huy' => 'Buổi học đã bị hủy.',
            default => 'Thông tin phòng học đang được cập nhật.',
        };
    }

    public function isDangDienRa(): bool
    {
        return $this->trang_thai === 'dang_dien_ra';
    }

    public function getTeachingTimelineStatusAttribute(): string
    {
        return match ($this->trang_thai) {
            'dang_dien_ra' => 'dang_dien_ra',
            'da_ket_thuc', 'huy' => 'da_ket_thuc',
            default => 'da_tao',
        };
    }

    public function getTeachingTimelineStatusLabelAttribute(): string
    {
        return match ($this->teaching_timeline_status) {
            'dang_dien_ra' => 'Đang diễn ra',
            'da_ket_thuc' => 'Đã kết thúc',
            default => 'Đã tạo',
        };
    }

    public function getTeachingTimelineStatusColorAttribute(): string
    {
        return match ($this->teaching_timeline_status) {
            'dang_dien_ra' => 'success',
            'da_ket_thuc' => 'dark',
            default => 'info',
        };
    }
}
