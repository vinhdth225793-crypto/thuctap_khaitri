<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhongHocLiveBanGhi extends Model
{
    use HasFactory;

    protected $table = 'phong_hoc_live_ban_ghi';

    protected $fillable = [
        'phong_hoc_live_id',
        'nguon_ban_ghi',
        'tieu_de',
        'duong_dan_file',
        'link_ngoai',
        'thoi_luong',
        'trang_thai',
    ];

    protected $casts = [
        'thoi_luong' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function phongHocLive()
    {
        return $this->belongsTo(PhongHocLive::class, 'phong_hoc_live_id');
    }

    public function getPlaybackUrlAttribute(): ?string
    {
        if (!empty($this->link_ngoai)) {
            return $this->link_ngoai;
        }

        if (empty($this->duong_dan_file)) {
            return null;
        }

        if (str_starts_with($this->duong_dan_file, 'storage/')) {
            return asset($this->duong_dan_file);
        }

        return asset('storage/' . ltrim($this->duong_dan_file, '/'));
    }

    public function getDurationLabelAttribute(): string
    {
        if (!$this->thoi_luong) {
            return 'Chưa cập nhật';
        }

        $hours = intdiv($this->thoi_luong, 3600);
        $minutes = intdiv($this->thoi_luong % 3600, 60);

        if ($hours > 0) {
            return sprintf('%dh %02dph', $hours, $minutes);
        }

        return sprintf('%d phút', $minutes);
    }
}
