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

    public const LOAI_TRAC_NGHIEM = 'trac_nghiem';
    public const LOAI_TU_LUAN = 'tu_luan';

    public const KIEU_MOT_DAP_AN = 'mot_dap_an';
    public const KIEU_NHIEU_DAP_AN = 'nhieu_dap_an';
    public const KIEU_DUNG_SAI = 'dung_sai';

    public const TRANG_THAI_NHAP = 'nhap';
    public const TRANG_THAI_SAN_SANG = 'san_sang';
    public const TRANG_THAI_TAM_AN = 'tam_an';

    protected $table = 'ngan_hang_cau_hoi';

    protected $fillable = [
        'khoa_hoc_id',
        'nguoi_tao_id',
        'module_hoc_id',
        'ma_cau_hoi',
        'noi_dung',
        'loai_cau_hoi',
        'kieu_dap_an',
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

            if ($cauHoi->loai_cau_hoi === self::LOAI_TRAC_NGHIEM && blank($cauHoi->kieu_dap_an)) {
                $cauHoi->kieu_dap_an = self::KIEU_MOT_DAP_AN;
            }
        });
    }

    public function scopeSanSang($query)
    {
        return $query->where('trang_thai', self::TRANG_THAI_SAN_SANG);
    }

    public function scopeDungChoFlowRaDeHienTai($query)
    {
        return $query->where(function ($nestedQuery) {
            $nestedQuery->where('loai_cau_hoi', self::LOAI_TU_LUAN)
                ->orWhere(function ($objectiveQuery) {
                    $objectiveQuery->where('loai_cau_hoi', self::LOAI_TRAC_NGHIEM)
                        ->whereIn('kieu_dap_an', [self::KIEU_MOT_DAP_AN, self::KIEU_DUNG_SAI]);
                });
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
        return match (true) {
            $this->is_true_false => 'Đúng/Sai',
            $this->is_multiple_correct => 'Trắc nghiệm nhiều đáp án',
            $this->is_single_correct => 'Trắc nghiệm một đáp án',
            $this->is_essay => 'Tự luận',
            default => 'Không xác định',
        };
    }

    public function getKieuDapAnLabelAttribute(): string
    {
        return match ($this->kieu_dap_an) {
            self::KIEU_MOT_DAP_AN => 'Một đáp án đúng',
            self::KIEU_NHIEU_DAP_AN => 'Nhiều đáp án đúng',
            self::KIEU_DUNG_SAI => 'Đúng/Sai',
            default => $this->is_essay ? 'Tự luận' : 'Chưa cấu hình',
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

    public function getTrangThaiLabelAttribute(): string
    {
        return match ($this->trang_thai) {
            self::TRANG_THAI_NHAP => 'Nháp',
            self::TRANG_THAI_SAN_SANG => 'Sẵn sàng',
            self::TRANG_THAI_TAM_AN => 'Tạm ẩn',
            default => 'Không xác định',
        };
    }

    public function getTrangThaiColorAttribute(): string
    {
        return match ($this->trang_thai) {
            self::TRANG_THAI_NHAP => 'secondary',
            self::TRANG_THAI_SAN_SANG => 'success',
            self::TRANG_THAI_TAM_AN => 'warning',
            default => 'secondary',
        };
    }

    public function getLoaiHienThiLabelAttribute(): string
    {
        return $this->moduleHoc?->ten_module
            ? ($this->khoaHoc?->ten_khoa_hoc . ' / ' . $this->moduleHoc->ten_module)
            : ($this->khoaHoc?->ten_khoa_hoc ?? 'Chưa gắn khóa học');
    }

    public function getIsEssayAttribute(): bool
    {
        return $this->loai_cau_hoi === self::LOAI_TU_LUAN;
    }

    public function getIsObjectiveAttribute(): bool
    {
        return $this->loai_cau_hoi === self::LOAI_TRAC_NGHIEM;
    }

    public function getIsSingleCorrectAttribute(): bool
    {
        return $this->is_objective && $this->kieu_dap_an === self::KIEU_MOT_DAP_AN;
    }

    public function getIsMultipleCorrectAttribute(): bool
    {
        return $this->is_objective && $this->kieu_dap_an === self::KIEU_NHIEU_DAP_AN;
    }

    public function getIsTrueFalseAttribute(): bool
    {
        return $this->is_objective && $this->kieu_dap_an === self::KIEU_DUNG_SAI;
    }

    public function getSupportsCurrentExamBuilderAttribute(): bool
    {
        return $this->is_essay || $this->is_single_correct || $this->is_true_false;
    }

    public function getCorrectAnswerTextsAttribute()
    {
        return $this->resolvedAnswers()
            ->where('is_dap_an_dung', true)
            ->pluck('noi_dung')
            ->values();
    }

    public function getCorrectAnswerSummaryAttribute(): string
    {
        if ($this->is_essay) {
            return 'Giảng viên chấm tự luận';
        }

        $answers = $this->correct_answer_texts;

        return $answers->isNotEmpty()
            ? $answers->implode(' | ')
            : 'Chưa có đáp án đúng';
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
