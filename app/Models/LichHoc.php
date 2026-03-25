<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    /**
     * Relationship: Một buổi học có nhiều tài nguyên
     */
    public function taiNguyen(): HasMany
    {
        return $this->hasMany(TaiNguyenBuoiHoc::class, 'lich_hoc_id');
    }

    /**
     * Relationship: M?t bu?i h?c c� nhi?u b�i gi?ng
     */
    public function baiGiangs(): HasMany
    {
        return $this->hasMany(BaiGiang::class, 'lich_hoc_id');
    }

    /**
     * Relationship: Một buổi học có nhiều bài kiểm tra
     */
    public function baiKiemTras(): HasMany
    {
        return $this->hasMany(BaiKiemTra::class, 'lich_hoc_id');
    }

    /**
     * Relationship: Một buổi học có nhiều bản ghi điểm danh
     */
    public function diemDanhs(): HasMany
    {
        return $this->hasMany(DiemDanh::class, 'lich_hoc_id');
    }

    protected $casts = [
        'ngay_hoc' => 'date',
        'thoi_gian_bao_cao' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Thuộc về một khóa học
     */
    public function khoaHoc(): BelongsTo
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    /**
     * Relationship: Thuộc về một module học
     */
    public function moduleHoc(): BelongsTo
    {
        return $this->belongsTo(ModuleHoc::class, 'module_hoc_id');
    }

    /**
     * Relationship: Giảng viên phụ trách buổi này
     */
    public function giangVien(): BelongsTo
    {
        return $this->belongsTo(GiangVien::class, 'giang_vien_id');
    }

    /**
     * Nhãn cho các thứ trong tuần
     */
    public static $thuLabels = [
        2 => 'Thứ 2',
        3 => 'Thứ 3',
        4 => 'Thứ 4',
        5 => 'Thứ 5',
        6 => 'Thứ 6',
        7 => 'Thứ 7',
        8 => 'Chủ nhật',
    ];

    /**
     * Accessor: Nhãn thứ trong tuần
     */
    public function getThuLabelAttribute(): string
    {
        return self::$thuLabels[$this->thu_trong_tuan] ?? '─';
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

    /**
     * Accessor: Nhãn trạng thái buổi học
     */
    public function getTrangThaiLabelAttribute(): string
    {
        return match ($this->timeline_trang_thai) {
            'cho' => 'Chờ',
            'dang_hoc' => 'Đang học',
            'hoan_thanh' => 'Hoàn thành',
            'huy' => 'Đã hủy',
            default => '─',
        };
    }

    /**
     * Accessor: Màu sắc trạng thái buổi học
     */
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

    /**
     * Accessor: Nhãn hình thức học
     */
    public function getHinhThucLabelAttribute(): string
    {
        return match ($this->hinh_thuc) {
            'online' => 'Online',
            'truc_tiep' => 'Trực tiếp',
            default => 'Chưa cập nhật',
        };
    }

    /**
     * Accessor: Màu hiển thị cho hình thức học
     */
    public function getHinhThucColorAttribute(): string
    {
        return match ($this->hinh_thuc) {
            'online' => 'info',
            'truc_tiep' => 'success',
            default => 'secondary',
        };
    }

    /**
     * Accessor: Tên nền tảng học online
     */
    public function getNenTangLabelAttribute(): string
    {
        return filled($this->nen_tang) ? $this->nen_tang : 'Chưa cập nhật';
    }

    /**
     * Accessor: Kiểm tra học viên có thể vào lớp online hay không
     */
    public function getCanJoinOnlineAttribute(): bool
    {
        if ($this->hinh_thuc !== 'online' || blank($this->link_online) || $this->trang_thai === 'huy') {
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

    /**
     * Accessor: Nhãn trạng thái truy cập lớp online
     */
    public function getOnlineJoinStateLabelAttribute(): string
    {
        if ($this->hinh_thuc !== 'online') {
            return 'Không áp dụng';
        }

        if (blank($this->link_online)) {
            return 'Chưa có link';
        }

        if ($this->can_join_online) {
            return 'Có thể vào lớp';
        }

        return match ($this->timeline_trang_thai) {
            'dang_hoc' => 'Có thể vào lớp',
            'cho' => 'Chưa tới giờ',
            'hoan_thanh' => 'Đã kết thúc',
            'huy' => 'Đã hủy',
            default => 'Chưa thể vào lớp',
        };
    }

    /**
     * Accessor: Màu trạng thái truy cập lớp online
     */
    public function getOnlineJoinStateColorAttribute(): string
    {
        if ($this->hinh_thuc !== 'online') {
            return 'secondary';
        }

        if (blank($this->link_online)) {
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

    /**
     * Accessor: Thông điệp hướng dẫn cho học viên khi vào lớp online
     */
    public function getOnlineJoinMessageAttribute(): string
    {
        if ($this->hinh_thuc !== 'online') {
            return 'Buổi học này diễn ra trực tiếp tại lớp.';
        }

        if (blank($this->link_online)) {
            return 'Giảng viên chưa cập nhật link phòng học online cho buổi này.';
        }

        if ($this->can_join_online) {
            if ($this->starts_at && now()->lt($this->starts_at)) {
                return 'Phòng học online đã mở sớm để bạn chuẩn bị trước buổi học.';
            }

            return 'Buổi học online đang diễn ra. Bạn có thể vào phòng học ngay bây giờ.';
        }

        return match ($this->timeline_trang_thai) {
            'dang_hoc' => 'Buổi học online đang diễn ra nhưng bạn chưa thể vào phòng học lúc này.',
            'cho' => 'Phòng học sẽ mở trước giờ bắt đầu khoảng ' . self::ONLINE_JOIN_EARLY_MINUTES . ' phút.',
            'hoan_thanh' => 'Buổi học online này đã hoàn thành, phòng học không còn mở cho học viên.',
            'huy' => 'Buổi học online này đã bị hủy. Vui lòng theo dõi thông báo mới từ giảng viên hoặc trung tâm.',
            default => 'Hiện chưa đủ điều kiện để vào phòng học online.',
        };
    }
}
