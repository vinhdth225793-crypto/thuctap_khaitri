<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaiNguyenBuoiHoc extends Model
{
    use HasFactory;

    protected $table = 'tai_nguyen_buoi_hoc';

    protected $fillable = [
        'lich_hoc_id',
        'loai_tai_nguyen',
        'tieu_de',
        'mo_ta',
        'duong_dan_file',
        'link_ngoai',
    ];

    public function lichHoc()
    {
        return $this->belongsTo(LichHoc::class, 'lich_hoc_id');
    }
}
