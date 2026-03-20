<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class LichHoc extends Model
{
    use HasFactory;

    public const ONLINE_JOIN_EARLY_MINUTES = 15;

    protected $table = 'lich_hoc';

    protected $fillable = [
        'khoa_hoc_id',
        'module_hoc_id',
        'giang_vien_id',
        'ngay_hoc',
        'gio_bat_dau',
        'gio_ket_thuc',
        'thu_trong_tuan',
        'buoi_so',
        'phong_hoc',
        'hinh_thuc',
        'link_online',
        'nen_tang',
        'meeting_id',
        'mat_khau_cuoc_hop',
        'trang_thai',
        'ghi_chu',
        'bao_cao_giang_vien',
        'thoi_gian_bao_cao',
        'trang_thai_bao_cao',
    ];

    /**
     * Relationship: Má»™t buá»•i há»c cÃ³ nhiá»u tÃ i nguyÃªn
     */
    public function taiNguyen(): HasMany
    {
        return $this->hasMany(TaiNguyenBuoiHoc::class, 'lich_hoc_id');
    }

    /**
     * Relationship: Má»™t buá»•i há»c cÃ³ nhiá»u bÃ i kiá»ƒm tra
     */
    public function baiKiemTras(): HasMany
    {
        return $this->hasMany(BaiKiemTra::class, 'lich_hoc_id');
    }

    /**
     * Relationship: Má»™t buá»•i há»c cÃ³ nhiá»u báº£n ghi Ä‘iá»ƒm danh
     */
    public function diemDanhs(): HasMany
    {
        return $this->hasMany(DiemDanh::class, 'lich_hoc_id');
    }

    protected $casts = [
        'ngay_hoc' => 'date',
        'thoi_gian_bao_cao' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Thuá»™c vá» má»™t khÃ³a há»c
     */
    public function khoaHoc(): BelongsTo
    {
        return $this->belongsTo(KhoaHoc::class, 'khoa_hoc_id');
    }

    /**
     * Relationship: Thuá»™c vá» má»™t module há»c
     */
    public function moduleHoc(): BelongsTo
    {
        return $this->belongsTo(ModuleHoc::class, 'module_hoc_id');
    }

    /**
     * Relationship: Giáº£ng viÃªn phá»¥ trÃ¡ch buá»•i nÃ y
     */
    public function giangVien(): BelongsTo
    {
        return $this->belongsTo(GiangVien::class, 'giang_vien_id');
    }

    /**
     * NhÃ£n cho cÃ¡c thá»© trong tuáº§n
     */
    public static $thuLabels = [
        2 => 'Thá»© 2',
        3 => 'Thá»© 3',
        4 => 'Thá»© 4',
        5 => 'Thá»© 5',
        6 => 'Thá»© 6',
        7 => 'Thá»© 7',
        8 => 'Chá»§ nháº­t',
    ];

    /**
     * Accessor: NhÃ£n thá»© trong tuáº§n
     */
    public function getThuLabelAttribute(): string
    {
        return self::$thuLabels[$this->thu_trong_tuan] ?? 'â”€';
    }

    public function getStartsAtAttribute(): ?Carbon
    {
        if (!$this->ngay_hoc || blank($this->gio_bat_dau)) {
            return null;
        }

        return $this->ngay_hoc->copy()->setTimeFromTimeString((string) $this->gio_bat_dau);
    }

    public function getEndsAtAttribute(): ?Carbon
    {
        if (!$this->ngay_hoc || blank($this->gio_ket_thuc)) {
            return null;
        }

        return $this->ngay_hoc->copy()->setTimeFromTimeString((string) $this->gio_ket_thuc);
    }

    public function getTimelineTrangThaiAttribute(): string
    {
        if ($this->trang_thai === 'huy') {
            return 'huy';
        }

        if ($this->trang_thai === 'hoan_thanh') {
            return 'hoan_thanh';
        }

        $startsAt = $this->starts_at;
        $endsAt = $this->ends_at;

        if ($startsAt && $endsAt) {
            $now = now();

            if ($now->greaterThan($endsAt)) {
                return 'hoan_thanh';
            }

            if ($now->greaterThanOrEqualTo($startsAt) && $now->lessThanOrEqualTo($endsAt)) {
                return 'dang_hoc';
            }
        }

        return $this->trang_thai === 'dang_hoc' ? 'dang_hoc' : 'cho';
    }

    public function getIsUpcomingAttribute(): bool
    {
        return $this->timeline_trang_thai === 'cho'
            && $this->starts_at
            && $this->starts_at->isFuture();
    }

    public function getIsInProgressAttribute(): bool
    {
        return $this->timeline_trang_thai === 'dang_hoc';
    }

    public function getIsEndedAttribute(): bool
    {
        return $this->timeline_trang_thai === 'hoan_thanh';
    }

    /**
     * Accessor: NhÃ£n tráº¡ng thÃ¡i buá»•i há»c
     */
    public function getTrangThaiLabelAttribute(): string
    {
        return match ($this->timeline_trang_thai) {
            'cho' => 'Chá»',
            'dang_hoc' => 'Äang há»c',
            'hoan_thanh' => 'HoÃ n thÃ nh',
            'huy' => 'ÄÃ£ há»§y',
            default => 'â”€',
        };
    }

    /**
     * Accessor: MÃ u sáº¯c tráº¡ng thÃ¡i buá»•i há»c
     */
    public function getTrangThaiColorAttribute(): string
    {
        return match ($this->timeline_trang_thai) {
            'cho' => 'secondary',
            'dang_hoc' => 'primary',
            'hoan_thanh' => 'success',
            'huy' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Accessor: NhÃ£n hÃ¬nh thá»©c há»c
     */
    public function getHinhThucLabelAttribute(): string
    {
        return match ($this->hinh_thuc) {
            'online' => 'Online',
            'truc_tiep' => 'Trá»±c tiáº¿p',
            default => 'ChÆ°a cáº­p nháº­t',
        };
    }

    /**
     * Accessor: MÃ u hiá»ƒn thá»‹ cho hÃ¬nh thá»©c há»c
     */
    public function getHinhThucColorAttribute(): string
    {
        return match ($this->hinh_thuc) {
            'online' => 'info',
            'truc_tiep' => 'success',
            default => 'secondary',
        };
    }

    /**
     * Accessor: TÃªn ná»n táº£ng há»c online
     */
    public function getNenTangLabelAttribute(): string
    {
        return filled($this->nen_tang) ? $this->nen_tang : 'ChÆ°a cáº­p nháº­t';
    }

    /**
     * Accessor: Kiá»ƒm tra há»c viÃªn cÃ³ thá»ƒ vÃ o lá»›p online hay khÃ´ng
     */
    public function getCanJoinOnlineAttribute(): bool
    {
        if ($this->hinh_thuc !== 'online' || blank($this->link_online) || $this->trang_thai === 'huy') {
            return false;
        }

        $startsAt = $this->starts_at;
        $endsAt = $this->ends_at;

        if (!$startsAt || !$endsAt) {
            return $this->trang_thai === 'dang_hoc';
        }

        $joinOpensAt = $startsAt->copy()->subMinutes(self::ONLINE_JOIN_EARLY_MINUTES);
        $now = now();

        return $now->greaterThanOrEqualTo($joinOpensAt)
            && $now->lessThanOrEqualTo($endsAt);
    }

    /**
     * Accessor: NhÃ£n tráº¡ng thÃ¡i truy cáº­p lá»›p online
     */
    public function getOnlineJoinStateLabelAttribute(): string
    {
        if ($this->hinh_thuc !== 'online') {
            return 'KhÃ´ng Ã¡p dá»¥ng';
        }

        if (blank($this->link_online)) {
            return 'ChÆ°a cÃ³ link';
        }

        if ($this->can_join_online) {
            return 'CÃ³ thá»ƒ vÃ o lá»›p';
        }

        return match ($this->timeline_trang_thai) {
            'dang_hoc' => 'CÃ³ thá»ƒ vÃ o lá»›p',
            'cho' => 'ChÆ°a tá»›i giá»',
            'hoan_thanh' => 'ÄÃ£ káº¿t thÃºc',
            'huy' => 'ÄÃ£ há»§y',
            default => 'ChÆ°a thá»ƒ vÃ o lá»›p',
        };
    }

    /**
     * Accessor: MÃ u tráº¡ng thÃ¡i truy cáº­p lá»›p online
     */
    public function getOnlineJoinStateColorAttribute(): string
    {
        if ($this->hinh_thuc !== 'online') {
            return 'secondary';
        }

        if (blank($this->link_online)) {
            return 'warning';
        }

        if ($this->can_join_online) {
            return 'info';
        }

        return match ($this->timeline_trang_thai) {
            'dang_hoc' => 'info',
            'cho' => 'secondary',
            'hoan_thanh' => 'success',
            'huy' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Accessor: ThÃ´ng Ä‘iá»‡p hÆ°á»›ng dáº«n cho há»c viÃªn khi vÃ o lá»›p online
     */
    public function getOnlineJoinMessageAttribute(): string
    {
        if ($this->hinh_thuc !== 'online') {
            return 'Buá»•i há»c nÃ y diá»…n ra trá»±c tiáº¿p táº¡i lá»›p.';
        }

        if (blank($this->link_online)) {
            return 'Giáº£ng viÃªn chÆ°a cáº­p nháº­t link phÃ²ng há»c online cho buá»•i nÃ y.';
        }

        if ($this->can_join_online) {
            if ($this->starts_at && now()->lt($this->starts_at)) {
                return 'PhÃ²ng há»c online Ä‘Ã£ má»Ÿ sá»›m Ä‘á»ƒ báº¡n chuáº©n bá»‹ trÆ°á»›c buá»•i há»c.';
            }

            return 'Buá»•i há»c online Ä‘ang diá»…n ra. Báº¡n cÃ³ thá»ƒ vÃ o phÃ²ng há»c ngay bÃ¢y giá».';
        }

        return match ($this->timeline_trang_thai) {
            'dang_hoc' => 'Buá»•i há»c online Ä‘ang diá»…n ra nhÆ°ng báº¡n chÆ°a thá»ƒ vÃ o phÃ²ng há»c lÃºc nÃ y.',
            'cho' => 'PhÃ²ng há»c sáº½ má»Ÿ trÆ°á»›c giá» báº¯t Ä‘áº§u khoáº£ng ' . self::ONLINE_JOIN_EARLY_MINUTES . ' phÃºt.',
            'hoan_thanh' => 'Buá»•i há»c online nÃ y Ä‘Ã£ hoÃ n thÃ nh, phÃ²ng há»c khÃ´ng cÃ²n má»Ÿ cho há»c viÃªn.',
            'huy' => 'Buá»•i há»c online nÃ y Ä‘Ã£ bá»‹ há»§y. Vui lÃ²ng theo dÃµi thÃ´ng bÃ¡o má»›i tá»« giáº£ng viÃªn hoáº·c trung tÃ¢m.',
            default => 'Hiá»‡n chÆ°a Ä‘á»§ Ä‘iá»u kiá»‡n Ä‘á»ƒ vÃ o phÃ²ng há»c online.',
        };
    }
}
