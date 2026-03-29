<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tai_nguyen_buoi_hoc', function (Blueprint $table) {
            // Loại tài nguyên hiện đã có loai_tai_nguyen, tiêu đề đã có tieu_de
            // Bổ sung các cột mới phục vụ Phase 1
            $table->string('trang_thai_hien_thi')->default('an')->after('link_ngoai'); // an, hien
            $table->dateTime('ngay_mo_hien_thi')->nullable()->after('trang_thai_hien_thi');
            $table->integer('thu_tu_hien_thi')->default(0)->after('ngay_mo_hien_thi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tai_nguyen_buoi_hoc', function (Blueprint $table) {
            $table->dropColumn(['trang_thai_hien_thi', 'ngay_mo_hien_thi', 'thu_tu_hien_thi']);
        });
    }
};
