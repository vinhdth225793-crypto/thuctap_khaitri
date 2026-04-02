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
     * Relationship: Người tạo tài nguyên
     */
    public function nguoiTao()
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_tao_id', 'ma_nguoi_dung');
    }

    /**
     * Relationship: Người duyệt tài nguyên
     */
    public function nguoiDuyet()
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_duyet_id', 'ma_nguoi_dung');
    }

    /**
     * Relationship: Liên kết tới các bài giảng qua pivot
     */
    public function baiGiangs()
    {
        return $this->belongsToMany(BaiGiang::class, 'bai_giang_tai_nguyen', 'tai_nguyen_id', 'bai_giang_id')
            ->withPivot('vai_tro_tai_nguyen', 'thu_tu_hien_thi')
            ->withTimestamps();
    }

    /**
     * Accessor: Lấy URL đầy đủ của tài nguyên
     */
    public function getFileUrlAttribute()
    {
        if (!empty($this->link_ngoai)) {
            return $this->link_ngoai;
        }

        if (empty($this->duong_dan_file)) {
            return null;
        }

        // Tât cả tài nguyên lưu trong storage/app/public đều cần prefix 'storage/' để truy cập qua link public
        // Nếu path chưa có 'storage/' ở đầu, chúng ta thêm vào
        if (strpos($this->duong_dan_file, 'storage/') === 0) {
            return asset($this->duong_dan_file);
        }

        return asset('storage/' . $this->duong_dan_file);
    }

    /**
     * Accessor: Kiểm tra xem tài nguyên là link ngoài hay không
     */
    public function getIsExternalAttribute()
    {
        return !empty($this->link_ngoai);
    }

    /**
     * Accessor: Kiểm tra file nội bộ có tồn tại không
     */
    public function getIsFileExistsAttribute()
    {
        if ($this->getIsExternalAttribute()) {
            return true;
        }

        if (empty($this->duong_dan_file)) {
            return false;
        }

        // Kiểm tra trong thư mục public (cách lưu mới)
        if (file_exists(public_path($this->duong_dan_file))) {
            return true;
        }

        // Kiểm tra trong disk storage (cách lưu cũ)
        return \Illuminate\Support\Facades\Storage::disk('public')->exists($this->duong_dan_file);
    }

    /**
     * Accessor: Lấy đường dẫn lưu trữ vật lý (để debug/kiểm tra)
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
     * Accessor: Lấy tên file gốc (nếu là file nội bộ)
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
     * Accessor: Kiểm tra tài liệu có thể tải xuống được không (thường là file upload)
     */
    public function getIsDownloadableAttribute()
    {
        return !$this->getIsExternalAttribute() && !empty($this->duong_dan_file) && $this->getIsFileExistsAttribute();
    }

    /**
     * Accessor: Lấy nhãn hiển thị cho loại tài nguyên
     */
    public function getLoaiLabelAttribute()
    {
        return match($this->loai_tai_nguyen) {
            'video'         => 'Video bài giảng',
            'pdf'           => 'Tài liệu PDF',
            'word'          => 'Tài liệu Word',
            'powerpoint'    => 'Bài thuyết trình',
            'excel'         => 'Bảng tính Excel',
            'image'         => 'Hình ảnh',
            'audio'         => 'Âm thanh',
            'archive'       => 'File nén',
            'link_ngoai'    => 'Liên kết ngoài',
            'bai_giang'     => 'Bài giảng (Cũ)',
            'tai_lieu'      => 'Tài liệu (Cũ)',
            'bai_tap'       => 'Bài tập (Cũ)',
            default         => 'Đính kèm'
        };
    }

    /**
     * Accessor: Lấy icon FontAwesome cho loại tài nguyên
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
     * Accessor: Lấy màu sắc Bootstrap cho loại tài nguyên
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
     * Accessor: Nhãn nguồn hiển thị của tài nguyên
     */
    public function getNguonHienThiLabelAttribute()
    {
        return $this->is_external ? 'Link ngoài' : 'File nội bộ';
    }

    /**
     * Accessor: Màu hiển thị theo nguồn tài nguyên
     */
    public function getNguonHienThiColorAttribute()
    {
        return $this->is_external ? 'info' : 'dark';
    }

    /**
     * Accessor: Trạng thái file/link cho học viên
     */
    public function getFileStatusMessageAttribute()
    {
        if ($this->is_external) {
            return 'Tài nguyên được cung cấp qua liên kết ngoài.';
        }

        if ($this->is_file_exists) {
            return 'Tệp sẵn sàng để xem hoặc tải về.';
        }

        return 'Tệp đính kèm hiện không còn tồn tại trên hệ thống.';
    }

    /**
     * Relationship: Thuộc về một buổi học cụ thể
     */
    public function lichHoc(): BelongsTo
    {
        return $this->belongsTo(LichHoc::class, 'lich_hoc_id');
    }

    /**
     * Scope: Chỉ lấy tài nguyên đã mở cho học viên (legacy)
     */
    public function scopeHienThi($query)
    {
        return $query->where('trang_thai_hien_thi', 'hien');
    }

    /**
     * Scope: Tai nguyen hop le de hoc vien truy cap.
     */
    public function scopeHienThiChoHocVien($query)
    {
        return $query
            ->where(function ($visibilityQuery) {
                $visibilityQuery->whereNull('trang_thai_hien_thi')
                    ->orWhere('trang_thai_hien_thi', 'hien');
            })
            ->where(function ($openQuery) {
                $openQuery->whereNull('ngay_mo_hien_thi')
                    ->orWhere('ngay_mo_hien_thi', '<=', now());
            })
            ->where(function ($approvalQuery) {
                $approvalQuery->whereNull('trang_thai_duyet')
                    ->orWhere('trang_thai_duyet', self::STATUS_DUYET_DA_DUYET);
            })
            ->where(function ($processingQuery) {
                $processingQuery->whereNull('trang_thai_xu_ly')
                    ->orWhereIn('trang_thai_xu_ly', [self::STATUS_XU_LY_NONE, self::STATUS_XU_LY_SAN_SANG]);
            });
    }

    /**
     * Scope: Tài nguyên đã duyệt
     */
    public function scopeDaDuyet($query)
    {
        return $query->where('trang_thai_duyet', self::STATUS_DUYET_DA_DUYET);
    }

    /**
     * Scope: Video đã xử lý xong hoặc tài liệu không cần xử lý
     */
    public function scopeSanSang($query)
    {
        return $query->whereIn('trang_thai_xu_ly', [self::STATUS_XU_LY_NONE, self::STATUS_XU_LY_SAN_SANG]);
    }

    /**
     * Scope: Tài nguyên có thể dùng cho bài giảng (đã duyệt + sẵn sàng)
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
