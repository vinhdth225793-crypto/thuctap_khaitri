<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaiKiemTra extends Model
{
    use HasFactory;

    protected $table = 'bai_kiem_tra';

    protected $fillable = [
        'khoa_hoc_id',
        'module_hoc_id',
        'lich_hoc_id',
        'tieu_de',
        'mo_ta',
        'thoi_gian_lam_bai',
        'ngay_mo',
        'ngay_dong',
        'pham_vi',
        'trang_thai',
    ];

    protected $casts = [
        'ngay_mo' => 'datetime',
        'ngay_dong' => 'datetime',
        'trang_thai' => 'boolean',
    ];

    public function khoaHoc()
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    public function moduleHoc()
    {
        return $this->belongsTo(ModuleHoc::class, 'module_hoc_id');
    }

    public function lichHoc()
    {
        return $this->belongsTo(LichHoc::class, 'lich_hoc_id');
    }
}
