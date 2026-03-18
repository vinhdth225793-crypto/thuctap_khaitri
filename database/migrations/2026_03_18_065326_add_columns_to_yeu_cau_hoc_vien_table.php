<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('yeu_cau_hoc_vien', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_duyet_id')->nullable()->after('trang_thai');
            $table->timestamp('thoi_gian_duyet')->nullable()->after('admin_duyet_id');
            
            $table->foreign('admin_duyet_id')->references('ma_nguoi_dung')->on('nguoi_dung')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('yeu_cau_hoc_vien', function (Blueprint $table) {
            $table->dropForeign(['admin_duyet_id']);
            $table->dropColumn(['admin_duyet_id', 'thoi_gian_duyet']);
        });
    }
};
