<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addMissingColumns('bai_kiem_tra', [
            'co_giam_sat' => fn (Blueprint $table) => $table->boolean('co_giam_sat')->default(false),
            'bat_buoc_fullscreen' => fn (Blueprint $table) => $table->boolean('bat_buoc_fullscreen')->default(false),
            'bat_buoc_camera' => fn (Blueprint $table) => $table->boolean('bat_buoc_camera')->default(false),
            'so_lan_vi_pham_toi_da' => fn (Blueprint $table) => $table->unsignedInteger('so_lan_vi_pham_toi_da')->default(3),
            'chu_ky_snapshot_giay' => fn (Blueprint $table) => $table->unsignedInteger('chu_ky_snapshot_giay')->default(30),
            'tu_dong_nop_khi_vi_pham' => fn (Blueprint $table) => $table->boolean('tu_dong_nop_khi_vi_pham')->default(false),
            'chan_copy_paste' => fn (Blueprint $table) => $table->boolean('chan_copy_paste')->default(false),
            'chan_chuot_phai' => fn (Blueprint $table) => $table->boolean('chan_chuot_phai')->default(false),
        ]);

        $this->addMissingColumns('bai_lam_bai_kiem_tra', [
            'dia_chi_ip' => fn (Blueprint $table) => $table->string('dia_chi_ip', 45)->nullable(),
            'user_agent' => fn (Blueprint $table) => $table->text('user_agent')->nullable(),
            'precheck_data' => fn (Blueprint $table) => $table->json('precheck_data')->nullable(),
            'precheck_completed_at' => fn (Blueprint $table) => $table->dateTime('precheck_completed_at')->nullable(),
            'tong_so_vi_pham' => fn (Blueprint $table) => $table->unsignedInteger('tong_so_vi_pham')->default(0),
            'trang_thai_giam_sat' => fn (Blueprint $table) => $table->string('trang_thai_giam_sat', 50)->default('khong_ap_dung'),
            'da_tu_dong_nop' => fn (Blueprint $table) => $table->boolean('da_tu_dong_nop')->default(false),
            'ghi_chu_giam_sat' => fn (Blueprint $table) => $table->text('ghi_chu_giam_sat')->nullable(),
            'nguoi_hau_kiem_id' => fn (Blueprint $table) => $table->unsignedBigInteger('nguoi_hau_kiem_id')->nullable(),
            'hau_kiem_luc' => fn (Blueprint $table) => $table->dateTime('hau_kiem_luc')->nullable(),
        ]);

        if (!Schema::hasTable('bai_lam_vi_pham_giam_sat')) {
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
        }

        if (!Schema::hasTable('bai_lam_snapshot_giam_sat')) {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('bai_lam_snapshot_giam_sat');
        Schema::dropIfExists('bai_lam_vi_pham_giam_sat');

        if (Schema::hasTable('bai_lam_bai_kiem_tra')) {
            $this->dropExistingColumns('bai_lam_bai_kiem_tra', [
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
        }

        $this->dropExistingColumns('bai_kiem_tra', [
            'co_giam_sat',
            'bat_buoc_fullscreen',
            'bat_buoc_camera',
            'so_lan_vi_pham_toi_da',
            'chu_ky_snapshot_giay',
            'tu_dong_nop_khi_vi_pham',
            'chan_copy_paste',
            'chan_chuot_phai',
        ]);
    }

    private function addMissingColumns(string $tableName, array $columnDefinitions): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        foreach ($columnDefinitions as $column => $definition) {
            if (Schema::hasColumn($tableName, $column)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($definition) {
                $definition($table);
            });
        }
    }

    private function dropExistingColumns(string $tableName, array $columns): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        $existingColumns = array_values(array_filter(
            $columns,
            fn (string $column) => Schema::hasColumn($tableName, $column)
        ));

        if ($existingColumns === []) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($existingColumns) {
            $table->dropColumn($existingColumns);
        });
    }
};
