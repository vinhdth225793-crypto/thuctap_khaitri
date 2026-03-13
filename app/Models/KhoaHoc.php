<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KhoaHoc extends Model
{
    use HasFactory;

    protected $table = 'khoa_hoc';

    protected $fillable = [
        'mon_hoc_id',
        'ma_khoa_hoc',
        'ten_khoa_hoc',
        'mo_ta_ngan',
        'mo_ta_chi_tiet',
        'hinh_anh',
        'cap_do',
        'tong_so_module',
        'trang_thai',
    ];

    protected $casts = [
        'trang_thai' => 'boolean',
        'tong_so_module' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Khóa học thuộc về một môn học
     */
    public function monHoc()
    {
        return $this->belongsTo(MonHoc::class, 'mon_hoc_id');
    }

    /**
     * Relationship: Một khóa học có nhiều module
     */
    public function moduleHocs()
    {
        return $this->hasMany(ModuleHoc::class, 'khoa_hoc_id')->orderBy('thu_tu_module');
    }

    /**
     * Relationship: Một khóa học có nhiều phân công giảng viên
     */
    public function phanCongGiangViens()
    {
        return $this->hasMany(PhanCongModuleGiangVien::class, 'khoa_hoc_id');
    }

    /**
     * Relationship: Lấy danh sách giảng viên được phân công cho khóa học này
     */
    public function giangViens()
    {
        return $this->belongsToMany(GiangVien::class, 'phan_cong_module_giang_vien', 'khoa_hoc_id', 'giao_vien_id')
                    ->withPivot('module_hoc_id', 'trang_thai', 'ghi_chu')
                    ->withTimestamps();
    }

    /**
     * Scope: Lấy những khóa học đang kích hoạt
     */
    public function scopeActive($query)
    {
        return $query->where('trang_thai', 1);
    }

    /**
     * Scope: Tìm kiếm theo tên khóa học
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('ten_khoa_hoc', 'LIKE', "%{$search}%")
                     ->orWhere('ma_khoa_hoc', 'LIKE', "%{$search}%");
    }

    /**
     * Accessor: lấy số module thực tế (không phụ thuộc vào tong_so_module cached)
     * Dùng trong blade: $khoaHoc->so_module_thuc_te
     */
    public function getSoModuleThucTeAttribute(): int
    {
        return $this->module_hocs_count ?? $this->moduleHocs()->count();
    }
}
