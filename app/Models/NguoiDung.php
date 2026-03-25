<?php

// app/Models/NguoiDung.php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NguoiDung extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'nguoi_dung';
    protected $primaryKey = 'ma_nguoi_dung';

    protected $fillable = [
        'ho_ten',
        'email',
        'mat_khau',
        'vai_tro',
        'so_dien_thoai',
        'ngay_sinh',
        'dia_chi',
        'anh_dai_dien',
        'trang_thai',
    ];

    protected $hidden = [
        'mat_khau',
        'remember_token',
    ];

    protected $casts = [
        'ngay_sinh' => 'date',
        'trang_thai' => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    /**
     * Ghi đè phương thức lấy mật khẩu của Authenticatable 
     * vì project dùng cột 'mat_khau' thay vì 'password'
     */
    public function getAuthPassword()
    {
        return $this->mat_khau;
    }

    /**
     * Các phương thức kiểm tra vai trò
     */
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
}
