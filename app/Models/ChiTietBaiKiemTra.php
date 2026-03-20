<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChiTietBaiKiemTra extends Model
{
    use HasFactory;

    protected $table = 'chi_tiet_bai_kiem_tra';

    protected $fillable = [
        'bai_kiem_tra_id',
        'ngan_hang_cau_hoi_id',
        'thu_tu',
        'diem_so',
        'bat_buoc',
    ];

    protected $casts = [
        'thu_tu' => 'integer',
        'diem_so' => 'decimal:2',
        'bat_buoc' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function baiKiemTra(): BelongsTo
    {
        return $this->belongsTo(BaiKiemTra::class, 'bai_kiem_tra_id');
    }

    public function cauHoi(): BelongsTo
    {
        return $this->belongsTo(NganHangCauHoi::class, 'ngan_hang_cau_hoi_id');
    }

    public function chiTietBaiLams(): HasMany
    {
        return $this->hasMany(ChiTietBaiLamBaiKiemTra::class, 'chi_tiet_bai_kiem_tra_id');
    }
}
