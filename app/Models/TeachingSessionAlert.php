<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeachingSessionAlert extends Model
{
    use HasFactory;

    public const TYPE_VAO_TRE = 'vao_tre';
    public const TYPE_KHONG_DAY = 'khong_day';
    public const TYPE_CHUA_CHECKOUT = 'chua_checkout';
    public const TYPE_DONG_SOM = 'dong_som';
    public const TYPE_NGOAI_KHUNG = 'ngoai_khung';

    public const SEVERITY_INFO = 'info';
    public const SEVERITY_WARNING = 'warning';
    public const SEVERITY_DANGER = 'danger';

    public const STATUS_OPEN = 'open';
    public const STATUS_NOTIFIED = 'notified';
    public const STATUS_RESOLVED = 'resolved';

    protected $table = 'teaching_session_alerts';

    protected $fillable = [
        'lich_hoc_id',
        'giang_vien_id',
        'alert_key',
        'alert_type',
        'severity',
        'status',
        'tieu_de',
        'noi_dung',
        'metadata',
        'notified_admin_at',
        'resolved_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'notified_admin_at' => 'datetime',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function lichHoc(): BelongsTo
    {
        return $this->belongsTo(LichHoc::class, 'lich_hoc_id');
    }

    public function giangVien(): BelongsTo
    {
        return $this->belongsTo(GiangVien::class, 'giang_vien_id');
    }
}
