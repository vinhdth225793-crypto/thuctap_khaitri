<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveRoomLinkHistory extends Model
{
    use HasFactory;

    protected $table = 'live_room_link_histories';

    protected $fillable = [
        'phong_hoc_live_id',
        'lich_hoc_id',
        'provider',
        'old_url',
        'new_url',
        'updated_by',
        'reason',
        'metadata_json',
    ];

    protected $casts = [
        'metadata_json' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function phongHocLive()
    {
        return $this->belongsTo(PhongHocLive::class, 'phong_hoc_live_id');
    }

    public function lichHoc()
    {
        return $this->belongsTo(LichHoc::class, 'lich_hoc_id');
    }

    public function nguoiCapNhat()
    {
        return $this->belongsTo(NguoiDung::class, 'updated_by', 'ma_nguoi_dung');
    }
}
