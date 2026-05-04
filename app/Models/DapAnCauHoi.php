<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DapAnCauHoi extends Model
{
    use HasFactory;

    protected $table = 'dap_an_cau_hoi';

    protected $fillable = [
        'ngan_hang_cau_hoi_id',
        'ky_hieu',
        'noi_dung',
        'is_dap_an_dung',
        'thu_tu',
        // Legacy aliases kept for older seeders/import helpers.
        'cau_hoi_id',
        'noi_dung_dap_an',
        'la_dap_an_dung',
    ];

    protected $casts = [
        'is_dap_an_dung' => 'boolean',
    ];

    public function cauHoi(): BelongsTo
    {
        return $this->nganHangCauHoi();
    }

    public function nganHangCauHoi(): BelongsTo
    {
        return $this->belongsTo(NganHangCauHoi::class, 'ngan_hang_cau_hoi_id');
    }

    public function getCauHoiIdAttribute(): mixed
    {
        return $this->attributes['ngan_hang_cau_hoi_id'] ?? null;
    }

    public function setCauHoiIdAttribute(mixed $value): void
    {
        $this->attributes['ngan_hang_cau_hoi_id'] = $value;
    }

    public function getNoiDungDapAnAttribute(): ?string
    {
        return $this->attributes['noi_dung'] ?? null;
    }

    public function setNoiDungDapAnAttribute(mixed $value): void
    {
        $this->attributes['noi_dung'] = $value;
    }

    public function getLaDapAnDungAttribute(): bool
    {
        return (bool) ($this->attributes['is_dap_an_dung'] ?? false);
    }

    public function setLaDapAnDungAttribute(mixed $value): void
    {
        $this->attributes['is_dap_an_dung'] = (bool) $value;
    }
}
