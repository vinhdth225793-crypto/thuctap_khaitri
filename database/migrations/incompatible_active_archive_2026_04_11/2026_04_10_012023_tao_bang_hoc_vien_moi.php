<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hoc_vien', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('nguoi_dung_id')->unique();
            $table->string('ma_hoc_vien', 50)->nullable()->unique();
            $table->string('lop_niem_khoa')->nullable();
            $table->string('nganh_hoc')->nullable();
            $table->decimal('diem_trung_binh', 5, 2)->nullable();
            $table->timestamps();

            $table->foreign('nguoi_dung_id')->references('id')->on('nguoi_dung')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hoc_vien');
    }
};
