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
        'cau_hoi_id',
        'noi_dung_dap_an',
        'la_dap_an_dung',
        'giai_thich',
    ];

    protected $casts = [
        'la_dap_an_dung' => 'boolean',
    ];

    public function cauHoi(): BelongsTo
    {
        return $this->belongsTo(CauHoi::class, 'cau_hoi_id');
    }
}
