<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Banner extends Model {
    use HasFactory;

    protected $table = 'banners';
    protected $fillable = ['tieu_de','mo_ta','duong_dan_anh','link','thu_tu','trang_thai'];
    protected $casts = ['trang_thai'=>'boolean','thu_tu'=>'integer'];

    public function scopeHienThi($query) {
        return $query->where('trang_thai', true)->orderBy('thu_tu');
    }
}
