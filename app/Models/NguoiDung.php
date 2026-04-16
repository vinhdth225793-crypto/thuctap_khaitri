<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NguoiDung extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'nguoi_dung';

    protected $primaryKey = 'ma_nguoi_dung';

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
        'email_xac_thuc' => 'datetime',
        'ngay_sinh' => 'date',
        'mat_khau' => 'hashed',
        'trang_thai' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::created(function (self $user): void {
            if (! Schema::hasColumn($user->getTable(), 'id') || ! Schema::hasColumn($user->getTable(), 'ma_nguoi_dung')) {
                return;
            }

            $id = $user->attributes['id'] ?? $user->getKey();

            if (! $id) {
                return;
            }

            DB::table($user->getTable())
                ->where('id', $id)
                ->whereNull('ma_nguoi_dung')
                ->update(['ma_nguoi_dung' => $id]);
        });
    }

    public function getIdAttribute($value): mixed
    {
        return $value ?? ($this->attributes[$this->primaryKey] ?? null);
    }

    public function getMaNguoiDungAttribute($value): mixed
    {
        return $value ?? ($this->attributes['id'] ?? null);
    }

    public function getAuthPassword(): string
    {
        return $this->mat_khau;
    }

    public function isAdmin(): bool
    {
        return $this->vai_tro === 'admin';
    }

    public function isGiangVien(): bool
    {
        return $this->vai_tro === 'giang_vien';
    }

    public function isHocVien(): bool
    {
        return $this->vai_tro === 'hoc_vien';
    }

    public function hocVien(): HasOne
    {
        return $this->hasOne(HocVien::class, 'nguoi_dung_id');
    }

    public function giangVien(): HasOne
    {
        return $this->hasOne(GiangVien::class, 'nguoi_dung_id');
    }

    public function khoaHocs(): BelongsToMany
    {
        return $this->belongsToMany(
            KhoaHoc::class,
            'hoc_vien_khoa_hoc',
            'hoc_vien_id',
            'khoa_hoc_id',
            'ma_nguoi_dung',
            'id'
        )->withPivot('ngay_tham_gia', 'trang_thai', 'ghi_chu', 'created_by')
            ->withTimestamps();
    }

    public function moderatedPhongHocLives(): HasMany
    {
        return $this->hasMany(PhongHocLive::class, 'moderator_id', 'ma_nguoi_dung');
    }

    public function assistedPhongHocLives(): HasMany
    {
        return $this->hasMany(PhongHocLive::class, 'tro_giang_id', 'ma_nguoi_dung');
    }

    public function createdPhongHocLives(): HasMany
    {
        return $this->hasMany(PhongHocLive::class, 'created_by', 'ma_nguoi_dung');
    }

    public function phongHocLiveThamGia(): HasMany
    {
        return $this->hasMany(PhongHocLiveNguoiThamGia::class, 'nguoi_dung_id', 'ma_nguoi_dung');
    }
}
