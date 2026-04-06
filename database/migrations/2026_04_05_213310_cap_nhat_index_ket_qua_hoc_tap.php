<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
            // Thử drop index cũ nếu nó tồn tại
            try {
                $table->dropUnique('uniq_ket_qua_hoc_tap');
            } catch (\Exception $e) {
                // Có thể nó đã bị drop hoặc là lỗi FK giả
            }

            // Thêm index mới phân cấp
            $table->unique(['hoc_vien_id', 'khoa_hoc_id', 'module_hoc_id', 'bai_kiem_tra_id'], 'uniq_ket_qua_phan_cap');
        });
    }

    public function down(): void
    {
        Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
            $table->dropUnique('uniq_ket_qua_phan_cap');
            $table->unique(['khoa_hoc_id', 'hoc_vien_id'], 'uniq_ket_qua_hoc_tap');
        });
    }
};
