<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class NhomNganh extends Model
{
    use HasFactory;

    protected $table = 'nhom_nganh';

    protected $fillable = [
        'ma_nhom_nganh',
        'ten_nhom_nganh',
        'mo_ta',
        'hinh_anh',
        'trang_thai'
    ];

    /**
     * Relationship: Một nhóm ngành có nhiều khóa học
     */
    public function khoaHocs()
    {
        return $this->hasMany(KhoaHoc::class, 'nhom_nganh_id');
    }

    /**
     * Scope: Lọc theo trạng thái hoạt động
     */
    public function scopeActive($query)
    {
        return $query->where('trang_thai', true);
    }

    /**
     * Tự động tạo mã nhóm ngành (NN001, NN002...)
     */
    public static function generateMaNhomNganh()
    {
        $prefix = 'NN';
        $lastRecord = self::orderBy('id', 'desc')->first();
        
        if ($lastRecord && preg_match('/' . $prefix . '(\d+)/', $lastRecord->ma_nhom_nganh, $matches)) {
            $lastNumber = (int)$matches[1];
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}
