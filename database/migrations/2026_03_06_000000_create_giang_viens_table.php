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
        Schema::create('giang_vien', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nguoi_dung_id')->unique();
            // Thêm các trường riêng cho giảng viên
            $table->string('chuyen_nganh')->nullable();
            $table->string('hoc_vi')->nullable();
            $table->string('so_gio_day')->nullable();
            $table->timestamps();

            $table->foreign('nguoi_dung_id')
                ->references('ma_nguoi_dung')
                ->on('nguoi_dung')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('giang_vien');
    }
};