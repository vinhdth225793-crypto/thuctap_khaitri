<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id', 'ma_nguoi_dung');
    }

    /**
     * Relationship: Mot giang vien co nhieu phan cong day module
     */
    public function phanCongModules(): HasMany
    {
        return $this->hasMany(PhanCongModuleGiangVien::class, 'giang_vien_id');
    }

    /**
     * Relationship: Lay danh sach module ma giang vien duoc phan cong day
     */
    public function modulesDuocPhanCong(): BelongsToMany
    {
        return $this->belongsToMany(ModuleHoc::class, 'phan_cong_module_giang_vien', 'giang_vien_id', 'module_hoc_id')
                    ->withPivot('khoa_hoc_id', 'trang_thai', 'ghi_chu')
                    ->withTimestamps();
    }

    /**
     * Relationship: Lay danh sach khoa hoc ma giang vien duoc phan cong day
     */
    public function khoaHocDuocPhanCong(): BelongsToMany
    {
        return $this->belongsToMany(KhoaHoc::class, 'phan_cong_module_giang_vien', 'giang_vien_id', 'khoa_hoc_id')
                    ->withPivot('module_hoc_id', 'trang_thai', 'ghi_chu')
                    ->withTimestamps();
    }

    public function lichHocs(): HasMany
    {
        return $this->hasMany(LichHoc::class, 'giang_vien_id')
            ->orderBy('ngay_hoc')
            ->orderBy('gio_bat_dau');
    }

    public function donXinNghis(): HasMany
    {
        return $this->hasMany(GiangVienDonXinNghi::class, 'giang_vien_id')
            ->orderByDesc('created_at');
    }

    public function teacherAttendanceLogs(): HasMany
    {
        return $this->hasMany(DiemDanhGiangVien::class, 'giang_vien_id')
            ->orderByDesc('created_at');
    }

    /**
     * Scope untuk lay giang vien hien thi tren trang chu
     */
    public function scopeHienThiTrangChu($query)
    {
        return $query->where('hien_thi_trang_chu', true)
                    ->whereHas('nguoiDung', function($q) {
                        $q->where('trang_thai', true);
                    });
    }
}
