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
        Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
            if (!Schema::hasColumn('ket_qua_hoc_tap', 'da_chot')) {
                $table->boolean('da_chot')->default(false)->after('trang_thai');
            }
            if (!Schema::hasColumn('ket_qua_hoc_tap', 'chot_boi')) {
                $table->unsignedBigInteger('chot_boi')->nullable()->after('da_chot');
            }
            if (!Schema::hasColumn('ket_qua_hoc_tap', 'chot_luc')) {
                $table->timestamp('chot_luc')->nullable()->after('chot_boi');
            }
            if (!Schema::hasColumn('ket_qua_hoc_tap', 'ghi_chu_chot')) {
                $table->text('ghi_chu_chot')->nullable()->after('chot_luc');
            }
            if (!Schema::hasColumn('ket_qua_hoc_tap', 'diem_trung_binh_bai_kiem_tra')) {
                $table->decimal('diem_trung_binh_bai_kiem_tra', 5, 2)->nullable()->after('diem_kiem_tra');
            }
            if (!Schema::hasColumn('ket_qua_hoc_tap', 'diem_qua_trinh')) {
                $table->decimal('diem_qua_trinh', 5, 2)->nullable()->after('diem_trung_binh_bai_kiem_tra');
            }
            if (!Schema::hasColumn('ket_qua_hoc_tap', 'diem_giang_vien_chot')) {
                $table->decimal('diem_giang_vien_chot', 5, 2)->nullable()->after('diem_tong_ket');
            }
            if (!Schema::hasColumn('ket_qua_hoc_tap', 'trang_thai_luu_ho_so')) {
                $table->string('trang_thai_luu_ho_so', 50)->default('chua_luu')->after('trang_thai');
            }
            if (!Schema::hasColumn('ket_qua_hoc_tap', 'admin_duyet_id')) {
                $table->unsignedBigInteger('admin_duyet_id')->nullable()->after('trang_thai_luu_ho_so');
            }
            if (!Schema::hasColumn('ket_qua_hoc_tap', 'duyet_luc')) {
                $table->timestamp('duyet_luc')->nullable()->after('admin_duyet_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
            $table->dropColumn([
                'da_chot',
                'chot_boi',
                'chot_luc',
                'ghi_chu_chot',
                'diem_trung_binh_bai_kiem_tra',
                'diem_qua_trinh',
                'diem_giang_vien_chot',
                'trang_thai_luu_ho_so',
                'admin_duyet_id',
                'duyet_luc'
            ]);
        });
    }
};
