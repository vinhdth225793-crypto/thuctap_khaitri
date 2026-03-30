<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('giang_vien_lich_ranh', function (Blueprint $table) {
            $table->tinyInteger('tiet_bat_dau')->nullable()->after('ngay_cu_the');
            $table->tinyInteger('tiet_ket_thuc')->nullable()->after('tiet_bat_dau');
            $table->string('buoi_hoc', 20)->nullable()->after('tiet_ket_thuc');
        });

        Schema::table('lich_hoc', function (Blueprint $table) {
            $table->tinyInteger('tiet_bat_dau')->nullable()->after('gio_ket_thuc');
            $table->tinyInteger('tiet_ket_thuc')->nullable()->after('tiet_bat_dau');
            $table->string('buoi_hoc', 20)->nullable()->after('tiet_ket_thuc');
        });
    }

    public function down(): void
    {
        Schema::table('giang_vien_lich_ranh', function (Blueprint $table) {
            $table->dropColumn(['tiet_bat_dau', 'tiet_ket_thuc', 'buoi_hoc']);
        });

        Schema::table('lich_hoc', function (Blueprint $table) {
            $table->dropColumn(['tiet_bat_dau', 'tiet_ket_thuc', 'buoi_hoc']);
        });
    }
};
