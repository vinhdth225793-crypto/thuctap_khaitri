<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CauHoi extends Model
{
    use HasFactory;

    protected $table = 'cau_hoi';

    protected $fillable = [
        'khoa_hoc_id',
        'module_hoc_id',
        'noi_dung',
        'loai_cau_hoi',
        'muc_do',
        'la_cau_hoi_dung_chung',
        'nguoi_tao_id',
    ];

    public function khoaHoc(): BelongsTo
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    public function moduleHoc(): BelongsTo
    {
        return $this->belongsTo(ModuleHoc::class, 'module_hoc_id');
    }

    public function nguoiTao(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_tao_id');
    }

    public function dapAns(): HasMany
    {
        return $this->hasMany(DapAnCauHoi::class, 'cau_hoi_id');
    }
}
