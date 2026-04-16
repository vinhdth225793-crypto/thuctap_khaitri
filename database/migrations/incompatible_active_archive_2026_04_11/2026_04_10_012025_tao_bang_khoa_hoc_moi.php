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
            $table->foreignId('nhom_nganh_id')->constrained('nhom_nganh')->cascadeOnDelete();
            $table->string('ma_khoa_hoc', 50)->unique();
            $table->string('ten_khoa_hoc', 200);
            $table->string('mo_ta_ngan', 500)->nullable();
            $table->text('mo_ta_chi_tiet')->nullable();
            $table->string('hinh_anh')->nullable();
            $table->enum('cap_do', ['co_ban', 'trung_binh', 'nang_cao'])->default('co_ban');
            $table->string('phuong_thuc_danh_gia', 50)->default('cuoi_khoa');
            $table->boolean('trang_thai')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('nguoi_dung')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('khoa_hoc');
    }
};
