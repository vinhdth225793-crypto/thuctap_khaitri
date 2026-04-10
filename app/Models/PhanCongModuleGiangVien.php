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
        'giang_vien_id', // Đã đổi từ giang_vien_id
        'ngay_phan_cong',
        'trang_thai',
        'ghi_chu',
        'created_by'
    ];

    protected $casts = [
        'ngay_phan_cong' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Thuộc về một khóa học
     */
    public function khoaHoc()
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    /**
     * Relationship: Thuộc về một module cụ thể
     */
    public function moduleHoc()
    {
        return $this->belongsTo(ModuleHoc::class, 'module_hoc_id');
    }

    /**
     * Relationship: Giảng viên được phân công
     */
    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'giang_vien_id');
    }

    /**
     * Scope: Lấy phân công đã được chấp nhận
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

    public function getTrangThaiLabelAttribute(): array
    {
        return match ($this->trang_thai) {
            'cho_xac_nhan' => [
                'label' => 'Chờ xác nhận',
                'color' => 'warning',
                'icon' => 'fa-hourglass-half',
            ],
            'da_nhan' => [
                'label' => 'Đã nhận',
                'color' => 'success',
                'icon' => 'fa-check-circle',
            ],
            'tu_choi' => [
                'label' => 'Từ chối',
                'color' => 'danger',
                'icon' => 'fa-times-circle',
            ],
            default => [
                'label' => 'Không xác định',
                'color' => 'secondary',
                'icon' => 'fa-question-circle',
            ],
        };
    }
}

