<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ThongBao extends Model
{
    use HasFactory;

    protected $table    = 'thong_bao';
    protected $fillable = ['nguoi_nhan_id','tieu_de','noi_dung','loai','url','da_doc'];

    public function nguoiNhan()
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_nhan_id', 'ma_nguoi_dung');
    }

    /**
     * Scope: Lấy thông báo chưa đọc
     */
    public function scopeChuaDoc($q) 
    { 
        return $q->where('da_doc', 0); 
    }
}
