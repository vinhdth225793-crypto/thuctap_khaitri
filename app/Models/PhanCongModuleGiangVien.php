<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PhanCongModuleGiangVien extends Model
{
    use HasFactory;

    protected $table = 'phan_cong_module_giang_vien';

    protected $fillable = [
        'khoa_hoc_id',
        'module_hoc_id',
        'giao_vien_id',
        'ngay_phan_cong',
        'trang_thai',
        'ghi_chu',
        'created_by',
    ];

    protected $casts = [
        'ngay_phan_cong' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Phân công thuộc về khóa học
     */
    public function khoaHoc()
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    /**
     * Relationship: Phân công thuộc về module học
     */
    public function moduleHoc()
    {
        return $this->belongsTo(ModuleHoc::class, 'module_hoc_id');
    }

    /**
     * Relationship: Phân công thuộc về giảng viên
     */
    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'giao_vien_id');
    }

    /**
     * Relationship: Phân công được tạo bởi người dùng nào
     */
    public function nguoiTao()
    {
        return $this->belongsTo(NguoiDung::class, 'created_by', 'ma_nguoi_dung');
    }

    /**
     * Scope: Lấy phân công đã nhận
     */
    public function scopeDaNhan($query)
    {
        return $query->where('trang_thai', 'da_nhan');
    }

    /**
     * Scope: Lấy phân công đang chờ xác nhận
     */
    public function scopeChoXacNhan($query)
    {
        return $query->where('trang_thai', 'cho_xac_nhan');
    }
}
