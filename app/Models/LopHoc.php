<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class LopHoc extends Model
{
    use HasFactory;

    protected $table = 'lop_hoc';

    protected $fillable = [
        'khoa_hoc_id',
        'ma_lop_hoc',
        'ngay_khai_giang',
        'ngay_ket_thuc',
        'trang_thai_van_hanh',
        'ty_trong_diem_danh',
        'ty_trong_kiem_tra',
        'ghi_chu',
        'created_by',
    ];

    protected $casts = [
        'ngay_khai_giang' => 'date',
        'ngay_ket_thuc' => 'date',
    ];

    public function khoaHoc(): BelongsTo
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    public function moduleHocs(): HasMany
    {
        return $this->hasMany(ModuleHoc::class, 'khoa_hoc_id', 'khoa_hoc_id');
    }

    public function hocVienKhoaHocs(): HasMany
    {
        return $this->hasMany(HocVienKhoaHoc::class, 'lop_hoc_id');
    }

    public function lichHocs(): HasMany
    {
        return $this->hasMany(LichHoc::class, 'lop_hoc_id');
    }

    public function ketQuaHocTaps(): HasMany
    {
        return $this->hasMany(KetQuaHocTap::class, 'lop_hoc_id');
    }

    public function phanCongGiangViens(): HasMany
    {
        return $this->hasMany(PhanCongModuleGiangVien::class, 'khoa_hoc_id', 'khoa_hoc_id');
    }

    protected function tenKhoaHoc(): Attribute
    {
        return Attribute::get(fn () => $this->khoaHoc?->ten_khoa_hoc);
    }

    protected function maKhoaHoc(): Attribute
    {
        return Attribute::get(fn () => $this->khoaHoc?->ma_khoa_hoc);
    }

    protected function moTaNgan(): Attribute
    {
        return Attribute::get(fn () => $this->khoaHoc?->mo_ta_ngan);
    }

    protected function hinhAnh(): Attribute
    {
        return Attribute::get(fn () => $this->khoaHoc?->hinh_anh);
    }

    protected function capDo(): Attribute
    {
        return Attribute::get(fn () => $this->khoaHoc?->cap_do);
    }

    protected function nhomNganh(): Attribute
    {
        return Attribute::get(fn () => $this->khoaHoc?->nhomNganh);
    }

    protected function labelTrangThaiVanHanh(): Attribute
    {
        return Attribute::get(fn () => match ($this->trang_thai_van_hanh) {
            'cho_mo' => 'Chờ mở lớp',
            'cho_giang_vien' => 'Chờ giảng viên',
            'san_sang' => 'Sẵn sàng',
            'dang_day' => 'Đang dạy',
            'ket_thuc' => 'Kết thúc',
            'huy' => 'Đã hủy',
            default => 'Đang cập nhật',
        });
    }

    protected function badgeTrangThai(): Attribute
    {
        return Attribute::get(fn () => match ($this->trang_thai_van_hanh) {
            'cho_mo' => 'secondary',
            'cho_giang_vien' => 'warning',
            'san_sang' => 'primary',
            'dang_day' => 'success',
            'ket_thuc' => 'dark',
            'huy' => 'danger',
            default => 'secondary',
        });
    }
}
