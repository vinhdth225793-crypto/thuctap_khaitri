<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThongBao extends Model
{
    use HasFactory;

    protected $table    = 'thong_bao';
    protected $fillable = ['nguoi_nhan_id', 'tieu_de', 'noi_dung', 'loai', 'level', 'icon', 'url', 'metadata', 'da_doc'];

    protected $casts = [
        'metadata' => 'array',
        'da_doc'   => 'boolean',
    ];

    public function nguoiNhan()
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_nhan_id', 'ma_nguoi_dung');
    }

    /* ===== Scopes ===== */
    public function scopeChuaDoc($q)
    {
        return $q->where('da_doc', false);
    }

    public function scopeMoiNhat($q)
    {
        return $q->orderByDesc('created_at');
    }

    public function scopeOfUser($q, int $userId)
    {
        return $q->where('nguoi_nhan_id', $userId);
    }

    /* ===== Helpers cho UI ===== */
    public function getLevelClassAttribute(): string
    {
        return match ($this->level) {
            'success' => 'is-success',
            'warning' => 'is-warning',
            'danger'  => 'is-danger',
            'info'    => 'is-info',
            default   => 'is-info',
        };
    }

    public function getIconClassAttribute(): string
    {
        if ($this->icon) {
            return 'fas ' . $this->icon;
        }
        return 'fas ' . match ($this->level) {
            'success' => 'fa-circle-check',
            'warning' => 'fa-triangle-exclamation',
            'danger'  => 'fa-circle-xmark',
            default   => 'fa-bell',
        };
    }
}
