<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Schema;

class Banner extends Model {
    use HasFactory;

    protected $table = 'banners';
    protected $fillable = ['tieu_de','mo_ta','duong_dan_anh','hinh_anh','link','lien_ket','thu_tu','trang_thai'];
    protected $casts = ['trang_thai'=>'boolean','thu_tu'=>'integer'];

    public function getTable()
    {
        if (Schema::hasTable('banners')) {
            return 'banners';
        }

        if (Schema::hasTable('banner')) {
            return 'banner';
        }

        return parent::getTable();
    }

    public function scopeHienThi($query) {
        return $query->where('trang_thai', true)->orderBy('thu_tu');
    }

    protected function duongDanAnh(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) => $attributes['duong_dan_anh'] ?? $attributes['hinh_anh'] ?? null,
            set: fn ($value) => [$this->imageColumn() => $value],
        );
    }

    protected function hinhAnh(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) => $attributes['hinh_anh'] ?? $attributes['duong_dan_anh'] ?? null,
            set: fn ($value) => [$this->imageColumn() => $value],
        );
    }

    protected function link(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) => $attributes['link'] ?? $attributes['lien_ket'] ?? null,
            set: fn ($value) => [$this->linkColumn() => $value],
        );
    }

    protected function lienKet(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) => $attributes['lien_ket'] ?? $attributes['link'] ?? null,
            set: fn ($value) => [$this->linkColumn() => $value],
        );
    }

    private function imageColumn(): string
    {
        return Schema::hasColumn($this->getTable(), 'duong_dan_anh') ? 'duong_dan_anh' : 'hinh_anh';
    }

    private function linkColumn(): string
    {
        return Schema::hasColumn($this->getTable(), 'link') ? 'link' : 'lien_ket';
    }
}
