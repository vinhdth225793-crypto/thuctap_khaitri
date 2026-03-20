<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bai_kiem_tra', function (Blueprint $table) {
            if (!Schema::hasColumn('bai_kiem_tra', 'loai_bai_kiem_tra')) {
                $table->string('loai_bai_kiem_tra', 50)->default('module')->after('pham_vi');
            }

            if (!Schema::hasColumn('bai_kiem_tra', 'loai_noi_dung')) {
                $table->string('loai_noi_dung', 50)->default('tu_luan')->after('loai_bai_kiem_tra');
            }

            if (!Schema::hasColumn('bai_kiem_tra', 'trang_thai_duyet')) {
                $table->string('trang_thai_duyet', 50)->default('da_duyet')->after('loai_noi_dung');
            }

            if (!Schema::hasColumn('bai_kiem_tra', 'trang_thai_phat_hanh')) {
                $table->string('trang_thai_phat_hanh', 50)->default('phat_hanh')->after('trang_thai_duyet');
            }

            if (!Schema::hasColumn('bai_kiem_tra', 'tong_diem')) {
                $table->decimal('tong_diem', 8, 2)->default(10)->after('trang_thai_phat_hanh');
            }

            if (!Schema::hasColumn('bai_kiem_tra', 'so_lan_duoc_lam')) {
                $table->unsignedInteger('so_lan_duoc_lam')->default(1)->after('tong_diem');
            }

            if (!Schema::hasColumn('bai_kiem_tra', 'randomize_questions')) {
                $table->boolean('randomize_questions')->default(false)->after('so_lan_duoc_lam');
            }

            if (!Schema::hasColumn('bai_kiem_tra', 'nguoi_tao_id')) {
                $table->unsignedBigInteger('nguoi_tao_id')->nullable()->after('randomize_questions');
            }

            if (!Schema::hasColumn('bai_kiem_tra', 'nguoi_duyet_id')) {
                $table->unsignedBigInteger('nguoi_duyet_id')->nullable()->after('nguoi_tao_id');
            }

            if (!Schema::hasColumn('bai_kiem_tra', 'de_xuat_duyet_luc')) {
                $table->dateTime('de_xuat_duyet_luc')->nullable()->after('nguoi_duyet_id');
            }

            if (!Schema::hasColumn('bai_kiem_tra', 'duyet_luc')) {
                $table->dateTime('duyet_luc')->nullable()->after('de_xuat_duyet_luc');
            }

            if (!Schema::hasColumn('bai_kiem_tra', 'phat_hanh_luc')) {
                $table->dateTime('phat_hanh_luc')->nullable()->after('duyet_luc');
            }

            if (!Schema::hasColumn('bai_kiem_tra', 'ghi_chu_duyet')) {
                $table->text('ghi_chu_duyet')->nullable()->after('phat_hanh_luc');
            }
        });

        Schema::table('bai_kiem_tra', function (Blueprint $table) {
            try {
                $table->foreign('nguoi_tao_id')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();
            } catch (\Throwable $exception) {
            }

            try {
                $table->foreign('nguoi_duyet_id')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();
            } catch (\Throwable $exception) {
            }
        });
    }

    public function down(): void
    {
        Schema::table('bai_kiem_tra', function (Blueprint $table) {
            try {
                $table->dropForeign(['nguoi_tao_id']);
            } catch (\Throwable $exception) {
            }

            try {
                $table->dropForeign(['nguoi_duyet_id']);
            } catch (\Throwable $exception) {
            }
        });

        $columns = array_values(array_filter([
            Schema::hasColumn('bai_kiem_tra', 'loai_bai_kiem_tra') ? 'loai_bai_kiem_tra' : null,
            Schema::hasColumn('bai_kiem_tra', 'loai_noi_dung') ? 'loai_noi_dung' : null,
            Schema::hasColumn('bai_kiem_tra', 'trang_thai_duyet') ? 'trang_thai_duyet' : null,
            Schema::hasColumn('bai_kiem_tra', 'trang_thai_phat_hanh') ? 'trang_thai_phat_hanh' : null,
            Schema::hasColumn('bai_kiem_tra', 'tong_diem') ? 'tong_diem' : null,
            Schema::hasColumn('bai_kiem_tra', 'so_lan_duoc_lam') ? 'so_lan_duoc_lam' : null,
            Schema::hasColumn('bai_kiem_tra', 'randomize_questions') ? 'randomize_questions' : null,
            Schema::hasColumn('bai_kiem_tra', 'nguoi_tao_id') ? 'nguoi_tao_id' : null,
            Schema::hasColumn('bai_kiem_tra', 'nguoi_duyet_id') ? 'nguoi_duyet_id' : null,
            Schema::hasColumn('bai_kiem_tra', 'de_xuat_duyet_luc') ? 'de_xuat_duyet_luc' : null,
            Schema::hasColumn('bai_kiem_tra', 'duyet_luc') ? 'duyet_luc' : null,
            Schema::hasColumn('bai_kiem_tra', 'phat_hanh_luc') ? 'phat_hanh_luc' : null,
            Schema::hasColumn('bai_kiem_tra', 'ghi_chu_duyet') ? 'ghi_chu_duyet' : null,
        ]));

        if ($columns !== []) {
            Schema::table('bai_kiem_tra', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
