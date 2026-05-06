<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm cột pham_vi vào ngan_hang_cau_hoi để phân biệt:
 *   - rieng_tu: chỉ người tạo + admin thấy được
 *   - cong_bo: tất cả giảng viên có thể xem & dùng câu hỏi này
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ngan_hang_cau_hoi', function (Blueprint $table) {
            if (! Schema::hasColumn('ngan_hang_cau_hoi', 'pham_vi')) {
                $table->enum('pham_vi', ['rieng_tu', 'cong_bo'])
                    ->default('rieng_tu')
                    ->after('trang_thai')
                    ->comment('rieng_tu: chỉ creator + admin thấy; cong_bo: tất cả GV thấy');
                $table->timestamp('cong_bo_luc')->nullable()->after('pham_vi');
                $table->unsignedBigInteger('cong_bo_boi_id')->nullable()->after('cong_bo_luc');
                $table->index(['pham_vi', 'trang_thai']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('ngan_hang_cau_hoi', function (Blueprint $table) {
            if (Schema::hasColumn('ngan_hang_cau_hoi', 'pham_vi')) {
                $table->dropIndex(['pham_vi', 'trang_thai']);
                $table->dropColumn(['pham_vi', 'cong_bo_luc', 'cong_bo_boi_id']);
            }
        });
    }
};
