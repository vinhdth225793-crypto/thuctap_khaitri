<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BaiKiemTra extends Model
{
    use HasFactory;

    protected $table = 'bai_kiem_tra';

    protected $fillable = [
        'khoa_hoc_id',
        'module_hoc_id',
        'lich_hoc_id',
        'tieu_de',
        'mo_ta',
        'thoi_gian_lam_bai',
        'ngay_mo',
        'ngay_dong',
        'pham_vi',
        'trang_thai',
    ];

    protected $casts = [
        'ngay_mo' => 'datetime',
        'ngay_dong' => 'datetime',
        'trang_thai' => 'boolean',
    ];

    public function khoaHoc(): BelongsTo
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    public function moduleHoc(): BelongsTo
    {
        return $this->belongsTo(ModuleHoc::class, 'module_hoc_id');
    }

    public function lichHoc(): BelongsTo
    {
        return $this->belongsTo(LichHoc::class, 'lich_hoc_id');
    }

    public function baiLams(): HasMany
    {
        return $this->hasMany(BaiLamBaiKiemTra::class, 'bai_kiem_tra_id');
    }

    public function getPhamViLabelAttribute(): string
    {
        return match ($this->pham_vi) {
            'module' => 'Theo module',
            'buoi_hoc' => 'Theo buoi hoc',
            default => 'Khong xac dinh',
        };
    }

    public function getAccessStatusKeyAttribute(): string
    {
        if (!$this->trang_thai) {
            return 'an';
        }

        if ($this->ngay_mo && $this->ngay_mo->isFuture()) {
            return 'sap_mo';
        }

        if ($this->ngay_dong && $this->ngay_dong->isPast()) {
            return 'da_dong';
        }

        return 'dang_mo';
    }

    public function getAccessStatusLabelAttribute(): string
    {
        return match ($this->access_status_key) {
            'dang_mo' => 'Dang mo',
            'sap_mo' => 'Sap mo',
            'da_dong' => 'Da dong',
            'an' => 'Tam an',
            default => 'Khong xac dinh',
        };
    }

    public function getAccessStatusColorAttribute(): string
    {
        return match ($this->access_status_key) {
            'dang_mo' => 'success',
            'sap_mo' => 'warning',
            'da_dong' => 'secondary',
            'an' => 'dark',
            default => 'secondary',
        };
    }

    public function getCanStudentStartAttribute(): bool
    {
        return $this->access_status_key === 'dang_mo';
    }
}
