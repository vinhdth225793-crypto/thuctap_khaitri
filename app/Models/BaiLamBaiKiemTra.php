<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BaiLamBaiKiemTra extends Model
{
    use HasFactory;

    protected $table = 'bai_lam_bai_kiem_tra';

    protected $fillable = [
        'bai_kiem_tra_id',
        'hoc_vien_id',
        'dia_chi_ip',
        'user_agent',
        'precheck_data',
        'precheck_completed_at',
        'lan_lam_thu',
        'noi_dung_bai_lam',
        'trang_thai',
        'bat_dau_luc',
        'nop_luc',
        'diem_so',
        'tong_diem_trac_nghiem',
        'tong_diem_tu_luan',
        'trang_thai_cham',
        'auto_graded_at',
        'manual_graded_at',
        'nguoi_cham_id',
        'nhan_xet',
        'tong_so_vi_pham',
        'trang_thai_giam_sat',
        'da_tu_dong_nop',
        'ghi_chu_giam_sat',
        'nguoi_hau_kiem_id',
        'hau_kiem_luc',
    ];

    protected $casts = [
        'lan_lam_thu' => 'integer',
        'precheck_data' => 'array',
        'precheck_completed_at' => 'datetime',
        'bat_dau_luc' => 'datetime',
        'nop_luc' => 'datetime',
        'diem_so' => 'decimal:2',
        'tong_diem_trac_nghiem' => 'decimal:2',
        'tong_diem_tu_luan' => 'decimal:2',
        'auto_graded_at' => 'datetime',
        'manual_graded_at' => 'datetime',
        'tong_so_vi_pham' => 'integer',
        'da_tu_dong_nop' => 'boolean',
        'hau_kiem_luc' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function baiKiemTra(): BelongsTo
    {
        return $this->belongsTo(BaiKiemTra::class, 'bai_kiem_tra_id');
    }

    public function hocVien(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'hoc_vien_id', 'ma_nguoi_dung');
    }

    public function nguoiCham(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_cham_id', 'ma_nguoi_dung');
    }

    public function nguoiHauKiem(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_hau_kiem_id', 'ma_nguoi_dung');
    }

    public function chiTietTraLois(): HasMany
    {
        return $this->hasMany(ChiTietBaiLamBaiKiemTra::class, 'bai_lam_bai_kiem_tra_id');
    }

    public function giamSatLogs(): HasMany
    {
        return $this->hasMany(BaiLamViPhamGiamSat::class, 'bai_lam_bai_kiem_tra_id')->latest('created_at');
    }

    public function giamSatSnapshots(): HasMany
    {
        return $this->hasMany(BaiLamSnapshotGiamSat::class, 'bai_lam_bai_kiem_tra_id')->latest('captured_at');
    }

    public function getTrangThaiLabelAttribute(): string
    {
        return match ($this->trang_thai) {
            'dang_lam' => 'Đang làm',
            'da_nop' => 'Đã nộp',
            'cho_cham' => 'Chờ chấm',
            'da_cham' => 'Đã chấm',
            default => 'Chưa xác định',
        };
    }

    public function getTrangThaiColorAttribute(): string
    {
        return match ($this->trang_thai) {
            'dang_lam' => 'warning',
            'da_nop' => 'success',
            'cho_cham' => 'info',
            'da_cham' => 'primary',
            default => 'secondary',
        };
    }

    public function getIsSubmittedAttribute(): bool
    {
        return in_array($this->trang_thai, ['da_nop', 'cho_cham', 'da_cham'], true) && $this->nop_luc !== null;
    }

    public function getCanResumeAttribute(): bool
    {
        return $this->trang_thai === 'dang_lam' && !$this->is_submitted;
    }

    public function getNeedManualGradingAttribute(): bool
    {
        return $this->trang_thai_cham === 'cho_cham';
    }

    public function getTrangThaiGiamSatLabelAttribute(): string
    {
        return match ($this->trang_thai_giam_sat) {
            'binh_thuong' => 'Bình thường',
            'can_xem_xet' => 'Cần xem xét',
            'da_xac_nhan' => 'Đã xác nhận',
            'nghi_ngo' => 'Nghi ngờ',
            default => 'Không áp dụng',
        };
    }

    public function getTrangThaiGiamSatColorAttribute(): string
    {
        return match ($this->trang_thai_giam_sat) {
            'binh_thuong' => 'success',
            'can_xem_xet' => 'warning',
            'da_xac_nhan' => 'info',
            'nghi_ngo' => 'danger',
            default => 'secondary',
        };
    }

    public function getCanReviewSurveillanceAttribute(): bool
    {
        return $this->baiKiemTra?->co_giam_sat ?? false;
    }
}
