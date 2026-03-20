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
        Schema::create('bai_giangs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('khoa_hoc_id');
            $table->unsignedBigInteger('module_hoc_id');
            $table->unsignedBigInteger('lich_hoc_id')->nullable();
            $table->unsignedBigInteger('nguoi_tao_id')->nullable();
            
            $table->string('tieu_de');
            $table->text('mo_ta')->nullable();
            $table->string('loai_bai_giang')->default('tai_lieu');
            // video, tai_lieu, bai_doc, bai_tap, hon_hop
            
            $table->unsignedBigInteger('tai_nguyen_chinh_id')->nullable();
            $table->integer('thu_tu_hien_thi')->default(0);
            $table->dateTime('thoi_diem_mo')->nullable();
            
            $table->string('trang_thai_duyet')->default('da_duyet');
            // nhap, cho_duyet, da_duyet, can_chinh_sua, tu_choi
            
            $table->string('trang_thai_cong_bo')->default('an');
            // an, da_cong_bo
            
            $table->text('ghi_chu_admin')->nullable();
            $table->dateTime('ngay_gui_duyet')->nullable();
            $table->dateTime('ngay_duyet')->nullable();
            $table->unsignedBigInteger('nguoi_duyet_id')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('khoa_hoc_id')->references('id')->on('khoa_hoc')->onDelete('cascade');
            $table->foreign('module_hoc_id')->references('id')->on('module_hoc')->onDelete('cascade');
            $table->foreign('lich_hoc_id')->references('id')->on('lich_hoc')->onDelete('set null');
            $table->foreign('nguoi_tao_id')->references('ma_nguoi_dung')->on('nguoi_dung')->onDelete('set null');
            $table->foreign('nguoi_duyet_id')->references('ma_nguoi_dung')->on('nguoi_dung')->onDelete('set null');
            $table->foreign('tai_nguyen_chinh_id')->references('id')->on('tai_nguyen_buoi_hoc')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bai_giangs');
    }
};
