<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'loai',
        'trang_thai_van_hanh',
        'khoa_hoc_mau_id',
        'lan_mo_thu',
        'ngay_khai_giang',
        'ngay_mo_lop',
        'ngay_ket_thuc',
        'ghi_chu_noi_bo',
        'created_by'
    ];

    protected $casts = [
        'trang_thai' => 'boolean',
        'tong_so_module' => 'integer',
        'lan_mo_thu' => 'integer',
        'ngay_khai_giang' => 'date',
        'ngay_mo_lop' => 'date',
        'ngay_ket_thuc' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Khóa học thuộc về một môn học
     */
    public function monHoc(): BelongsTo
    {
        return $this->belongsTo(MonHoc::class, 'mon_hoc_id');
    }

    /**
     * Relationship: Một khóa học có nhiều module
     */
    public function moduleHocs(): HasMany
    {
        return $this->hasMany(ModuleHoc::class, 'khoa_hoc_id')->orderBy('thu_tu_module');
    }

    /**
     * Relationship: KH hoạt động -> KH mẫu gốc
     */
    public function khoaHocMau(): BelongsTo
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_mau_id');
    }

    /**
     * Relationship: KH mẫu -> tất cả lớp đã mở từ mẫu này
     */
    public function lopDaMo(): HasMany
    {
        return $this->hasMany(KhoaHoc::class, 'khoa_hoc_mau_id');
    }

    /**
     * Relationship: Một khóa học có nhiều phân công giảng viên
     */
    public function phanCongGiangViens(): HasMany
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
     * Relationship: Người tạo khóa học
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'created_by', 'ma_nguoi_dung');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('trang_thai', 1);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('ten_khoa_hoc', 'LIKE', "%{$search}%")
                     ->orWhere('ma_khoa_hoc', 'LIKE', "%{$search}%");
    }

    public function scopeMau($query)
    {
        return $query->where('loai', 'mau');
    }

    public function scopeHoatDong($query)
    {
        return $query->where('loai', 'hoat_dong');
    }

    /**
     * Accessors
     */
    public function getSoLanMoAttribute(): int
    {
        return $this->lopDaMo()->count();
    }

    public function getLabelTrangThaiVanHanhAttribute(): string
    {
        return match($this->trang_thai_van_hanh) {
            'cho_mo'          => 'Chờ mở lớp',
            'cho_giang_vien'  => 'Chờ giảng viên xác nhận',
            'san_sang'        => 'Sẵn sàng khai giảng',
            'dang_day'        => 'Đang giảng dạy',
            'ket_thuc'        => 'Đã kết thúc',
            default           => 'Không xác định',
        };
    }

    public function getBadgeTrangThaiAttribute(): string
    {
        return match($this->trang_thai_van_hanh) {
            'cho_mo'          => 'secondary',
            'cho_giang_vien'  => 'warning',
            'san_sang'        => 'info',
            'dang_day'        => 'success',
            'ket_thuc'        => 'dark',
            default           => 'light',
        };
    }

    // Keep the array-based label for Blade flexibility if already used
    public function getTrangThaiVanHanhLabelAttribute(): array
    {
        $map = [
            'cho_mo'         => ['label'=>'Chờ mở',          'color'=>'secondary', 'icon'=>'fa-pause-circle'],
            'cho_giang_vien' => ['label'=>'Chờ GV xác nhận', 'color'=>'warning',   'icon'=>'fa-clock'],
            'san_sang'       => ['label'=>'Sẵn sàng',         'color'=>'success',   'icon'=>'fa-check-circle'],
            'dang_day'       => ['label'=>'Đang dạy',          'color'=>'primary',   'icon'=>'fa-play-circle'],
            'ket_thuc'       => ['label'=>'Kết thúc',          'color'=>'dark',      'icon'=>'fa-flag-checkered'],
        ];
        return $map[$this->trang_thai_van_hanh]
            ?? ['label'=>'Không xác định','color'=>'secondary','icon'=>'fa-question'];
    }

    public function getLoaiLabelAttribute(): array
    {
        return [
            'mau'       => ['label'=>'Khóa mẫu',  'color'=>'info'],
            'hoat_dong' => ['label'=>'Hoạt động', 'color'=>'primary'],
        ][$this->loai] ?? ['label'=>'?','color'=>'secondary'];
    }

    public function getSoModuleThucTeAttribute(): int
    {
        return $this->module_hocs_count ?? $this->moduleHocs()->count();
    }

    /**
     * Methods
     */
    public function isFullyAssigned(): bool
    {
        if ($this->tong_so_module === 0) return false;
        
        $co = $this->moduleHocs()
            ->whereHas('phanCongGiangViens', fn($q) => $q->where('trang_thai','da_nhan'))
            ->count();
            
        return $co >= $this->tong_so_module;
    }

    public function checkAndUpdateTrangThai(): void
    {
        if ($this->isFullyAssigned()) {
            $this->update(['trang_thai_van_hanh' => 'san_sang']);
        }
    }
}
