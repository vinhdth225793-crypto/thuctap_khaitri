<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HocVien extends Model
{
    protected $table = 'hoc_vien';

    protected $fillable = [
        'nguoi_dung_id',
        'lop',
        'nganh',
        'diem_trung_binh',
    ];

    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id', 'ma_nguoi_dung');
    }
}
