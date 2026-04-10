<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;

class NguoiDung extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'nguoi_dung';

    protected $fillable = [
        'ho_ten',
        'email',
        'mat_khau',
        'vai_tro',
        'so_dien_thoai',
        'dia_chi',
        'ngay_sinh',
        'anh_dai_dien',
        'trang_thai',
    ];

    protected $hidden = [
        'mat_khau',
        'remember_token',
    ];

    protected $casts = [
        'email_xac_thuc_luc' => 'datetime',
        'mat_khau' => 'hashed',
        'trang_thai' => 'boolean',
    ];

    public function hocVien(): HasOne
    {
        return $this->hasOne(HocVien::class, 'nguoi_dung_id');
    }

    public function giangVien(): HasOne
    {
        return $this->hasOne(GiangVien::class, 'nguoi_dung_id');
    }
}
