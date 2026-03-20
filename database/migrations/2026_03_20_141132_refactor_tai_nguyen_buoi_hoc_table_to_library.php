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
        Schema::table('tai_nguyen_buoi_hoc', function (Blueprint $table) {
            // Make lich_hoc_id nullable for library usage
            $table->unsignedBigInteger('lich_hoc_id')->nullable()->change();

            // Additional fields for Library Resource
            $table->unsignedBigInteger('nguoi_tao_id')->nullable()->after('thu_tu_hien_thi');
            $table->string('vai_tro_nguoi_tao')->nullable()->after('nguoi_tao_id');
            
            $table->string('trang_thai_duyet')->default('da_duyet')->after('vai_tro_nguoi_tao'); 
            // values: nhap, cho_duyet, da_duyet, can_chinh_sua, tu_choi
            
            $table->string('trang_thai_xu_ly')->default('khong_ap_dung')->after('trang_thai_duyet');
            // values: khong_ap_dung, cho_xu_ly, dang_xu_ly, san_sang, loi_xu_ly
            
            $table->text('ghi_chu_admin')->nullable()->after('trang_thai_xu_ly');
            $table->dateTime('ngay_gui_duyet')->nullable()->after('ghi_chu_admin');
            $table->dateTime('ngay_duyet')->nullable()->after('ngay_gui_duyet');
            $table->unsignedBigInteger('nguoi_duyet_id')->nullable()->after('ngay_duyet');
            
            $table->string('pham_vi_su_dung')->default('ca_nhan')->after('nguoi_duyet_id');
            // values: ca_nhan, khoa_hoc, cong_khai
            
            $table->string('file_name')->nullable()->after('duong_dan_file');
            $table->string('file_extension')->nullable()->after('file_name');
            $table->unsignedBigInteger('file_size')->nullable()->after('file_extension');
            $table->string('mime_type')->nullable()->after('file_size');
            
            $table->softDeletes();

            $table->foreign('nguoi_tao_id')->references('ma_nguoi_dung')->on('nguoi_dung')->onDelete('set null');
            $table->foreign('nguoi_duyet_id')->references('ma_nguoi_dung')->on('nguoi_dung')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tai_nguyen_buoi_hoc', function (Blueprint $table) {
            $table->dropForeign(['nguoi_tao_id']);
            $table->dropForeign(['nguoi_duyet_id']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'nguoi_tao_id',
                'vai_tro_nguoi_tao',
                'trang_thai_duyet',
                'trang_thai_xu_ly',
                'ghi_chu_admin',
                'ngay_gui_duyet',
                'ngay_duyet',
                'nguoi_duyet_id',
                'pham_vi_su_dung',
                'file_name',
                'file_extension',
                'file_size',
                'mime_type'
            ]);
            $table->unsignedBigInteger('lich_hoc_id')->nullable(false)->change();
        });
    }
};
