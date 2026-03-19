<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LichHoc extends Model
{
    use HasFactory;

    protected $table = 'lich_hoc';

    protected $fillable = [
        'khoa_hoc_id',
        'module_hoc_id',
        'giang_vien_id',
        'ngay_hoc',
        'gio_bat_dau',
        'gio_ket_thuc',
        'thu_trong_tuan',
        'buoi_so',
        'phong_hoc',
        'hinh_thuc',
        'link_online',
        'nen_tang',
        'meeting_id',
        'mat_khau_cuoc_hop',
        'trang_thai',
        'ghi_chu',
        'bao_cao_giang_vien',
        'thoi_gian_bao_cao',
        'trang_thai_bao_cao',
    ];

    /**
     * Relationship: Một buổi học có nhiều tài nguyên
     */
    public function taiNguyen()
    {
        return $this->hasMany(TaiNguyenBuoiHoc::class, 'lich_hoc_id');
    }

    /**
     * Relationship: Một buổi học có nhiều bài kiểm tra
     */
    public function baiKiemTras()
    {
        return $this->hasMany(BaiKiemTra::class, 'lich_hoc_id');
    }

    /**
     * Relationship: Một buổi học có nhiều bản ghi điểm danh
     */
    public function diemDanhs()
    {
        return $this->hasMany(DiemDanh::class, 'lich_hoc_id');
    }

    protected $casts = [
        'ngay_hoc' => 'date',
        'thoi_gian_bao_cao' => 'datetime',
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
     * Relationship: Thuộc về một module học
     */
    public function moduleHoc(): BelongsTo
    {
        return $this->belongsTo(ModuleHoc::class, 'module_hoc_id');
    }

    /**
     * Relationship: Giảng viên phụ trách buổi này
     */
    public function giangVien(): BelongsTo
    {
        return $this->belongsTo(GiangVien::class, 'giang_vien_id');
    }

    /**
     * Nhãn cho các thứ trong tuần
     */
    public static $thuLabels = [
        2 => 'Thứ 2',
        3 => 'Thứ 3',
        4 => 'Thứ 4',
        5 => 'Thứ 5',
        6 => 'Thứ 6',
        7 => 'Thứ 7',
        8 => 'Chủ nhật'
    ];

    /**
     * Accessor: Nhãn thứ trong tuần
     */
    public function getThuLabelAttribute(): string
    {
        return self::$thuLabels[$this->thu_trong_tuan] ?? '─';
    }

    /**
     * Accessor: Nhãn trạng thái buổi học
     */
    public function getTrangThaiLabelAttribute(): string
    {
        return match($this->trang_thai) {
            'cho'        => 'Chờ',
            'dang_hoc'   => 'Đang học',
            'hoan_thanh' => 'Hoàn thành',
            'huy'        => 'Đã hủy',
            default      => '─',
        };
    }

    /**
     * Accessor: Màu sắc trạng thái buổi học
     */
    public function getTrangThaiColorAttribute(): string
    {
        return match($this->trang_thai) {
            'cho'        => 'secondary',
            'dang_hoc'   => 'primary',
            'hoan_thanh' => 'success',
            'huy'        => 'danger',
            default      => 'secondary',
        };
    }
}
