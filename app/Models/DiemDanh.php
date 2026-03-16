<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiemDanh extends Model
{
    use HasFactory;

    protected $table = 'diem_danh';

    protected $fillable = [
        'lich_hoc_id',
        'hoc_vien_id',
        'trang_thai',
        'ghi_chu',
    ];

    public function lichHoc()
    {
        return $this->belongsTo(LichHoc::class, 'lich_hoc_id');
    }

    public function hocVien()
    {
        return $this->belongsTo(HocVien::class, 'hoc_vien_id', 'ma_nguoi_dung');
    }
}
