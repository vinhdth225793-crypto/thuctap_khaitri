<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phong_hoc_live_ban_ghi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('phong_hoc_live_id');
            $table->string('nguon_ban_ghi', 50)->default('upload');
            $table->string('tieu_de');
            $table->string('duong_dan_file')->nullable();
            $table->string('link_ngoai', 500)->nullable();
            $table->unsignedInteger('thoi_luong')->nullable();
            $table->string('trang_thai', 50)->default('san_sang');
            $table->timestamps();

            $table->foreign('phong_hoc_live_id')->references('id')->on('phong_hoc_live')->cascadeOnDelete();
            $table->index(['phong_hoc_live_id', 'trang_thai'], 'idx_phong_hoc_live_ban_ghi_room_state');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phong_hoc_live_ban_ghi');
    }
};