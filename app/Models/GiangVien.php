<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GiangVien extends Model
{
    use HasFactory;

    protected $table = 'giang_vien';

    protected $fillable = [
        'nguoi_dung_id',
        'chuyen_nganh',
        'hoc_vi',
        'mo_ta_ngan',
        'avatar_url',
        'hien_thi_trang_chu',
    ];

    protected $casts = [
        'hien_thi_trang_chu' => 'boolean',
    ];

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id');
    }

    public function phanCongs(): HasMany
    {
        return $this->hasMany(PhanCongGiangVien::class, 'giang_vien_id');
    }

    public function lichHocs(): HasMany
    {
        return $this->hasMany(LichHoc::class, 'giang_vien_id');
    }
}
