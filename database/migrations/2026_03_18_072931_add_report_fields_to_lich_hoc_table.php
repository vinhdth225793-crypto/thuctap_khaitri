<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lich_hoc', function (Blueprint $table) {
            $table->text('bao_cao_giang_vien')->nullable()->after('ghi_chu');
            $table->timestamp('thoi_gian_bao_cao')->nullable()->after('bao_cao_giang_vien');
            $table->string('trang_thai_bao_cao')->default('chua_bao_cao')->after('thoi_gian_bao_cao'); // chua_bao_cao, da_bao_cao
        });
    }

    public function down(): void
    {
        Schema::table('lich_hoc', function (Blueprint $table) {
            $table->dropColumn(['bao_cao_giang_vien', 'thoi_gian_bao_cao', 'trang_thai_bao_cao']);
        });
    }
};
