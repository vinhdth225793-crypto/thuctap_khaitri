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
        // First, try to drop foreign keys if they exist from a previous failed run
        try {
            Schema::table('khoa_hoc', function (Blueprint $table) {
                $table->dropForeign(['khoa_hoc_mau_id']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('khoa_hoc', function (Blueprint $table) {
                $table->dropForeign(['created_by']);
            });
        } catch (\Exception $e) {}

        Schema::table('khoa_hoc', function (Blueprint $table) {
            // Drop existing columns if they conflict with the new spec to ensure clean state
            // These were added in a previous migration but with slightly different specs
            $colsToDrop = [];
            if (Schema::hasColumn('khoa_hoc', 'loai')) $colsToDrop[] = 'loai';
            if (Schema::hasColumn('khoa_hoc', 'trang_thai_van_hanh')) $colsToDrop[] = 'trang_thai_van_hanh';
            if (Schema::hasColumn('khoa_hoc', 'khoa_hoc_mau_id')) $colsToDrop[] = 'khoa_hoc_mau_id';
            if (Schema::hasColumn('khoa_hoc', 'lan_mo_thu')) $colsToDrop[] = 'lan_mo_thu';
            if (Schema::hasColumn('khoa_hoc', 'ngay_khai_giang')) $colsToDrop[] = 'ngay_khai_giang';
            if (Schema::hasColumn('khoa_hoc', 'ngay_mo_lop')) $colsToDrop[] = 'ngay_mo_lop';
            if (Schema::hasColumn('khoa_hoc', 'ngay_ket_thuc_du_kien')) $colsToDrop[] = 'ngay_ket_thuc_du_kien';
            if (Schema::hasColumn('khoa_hoc', 'ngay_ket_thuc')) $colsToDrop[] = 'ngay_ket_thuc';
            if (Schema::hasColumn('khoa_hoc', 'ghi_chu_noi_bo')) $colsToDrop[] = 'ghi_chu_noi_bo';
            if (Schema::hasColumn('khoa_hoc', 'created_by')) $colsToDrop[] = 'created_by';
            
            if (count($colsToDrop) > 0) {
                $table->dropColumn($colsToDrop);
            }
        });

        Schema::table('khoa_hoc', function (Blueprint $table) {
            $table->enum('loai', ['mau', 'hoat_dong'])->default('mau')->after('trang_thai');
            $table->enum('trang_thai_van_hanh', ['cho_mo', 'cho_giang_vien', 'san_sang', 'dang_day', 'ket_thuc'])
                  ->default('cho_mo')->after('loai');
            $table->bigInteger('khoa_hoc_mau_id')->unsigned()->nullable()->after('trang_thai_van_hanh');
            $table->integer('lan_mo_thu')->unsigned()->default(0)->after('khoa_hoc_mau_id');
            $table->date('ngay_khai_giang')->nullable()->after('lan_mo_thu');
            $table->date('ngay_mo_lop')->nullable()->after('ngay_khai_giang');
            $table->date('ngay_ket_thuc')->nullable()->after('ngay_mo_lop');
            $table->text('ghi_chu_noi_bo')->nullable()->after('ngay_ket_thuc');
            $table->unsignedBigInteger('created_by')->nullable()->after('ghi_chu_noi_bo');

            // Foreign keys
            $table->foreign('khoa_hoc_mau_id')->references('id')->on('khoa_hoc')->onDelete('set null');
            $table->foreign('created_by')->references('ma_nguoi_dung')->on('nguoi_dung')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('khoa_hoc', function (Blueprint $table) {
            $table->dropForeign(['khoa_hoc_mau_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'loai', 
                'trang_thai_van_hanh', 
                'khoa_hoc_mau_id', 
                'lan_mo_thu', 
                'ngay_khai_giang', 
                'ngay_mo_lop', 
                'ngay_ket_thuc', 
                'ghi_chu_noi_bo', 
                'created_by'
            ]);
        });
    }
};
