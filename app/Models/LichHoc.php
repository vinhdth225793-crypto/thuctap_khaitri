<?php

namespace App\Models;

use App\Services\LearningProgressStatusService;
use App\Support\Scheduling\TeachingPeriodCatalog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;

class LichHoc extends Model
{
    use HasFactory;

    public const ONLINE_JOIN_EARLY_MINUTES = 15;

    protected $table = 'lich_hoc';

    protected $fillable = [
        'khoa_hoc_id',
        'module_hoc_id',
        'giang_vien_id',
        'ngay_hoc',
        'gio_bat_dau',
        'gio_ket_thuc',
        'tiet_bat_dau',
        'tiet_ket_thuc',
        'buoi_hoc',
        'thu_trong_tuan',
        'buoi_so',
        'phong_hoc',
        'hinh_thuc',
        'link_online',
        'nen_tang',
        'meeting_id',
        'mat_khau_cuoc_hop',
        'trang_thai',
        'ghi_chu',
        'bao_cao_giang_vien',
        'thoi_gian_bao_cao',
        'trang_thai_bao_cao',
    ];

    protected $casts = [
        'ngay_hoc' => 'date',
        'tiet_bat_dau' => 'integer',
        'tiet_ket_thuc' => 'integer',
        'thoi_gian_bao_cao' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static $thuLabels = [
        2 => 'Thứ 2',
        3 => 'Thứ 3',
        4 => 'Thứ 4',
        5 => 'Thứ 5',
        6 => 'Thứ 6',
        7 => 'Thứ 7',
        8 => 'Chủ nhật',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $schedule) {
            $hasExplicitPeriods = $schedule->tiet_bat_dau !== null && $schedule->tiet_ket_thuc !== null;
            $shouldCanonicalizeTimes = $hasExplicitPeriods || blank($schedule->gio_bat_dau) || blank($schedule->gio_ket_thuc);

            $range = $hasExplicitPeriods
                ? TeachingPeriodCatalog::normalizeRange((int) $schedule->tiet_bat_dau, (int) $schedule->tiet_ket_thuc)
                : null;

            if ($range === null && filled($schedule->buoi_hoc)) {
                $range = TeachingPeriodCatalog::normalizeRange(null, null, $schedule->buoi_hoc);
            }

            if ($range === null) {
                $range = TeachingPeriodCatalog::periodsFromTimes(
                    substr((string) $schedule->gio_bat_dau, 0, 5) ?: null,
                    substr((string) $schedule->gio_ket_thuc, 0, 5) ?: null,
                );
            }

            if ($range !== null) {
                $schedule->tiet_bat_dau = $range['start'];
                $schedule->tiet_ket_thuc = $range['end'];
                $schedule->buoi_hoc = $range['session'];

                if ($shouldCanonicalizeTimes) {
                    $times = TeachingPeriodCatalog::timeRangeFromPeriods($range['start'], $range['end']);
                    $schedule->gio_bat_dau = $times['start_time'];
                    $schedule->gio_ket_thuc = $times['end_time'];
                }
            }

            if ($schedule->ngay_hoc instanceof Carbon) {
                $schedule->thu_trong_tuan = $schedule->ngay_hoc->dayOfWeek === Carbon::SUNDAY
                    ? 8
                    : ($schedule->ngay_hoc->dayOfWeek + 1);
            } elseif (filled($schedule->ngay_hoc)) {
                $date = Carbon::parse((string) $schedule->ngay_hoc);
                $schedule->thu_trong_tuan = $date->dayOfWeek === Carbon::SUNDAY ? 8 : ($date->dayOfWeek + 1);
            }
        });

        static::saved(function (self $schedule) {
            app(LearningProgressStatusService::class)->syncFromSchedule($schedule);
        });

        static::deleted(function (self $schedule) {
            app(LearningProgressStatusService::class)->syncFromSchedule($schedule);
        });
    }

    public function taiNguyen(): HasMany
    {
        return $this->hasMany(TaiNguyenBuoiHoc::class, 'lich_hoc_id');
    }

    public function baiGiangs(): HasMany
    {
        return $this->hasMany(BaiGiang::class, 'lich_hoc_id');
    }

    public function baiKiemTras(): HasMany
    {
        return $this->hasMany(BaiKiemTra::class, 'lich_hoc_id');
    }

    public function diemDanhs(): HasMany
    {
        return $this->hasMany(DiemDanh::class, 'lich_hoc_id');
    }

    public function teacherAttendanceLogs(): HasMany
    {
        return $this->hasMany(DiemDanhGiangVien::class, 'lich_hoc_id')
            ->orderByDesc('created_at');
    }

    public function teacherLeaveRequests(): HasMany
    {
        return $this->hasMany(GiangVienDonXinNghi::class, 'lich_hoc_id')
            ->orderByDesc('created_at');
    }

    public function phongHocLives(): HasManyThrough
    {
        return $this->hasManyThrough(
            PhongHocLive::class,
            BaiGiang::class,
            'lich_hoc_id',
            'bai_giang_id',
            'id',
            'id'
        );
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

    public function getThuLabelAttribute(): string
    {
        return self::$thuLabels[$this->thu_trong_tuan] ?? '-';
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

    public function getStartsAtAttribute(): ?Carbon
    {
        if (!$this->ngay_hoc || blank($this->gio_bat_dau)) {
            return null;
        }

        return $this->ngay_hoc->copy()->setTimeFromTimeString((string) $this->gio_bat_dau);
    }

    public function getEndsAtAttribute(): ?Carbon
    {
        if (!$this->ngay_hoc || blank($this->gio_ket_thuc)) {
            return null;
        }

        return $this->ngay_hoc->copy()->setTimeFromTimeString((string) $this->gio_ket_thuc);
    }

    public function getTimelineTrangThaiAttribute(): string
    {
        if ($this->trang_thai === 'huy') {
            return 'huy';
        }

        if ($this->trang_thai === 'hoan_thanh') {
            return 'hoan_thanh';
        }

        $startsAt = $this->starts_at;
        $endsAt = $this->ends_at;

        if ($startsAt && $endsAt) {
            $now = now();

            if ($now->greaterThan($endsAt)) {
                return 'hoan_thanh';
            }

            if ($now->greaterThanOrEqualTo($startsAt) && $now->lessThanOrEqualTo($endsAt)) {
                return 'dang_hoc';
            }
        }

        return $this->trang_thai === 'dang_hoc' ? 'dang_hoc' : 'cho';
    }

    public function getIsUpcomingAttribute(): bool
    {
        return $this->timeline_trang_thai === 'cho'
            && $this->starts_at
            && $this->starts_at->isFuture();
    }

    public function getIsInProgressAttribute(): bool
    {
        return $this->timeline_trang_thai === 'dang_hoc';
    }

    public function getIsEndedAttribute(): bool
    {
        return $this->timeline_trang_thai === 'hoan_thanh';
    }

    public function getTrangThaiLabelAttribute(): string
    {
        return match ($this->timeline_trang_thai) {
            'cho' => 'Chờ',
            'dang_hoc' => 'Đang học',
            'hoan_thanh' => 'Hoàn thành',
            'huy' => 'Đã hủy',
            default => '-',
        };
    }

    public function getTrangThaiColorAttribute(): string
    {
        return match ($this->timeline_trang_thai) {
            'cho' => 'secondary',
            'dang_hoc' => 'primary',
            'hoan_thanh' => 'success',
            'huy' => 'danger',
            default => 'secondary',
        };
    }

    public function getHinhThucLabelAttribute(): string
    {
        return match ($this->hinh_thuc) {
            'online' => 'Trực tuyến (Online)',
            'truc_tiep' => 'Trực tiếp tại lớp',
            default => 'Chưa cập nhật',
        };
    }

    public function getHinhThucColorAttribute(): string
    {
        return match ($this->hinh_thuc) {
            'online' => 'info',
            'truc_tiep' => 'success',
            default => 'secondary',
        };
    }

    public function getNenTangLabelAttribute(): string
    {
        if ($phongHocLive = $this->studentLiveRoom) {
            return $phongHocLive->platform_label;
        }

        $nenTang = $this->getLegacyNenTang();

        return filled($nenTang) ? $nenTang : 'Chưa cập nhật';
    }

    public function getCanJoinOnlineAttribute(): bool
    {
        if ($phongHocLive = $this->studentLiveRoom) {
            return $phongHocLive->can_student_join;
        }

        $linkOnline = $this->getLegacyOnlineLink();

        if ($this->hinh_thuc !== 'online' || blank($linkOnline) || $this->trang_thai === 'huy') {
            return false;
        }

        $startsAt = $this->starts_at;
        $endsAt = $this->ends_at;

        if (!$startsAt || !$endsAt) {
            return $this->trang_thai === 'dang_hoc';
        }

        $joinOpensAt = $startsAt->copy()->subMinutes(self::ONLINE_JOIN_EARLY_MINUTES);
        $now = now();

        return $now->greaterThanOrEqualTo($joinOpensAt)
            && $now->lessThanOrEqualTo($endsAt);
    }

    public function getCanOpenOnlineRoomAttribute(): bool
    {
        return $this->studentLiveRoom !== null || $this->can_join_online;
    }

    public function getOnlineEntryUrlAttribute(): ?string
    {
        if ($baiGiangLive = $this->studentLiveLecture) {
            return route('hoc-vien.live-room.show', $baiGiangLive->id);
        }

        return $this->getLegacyOnlineLink();
    }

    public function getOnlineEntryTargetBlankAttribute(): bool
    {
        return $this->studentLiveRoom === null && filled($this->getLegacyOnlineLink());
    }

    public function getOnlineEntryLabelAttribute(): string
    {
        if ($phongHocLive = $this->studentLiveRoom) {
            return $phongHocLive->can_student_join ? 'Vao phong live' : 'Xem phong live';
        }

        return 'Vao phong hoc';
    }

    public function getOnlineJoinStateLabelAttribute(): string
    {
        if ($phongHocLive = $this->studentLiveRoom) {
            return $phongHocLive->timeline_trang_thai_label;
        }

        if ($this->hinh_thuc !== 'online') {
            return 'Khong ap dung';
        }

        if (blank($this->getLegacyOnlineLink())) {
            return 'Chua co link';
        }

        if ($this->can_join_online) {
            return 'Co the vao lop';
        }

        return match ($this->timeline_trang_thai) {
            'dang_hoc' => 'Co the vao lop',
            'cho' => 'Chua toi gio',
            'hoan_thanh' => 'Da ket thuc',
            'huy' => 'Da huy',
            default => 'Chua the vao lop',
        };
    }

    public function getOnlineJoinStateColorAttribute(): string
    {
        if ($phongHocLive = $this->studentLiveRoom) {
            return $phongHocLive->timeline_trang_thai_color;
        }

        if ($this->hinh_thuc !== 'online') {
            return 'secondary';
        }

        if (blank($this->getLegacyOnlineLink())) {
            return 'warning';
        }

        if ($this->can_join_online) {
            return 'info';
        }

        return match ($this->timeline_trang_thai) {
            'dang_hoc' => 'info',
            'cho' => 'secondary',
            'hoan_thanh' => 'success',
            'huy' => 'danger',
            default => 'secondary',
        };
    }

    public function getOnlineJoinMessageAttribute(): string
    {
        if ($phongHocLive = $this->studentLiveRoom) {
            return $phongHocLive->status_hint;
        }

        if ($this->hinh_thuc !== 'online') {
            return 'Buoi hoc nay dien ra truc tiep tai lop.';
        }

        if (blank($this->getLegacyOnlineLink())) {
            return 'Giang vien chua cap nhat link phong hoc online cho buoi nay.';
        }

        if ($this->can_join_online) {
            if ($this->starts_at && now()->lt($this->starts_at)) {
                return 'Phong hoc online da mo som de ban chuan bi truoc buoi hoc.';
            }

            return 'Buoi hoc online dang dien ra. Ban co the vao phong hoc ngay bay gio.';
        }

        return match ($this->timeline_trang_thai) {
            'dang_hoc' => 'Buoi hoc online dang dien ra nhung ban chua the vao phong hoc luc nay.',
            'cho' => 'Phong hoc se mo truoc gio bat dau khoang ' . self::ONLINE_JOIN_EARLY_MINUTES . ' phut.',
            'hoan_thanh' => 'Buoi hoc online nay da hoan thanh, phong hoc khong con mo cho hoc vien.',
            'huy' => 'Buoi hoc online nay da bi huy. Vui long theo doi thong bao moi tu giang vien hoac trung tam.',
            default => 'Hien chua du dieu kien de vao phong hoc online.',
        };
    }

    public function getMeetingIdAttribute($value): ?string
    {
        if ($phongHocLive = $this->studentLiveRoom) {
            return $phongHocLive->du_lieu_nen_tang_json['meeting_id']
                ?? $phongHocLive->du_lieu_nen_tang_json['meeting_code']
                ?? $value;
        }

        return $value;
    }

    public function getMatKhauCuocHopAttribute($value): ?string
    {
        if ($phongHocLive = $this->studentLiveRoom) {
            return $phongHocLive->du_lieu_nen_tang_json['passcode'] ?? $value;
        }

        return $value;
    }

    public function getStudentLiveLectureAttribute(): ?BaiGiang
    {
        if ($this->relationLoaded('baiGiangs')) {
            return $this->baiGiangs
                ->first(fn (BaiGiang $baiGiang) => $this->isStudentVisibleLiveLecture($baiGiang));
        }

        return $this->baiGiangs()
            ->with('phongHocLive')
            ->where('loai_bai_giang', BaiGiang::TYPE_LIVE)
            ->where('trang_thai_duyet', BaiGiang::STATUS_DUYET_DA_DUYET)
            ->where('trang_thai_cong_bo', BaiGiang::CONG_BO_DA_CONG_BO)
            ->whereHas('phongHocLive', function ($query) {
                $query->where('trang_thai_duyet', PhongHocLive::APPROVAL_DA_DUYET)
                    ->where('trang_thai_cong_bo', PhongHocLive::PUBLISH_DA_CONG_BO);
            })
            ->orderBy('thu_tu_hien_thi')
            ->first();
    }

    public function getStudentLiveRoomAttribute(): ?PhongHocLive
    {
        return $this->studentLiveLecture?->phongHocLive;
    }

    public function getTeacherAttendanceLogAttribute(): ?DiemDanhGiangVien
    {
        if ($this->relationLoaded('teacherAttendanceLogs')) {
            return $this->teacherAttendanceLogs->first();
        }

        return $this->teacherAttendanceLogs()->first();
    }

    public function getAssignedTeacherAttribute(): ?GiangVien
    {
        if ($this->relationLoaded('giangVien') && $this->giangVien) {
            return $this->giangVien;
        }

        if ($this->relationLoaded('moduleHoc') && $this->moduleHoc?->relationLoaded('phanCongGiangViens')) {
            $assignment = $this->moduleHoc->phanCongGiangViens
                ->first(function (PhanCongModuleGiangVien $item) {
                    return (int) $item->khoa_hoc_id === (int) $this->khoa_hoc_id
                        && $item->trang_thai === 'da_nhan';
                });

            return $assignment?->giangVien;
        }

        if ($this->giang_vien_id) {
            return $this->giangVien()->with('nguoiDung')->first();
        }

        return PhanCongModuleGiangVien::query()
            ->with('giangVien.nguoiDung')
            ->where('khoa_hoc_id', $this->khoa_hoc_id)
            ->where('module_hoc_id', $this->module_hoc_id)
            ->where('trang_thai', 'da_nhan')
            ->latest('id')
            ->first()?->giangVien;
    }

    private function isStudentVisibleLiveLecture(BaiGiang $baiGiang): bool
    {
        return $baiGiang->isLive()
            && $baiGiang->trang_thai_duyet === BaiGiang::STATUS_DUYET_DA_DUYET
            && $baiGiang->trang_thai_cong_bo === BaiGiang::CONG_BO_DA_CONG_BO
            && $baiGiang->phongHocLive
            && $baiGiang->phongHocLive->trang_thai_duyet === PhongHocLive::APPROVAL_DA_DUYET
            && $baiGiang->phongHocLive->trang_thai_cong_bo === PhongHocLive::PUBLISH_DA_CONG_BO;
    }

    private function getLegacyOnlineLink(): ?string
    {
        return $this->getRawOriginal('link_online')
            ?: ($this->attributes['link_online'] ?? null);
    }

    private function getLegacyNenTang(): ?string
    {
        return $this->getRawOriginal('nen_tang')
            ?: ($this->attributes['nen_tang'] ?? null);
    }
}

