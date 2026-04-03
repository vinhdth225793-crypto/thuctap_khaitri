<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BaiLamSnapshotGiamSat extends Model
{
    use HasFactory;

    protected $table = 'bai_lam_snapshot_giam_sat';

    protected $fillable = [
        'bai_lam_bai_kiem_tra_id',
        'duong_dan_file',
        'captured_at',
        'status',
        'meta',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function baiLam(): BelongsTo
    {
        return $this->belongsTo(BaiLamBaiKiemTra::class, 'bai_lam_bai_kiem_tra_id');
    }

    public function getFileUrlAttribute(): ?string
    {
        if (empty($this->duong_dan_file)) {
            return null;
        }

        if (str_starts_with($this->duong_dan_file, 'storage/')) {
            return asset($this->duong_dan_file);
        }

        return asset('storage/' . ltrim($this->duong_dan_file, '/'));
    }
}
