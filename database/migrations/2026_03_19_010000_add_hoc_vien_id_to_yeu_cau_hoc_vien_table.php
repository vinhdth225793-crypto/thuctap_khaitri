<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('yeu_cau_hoc_vien', function (Blueprint $table) {
            if (!Schema::hasColumn('yeu_cau_hoc_vien', 'hoc_vien_id')) {
                $table->unsignedBigInteger('hoc_vien_id')->nullable()->after('giang_vien_id');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE yeu_cau_hoc_vien MODIFY giang_vien_id BIGINT UNSIGNED NULL');
        }

        Schema::table('yeu_cau_hoc_vien', function (Blueprint $table) {
            $table->foreign('hoc_vien_id')
                ->references('ma_nguoi_dung')
                ->on('nguoi_dung')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('yeu_cau_hoc_vien', function (Blueprint $table) {
            $table->dropForeign(['hoc_vien_id']);
            $table->dropColumn('hoc_vien_id');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE yeu_cau_hoc_vien MODIFY giang_vien_id BIGINT UNSIGNED NOT NULL');
        }
    }
};
