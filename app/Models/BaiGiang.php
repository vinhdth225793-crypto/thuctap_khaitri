<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BaiGiang extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bai_giangs';

    protected $fillable = [
        'khoa_hoc_id',
        'module_hoc_id',
        'lich_hoc_id',
        'nguoi_tao_id',
        'tieu_de',
        'mo_ta',
        'loai_bai_giang', // video, tai_lieu, bai_doc, bai_tap, hon_hop
        'tai_nguyen_chinh_id',
        'thu_tu_hien_thi',
        'thoi_diem_mo',
        'trang_thai_duyet', // nhap, cho_duyet, da_duyet, can_chinh_sua, tu_choi
        'trang_thai_cong_bo', // an, da_cong_bo
        'ghi_chu_admin',
        'ngay_gui_duyet',
        'ngay_duyet',
        'nguoi_duyet_id',
    ];

    protected $casts = [
        'thoi_diem_mo' => 'datetime',
        'ngay_gui_duyet' => 'datetime',
        'ngay_duyet' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Constants
    public const STATUS_DUYET_NHAP = 'nhap';
    public const STATUS_DUYET_CHO = 'cho_duyet';
    public const STATUS_DUYET_DA_DUYET = 'da_duyet';
    public const STATUS_DUYET_CAN_SUA = 'can_chinh_sua';
    public const STATUS_DUYET_TU_CHOI = 'tu_choi';

    public const CONG_BO_AN = 'an';
    public const CONG_BO_DA_CONG_BO = 'da_cong_bo';

    /**
     * Relationships
     */
    public function khoaHoc()
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    public function moduleHoc()
    {
        return $this->belongsTo(ModuleHoc::class, 'module_hoc_id');
    }

    public function lichHoc()
    {
        return $this->belongsTo(LichHoc::class, 'lich_hoc_id');
    }

    public function nguoiTao()
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_tao_id', 'ma_nguoi_dung');
    }

    public function nguoiDuyet()
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_duyet_id', 'ma_nguoi_dung');
    }

    public function taiNguyenChinh()
    {
        return $this->belongsTo(TaiNguyenBuoiHoc::class, 'tai_nguyen_chinh_id');
    }

    public function taiNguyenPhu()
    {
        return $this->belongsToMany(TaiNguyenBuoiHoc::class, 'bai_giang_tai_nguyen', 'bai_giang_id', 'tai_nguyen_id')
            ->wherePivot('vai_tro_tai_nguyen', 'phu')
            ->withPivot('thu_tu_hien_thi')
            ->withTimestamps();
    }

    /**
     * Scopes
     */
    public function scopeDaDuyet($query)
    {
        return $query->where('trang_thai_duyet', self::STATUS_DUYET_DA_DUYET);
    }

    public function scopeDaCongBo($query)
    {
        return $query->where('trang_thai_cong_bo', self::CONG_BO_DA_CONG_BO);
    }

    public function scopeHienThiChoHocVien($query)
    {
        return $query->daDuyet()
            ->daCongBo()
            ->where(function($q) {
                $q->whereNull('thoi_diem_mo')
                  ->orWhere('thoi_diem_mo', '<=', now());
            });
    }

    /**
     * Helpers
     */
    public function isDaDuyet(): bool
    {
        return $this->trang_thai_duyet === self::STATUS_DUYET_DA_DUYET;
    }

    public function isDaCongBo(): bool
    {
        return $this->trang_thai_cong_bo === self::CONG_BO_DA_CONG_BO;
    }

    public function canHienThiChoHocVien(): bool
    {
        return $this->isDaDuyet() && 
               $this->isDaCongBo() && 
               ($this->thoi_diem_mo === null || $this->thoi_diem_mo->isPast());
    }
}
