<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiemDanhGiangVien extends Model
{
    use HasFactory;

    protected $table = 'diem_danh_giang_vien';

    protected $fillable = [
        'lich_hoc_id',
        'khoa_hoc_id',
        'module_hoc_id',
        'giang_vien_id',
        'hinh_thuc_hoc',
        'thoi_gian_bat_dau_day',
        'thoi_gian_ket_thuc_day',
        'thoi_gian_mo_live',
        'thoi_gian_tat_live',
        'tong_thoi_luong_day_phut',
        'trang_thai',
        'ghi_chu',
        'nguoi_tao_id',
    ];

    protected $casts = [
        'thoi_gian_bat_dau_day' => 'datetime',
        'thoi_gian_ket_thuc_day' => 'datetime',
        'thoi_gian_mo_live' => 'datetime',
        'thoi_gian_tat_live' => 'datetime',
        'tong_thoi_luong_day_phut' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function lichHoc(): BelongsTo
    {
        return $this->belongsTo(LichHoc::class, 'lich_hoc_id');
    }

    public function khoaHoc(): BelongsTo
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    public function moduleHoc(): BelongsTo
    {
        return $this->belongsTo(ModuleHoc::class, 'module_hoc_id');
    }

    public function giangVien(): BelongsTo
    {
        return $this->belongsTo(GiangVien::class, 'giang_vien_id');
    }

    public function nguoiTao(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_tao_id', 'ma_nguoi_dung');
    }

    public function getTrangThaiLabelAttribute(): string
    {
        return match ($this->trang_thai) {
            'dang_day' => 'Đang dạy',
            'da_ket_thuc' => 'Đã kết thúc',
            default => 'Chưa bắt đầu',
        };
    }

    public function getTrangThaiColorAttribute(): string
    {
        return match ($this->trang_thai) {
            'dang_day' => 'warning',
            'da_ket_thuc' => 'success',
            default => 'secondary',
        };
    }
}
