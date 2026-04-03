<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bai_kiem_tra', function (Blueprint $table) {
            $table->boolean('co_giam_sat')->default(false)->after('randomize_answers');
            $table->boolean('bat_buoc_fullscreen')->default(false)->after('co_giam_sat');
            $table->boolean('bat_buoc_camera')->default(false)->after('bat_buoc_fullscreen');
            $table->unsignedInteger('so_lan_vi_pham_toi_da')->default(3)->after('bat_buoc_camera');
            $table->unsignedInteger('chu_ky_snapshot_giay')->default(30)->after('so_lan_vi_pham_toi_da');
            $table->boolean('tu_dong_nop_khi_vi_pham')->default(false)->after('chu_ky_snapshot_giay');
            $table->boolean('chan_copy_paste')->default(false)->after('tu_dong_nop_khi_vi_pham');
            $table->boolean('chan_chuot_phai')->default(false)->after('chan_copy_paste');
        });

        Schema::table('bai_lam_bai_kiem_tra', function (Blueprint $table) {
            $table->string('dia_chi_ip', 45)->nullable()->after('hoc_vien_id');
            $table->text('user_agent')->nullable()->after('dia_chi_ip');
            $table->json('precheck_data')->nullable()->after('user_agent');
            $table->dateTime('precheck_completed_at')->nullable()->after('precheck_data');
            $table->unsignedInteger('tong_so_vi_pham')->default(0)->after('nhan_xet');
            $table->string('trang_thai_giam_sat', 50)->default('khong_ap_dung')->after('tong_so_vi_pham');
            $table->boolean('da_tu_dong_nop')->default(false)->after('trang_thai_giam_sat');
            $table->text('ghi_chu_giam_sat')->nullable()->after('da_tu_dong_nop');
            $table->unsignedBigInteger('nguoi_hau_kiem_id')->nullable()->after('ghi_chu_giam_sat');
            $table->dateTime('hau_kiem_luc')->nullable()->after('nguoi_hau_kiem_id');

            $table->foreign('nguoi_hau_kiem_id')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();
            $table->index(['trang_thai_giam_sat', 'tong_so_vi_pham'], 'idx_bai_lam_giam_sat_status');
        });

        Schema::create('bai_lam_vi_pham_giam_sat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bai_lam_bai_kiem_tra_id');
            $table->string('loai_su_kien', 80);
            $table->text('mo_ta')->nullable();
            $table->boolean('la_vi_pham')->default(false);
            $table->unsignedInteger('so_lan_vi_pham_hien_tai')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('bai_lam_bai_kiem_tra_id')->references('id')->on('bai_lam_bai_kiem_tra')->cascadeOnDelete();
            $table->index(['bai_lam_bai_kiem_tra_id', 'loai_su_kien'], 'idx_giam_sat_log_attempt_event');
            $table->index(['bai_lam_bai_kiem_tra_id', 'created_at'], 'idx_giam_sat_log_attempt_time');
        });

        Schema::create('bai_lam_snapshot_giam_sat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bai_lam_bai_kiem_tra_id');
            $table->string('duong_dan_file')->nullable();
            $table->dateTime('captured_at')->nullable();
            $table->string('status', 50)->default('captured');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('bai_lam_bai_kiem_tra_id')->references('id')->on('bai_lam_bai_kiem_tra')->cascadeOnDelete();
            $table->index(['bai_lam_bai_kiem_tra_id', 'captured_at'], 'idx_giam_sat_snapshot_attempt_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bai_lam_snapshot_giam_sat');
        Schema::dropIfExists('bai_lam_vi_pham_giam_sat');

        Schema::table('bai_lam_bai_kiem_tra', function (Blueprint $table) {
            $table->dropForeign(['nguoi_hau_kiem_id']);
            $table->dropIndex('idx_bai_lam_giam_sat_status');
            $table->dropColumn([
                'dia_chi_ip',
                'user_agent',
                'precheck_data',
                'precheck_completed_at',
                'tong_so_vi_pham',
                'trang_thai_giam_sat',
                'da_tu_dong_nop',
                'ghi_chu_giam_sat',
                'nguoi_hau_kiem_id',
                'hau_kiem_luc',
            ]);
        });

        Schema::table('bai_kiem_tra', function (Blueprint $table) {
            $table->dropColumn([
                'co_giam_sat',
                'bat_buoc_fullscreen',
                'bat_buoc_camera',
                'so_lan_vi_pham_toi_da',
                'chu_ky_snapshot_giay',
                'tu_dong_nop_khi_vi_pham',
                'chan_copy_paste',
                'chan_chuot_phai',
            ]);
        });
    }
};
