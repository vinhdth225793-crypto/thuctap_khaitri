<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('khoa_hoc', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nhom_nganh_id');
            $table->string('ma_khoa_hoc', 50)->unique();
            $table->string('ten_khoa_hoc', 200);
            $table->string('mo_ta_ngan', 500)->nullable();
            $table->text('mo_ta_chi_tiet')->nullable();
            $table->string('hinh_anh', 255)->nullable();
            $table->enum('cap_do', ['co_ban', 'trung_binh', 'nang_cao'])->default('co_ban');
            $table->unsignedInteger('tong_so_module')->default(0);
            $table->string('phuong_thuc_danh_gia', 50)->default('cuoi_khoa');
            $table->decimal('ty_trong_diem_danh', 5, 2)->default(20);
            $table->decimal('ty_trong_kiem_tra', 5, 2)->default(80);
            $table->boolean('trang_thai')->default(true);
            $table->enum('loai', ['mau', 'hoat_dong'])->default('mau');
            $table->enum('trang_thai_van_hanh', ['cho_mo', 'cho_giang_vien', 'san_sang', 'dang_day', 'ket_thuc'])->default('cho_mo');
            $table->unsignedBigInteger('khoa_hoc_mau_id')->nullable();
            $table->unsignedInteger('lan_mo_thu')->default(0);
            $table->date('ngay_khai_giang')->nullable();
            $table->date('ngay_mo_lop')->nullable();
            $table->date('ngay_ket_thuc')->nullable();
            $table->text('ghi_chu_noi_bo')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('nhom_nganh_id')->references('id')->on('nhom_nganh')->cascadeOnDelete();
            $table->foreign('khoa_hoc_mau_id')->references('id')->on('khoa_hoc')->nullOnDelete();
            $table->foreign('created_by')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();
            $table->index(['loai', 'trang_thai_van_hanh']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('khoa_hoc');
    }
};