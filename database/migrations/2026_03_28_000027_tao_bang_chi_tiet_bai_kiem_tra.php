<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chi_tiet_bai_kiem_tra', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bai_kiem_tra_id');
            $table->unsignedBigInteger('ngan_hang_cau_hoi_id');
            $table->unsignedInteger('thu_tu')->default(1);
            $table->decimal('diem_so', 8, 2)->default(1);
            $table->boolean('bat_buoc')->default(true);
            $table->timestamps();

            $table->foreign('bai_kiem_tra_id')->references('id')->on('bai_kiem_tra')->cascadeOnDelete();
            $table->foreign('ngan_hang_cau_hoi_id')->references('id')->on('ngan_hang_cau_hoi')->cascadeOnDelete();
            $table->unique(['bai_kiem_tra_id', 'ngan_hang_cau_hoi_id'], 'uniq_bai_kiem_tra_cau_hoi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_bai_kiem_tra');
    }
};