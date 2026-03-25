<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaiNguyenBuoiHoc extends Model
{
    use HasFactory;

    protected $table = 'tai_nguyen_buoi_hoc';

    protected $fillable = [
        'lich_hoc_id',
        'loai_tai_nguyen', // video, pdf, word, powerpoint, excel, image, audio, archive, link_ngoai, tai_lieu_khac
        'tieu_de',
        'mo_ta',
        'duong_dan_file',
        'link_ngoai',
        'trang_thai_hien_thi', // an, hien (legacy)
        'ngay_mo_hien_thi',
        'thu_tu_hien_thi',
        'nguoi_tao_id',
        'vai_tro_nguoi_tao',
        'trang_thai_duyet', // nhap, cho_duyet, da_duyet, can_chinh_sua, tu_choi
        'trang_thai_xu_ly', // khong_ap_dung, cho_xu_ly, dang_xu_ly, san_sang, loi_xu_ly
        'ghi_chu_admin',
        'ngay_gui_duyet',
        'ngay_duyet',
        'nguoi_duyet_id',
        'pham_vi_su_dung', // ca_nhan, khoa_hoc, cong_khai
        'file_name',
        'file_extension',
        'file_size',
        'mime_type',
    ];

    protected $casts = [
        'ngay_mo_hien_thi' => 'datetime',
        'thu_tu_hien_thi' => 'integer',
        'ngay_gui_duyet' => 'datetime',
        'ngay_duyet' => 'datetime',
        'file_size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Constants for Status
    public const STATUS_DUYET_NHAP = 'nhap';
    public const STATUS_DUYET_CHO = 'cho_duyet';
    public const STATUS_DUYET_DA_DUYET = 'da_duyet';
    public const STATUS_DUYET_CAN_SUA = 'can_chinh_sua';
    public const STATUS_DUYET_TU_CHOI = 'tu_choi';

    public const STATUS_XU_LY_NONE = 'khong_ap_dung';
    public const STATUS_XU_LY_CHO = 'cho_xu_ly';
    public const STATUS_XU_LY_DANG = 'dang_xu_ly';
    public const STATUS_XU_LY_SAN_SANG = 'san_sang';
    public const STATUS_XU_LY_LOI = 'loi_xu_ly';

    public const PHAM_VI_CA_NHAN = 'ca_nhan';
    public const PHAM_VI_KHOA_HOC = 'khoa_hoc';
    public const PHAM_VI_CONG_KHAI = 'cong_khai';

    /**
     * Relationship: Ngu?i t?o t�i nguy�n
     */
    public function nguoiTao()
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_tao_id', 'ma_nguoi_dung');
    }

    /**
     * Relationship: Ngu?i duy?t t�i nguy�n
     */
    public function nguoiDuyet()
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_duyet_id', 'ma_nguoi_dung');
    }

    /**
     * Relationship: Li�n k?t t?i c�c b�i gi?ng qua pivot
     */
    public function baiGiangs()
    {
        return $this->belongsToMany(BaiGiang::class, 'bai_giang_tai_nguyen', 'tai_nguyen_id', 'bai_giang_id')
            ->withPivot('vai_tro_tai_nguyen', 'thu_tu_hien_thi')
            ->withTimestamps();
    }

    /**
     * Accessor: L?y URL d?y d? c?a t�i nguy�n
     */
    public function getFileUrlAttribute()
    {
        if (!empty($this->link_ngoai)) {
            return $this->link_ngoai;
        }

        if (empty($this->duong_dan_file)) {
            return null;
        }

        // T�t c? t�i nguy�n luu trong storage/app/public d?u c?n prefix 'storage/' d? truy c?p qua link public
        // N?u path chua c� 'storage/' ? d?u, ch�ng ta th�m v�o
        if (strpos($this->duong_dan_file, 'storage/') === 0) {
            return asset($this->duong_dan_file);
        }

        return asset('storage/' . $this->duong_dan_file);
    }

    /**
     * Accessor: Ki?m tra xem t�i nguy�n l� link ngo�i hay kh�ng
     */
    public function getIsExternalAttribute()
    {
        return !empty($this->link_ngoai);
    }

    /**
     * Accessor: Ki?m tra file n?i b? c� t?n t?i kh�ng
     */
    public function getIsFileExistsAttribute()
    {
        if ($this->getIsExternalAttribute()) {
            return true;
        }

        if (empty($this->duong_dan_file)) {
            return false;
        }

        // Ki?m tra trong thu m?c public (c�ch luu m?i)
        if (file_exists(public_path($this->duong_dan_file))) {
            return true;
        }

        // Ki?m tra trong disk storage (c�ch luu cu)
        return \Illuminate\Support\Facades\Storage::disk('public')->exists($this->duong_dan_file);
    }

    /**
     * Accessor: L?y du?ng d?n luu tr? v?t l� (d? debug/ki?m tra)
     */
    public function getStoragePathAttribute()
    {
        if ($this->getIsExternalAttribute() || empty($this->duong_dan_file)) {
            return 'N/A';
        }

        if (file_exists(public_path($this->duong_dan_file))) {
            return 'public/' . $this->duong_dan_file;
        }

        return 'storage/app/public/' . $this->duong_dan_file;
    }

    /**
     * Accessor: L?y t�n file g?c (n?u l� file n?i b?)
     */
    public function getOriginalFileNameAttribute()
    {
        if ($this->getIsExternalAttribute() || empty($this->duong_dan_file)) {
            return null;
        }

        $parts = explode('_', basename($this->duong_dan_file), 2);
        return count($parts) > 1 ? $parts[1] : $parts[0];
    }

    /**
     * Accessor: Ki?m tra t�i li?u c� th? t?i xu?ng du?c kh�ng (thu?ng l� file upload)
     */
    public function getIsDownloadableAttribute()
    {
        return !$this->getIsExternalAttribute() && !empty($this->duong_dan_file) && $this->getIsFileExistsAttribute();
    }

    /**
     * Accessor: L?y nh�n hi?n th? cho lo?i t�i nguy�n
     */
    public function getLoaiLabelAttribute()
    {
        return match($this->loai_tai_nguyen) {
            'video'         => 'Video b�i gi?ng',
            'pdf'           => 'T�i li?u PDF',
            'word'          => 'T�i li?u Word',
            'powerpoint'    => 'B�i thuy?t tr�nh',
            'excel'         => 'B?ng t�nh Excel',
            'image'         => 'H�nh ?nh',
            'audio'         => '�m thanh',
            'archive'       => 'File n�n',
            'link_ngoai'    => 'Li�n k?t ngo�i',
            'bai_giang'     => 'B�i gi?ng (Cu)',
            'tai_lieu'      => 'T�i li?u (Cu)',
            'bai_tap'       => 'B�i t?p (Cu)',
            default         => '��nh k�m'
        };
    }

    /**
     * Accessor: L?y icon FontAwesome cho lo?i t�i nguy�n
     */
    public function getLoaiIconAttribute()
    {
        return match($this->loai_tai_nguyen) {
            'video'         => 'fa-video',
            'pdf'           => 'fa-file-pdf',
            'word'          => 'fa-file-word',
            'powerpoint'    => 'fa-file-powerpoint',
            'excel'         => 'fa-file-excel',
            'image'         => 'fa-file-image',
            'audio'         => 'fa-file-audio',
            'archive'       => 'fa-file-archive',
            'link_ngoai'    => 'fa-link',
            'bai_giang'     => 'fa-chalkboard',
            'tai_lieu'      => 'fa-file-alt',
            'bai_tap'       => 'fa-pencil-alt',
            default         => 'fa-paperclip'
        };
    }

    /**
     * Accessor: L?y m�u s?c Bootstrap cho lo?i t�i nguy�n
     */
    public function getLoaiColorAttribute()
    {
        return match($this->loai_tai_nguyen) {
            'video'         => 'primary',
            'pdf'           => 'danger',
            'word'          => 'info',
            'powerpoint'    => 'warning',
            'excel'         => 'success',
            'image'         => 'primary',
            'audio'         => 'secondary',
            'archive'       => 'dark',
            'link_ngoai'    => 'info',
            'bai_giang'     => 'primary',
            'tai_lieu'      => 'success',
            'bai_tap'       => 'warning',
            default         => 'secondary'
        };
    }

    /**
     * Accessor: Nh�n ngu?n hi?n th? c?a t�i nguy�n
     */
    public function getNguonHienThiLabelAttribute()
    {
        return $this->is_external ? 'Link ngo�i' : 'File n?i b?';
    }

    /**
     * Accessor: M�u hi?n th? theo ngu?n t�i nguy�n
     */
    public function getNguonHienThiColorAttribute()
    {
        return $this->is_external ? 'info' : 'dark';
    }

    /**
     * Accessor: Tr?ng th�i file/link cho h?c vi�n
     */
    public function getFileStatusMessageAttribute()
    {
        if ($this->is_external) {
            return 'T�i nguy�n du?c cung c?p qua li�n k?t ngo�i.';
        }

        if ($this->is_file_exists) {
            return 'T?p s?n s�ng d? xem ho?c t?i v?.';
        }

        return 'T?p d�nh k�m hi?n kh�ng c�n t?n t?i tr�n h? th?ng.';
    }

    /**
     * Relationship: Thu?c v? m?t bu?i h?c c? th?
     */
    public function lichHoc(): BelongsTo
    {
        return $this->belongsTo(LichHoc::class, 'lich_hoc_id');
    }

    /**
     * Scope: Ch? l?y t�i nguy�n d� m? cho h?c vi�n (legacy)
     */
    public function scopeHienThi($query)
    {
        return $query->where('trang_thai_hien_thi', 'hien');
    }

    /**
     * Scope: T�i nguy�n d� duy?t
     */
    public function scopeDaDuyet($query)
    {
        return $query->where('trang_thai_duyet', self::STATUS_DUYET_DA_DUYET);
    }

    /**
     * Scope: Video d� x? l� xong ho?c t�i li?u kh�ng c?n x? l�
     */
    public function scopeSanSang($query)
    {
        return $query->whereIn('trang_thai_xu_ly', [self::STATUS_XU_LY_NONE, self::STATUS_XU_LY_SAN_SANG]);
    }

    /**
     * Scope: T�i nguy�n c� th? d�ng cho b�i gi?ng (d� duy?t + s?n s�ng)
     */
    public function scopeDungDuoc($query)
    {
        return $query->daDuyet()->sanSang();
    }

    /**
     * Helpers
     */
    public function isDaDuyet(): bool
    {
        return $this->trang_thai_duyet === self::STATUS_DUYET_DA_DUYET;
    }

    public function isSanSang(): bool
    {
        return in_array($this->trang_thai_xu_ly, [self::STATUS_XU_LY_NONE, self::STATUS_XU_LY_SAN_SANG]);
    }

    public function isVideo(): bool
    {
        return $this->loai_tai_nguyen === 'video';
    }

    public function isPdf(): bool
    {
        return $this->loai_tai_nguyen === 'pdf';
    }
}
