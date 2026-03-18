<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaiNguyenBuoiHoc extends Model
{
    use HasFactory;

    protected $table = 'tai_nguyen_buoi_hoc';

    protected $fillable = [
        'lich_hoc_id',
        'loai_tai_nguyen', // bai_giang, tai_lieu, bai_tap, link_ngoai
        'tieu_de',
        'mo_ta',
        'duong_dan_file',
        'link_ngoai',
        'trang_thai_hien_thi', // an, hien
        'ngay_mo_hien_thi',
        'thu_tu_hien_thi',
    ];

    protected $casts = [
        'ngay_mo_hien_thi' => 'datetime',
        'thu_tu_hien_thi' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Accessor: Lấy URL đầy đủ của tài nguyên
     */
    public function getFileUrlAttribute()
    {
        if ($this->loai_tai_nguyen === 'link_ngoai' || empty($this->duong_dan_file)) {
            return $this->link_ngoai;
        }

        // Nếu path bắt đầu bằng uploads/ (cách lưu mới)
        if (strpos($this->duong_dan_file, 'uploads/') === 0) {
            return asset($this->duong_dan_file);
        }

        // Tương thích ngược với cách lưu storage cũ
        return asset('storage/' . $this->duong_dan_file);
    }

    /**
     * Accessor: Kiểm tra xem tài nguyên là link ngoài hay không
     */
    public function getIsExternalAttribute()
    {
        return $this->loai_tai_nguyen === 'link_ngoai' || !empty($this->link_ngoai);
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
            return 'N/A (Link ngoài)';
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
            'bai_giang' => 'Bài giảng',
            'tai_lieu'  => 'Tài liệu',
            'bai_tap'   => 'Bài tập',
            'link_ngoai'=> 'Liên kết',
            default     => 'Đính kèm'
        };
    }

    /**
     * Accessor: Lấy icon FontAwesome cho loại tài nguyên
     */
    public function getLoaiIconAttribute()
    {
        return match($this->loai_tai_nguyen) {
            'bai_giang' => 'fa-chalkboard',
            'tai_lieu'  => 'fa-file-alt',
            'bai_tap'   => 'fa-pencil-alt',
            'link_ngoai'=> 'fa-link',
            default     => 'fa-paperclip'
        };
    }

    /**
     * Accessor: Lấy màu sắc Bootstrap cho loại tài nguyên
     */
    public function getLoaiColorAttribute()
    {
        return match($this->loai_tai_nguyen) {
            'bai_giang' => 'primary',
            'tai_lieu'  => 'success',
            'bai_tap'   => 'warning',
            'link_ngoai'=> 'info',
            default     => 'secondary'
        };
    }

    /**
     * Relationship: Thuộc về một buổi học cụ thể
     */
    public function lichHoc()
    {
        return $this->belongsTo(LichHoc::class, 'lich_hoc_id');
    }

    /**
     * Scope: Chỉ lấy tài nguyên đã mở cho học viên
     */
    public function scopeHienThi($query)
    {
        return $query->where('trang_thai_hien_thi', 'hien');
    }
}
