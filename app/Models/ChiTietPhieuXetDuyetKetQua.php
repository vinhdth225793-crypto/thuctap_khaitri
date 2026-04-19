<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChiTietPhieuXetDuyetKetQua extends Model
{
    use HasFactory;

    public const KET_QUA_DAT = 'dat';
    public const KET_QUA_KHONG_DAT = 'khong_dat';
    public const KET_QUA_CHUA_DU = 'chua_du';

    protected $table = 'chi_tiet_phieu_xet_duyet_ket_qua';

    protected $fillable = [
        'phieu_id',
        'hoc_vien_id',
        'tong_so_buoi',
        'so_buoi_tham_du',
        'ty_le_tham_du',
        'diem_chuyen_can',
        'diem_kiem_tra',
        'diem_xet_duyet',
        'ket_qua',
        'chi_tiet_bai_kiem_tra',
        'calculation_metadata',
    ];

    protected $casts = [
        'phieu_id' => 'integer',
        'hoc_vien_id' => 'integer',
        'tong_so_buoi' => 'integer',
        'so_buoi_tham_du' => 'integer',
        'ty_le_tham_du' => 'decimal:2',
        'diem_chuyen_can' => 'decimal:2',
        'diem_kiem_tra' => 'decimal:2',
        'diem_xet_duyet' => 'decimal:2',
        'chi_tiet_bai_kiem_tra' => 'array',
        'calculation_metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function phieu(): BelongsTo
    {
        return $this->belongsTo(PhieuXetDuyetKetQua::class, 'phieu_id');
    }

    public function hocVien(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'hoc_vien_id', 'ma_nguoi_dung');
    }

    public function getKetQuaLabelAttribute(): string
    {
        return match ($this->ket_qua) {
            self::KET_QUA_DAT => 'Dat',
            self::KET_QUA_KHONG_DAT => 'Khong dat',
            default => 'Chua du du lieu',
        };
    }

    public function getKetQuaColorAttribute(): string
    {
        return match ($this->ket_qua) {
            self::KET_QUA_DAT => 'success',
            self::KET_QUA_KHONG_DAT => 'danger',
            default => 'secondary',
        };
    }
}
