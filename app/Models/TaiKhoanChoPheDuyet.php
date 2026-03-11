<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaiKhoanChoPheDuyet extends Model
{
    protected $table = 'tai_khoan_cho_phe_duyet';

    protected $fillable = [
        'ho_ten',
        'email',
        'mat_khau',
        'vai_tro',
        'so_dien_thoai',
        'dia_chi',
        'ngay_sinh',
        'trang_thai'
    ];

    protected $casts = [
        'ngay_sinh' => 'date',
        'trang_thai' => 'string'
    ];
}
