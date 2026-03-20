<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BaiLamBaiKiemTra extends Model
{
    use HasFactory;

    protected $table = 'bai_lam_bai_kiem_tra';

    protected $fillable = [
        'bai_kiem_tra_id',
        'hoc_vien_id',
        'noi_dung_bai_lam',
        'trang_thai',
        'bat_dau_luc',
        'nop_luc',
        'diem_so',
        'nhan_xet',
    ];

    protected $casts = [
        'bat_dau_luc' => 'datetime',
        'nop_luc' => 'datetime',
        'diem_so' => 'decimal:2',
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

    public function getTrangThaiLabelAttribute(): string
    {
        return match ($this->trang_thai) {
            'dang_lam' => 'Dang lam',
            'da_nop' => 'Da nop',
            default => 'Chua xac dinh',
        };
    }

    public function getTrangThaiColorAttribute(): string
    {
        return match ($this->trang_thai) {
            'dang_lam' => 'warning',
            'da_nop' => 'success',
            default => 'secondary',
        };
    }

    public function getIsSubmittedAttribute(): bool
    {
        return $this->trang_thai === 'da_nop' && $this->nop_luc !== null;
    }
}
