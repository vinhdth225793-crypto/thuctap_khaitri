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
        Schema::table('ngan_hang_cau_hoi', function (Blueprint $table) {
            // Thêm các cột theo prompt v2 nếu chưa có
            if (!Schema::hasColumn('ngan_hang_cau_hoi', 'noi_dung_cau_hoi')) {
                $table->text('noi_dung_cau_hoi')->after('khoa_hoc_id');
            }
            if (!Schema::hasColumn('ngan_hang_cau_hoi', 'dap_an_sai_1')) {
                $table->text('dap_an_sai_1')->after('noi_dung_cau_hoi');
            }
            if (!Schema::hasColumn('ngan_hang_cau_hoi', 'dap_an_sai_2')) {
                $table->text('dap_an_sai_2')->after('dap_an_sai_1');
            }
            if (!Schema::hasColumn('ngan_hang_cau_hoi', 'dap_an_sai_3')) {
                $table->text('dap_an_sai_3')->after('dap_an_sai_2');
            }
            if (!Schema::hasColumn('ngan_hang_cau_hoi', 'dap_an_dung')) {
                $table->text('dap_an_dung')->after('dap_an_sai_3');
            }
            
            // Thêm Soft Deletes nếu chưa có
            if (!Schema::hasColumn('ngan_hang_cau_hoi', 'deleted_at')) {
                $table->softDeletes();
            }

            // Fix các cột cũ bắt buộc nhưng cấu trúc mới không dùng tới
            $table->string('ma_cau_hoi', 60)->nullable()->change();
            $table->longText('noi_dung')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ngan_hang_cau_hoi', function (Blueprint $table) {
            $table->dropColumn([
                'noi_dung_cau_hoi',
                'dap_an_sai_1',
                'dap_an_sai_2',
                'dap_an_sai_3',
                'dap_an_dung'
            ]);
            $table->dropSoftDeletes();
        });
    }
};
