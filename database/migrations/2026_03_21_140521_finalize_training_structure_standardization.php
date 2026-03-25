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
        // 1. Chuẩn hóa bảng phan_cong_module_giang_vien
        if (Schema::hasTable('phan_cong_module_giang_vien')) {
            Schema::table('phan_cong_module_giang_vien', function (Blueprint $table) {
                // Đổi tên giao_vien_id thành giang_vien_id nếu đang dùng tên cũ
                if (Schema::hasColumn('phan_cong_module_giang_vien', 'giao_vien_id')) {
                    $table->renameColumn('giao_vien_id', 'giang_vien_id');
                }
            });
        }

        // 2. Đảm bảo ràng buộc cascade cho lich_hoc
        if (Schema::hasTable('lich_hoc')) {
            // Lấy danh sách foreign keys để kiểm tra
            Schema::table('lich_hoc', function (Blueprint $table) {
                try {
                    $table->dropForeign(['module_hoc_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist with this name or already dropped
                }
                
                $table->foreign('module_hoc_id')
                      ->references('id')->on('module_hoc')
                      ->onDelete('cascade');
            });
        }

        // 3. Chuẩn hóa bảng hoc_vien_khoa_hoc
        if (Schema::hasTable('hoc_vien_khoa_hoc')) {
            Schema::table('hoc_vien_khoa_hoc', function (Blueprint $table) {
                if (Schema::hasColumn('hoc_vien_khoa_hoc', 'created_by')) {
                    // Phải để nullable để khớp với SET NULL constraint hiện có
                    $table->unsignedBigInteger('created_by')->nullable()->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Logic rollback nếu cần
    }
};
