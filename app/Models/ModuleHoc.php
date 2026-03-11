<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ModuleHoc extends Model
{
    use HasFactory;

    protected $table = 'module_hoc';

    protected $fillable = [
        'khoa_hoc_id',
        'ma_module',
        'ten_module',
        'mo_ta',
        'thu_tu_module',
        'thoi_luong_du_kien',
        'trang_thai',
    ];

    protected $casts = [
        'trang_thai' => 'boolean',
        'thu_tu_module' => 'integer',
        'thoi_luong_du_kien' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Module thuộc về một khóa học
     */
    public function khoaHoc()
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    /**
     * Relationship: Một module có nhiều phân công giảng viên
     */
    public function phanCongGiangViens()
    {
        return $this->hasMany(PhanCongModuleGiangVien::class, 'module_hoc_id');
    }

    /**
     * Relationship: Lấy danh sách giảng viên được phân công dạy module này
     */
    public function giangViens()
    {
        return $this->belongsToMany(GiangVien::class, 'phan_cong_module_giang_vien', 'module_hoc_id', 'giao_vien_id')
                    ->withPivot('khoa_hoc_id', 'trang_thai', 'ghi_chu')
                    ->withTimestamps();
    }

    /**
     * Scope: Lấy những module đang kích hoạt
     */
    public function scopeActive($query)
    {
        return $query->where('trang_thai', 1);
    }

    /**
     * Scope: Tìm kiếm theo tên module
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('ten_module', 'LIKE', "%{$search}%")
                     ->orWhere('ma_module', 'LIKE', "%{$search}%");
    }
}
