<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhongHocLiveNguoiThamGia extends Model
{
    use HasFactory;

    protected $table = 'phong_hoc_live_nguoi_tham_gia';

    protected $fillable = [
        'phong_hoc_live_id',
        'nguoi_dung_id',
        'vai_tro',
        'joined_at',
        'left_at',
        'trang_thai',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function phongHocLive()
    {
        return $this->belongsTo(PhongHocLive::class, 'phong_hoc_live_id');
    }

    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id', 'ma_nguoi_dung');
    }
}
