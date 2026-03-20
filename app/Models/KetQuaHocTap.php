<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KetQuaHocTap extends Model
{
    use HasFactory;

    protected $table = 'ket_qua_hoc_tap';

    protected $fillable = [
        'khoa_hoc_id',
        'hoc_vien_id',
        'phuong_thuc_danh_gia',
        'diem_diem_danh',
        'diem_kiem_tra',
        'diem_tong_ket',
        'tong_so_buoi',
        'so_buoi_tham_du',
        'ty_le_tham_du',
        'so_bai_kiem_tra_hoan_thanh',
        'chi_tiet',
        'cap_nhat_luc',
    ];

    protected $casts = [
        'diem_diem_danh' => 'decimal:2',
        'diem_kiem_tra' => 'decimal:2',
        'diem_tong_ket' => 'decimal:2',
        'ty_le_tham_du' => 'decimal:2',
        'chi_tiet' => 'array',
        'cap_nhat_luc' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function khoaHoc(): BelongsTo
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    public function hocVien(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'hoc_vien_id', 'ma_nguoi_dung');
    }
}
