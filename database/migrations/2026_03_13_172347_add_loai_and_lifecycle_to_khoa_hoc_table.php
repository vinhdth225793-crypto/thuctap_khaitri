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
        Schema::table('khoa_hoc', function (Blueprint $table) {
            $table->enum('loai', ['mau', 'truc_tiep'])
                  ->default('mau')->after('cap_do');
            $table->enum('trang_thai_van_hanh',
                         ['cho_mo', 'cho_giang_vien', 'san_sang', 'dang_day', 'ket_thuc'])
                  ->default('cho_mo')->after('loai');
            $table->date('ngay_khai_giang')->nullable()->after('trang_thai_van_hanh');
            $table->date('ngay_ket_thuc_du_kien')->nullable()->after('ngay_khai_giang');
            $table->text('ghi_chu_noi_bo')->nullable()->after('ngay_ket_thuc_du_kien');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('khoa_hoc', function (Blueprint $table) {
            $table->dropColumn(['loai', 'trang_thai_van_hanh', 'ngay_khai_giang',
                                'ngay_ket_thuc_du_kien', 'ghi_chu_noi_bo']);
        });
    }
};
