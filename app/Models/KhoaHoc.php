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
        'loai',
        'trang_thai_van_hanh',
        'ngay_khai_giang',
        'ngay_ket_thuc_du_kien',
        'ghi_chu_noi_bo'
    ];

    protected $casts = [
        'trang_thai' => 'boolean',
        'tong_so_module' => 'integer',
        'ngay_khai_giang' => 'date',
        'ngay_ket_thuc_du_kien' => 'date',
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
     * Relationship: Lấy danh sách giảng viên được phân công cho khóa học này thông qua bảng trung gian
     */
    public function giangViens()
    {
        return $this->belongsToMany(GiangVien::class, 'phan_cong_module_giang_vien', 'khoa_hoc_id', 'giao_vien_id')
                    ->withPivot('module_hoc_id', 'trang_thai', 'ghi_chu')
                    ->withTimestamps();
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

    public function scopeMau($q)          { return $q->where('loai','mau'); }
    public function scopeTrucTiep($q)     { return $q->where('loai','truc_tiep'); }
    public function scopeDangHoatDong($q) {
        return $q->whereIn('trang_thai_van_hanh',['cho_giang_vien','san_sang','dang_day']);
    }

    /**
     * Accessors
     */
    public function getSoModuleThucTeAttribute(): int
    {
        return $this->module_hocs_count ?? $this->moduleHocs()->count();
    }

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
            'truc_tiep' => ['label'=>'Trực tiếp', 'color'=>'primary'],
        ][$this->loai] ?? ['label'=>'?','color'=>'secondary'];
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
