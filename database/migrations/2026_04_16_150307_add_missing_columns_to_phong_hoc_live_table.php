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
        Schema::table('phong_hoc_live', function (Blueprint $table) {
            if (!Schema::hasColumn('phong_hoc_live', 'lop_hoc_id')) {
                $table->unsignedBigInteger('lop_hoc_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('phong_hoc_live', 'giang_vien_id')) {
                $table->unsignedBigInteger('giang_vien_id')->nullable()->after('lop_hoc_id');
            }
            if (!Schema::hasColumn('phong_hoc_live', 'nen_tang')) {
                $table->string('nen_tang', 50)->default('internal')->after('tieu_de');
            }
            if (!Schema::hasColumn('phong_hoc_live', 'phong_id')) {
                $table->string('phong_id')->nullable()->after('nen_tang');
            }
            if (!Schema::hasColumn('phong_hoc_live', 'mat_khau')) {
                $table->string('mat_khau')->nullable()->after('phong_id');
            }
            if (!Schema::hasColumn('phong_hoc_live', 'bat_dau_du_kien')) {
                $table->dateTime('bat_dau_du_kien')->nullable()->after('mat_khau');
            }
            if (!Schema::hasColumn('phong_hoc_live', 'ket_thuc_du_kien')) {
                $table->dateTime('ket_thuc_du_kien')->nullable()->after('bat_dau_du_kien');
            }
            if (!Schema::hasColumn('phong_hoc_live', 'bat_dau_thuc_te')) {
                $table->dateTime('bat_dau_thuc_te')->nullable()->after('ket_thuc_du_kien');
            }
            if (!Schema::hasColumn('phong_hoc_live', 'ket_thuc_thuc_te')) {
                $table->dateTime('ket_thuc_thuc_te')->nullable()->after('bat_dau_thuc_te');
            }
            if (!Schema::hasColumn('phong_hoc_live', 'trang_thai')) {
                $table->string('trang_thai', 50)->default('cho')->after('ket_thuc_thuc_te');
            }
            if (!Schema::hasColumn('phong_hoc_live', 'du_lieu_nen_tang')) {
                $table->json('du_lieu_nen_tang')->nullable()->after('trang_thai');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('phong_hoc_live', function (Blueprint $table) {
            $table->dropColumn([
                'lop_hoc_id',
                'giang_vien_id',
                'nen_tang',
                'phong_id',
                'mat_khau',
                'bat_dau_du_kien',
                'ket_thuc_du_kien',
                'bat_dau_thuc_te',
                'ket_thuc_thuc_te',
                'trang_thai',
                'du_lieu_nen_tang',
            ]);
        });
    }
};
