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
        // 1. Rename bảng chính
        Schema::rename('mon_hoc', 'nhom_nganh');

        // 2. Đổi tên các cột trong bảng nhom_nganh
        Schema::table('nhom_nganh', function (Blueprint $table) {
            $table->renameColumn('ma_mon_hoc', 'ma_nhom_nganh');
            $table->renameColumn('ten_mon_hoc', 'ten_nhom_nganh');
        });

        // 3. Đổi tên cột và cập nhật khóa ngoại trong bảng khoa_hoc
        Schema::table('khoa_hoc', function (Blueprint $table) {
            // Xóa khóa ngoại cũ (theo tên mặc định của Laravel)
            $table->dropForeign(['mon_hoc_id']);
            
            // Rename cột
            $table->renameColumn('mon_hoc_id', 'nhom_nganh_id');
            
            // Tạo khóa ngoại mới
            $table->foreign('nhom_nganh_id')->references('id')->on('nhom_nganh')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('khoa_hoc', function (Blueprint $table) {
            $table->dropForeign(['nhom_nganh_id']);
            $table->renameColumn('nhom_nganh_id', 'mon_hoc_id');
            $table->foreign('mon_hoc_id')->references('id')->on('mon_hoc')->onDelete('cascade');
        });

        Schema::table('nhom_nganh', function (Blueprint $table) {
            $table->renameColumn('ma_nhom_nganh', 'ma_mon_hoc');
            $table->renameColumn('ten_nhom_nganh', 'ten_mon_hoc');
        });

        Schema::rename('nhom_nganh', 'mon_hoc');
    }
};
