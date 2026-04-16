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
        'lop',
        'nganh',
        'lop_niem_khoa',
        'nganh_hoc',
        'diem_trung_binh',
    ];

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id', 'ma_nguoi_dung');
    }

    public function diemDanhs(): HasMany
    {
        return $this->hasMany(DiemDanh::class, 'hoc_vien_id', 'nguoi_dung_id');
    }

    public function ketQuaHocTaps(): HasMany
    {
        return $this->hasMany(KetQuaHocTap::class, 'hoc_vien_id', 'nguoi_dung_id');
    }

    public function khoaHocs(): HasMany
    {
        return $this->hasMany(HocVienKhoaHoc::class, 'hoc_vien_id', 'nguoi_dung_id');
    }

    public function yeuCaus(): HasMany
    {
        return $this->hasMany(YeuCauHocVien::class, 'hoc_vien_id', 'nguoi_dung_id');
    }

    public function baiLams(): HasMany
    {
        return $this->hasMany(BaiLamBaiKiemTra::class, 'hoc_vien_id', 'nguoi_dung_id');
    }

    public function getLopNiemKhoaAttribute(): ?string
    {
        return $this->attributes['lop'] ?? null;
    }

    public function setLopNiemKhoaAttribute(mixed $value): void
    {
        $this->attributes['lop'] = $value;
    }

    public function getNganhHocAttribute(): ?string
    {
        return $this->attributes['nganh'] ?? null;
    }

    public function setNganhHocAttribute(mixed $value): void
    {
        $this->attributes['nganh'] = $value;
    }
}
