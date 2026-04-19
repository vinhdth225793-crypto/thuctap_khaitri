<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KetQuaHocTapChotLog extends Model
{
    protected $table = 'ket_qua_hoc_tap_chot_logs';

    protected $fillable = [
        'ket_qua_hoc_tap_id',
        'hoc_vien_id',
        'module_hoc_id',
        'khoa_hoc_id',
        'nguoi_thuc_hien_id',
        'hanh_dong',
        'diem_truoc',
        'diem_sau',
        'ly_do',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'diem_truoc' => 'decimal:2',
        'diem_sau' => 'decimal:2',
    ];

    public function ketQuaHocTap(): BelongsTo
    {
        return $this->belongsTo(KetQuaHocTap::class, 'ket_qua_hoc_tap_id');
    }

    public function hocVien(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'hoc_vien_id', 'ma_nguoi_dung');
    }

    public function nguoiThucHien(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_thuc_hien_id', 'ma_nguoi_dung');
    }
}
