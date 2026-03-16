<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yeu_cau_hoc_vien', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('khoa_hoc_id');
            $table->unsignedBigInteger('giang_vien_id');
            $table->string('loai_yeu_cau'); // them, xoa, sua
            $table->json('du_lieu_yeu_cau'); // Luu thong tin HV can thay doi
            $table->text('ly_do')->nullable();
            $table->string('trang_thai')->default('cho_duyet'); // cho_duyet, da_duyet, tu_choi
            $table->text('phan_hoi_admin')->nullable();
            $table->timestamps();

            $table->foreign('khoa_hoc_id')->references('id')->on('khoa_hoc')->onDelete('cascade');
            $table->foreign('giang_vien_id')->references('id')->on('giang_vien')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yeu_cau_hoc_vien');
    }
};
