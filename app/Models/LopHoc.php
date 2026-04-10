<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LopHoc extends Model
{
    use HasFactory;

    protected $table = 'lop_hoc';

    protected $fillable = [
        'khoa_hoc_id',
        'ma_lop_hoc',
        'ngay_khai_giang',
        'ngay_ket_thuc',
        'trang_thai_van_hanh',
        'ty_trong_diem_danh',
        'ty_trong_kiem_tra',
        'ghi_chu',
        'created_by',
    ];

    protected $casts = [
        'ngay_khai_giang' => 'date',
        'ngay_ket_thuc' => 'date',
    ];

    public function khoaHoc(): BelongsTo
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    public function lichHocs(): HasMany
    {
        return $this->hasMany(LichHoc::class, 'lop_hoc_id');
    }

    public function ketQuaHocTaps(): HasMany
    {
        return $this->hasMany(KetQuaHocTap::class, 'lop_hoc_id');
    }

    public function phanCongGiangViens(): HasMany
    {
        return $this->hasMany(PhanCongGiangVien::class, 'lop_hoc_id');
    }
}
