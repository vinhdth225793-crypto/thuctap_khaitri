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
        'admin_duyet_id',
        'thoi_gian_duyet',
        'phan_hoi_admin'
    ];

    protected $casts = [
        'du_lieu_yeu_cau' => 'array',
        'thoi_gian_duyet' => 'datetime',
    ];

    public function khoaHoc()
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'giang_vien_id');
    }

    public function admin()
    {
        return $this->belongsTo(NguoiDung::class, 'admin_duyet_id', 'ma_nguoi_dung');
    }

    // Accessor hiển thị nhãn loại yêu cầu
    public function getLoaiLabelAttribute()
    {
        return match($this->loai_yeu_cau) {
            'them' => 'Thêm học viên',
            'xoa'  => 'Xóa học viên',
            'sua'  => 'Cập nhật thông tin',
            default => $this->loai_yeu_cau
        };
    }
}
