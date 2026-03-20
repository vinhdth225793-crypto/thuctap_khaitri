<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class NganHangCauHoi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ngan_hang_cau_hoi';

    protected $fillable = [
        'khoa_hoc_id',
        'noi_dung_cau_hoi',
        'dap_an_sai_1',
        'dap_an_sai_2',
        'dap_an_sai_3',
        'dap_an_dung',
        'nguoi_tao_id',
        'module_hoc_id',
        'ma_cau_hoi',
        'noi_dung',
        'loai_cau_hoi',
        'muc_do',
        'diem_mac_dinh',
        'goi_y_tra_loi',
        'giai_thich_dap_an',
        'trang_thai',
        'co_the_tai_su_dung',
    ];

    public function khoaHoc(): BelongsTo
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    public function nguoiTao(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_tao_id', 'ma_nguoi_dung');
    }

    /**
     * Relationship: Một câu hỏi có nhiều đáp án (Cấu trúc cũ, dùng cho backward compatibility)
     */
    public function dapAns()
    {
        return $this->hasMany(DapAnCauHoi::class, 'ngan_hang_cau_hoi_id', 'id');
    }

    /**
     * Chuẩn hóa nội dung trước khi so sánh
     */
    public static function normalizeString($str): string
    {
        if (is_null($str)) return '';
        // trim khoảng trắng đầu cuối
        $str = trim((string) $str);
        // gộp nhiều khoảng trắng thành 1
        $str = preg_replace('/\s+/', ' ', $str);
        // lowercase
        return mb_strtolower((string) $str, 'UTF-8');
    }

    /**
     * Kiểm tra trùng lặp câu hỏi trong cùng khóa học
     */
    public static function isDuplicate($khoaHocId, $noiDung, $excludeId = null): bool
    {
        $normalizedInput = self::normalizeString($noiDung);
        
        $query = self::where('khoa_hoc_id', $khoaHocId);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        // Do nội dung câu hỏi có thể dài, ta sẽ lấy danh sách các câu hỏi trong khóa học đó về rồi so sánh
        // Tuy nhiên để tối ưu hơn, ta có thể dùng raw SQL nếu DB hỗ trợ tốt việc chuẩn hóa chuỗi
        // Ở đây ta dùng cách đơn giản là lấy các câu hỏi có nội dung gần giống hoặc query trực tiếp
        
        return $query->get()->contains(function ($item) use ($normalizedInput) {
            return self::normalizeString($item->noi_dung_cau_hoi) === $normalizedInput;
        });
    }
}
