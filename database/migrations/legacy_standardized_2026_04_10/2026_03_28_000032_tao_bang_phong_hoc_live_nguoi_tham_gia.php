<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phong_hoc_live_nguoi_tham_gia', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('phong_hoc_live_id');
            $table->unsignedBigInteger('nguoi_dung_id')->nullable();
            $table->string('vai_tro', 50)->default('student');
            $table->dateTime('joined_at')->nullable();
            $table->dateTime('left_at')->nullable();
            $table->string('trang_thai', 50)->default('cho_tham_gia');
            $table->timestamps();

            $table->foreign('phong_hoc_live_id')->references('id')->on('phong_hoc_live')->cascadeOnDelete();
            $table->foreign('nguoi_dung_id')->references('ma_nguoi_dung')->on('nguoi_dung')->nullOnDelete();
            $table->index(['phong_hoc_live_id', 'vai_tro'], 'idx_phong_hoc_live_tham_gia_room_role');
            $table->index(['nguoi_dung_id', 'trang_thai'], 'idx_phong_hoc_live_tham_gia_user_state');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phong_hoc_live_nguoi_tham_gia');
    }
};