<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KetQuaHocTap extends Model
{
    use HasFactory;

    public const TRANG_THAI_CHOT_CHUA_CHOT = 'chua_chot';
    public const TRANG_THAI_CHOT_DA_CHOT = 'da_chot';

    public const TRANG_THAI_DUYET_CHUA_GUI = 'chua_gui';
    public const TRANG_THAI_DUYET_CHO_DUYET = 'cho_duyet';
    public const TRANG_THAI_DUYET_DA_DUYET = 'da_duyet';
    public const TRANG_THAI_DUYET_TU_CHOI = 'tu_choi';

    protected $table = 'ket_qua_hoc_tap';

    protected $fillable = [
        'khoa_hoc_id',
        'hoc_vien_id',
        'module_hoc_id',
        'bai_kiem_tra_id',
        'phuong_thuc_danh_gia',
        'attempt_strategy_used',
        'aggregation_strategy_used',
        'source_attempt_id',
        'source_attempt_ids',
        'diem_diem_danh',
        'diem_kiem_tra',
        'diem_tong_ket',
        'diem_giang_vien_chot',
        'tong_so_buoi',
        'so_buoi_tham_du',
        'ty_le_tham_du',
        'so_bai_kiem_tra_hoan_thanh',
        'trang_thai',
        'trang_thai_chot',
        'chot_boi',
        'chot_luc',
        'ghi_chu_chot',
        'trang_thai_duyet',
        'admin_duyet_id',
        'duyet_luc',
        'ghi_chu_duyet',
        'luu_ho_so_luc',
        'nhan_xet_giang_vien',
        'chi_tiet',
        'calculation_metadata',
        'cap_nhat_luc',
    ];

    protected $casts = [
        'khoa_hoc_id' => 'integer',
        'hoc_vien_id' => 'integer',
        'module_hoc_id' => 'integer',
        'bai_kiem_tra_id' => 'integer',
        'source_attempt_id' => 'integer',
        'source_attempt_ids' => 'array',
        'diem_diem_danh' => 'decimal:2',
        'diem_kiem_tra' => 'decimal:2',
        'diem_tong_ket' => 'decimal:2',
        'diem_giang_vien_chot' => 'decimal:2',
        'ty_le_tham_du' => 'decimal:2',
        'chot_boi' => 'integer',
        'chot_luc' => 'datetime',
        'admin_duyet_id' => 'integer',
        'duyet_luc' => 'datetime',
        'luu_ho_so_luc' => 'datetime',
        'chi_tiet' => 'array',
        'calculation_metadata' => 'array',
        'cap_nhat_luc' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function khoaHoc(): BelongsTo
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    public function moduleHoc(): BelongsTo
    {
        return $this->belongsTo(ModuleHoc::class, 'module_hoc_id');
    }

    public function baiKiemTra(): BelongsTo
    {
        return $this->belongsTo(BaiKiemTra::class, 'bai_kiem_tra_id');
    }

    public function hocVien(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'hoc_vien_id', 'ma_nguoi_dung');
    }

    public function sourceAttempt(): BelongsTo
    {
        return $this->belongsTo(BaiLamBaiKiemTra::class, 'source_attempt_id');
    }

    public function nguoiChot(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'chot_boi', 'ma_nguoi_dung');
    }

    public function adminDuyet(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'admin_duyet_id', 'ma_nguoi_dung');
    }

    public function getDiemHienThiAttribute(): ?float
    {
        $score = $this->diem_giang_vien_chot ?? $this->diem_tong_ket;

        return $score !== null ? (float) $score : null;
    }

    public function getTrangThaiChotLabelAttribute(): string
    {
        return match ($this->trang_thai_chot) {
            self::TRANG_THAI_CHOT_DA_CHOT => 'Da chot',
            default => 'Chua chot',
        };
    }

    public function getTrangThaiDuyetLabelAttribute(): string
    {
        return match ($this->trang_thai_duyet) {
            self::TRANG_THAI_DUYET_CHO_DUYET => 'Cho admin duyet',
            self::TRANG_THAI_DUYET_DA_DUYET => 'Da duyet, luu ho so',
            self::TRANG_THAI_DUYET_TU_CHOI => 'Can chinh sua',
            default => 'Chua gui duyet',
        };
    }
}
