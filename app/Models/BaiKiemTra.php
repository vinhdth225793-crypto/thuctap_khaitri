<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BaiKiemTra extends Model
{
    use HasFactory;

    protected $table = 'bai_kiem_tra';

    protected $fillable = [
        'khoa_hoc_id',
        'module_hoc_id',
        'lich_hoc_id',
        'tieu_de',
        'mo_ta',
        'thoi_gian_lam_bai',
        'ngay_mo',
        'ngay_dong',
        'pham_vi',
        'loai_bai_kiem_tra',
        'loai_noi_dung',
        'trang_thai_duyet',
        'trang_thai_phat_hanh',
        'tong_diem',
        'che_do_tinh_diem',
        'so_cau_goi_diem',
        'so_lan_duoc_lam',
        'randomize_questions',
        'randomize_answers',
        'co_giam_sat',
        'bat_buoc_fullscreen',
        'bat_buoc_camera',
        'so_lan_vi_pham_toi_da',
        'chu_ky_snapshot_giay',
        'tu_dong_nop_khi_vi_pham',
        'chan_copy_paste',
        'chan_chuot_phai',
        'nguoi_tao_id',
        'nguoi_duyet_id',
        'de_xuat_duyet_luc',
        'duyet_luc',
        'phat_hanh_luc',
        'ghi_chu_duyet',
        'trang_thai',
    ];

    protected $casts = [
        'ngay_mo' => 'datetime',
        'ngay_dong' => 'datetime',
        'tong_diem' => 'decimal:2',
        'so_lan_duoc_lam' => 'integer',
        'so_cau_goi_diem' => 'integer',
        'randomize_questions' => 'boolean',
        'randomize_answers' => 'boolean',
        'co_giam_sat' => 'boolean',
        'bat_buoc_fullscreen' => 'boolean',
        'bat_buoc_camera' => 'boolean',
        'so_lan_vi_pham_toi_da' => 'integer',
        'chu_ky_snapshot_giay' => 'integer',
        'tu_dong_nop_khi_vi_pham' => 'boolean',
        'chan_copy_paste' => 'boolean',
        'chan_chuot_phai' => 'boolean',
        'de_xuat_duyet_luc' => 'datetime',
        'duyet_luc' => 'datetime',
        'phat_hanh_luc' => 'datetime',
        'trang_thai' => 'boolean',
    ];

    public function khoaHoc(): BelongsTo
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    public function moduleHoc(): BelongsTo
    {
        return $this->belongsTo(ModuleHoc::class, 'module_hoc_id');
    }

    public function lichHoc(): BelongsTo
    {
        return $this->belongsTo(LichHoc::class, 'lich_hoc_id');
    }

    public function baiLams(): HasMany
    {
        return $this->hasMany(BaiLamBaiKiemTra::class, 'bai_kiem_tra_id');
    }

    public function chiTietCauHois(): HasMany
    {
        return $this->hasMany(ChiTietBaiKiemTra::class, 'bai_kiem_tra_id')->orderBy('thu_tu');
    }

    public function cauHois(): BelongsToMany
    {
        return $this->belongsToMany(NganHangCauHoi::class, 'chi_tiet_bai_kiem_tra', 'bai_kiem_tra_id', 'ngan_hang_cau_hoi_id')
            ->withPivot(['id', 'thu_tu', 'diem_so', 'bat_buoc'])
            ->withTimestamps();
    }

    public function nguoiTao(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_tao_id', 'ma_nguoi_dung');
    }

    public function nguoiDuyet(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_duyet_id', 'ma_nguoi_dung');
    }

    public function getPhamViLabelAttribute(): string
    {
        return match ($this->pham_vi) {
            'module' => 'Theo module',
            'buoi_hoc' => 'Theo buổi học',
            'cuoi_khoa' => 'Cuối khóa',
            default => 'Không xác định',
        };
    }

    public function getLoaiBaiKiemTraLabelAttribute(): string
    {
        return match ($this->loai_bai_kiem_tra) {
            'cuoi_khoa' => 'Cuối khóa',
            'buoi_hoc' => 'Theo buổi học',
            default => 'Theo module',
        };
    }

    public function getLoaiNoiDungLabelAttribute(): string
    {
        return match ($this->loai_noi_dung) {
            'trac_nghiem' => 'Trắc nghiệm',
            'tu_luan' => 'Tự luận',
            'hon_hop' => 'Hỗn hợp',
            default => 'Không xác định',
        };
    }

    public function getCheDoGiamSatLabelAttribute(): string
    {
        return $this->co_giam_sat
            ? 'Giám sát nâng cao'
            : 'Bài kiểm tra thường';
    }

    public function getTrangThaiDuyetLabelAttribute(): string
    {
        return match ($this->trang_thai_duyet) {
            'nhap' => 'Nháp',
            'cho_duyet' => 'Chờ duyệt',
            'da_duyet' => 'Đã duyệt',
            'tu_choi' => 'Từ chối',
            default => 'Không xác định',
        };
    }

    public function getTrangThaiPhatHanhLabelAttribute(): string
    {
        return match ($this->trang_thai_phat_hanh) {
            'nhap' => 'Nháp',
            'phat_hanh' => 'Phát hành',
            'dong' => 'Đóng',
            default => 'Không xác định',
        };
    }

    public function getIsPublishedForStudentsAttribute(): bool
    {
        return $this->trang_thai
            && $this->trang_thai_duyet === 'da_duyet'
            && $this->trang_thai_phat_hanh === 'phat_hanh';
    }

    public function getAccessStatusKeyAttribute(): string
    {
        if (!$this->is_published_for_students) {
            return 'an';
        }

        if ($this->ngay_mo && $this->ngay_mo->isFuture()) {
            return 'sap_mo';
        }

        if ($this->ngay_dong && $this->ngay_dong->isPast()) {
            return 'da_dong';
        }

        return 'dang_mo';
    }

    public function getAccessStatusLabelAttribute(): string
    {
        return match ($this->access_status_key) {
            'dang_mo' => 'Đang mở',
            'sap_mo' => 'Sắp mở',
            'da_dong' => 'Đã đóng',
            'an' => 'Tạm ẩn',
            default => 'Không xác định',
        };
    }

    public function getAccessStatusColorAttribute(): string
    {
        return match ($this->access_status_key) {
            'dang_mo' => 'success',
            'sap_mo' => 'warning',
            'da_dong' => 'secondary',
            'an' => 'dark',
            default => 'secondary',
        };
    }

    public function getCanStudentStartAttribute(): bool
    {
        return $this->access_status_key === 'dang_mo';
    }

    public function getQuestionCountAttribute(): int
    {
        return $this->chi_tiet_cau_hois_count ?? $this->chiTietCauHois()->count();
    }

    public function getHasEssayQuestionsAttribute(): bool
    {
        return in_array($this->loai_noi_dung, ['tu_luan', 'hon_hop'], true);
    }

    public function getHasMultipleChoiceQuestionsAttribute(): bool
    {
        return in_array($this->loai_noi_dung, ['trac_nghiem', 'hon_hop'], true);
    }

    public function getCanUseSurveillanceAttribute(): bool
    {
        return $this->co_giam_sat;
    }
}
