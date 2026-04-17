<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HocVienKhoaHoc extends Model
{
    use HasFactory;

    protected $table = 'hoc_vien_khoa_hoc';

    protected $fillable = [
        'khoa_hoc_id',
        'hoc_vien_id',
        'ngay_tham_gia',
        'trang_thai',
        'ghi_chu',
        'created_by'
    ];

    protected $casts = [
        'ngay_tham_gia' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Thuộc về một khóa học
     */
    public function khoaHoc(): BelongsTo
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    /**
     * Relationship: Là một học viên
     */
    public function hocVien(): BelongsTo
    {
        return $this->belongsTo(HocVien::class, 'hoc_vien_id', 'nguoi_dung_id');
    }

    /**
     * Relationship: Người tạo bản ghi này
     */
    public function nguoiTao(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'created_by', 'ma_nguoi_dung');
    }

    /**
     * Accessor: Trạng thái học viên dạng nhãn tiếng Việt
     */
    public function getTrangThaiLabelAttribute(): string
    {
        return match($this->trang_thai) {
            'dang_hoc'   => 'Đang học',
            'hoan_thanh' => 'Hoàn thành',
            'ngung_hoc'  => 'Ngừng học',
            default      => 'Không xác định',
        };
    }

    /**
     * Accessor: Badge class tương ứng trạng thái
     */
    public function getTrangThaiBadgeAttribute(): string
    {
        return match($this->trang_thai) {
            'dang_hoc'   => 'bg-success',
            'hoan_thanh' => 'bg-primary',
            'ngung_hoc'  => 'bg-danger',
            default      => 'bg-secondary',
        };
    }
}
