<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GiangVien extends Model
{
    use HasFactory;

    protected $table = 'giang_vien';

    protected $fillable = [
        'nguoi_dung_id',
        'chuyen_nganh',
        'hoc_vi',
        'so_gio_day',
        'mo_ta_ngan',
        'avatar_url',
        'hien_thi_trang_chu',
    ];

    protected $casts = [
        'hien_thi_trang_chu' => 'boolean',
    ];

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id', 'ma_nguoi_dung');
    }

    public function phanCongs(): HasMany
    {
        return $this->phanCongModules();
    }

    public function phanCongModules(): HasMany
    {
        return $this->hasMany(PhanCongModuleGiangVien::class, 'giang_vien_id');
    }

    public function donXinNghis(): HasMany
    {
        return $this->hasMany(GiangVienDonXinNghi::class, 'giang_vien_id');
    }

    public function lichHocs(): HasMany
    {
        return $this->hasMany(LichHoc::class, 'giang_vien_id');
    }

    public function scopeHienThiTrangChu($query)
    {
        return $query->where('hien_thi_trang_chu', true);
    }

    /**
     * Tự động tính toán lại số giờ dạy dựa trên dữ liệu điểm danh.
     */
    public function recalculateTeachingHours(): void
    {
        $totalMinutes = DiemDanhGiangVien::query()
            ->where('giang_vien_id', $this->id)
            ->where('trang_thai', DiemDanhGiangVien::STATUS_HOAN_THANH)
            ->sum('tong_thoi_luong_day_phut');

        // Chuyển từ phút sang giờ, làm tròn 2 chữ số thập phân
        $hours = round($totalMinutes / 60, 2);
        
        $this->update(['so_gio_day' => (string) $hours]);
    }
}
