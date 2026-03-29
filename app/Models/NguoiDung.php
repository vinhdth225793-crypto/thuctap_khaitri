<?php

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
        'email_xac_thuc' => 'datetime',
    ];

    public function getAuthPassword()
    {
        return $this->mat_khau;
    }

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

    public function giangVien()
    {
        return $this->hasOne(GiangVien::class, 'nguoi_dung_id', 'ma_nguoi_dung');
    }

    public function hocVien()
    {
        return $this->hasOne(HocVien::class, 'nguoi_dung_id', 'ma_nguoi_dung');
    }

    public function khoaHocs()
    {
        return $this->belongsToMany(
            KhoaHoc::class,
            'hoc_vien_khoa_hoc',
            'hoc_vien_id',
            'khoa_hoc_id',
            'ma_nguoi_dung',
            'id'
        )->withPivot('ngay_tham_gia', 'trang_thai', 'ghi_chu', 'created_by')->withTimestamps();
    }

    public function diemDanhs()
    {
        return $this->hasMany(DiemDanh::class, 'hoc_vien_id', 'ma_nguoi_dung');
    }

    public function moderatedPhongHocLives()
    {
        return $this->hasMany(PhongHocLive::class, 'moderator_id', 'ma_nguoi_dung');
    }

    public function assistedPhongHocLives()
    {
        return $this->hasMany(PhongHocLive::class, 'tro_giang_id', 'ma_nguoi_dung');
    }

    public function createdPhongHocLives()
    {
        return $this->hasMany(PhongHocLive::class, 'created_by', 'ma_nguoi_dung');
    }

    public function approvedPhongHocLives()
    {
        return $this->hasMany(PhongHocLive::class, 'approved_by', 'ma_nguoi_dung');
    }

    public function phongHocLiveThamGia()
    {
        return $this->hasMany(PhongHocLiveNguoiThamGia::class, 'nguoi_dung_id', 'ma_nguoi_dung');
    }
}
