<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ModuleHoc extends Model
{
    use HasFactory;

    public const LEARNING_STATUS_CHUA_BAT_DAU = 'chua_bat_dau';
    public const LEARNING_STATUS_DANG_DIEN_RA = 'dang_dien_ra';
    public const LEARNING_STATUS_HOAN_THANH = 'hoan_thanh';

    protected $table = 'module_hoc';

    protected ?array $learningProgressSnapshotCache = null;

    protected $fillable = [
        'khoa_hoc_id',
        'ma_module',
        'ten_module',
        'mo_ta',
        'thu_tu_module',
        'thoi_luong_du_kien',
        'so_buoi',
        'trang_thai',
    ];

    protected $casts = [
        'trang_thai' => 'boolean',
        'thu_tu_module' => 'integer',
        'thoi_luong_du_kien' => 'integer',
        'so_buoi' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Module thuộc về một khóa học
     */
    public function khoaHoc()
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    /**
     * Relationship: Một module có nhiều phân công giảng viên
     */
    public function phanCongGiangViens()
    {
        return $this->hasMany(PhanCongModuleGiangVien::class, 'module_hoc_id');
    }

    /**
     * Relationship: Lấy danh sách giảng viên được phân công dạy module này
     */
    public function giangViens()
    {
        return $this->belongsToMany(GiangVien::class, 'phan_cong_module_giang_vien', 'module_hoc_id', 'giang_vien_id')
                    ->withPivot('khoa_hoc_id', 'trang_thai', 'ghi_chu')
                    ->withTimestamps();
    }

    /**
     * Scope: Lấy những module đang kích hoạt
     */
    public function scopeActive($query)
    {
        return $query->where('trang_thai', 1);
    }

    /**
     * Relationship: Lịch học của module này
     */
    public function lichHocs()
    {
        return $this->hasMany(LichHoc::class, 'module_hoc_id')->orderBy('ngay_hoc');
    }

    public function baiKiemTras()
    {
        return $this->hasMany(BaiKiemTra::class, 'module_hoc_id')->orderByDesc('created_at');
    }

    public function phongHocLives()
    {
        return $this->hasManyThrough(
            PhongHocLive::class,
            BaiGiang::class,
            'module_hoc_id',
            'bai_giang_id',
            'id',
            'id'
        );
    }

    public function nganHangCauHois()
    {
        return $this->hasMany(NganHangCauHoi::class, 'module_hoc_id')->orderByDesc('created_at');
    }

    /**
     * Accessor: Tổng số buổi đã lên lịch thực tế
     */
    public function getSoBuoiDaLenLichAttribute(): int
    {
        return $this->lich_hocs_count ?? $this->lichHocs()->count();
    }

    /**
     * Accessor: Số buổi còn thiếu so với quy định
     */
    public function getSoBuoiConLaiAttribute(): int
    {
        return max(0, $this->so_buoi - $this->so_buoi_da_len_lich);
    }

    /**
     * Scope: Tìm kiếm module theo tên module
     */
    public function forgetLearningProgressSnapshot(): void
    {
        $this->learningProgressSnapshotCache = null;
    }

    public function getLearningProgressSnapshotAttribute(): array
    {
        if ($this->learningProgressSnapshotCache !== null) {
            return $this->learningProgressSnapshotCache;
        }

        $schedules = $this->relationLoaded('lichHocs')
            ? $this->lichHocs
            : $this->lichHocs()->get();

        $cancelledSchedules = $schedules->filter(
            fn (LichHoc $schedule) => $schedule->timeline_trang_thai === 'huy'
        );
        $validSchedules = $schedules->reject(
            fn (LichHoc $schedule) => $schedule->timeline_trang_thai === 'huy'
        )->values();

        $completedSchedules = $validSchedules->filter(
            fn (LichHoc $schedule) => $schedule->timeline_trang_thai === 'hoan_thanh'
        );
        $inProgressSchedules = $validSchedules->filter(
            fn (LichHoc $schedule) => $schedule->timeline_trang_thai === 'dang_hoc'
        );
        $upcomingSchedules = $validSchedules->filter(
            fn (LichHoc $schedule) => $schedule->timeline_trang_thai === 'cho'
        );

        $status = self::LEARNING_STATUS_CHUA_BAT_DAU;
        if ($validSchedules->isNotEmpty() && $completedSchedules->count() === $validSchedules->count()) {
            $status = self::LEARNING_STATUS_HOAN_THANH;
        } elseif ($completedSchedules->isNotEmpty() || $inProgressSchedules->isNotEmpty()) {
            $status = self::LEARNING_STATUS_DANG_DIEN_RA;
        }

        return $this->learningProgressSnapshotCache = [
            'status' => $status,
            'label' => $this->resolveLearningStatusLabel($status),
            'badge' => $this->resolveLearningStatusBadge($status),
            'total_schedules' => $schedules->count(),
            'valid_schedules' => $validSchedules->count(),
            'completed_schedules' => $completedSchedules->count(),
            'in_progress_schedules' => $inProgressSchedules->count(),
            'upcoming_schedules' => $upcomingSchedules->count(),
            'cancelled_schedules' => $cancelledSchedules->count(),
            'remaining_schedules' => max(0, $validSchedules->count() - $completedSchedules->count()),
            'progress_percent' => $validSchedules->count() > 0
                ? (int) round(($completedSchedules->count() / $validSchedules->count()) * 100)
                : 0,
        ];
    }

    public function getTrangThaiHocTapAttribute(): string
    {
        return $this->learning_progress_snapshot['status'];
    }

    public function getTrangThaiHocTapLabelAttribute(): string
    {
        return $this->learning_progress_snapshot['label'];
    }

    public function getTrangThaiHocTapBadgeAttribute(): string
    {
        return $this->learning_progress_snapshot['badge'];
    }

    public function getSoBuoiHopLeAttribute(): int
    {
        return $this->learning_progress_snapshot['valid_schedules'];
    }

    public function getSoBuoiHoanThanhAttribute(): int
    {
        return $this->learning_progress_snapshot['completed_schedules'];
    }

    public function getSoBuoiChuaHoanThanhAttribute(): int
    {
        return $this->learning_progress_snapshot['remaining_schedules'];
    }

    public function getSoBuoiBiHuyAttribute(): int
    {
        return $this->learning_progress_snapshot['cancelled_schedules'];
    }

    public function getProgressTextAttribute(): string
    {
        $snapshot = $this->learning_progress_snapshot;
        return $snapshot['completed_schedules'] . '/' . $snapshot['valid_schedules'] . ' buổi';
    }

    public function getIsHoanThanhAttribute(): bool
    {
        return $this->trang_thai_hoc_tap === self::LEARNING_STATUS_HOAN_THANH;
    }

    private function resolveLearningStatusLabel(string $status): string
    {
        return match ($status) {
            self::LEARNING_STATUS_CHUA_BAT_DAU => 'Chưa bắt đầu',
            self::LEARNING_STATUS_DANG_DIEN_RA => 'Đang diễn ra',
            self::LEARNING_STATUS_HOAN_THANH => 'Đã hoàn thành',
            default => 'Đang cập nhật',
        };
    }

    private function resolveLearningStatusBadge(string $status): string
    {
        return match ($status) {
            self::LEARNING_STATUS_CHUA_BAT_DAU => 'secondary',
            self::LEARNING_STATUS_DANG_DIEN_RA => 'primary',
            self::LEARNING_STATUS_HOAN_THANH => 'success',
            default => 'secondary',
        };
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('ten_module', 'LIKE', "%{$search}%")
                     ->orWhere('ma_module', 'LIKE', "%{$search}%");
    }
}

