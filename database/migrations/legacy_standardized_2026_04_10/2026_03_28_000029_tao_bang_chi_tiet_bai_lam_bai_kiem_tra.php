<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chi_tiet_bai_lam_bai_kiem_tra', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bai_lam_bai_kiem_tra_id');
            $table->unsignedBigInteger('chi_tiet_bai_kiem_tra_id');
            $table->unsignedBigInteger('ngan_hang_cau_hoi_id');
            $table->unsignedBigInteger('dap_an_cau_hoi_id')->nullable();
            $table->longText('cau_tra_loi_text')->nullable();
            $table->boolean('is_dung')->nullable();
            $table->decimal('diem_tu_dong', 8, 2)->nullable();
            $table->decimal('diem_tu_luan', 8, 2)->nullable();
            $table->text('nhan_xet')->nullable();
            $table->timestamps();

            $table->foreign('bai_lam_bai_kiem_tra_id')->references('id')->on('bai_lam_bai_kiem_tra')->cascadeOnDelete();
            $table->foreign('chi_tiet_bai_kiem_tra_id')->references('id')->on('chi_tiet_bai_kiem_tra')->cascadeOnDelete();
            $table->foreign('ngan_hang_cau_hoi_id')->references('id')->on('ngan_hang_cau_hoi')->cascadeOnDelete();
            $table->foreign('dap_an_cau_hoi_id')->references('id')->on('dap_an_cau_hoi')->nullOnDelete();
            $table->unique(['bai_lam_bai_kiem_tra_id', 'chi_tiet_bai_kiem_tra_id'], 'uniq_bai_lam_cau_hoi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_bai_lam_bai_kiem_tra');
    }
};