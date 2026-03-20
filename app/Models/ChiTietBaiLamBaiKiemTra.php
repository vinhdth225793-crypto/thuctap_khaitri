<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChiTietBaiLamBaiKiemTra extends Model
{
    use HasFactory;

    protected $table = 'chi_tiet_bai_lam_bai_kiem_tra';

    protected $fillable = [
        'bai_lam_bai_kiem_tra_id',
        'chi_tiet_bai_kiem_tra_id',
        'ngan_hang_cau_hoi_id',
        'dap_an_cau_hoi_id',
        'cau_tra_loi_text',
        'is_dung',
        'diem_tu_dong',
        'diem_tu_luan',
        'nhan_xet',
    ];

    protected $casts = [
        'is_dung' => 'boolean',
        'diem_tu_dong' => 'decimal:2',
        'diem_tu_luan' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function baiLam(): BelongsTo
    {
        return $this->belongsTo(BaiLamBaiKiemTra::class, 'bai_lam_bai_kiem_tra_id');
    }

    public function chiTietBaiKiemTra(): BelongsTo
    {
        return $this->belongsTo(ChiTietBaiKiemTra::class, 'chi_tiet_bai_kiem_tra_id');
    }

    public function cauHoi(): BelongsTo
    {
        return $this->belongsTo(NganHangCauHoi::class, 'ngan_hang_cau_hoi_id');
    }

    public function dapAn(): BelongsTo
    {
        return $this->belongsTo(DapAnCauHoi::class, 'dap_an_cau_hoi_id');
    }

    public function getTongDiemAttribute(): float
    {
        return (float) ($this->diem_tu_luan ?? $this->diem_tu_dong ?? 0);
    }
}
