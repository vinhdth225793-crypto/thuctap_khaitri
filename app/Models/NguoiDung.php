<?php

// app/Models/NguoiDung.php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\GiangVien;
use App\Models\HocVien;

class NguoiDung extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $table = 'nguoi_dung';
    protected $primaryKey = 'ma_nguoi_dung';

    protected $fillable = [
        'ho_ten',
        'email',
        'mat_khau',
        'vai_tro',
        'so_dien_thoai',
        'dia_chi',
        'ngay_sinh',
        'anh_dai_dien',
        'trang_thai'
    ];

    protected $hidden = [
        'mat_khau',
        'remember_token',
    ];

    protected $casts = [
        'email_xac_thuc' => 'datetime',
        'trang_thai' => 'boolean',
        'ngay_sinh' => 'date'
    ];

    public function getAuthPassword()
    {
        return $this->mat_khau;
    }

    // Phương thức kiểm tra vai trò
    public function isAdmin()
    {
        return $this->vai_tro === 'admin';
    }

    public function isGiangVien()
    {
        return $this->vai_tro === 'giang_vien';
    }

    public function isHocVien()
    {
        return $this->vai_tro === 'hoc_vien';
    }

    /**
     * Mối quan hệ một-một tới bảng giảng viên (nếu vai trò là giảng viên)
     */
    public function giangVien()
    {
        return $this->hasOne(GiangVien::class, 'nguoi_dung_id', 'ma_nguoi_dung');
    }

    /**
     * Mối quan hệ một-một tới bảng học viên (nếu vai trò là học viên)
     */
    public function hocVien()
    {
        return $this->hasOne(HocVien::class, 'nguoi_dung_id', 'ma_nguoi_dung');
    }

    /**
     * Relationship: Các khóa học mà người dùng (học viên) tham gia
     */
    public function khoaHocs()
    {
        return $this->belongsToMany(
            KhoaHoc::class,
            'hoc_vien_khoa_hoc',
            'hoc_vien_id',
            'khoa_hoc_id',
            'ma_nguoi_dung',
            'id'
        )->withPivot('ngay_tham_gia', 'trang_thai', 'ghi_chu')->withTimestamps();
    }

    /**
     * Relationship: Dữ liệu điểm danh của học viên (Phase 2 Upgrade)
     */
    public function diemDanhs()
    {
        return $this->hasMany(DiemDanh::class, 'hoc_vien_id', 'ma_nguoi_dung');
    }
}
