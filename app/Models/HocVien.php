<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HocVien extends Model
{
    use HasFactory;

    protected $table = 'hoc_vien';

    protected $fillable = [
        'nguoi_dung_id',
        'ma_hoc_vien',
        'lop_niem_khoa',
        'nganh_hoc',
        'diem_trung_binh',
    ];

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id');
    }

    public function diemDanhs(): HasMany
    {
        return $this->hasMany(DiemDanhHocVien::class, 'hoc_vien_id');
    }

    public function ketQuaHocTaps(): HasMany
    {
        return $this->hasMany(KetQuaHocTap::class, 'hoc_vien_id');
    }
}
