<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhongHocLiveBanGhi extends Model
{
    use HasFactory;

    protected $table = 'phong_hoc_live_ban_ghi';

    protected $fillable = [
        'phong_hoc_live_id',
        'nguon_ban_ghi',
        'tieu_de',
        'duong_dan_file',
        'link_ngoai',
        'thoi_luong',
        'trang_thai',
    ];

    protected $casts = [
        'thoi_luong' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function phongHocLive()
    {
        return $this->belongsTo(PhongHocLive::class, 'phong_hoc_live_id');
    }
}
