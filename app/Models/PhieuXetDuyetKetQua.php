<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhieuXetDuyetKetQua extends Model
{
    use HasFactory;

    public const PHUONG_AN_FINAL_EXAM_ATTENDANCE = 'final_exam_attendance';
    public const PHUONG_AN_SELECTED_EXAMS_ATTENDANCE = 'selected_exams_attendance';

    public const TRANG_THAI_DRAFT = 'draft';
    public const TRANG_THAI_SUBMITTED = 'submitted';
    public const TRANG_THAI_REVIEWING = 'reviewing';
    public const TRANG_THAI_REJECTED = 'rejected';
    public const TRANG_THAI_APPROVED = 'approved';
    public const TRANG_THAI_FINALIZED = 'finalized';

    protected $table = 'phieu_xet_duyet_ket_qua';

    protected $fillable = [
        'khoa_hoc_id',
        'phan_cong_id',
        'giang_vien_id',
        'nguoi_lap_id',
        'phuong_an',
        'ty_trong_kiem_tra',
        'ty_trong_diem_danh',
        'diem_dat',
        'bai_kiem_tra_ids',
        'cong_thuc',
        'trang_thai',
        'ghi_chu',
        'submitted_at',
        'reviewing_by_id',
        'reviewing_at',
        'approved_by_id',
        'approved_at',
        'rejected_by_id',
        'rejected_at',
        'reject_reason',
        'finalized_by_id',
        'finalized_at',
    ];

    protected $casts = [
        'khoa_hoc_id' => 'integer',
        'phan_cong_id' => 'integer',
        'giang_vien_id' => 'integer',
        'nguoi_lap_id' => 'integer',
        'ty_trong_kiem_tra' => 'decimal:2',
        'ty_trong_diem_danh' => 'decimal:2',
        'diem_dat' => 'decimal:2',
        'bai_kiem_tra_ids' => 'array',
        'cong_thuc' => 'array',
        'submitted_at' => 'datetime',
        'reviewing_by_id' => 'integer',
        'reviewing_at' => 'datetime',
        'approved_by_id' => 'integer',
        'approved_at' => 'datetime',
        'rejected_by_id' => 'integer',
        'rejected_at' => 'datetime',
        'finalized_by_id' => 'integer',
        'finalized_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function khoaHoc(): BelongsTo
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    public function phanCong(): BelongsTo
    {
        return $this->belongsTo(PhanCongModuleGiangVien::class, 'phan_cong_id');
    }

    public function giangVien(): BelongsTo
    {
        return $this->belongsTo(GiangVien::class, 'giang_vien_id');
    }

    public function nguoiLap(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_lap_id', 'ma_nguoi_dung');
    }

    public function reviewingBy(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'reviewing_by_id', 'ma_nguoi_dung');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'approved_by_id', 'ma_nguoi_dung');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'rejected_by_id', 'ma_nguoi_dung');
    }

    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'finalized_by_id', 'ma_nguoi_dung');
    }

    public function chiTiets(): HasMany
    {
        return $this->hasMany(ChiTietPhieuXetDuyetKetQua::class, 'phieu_id');
    }

    public function getPhuongAnLabelAttribute(): string
    {
        return match ($this->phuong_an) {
            self::PHUONG_AN_SELECTED_EXAMS_ATTENDANCE => 'Module/buoi hoc + diem danh',
            default => 'Cuoi khoa + diem danh',
        };
    }

    public function getTrangThaiLabelAttribute(): string
    {
        return match ($this->trang_thai) {
            self::TRANG_THAI_SUBMITTED => 'Da gui admin',
            self::TRANG_THAI_REVIEWING => 'Admin dang xem',
            self::TRANG_THAI_REJECTED => 'Bi tu choi',
            self::TRANG_THAI_APPROVED => 'Da duyet',
            self::TRANG_THAI_FINALIZED => 'Da chot chinh thuc',
            default => 'Ban nhap',
        };
    }

    public function getTrangThaiColorAttribute(): string
    {
        return match ($this->trang_thai) {
            self::TRANG_THAI_SUBMITTED => 'warning',
            self::TRANG_THAI_REVIEWING => 'info',
            self::TRANG_THAI_REJECTED => 'danger',
            self::TRANG_THAI_APPROVED => 'primary',
            self::TRANG_THAI_FINALIZED => 'success',
            default => 'secondary',
        };
    }

    public function getIsEditableAttribute(): bool
    {
        return in_array($this->trang_thai, [
            self::TRANG_THAI_DRAFT,
            self::TRANG_THAI_REJECTED,
        ], true);
    }

    public function getIsFinalizedAttribute(): bool
    {
        return $this->trang_thai === self::TRANG_THAI_FINALIZED;
    }
}
