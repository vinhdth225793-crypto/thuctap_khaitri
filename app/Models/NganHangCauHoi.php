<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class NganHangCauHoi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ngan_hang_cau_hoi';

    protected $fillable = [
        'khoa_hoc_id',
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

    protected $casts = [
        'diem_mac_dinh' => 'decimal:2',
        'co_the_tai_su_dung' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $cauHoi) {
            if (blank($cauHoi->ma_cau_hoi)) {
                $cauHoi->ma_cau_hoi = self::generateQuestionCode();
            }
        });
    }

    public function khoaHoc(): BelongsTo
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    public function moduleHoc(): BelongsTo
    {
        return $this->belongsTo(ModuleHoc::class, 'module_hoc_id');
    }

    public function nguoiTao(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_tao_id', 'ma_nguoi_dung');
    }

    public function dapAns(): HasMany
    {
        return $this->hasMany(DapAnCauHoi::class, 'ngan_hang_cau_hoi_id', 'id')->orderBy('thu_tu');
    }

    public static function normalizeString($str): string
    {
        if (is_null($str)) {
            return '';
        }

        $str = trim((string) $str);
        $str = preg_replace('/\s+/', ' ', $str);

        return mb_strtolower((string) $str, 'UTF-8');
    }

    public static function isDuplicate($khoaHocId, $noiDung, $excludeId = null): bool
    {
        $normalizedInput = self::normalizeString($noiDung);

        $query = self::query()->where('khoa_hoc_id', $khoaHocId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->get()->contains(function (self $item) use ($normalizedInput) {
            return self::normalizeString($item->noi_dung) === $normalizedInput;
        });
    }

    public function getNoiDungCauHoiAttribute(): ?string
    {
        return $this->noi_dung;
    }

    public function getDapAnDungAttribute(): ?string
    {
        return optional($this->resolvedAnswers()->firstWhere('is_dap_an_dung', true))->noi_dung;
    }

    public function getDapAnSai1Attribute(): ?string
    {
        return optional($this->wrongAnswers()->get(0))->noi_dung;
    }

    public function getDapAnSai2Attribute(): ?string
    {
        return optional($this->wrongAnswers()->get(1))->noi_dung;
    }

    public function getDapAnSai3Attribute(): ?string
    {
        return optional($this->wrongAnswers()->get(2))->noi_dung;
    }

    public function getLoaiCauHoiLabelAttribute(): string
    {
        return match ($this->loai_cau_hoi) {
            'trac_nghiem' => 'Trắc nghiệm',
            'tu_luan' => 'Tự luận',
            default => 'Không xác định',
        };
    }

    public function getMucDoLabelAttribute(): string
    {
        return match ($this->muc_do) {
            'de' => 'Dễ',
            'trung_binh' => 'Trung bình',
            'kho' => 'Khó',
            default => 'Chưa phân loại',
        };
    }

    public static function generateQuestionCode(): string
    {
        return 'CH-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6));
    }

    private function resolvedAnswers()
    {
        return $this->relationLoaded('dapAns') ? $this->dapAns : $this->dapAns()->get();
    }

    private function wrongAnswers()
    {
        return $this->resolvedAnswers()->where('is_dap_an_dung', false)->values();
    }
}
