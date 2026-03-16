<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YeuCauHocVien extends Model
{
    use HasFactory;

    protected $table = 'yeu_cau_hoc_vien';

    protected $fillable = [
        'khoa_hoc_id',
        'giang_vien_id',
        'loai_yeu_cau',
        'du_lieu_yeu_cau',
        'ly_do',
        'trang_thai',
        'phan_hoi_admin',
    ];

    protected $casts = [
        'du_lieu_yeu_cau' => 'array',
    ];

    public function khoaHoc()
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'giang_vien_id');
    }
}
