<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bai_lam_bai_kiem_tra', function (Blueprint $table) {
            if (!Schema::hasColumn('bai_lam_bai_kiem_tra', 'lan_lam_thu')) {
                $table->unsignedInteger('lan_lam_thu')->default(1)->after('hoc_vien_id');
            }

            if (!Schema::hasColumn('bai_lam_bai_kiem_tra', 'tong_diem_trac_nghiem')) {
                $table->decimal('tong_diem_trac_nghiem', 8, 2)->nullable()->after('diem_so');
            }

            if (!Schema::hasColumn('bai_lam_bai_kiem_tra', 'tong_diem_tu_luan')) {
                $table->decimal('tong_diem_tu_luan', 8, 2)->nullable()->after('tong_diem_trac_nghiem');
            }

            if (!Schema::hasColumn('bai_lam_bai_kiem_tra', 'trang_thai_cham')) {
                $table->string('trang_thai_cham', 50)->default('chua_cham')->after('tong_diem_tu_luan');
            }

            if (!Schema::hasColumn('bai_lam_bai_kiem_tra', 'auto_graded_at')) {
                $table->dateTime('auto_graded_at')->nullable()->after('trang_thai_cham');
            }

            if (!Schema::hasColumn('bai_lam_bai_kiem_tra', 'manual_graded_at')) {
                $table->dateTime('manual_graded_at')->nullable()->after('auto_graded_at');
            }

            if (!Schema::hasColumn('bai_lam_bai_kiem_tra', 'nguoi_cham_id')) {
                $table->unsignedBigInteger('nguoi_cham_id')->nullable()->after('manual_graded_at');
            }
        });

        Schema::table('bai_lam_bai_kiem_tra', function (Blueprint $table) {
            // Drop foreign keys first because the unique index might be used by them
            try {
                $table->dropForeign(['bai_kiem_tra_id']);
            } catch (\Throwable $e) {}
            
            try {
                $table->dropForeign(['hoc_vien_id']);
            } catch (\Throwable $e) {}

            // Now drop the old unique index
            try {
                $table->dropUnique('unique_bai_lam_hoc_vien');
            } catch (\Throwable $exception) {}

            // Re-add the foreign keys
            try {
                $table->foreign('bai_kiem_tra_id')
                    ->references('id')
                    ->on('bai_kiem_tra')
                    ->onDelete('cascade');
            } catch (\Throwable $e) {}

            try {
                $table->foreign('hoc_vien_id')
                    ->references('ma_nguoi_dung')
                    ->on('nguoi_dung')
                    ->onDelete('cascade');
            } catch (\Throwable $e) {}

            // Add new unique index including lan_lam_thu
            try {
                $table->unique(['bai_kiem_tra_id', 'hoc_vien_id', 'lan_lam_thu'], 'uniq_bai_lam_hoc_vien_lan');
            } catch (\Throwable $exception) {}

            // Add new foreign key for nguoi_cham_id
            try {
                $table->foreign('nguoi_cham_id')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();
            } catch (\Throwable $exception) {}
        });
    }

    public function down(): void
    {
        Schema::table('bai_lam_bai_kiem_tra', function (Blueprint $table) {
            try {
                $table->dropForeign(['nguoi_cham_id']);
            } catch (\Throwable $exception) {}

            try {
                $table->dropForeign(['bai_kiem_tra_id']);
            } catch (\Throwable $e) {}

            try {
                $table->dropForeign(['hoc_vien_id']);
            } catch (\Throwable $e) {}

            try {
                $table->dropUnique('uniq_bai_lam_hoc_vien_lan');
            } catch (\Throwable $exception) {}

            try {
                $table->unique(['bai_kiem_tra_id', 'hoc_vien_id'], 'unique_bai_lam_hoc_vien');
            } catch (\Throwable $exception) {}

            try {
                $table->foreign('bai_kiem_tra_id')
                    ->references('id')
                    ->on('bai_kiem_tra')
                    ->onDelete('cascade');
            } catch (\Throwable $e) {}

            try {
                $table->foreign('hoc_vien_id')
                    ->references('ma_nguoi_dung')
                    ->on('nguoi_dung')
                    ->onDelete('cascade');
            } catch (\Throwable $e) {}
        });

        $columns = array_values(array_filter([
            Schema::hasColumn('bai_lam_bai_kiem_tra', 'lan_lam_thu') ? 'lan_lam_thu' : null,
            Schema::hasColumn('bai_lam_bai_kiem_tra', 'tong_diem_trac_nghiem') ? 'tong_diem_trac_nghiem' : null,
            Schema::hasColumn('bai_lam_bai_kiem_tra', 'tong_diem_tu_luan') ? 'tong_diem_tu_luan' : null,
            Schema::hasColumn('bai_lam_bai_kiem_tra', 'trang_thai_cham') ? 'trang_thai_cham' : null,
            Schema::hasColumn('bai_lam_bai_kiem_tra', 'auto_graded_at') ? 'auto_graded_at' : null,
            Schema::hasColumn('bai_lam_bai_kiem_tra', 'manual_graded_at') ? 'manual_graded_at' : null,
            Schema::hasColumn('bai_lam_bai_kiem_tra', 'nguoi_cham_id') ? 'nguoi_cham_id' : null,
        ]));

        if ($columns !== []) {
            Schema::table('bai_lam_bai_kiem_tra', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
