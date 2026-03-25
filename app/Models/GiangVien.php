<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiangVien extends Model
{
    protected $table = 'giang_vien';

    protected $fillable = [
        'nguoi_dung_id',
        'chuyen_nganh',
        'hoc_vi',
        'so_gio_day',
        'hien_thi_trang_chu',
        'mo_ta_ngan',
        'avatar_url',
    ];

    protected $casts = [
        'hien_thi_trang_chu' => 'boolean',
    ];

    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id', 'ma_nguoi_dung');
    }

    /**
     * Relationship: Một giảng viên có nhiều phân công dạy module
     */
    public function phanCongModules()
    {
        return $this->hasMany(PhanCongModuleGiangVien::class, 'giang_vien_id');
    }

    /**
     * Relationship: Lấy danh sách module mà giảng viên được phân công dạy
     */
    public function modulesDuocPhanCong()
    {
        return $this->belongsToMany(ModuleHoc::class, 'phan_cong_module_giang_vien', 'giang_vien_id', 'module_hoc_id')
                    ->withPivot('khoa_hoc_id', 'trang_thai', 'ghi_chu')
                    ->withTimestamps();
    }

    /**
     * Relationship: Lấy danh sách khóa học mà giảng viên được phân công dạy
     */
    public function khoaHocDuocPhanCong()
    {
        return $this->belongsToMany(KhoaHoc::class, 'phan_cong_module_giang_vien', 'giang_vien_id', 'khoa_hoc_id')
                    ->withPivot('module_hoc_id', 'trang_thai', 'ghi_chu')
                    ->withTimestamps();
    }

    /**
     * Scope untuk lấy giảng viên hiển thị trên trang chủ
     */
    public function scopeHienThiTrangChu($query)
    {
        return $query->where('hien_thi_trang_chu', true)
                    ->whereHas('nguoiDung', function($q) {
                        $q->where('trang_thai', true);
                    });
    }
}

