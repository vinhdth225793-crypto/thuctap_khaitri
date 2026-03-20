<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DapAnCauHoi extends Model
{
    use HasFactory;

    protected $table = 'dap_an_cau_hoi';

    protected $fillable = [
        'ngan_hang_cau_hoi_id',
        'ky_hieu',
        'noi_dung',
        'is_dap_an_dung',
        'thu_tu',
    ];

    protected $casts = [
        'is_dap_an_dung' => 'boolean',
        'thu_tu' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function cauHoi(): BelongsTo
    {
        return $this->belongsTo(NganHangCauHoi::class, 'ngan_hang_cau_hoi_id');
    }
}
