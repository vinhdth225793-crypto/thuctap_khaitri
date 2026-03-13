<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class MonHoc extends Model
{
    use HasFactory;

    protected $table = 'mon_hoc';

    protected $fillable = [
        'ma_mon_hoc',
        'ten_mon_hoc',
        'mo_ta',
        'hinh_anh',
        'trang_thai',
    ];

    protected $casts = [
        'trang_thai' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Một môn học có nhiều khóa học
     */
    public function khoaHocs()
    {
        return $this->hasMany(KhoaHoc::class, 'mon_hoc_id');
    }

    /**
     * Scope: Lấy những môn học đang kích hoạt
     */
    public function scopeActive($query)
    {
        return $query->where('trang_thai', 1);
    }

    /**
     * Scope: Tìm kiếm theo tên môn học
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('ten_mon_hoc', 'LIKE', "%{$search}%")
                     ->orWhere('ma_mon_hoc', 'LIKE', "%{$search}%");
    }

    /**
     * Tự động tạo mã môn học
     */
    public static function generateMaMonHoc($tenMonHoc)
    {
        $slug = strtoupper(Str::slug($tenMonHoc, '_'));
        $prefix = substr($slug, 0, 3);

        $lastMonHoc = self::where('ma_mon_hoc', 'LIKE', $prefix . '%')
                          ->orderBy('ma_mon_hoc', 'desc')
                          ->first();

        if ($lastMonHoc) {
            $lastNumber = intval(substr($lastMonHoc->ma_mon_hoc, strlen($prefix)));
            $newNumber = $lastNumber + 1;  // ← SỬA: dùng $lastNumber, không phải $lastMonHoc
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}
