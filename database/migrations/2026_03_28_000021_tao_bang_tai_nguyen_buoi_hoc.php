<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tai_nguyen_buoi_hoc', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lich_hoc_id')->nullable();
            $table->string('loai_tai_nguyen', 50);
            $table->string('tieu_de');
            $table->text('mo_ta')->nullable();
            $table->string('duong_dan_file')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_extension')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('link_ngoai', 500)->nullable();
            $table->string('trang_thai_hien_thi', 50)->default('an');
            $table->dateTime('ngay_mo_hien_thi')->nullable();
            $table->integer('thu_tu_hien_thi')->default(0);
            $table->unsignedBigInteger('nguoi_tao_id')->nullable();
            $table->string('vai_tro_nguoi_tao', 50)->nullable();
            $table->string('trang_thai_duyet', 50)->default('da_duyet');
            $table->string('trang_thai_xu_ly', 50)->default('khong_ap_dung');
            $table->text('ghi_chu_admin')->nullable();
            $table->dateTime('ngay_gui_duyet')->nullable();
            $table->dateTime('ngay_duyet')->nullable();
            $table->unsignedBigInteger('nguoi_duyet_id')->nullable();
            $table->string('pham_vi_su_dung', 50)->default('ca_nhan');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('lich_hoc_id')->references('id')->on('lich_hoc')->cascadeOnDelete();
            $table->foreign('nguoi_tao_id')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();
            $table->foreign('nguoi_duyet_id')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();
            $table->index(['trang_thai_duyet', 'trang_thai_xu_ly']);
            $table->index(['nguoi_tao_id', 'pham_vi_su_dung']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tai_nguyen_buoi_hoc');
    }
};