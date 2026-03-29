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
            $table->unsignedBigInteger('lich_hoc_id');
            $table->string('loai_tai_nguyen'); // bai_giang, tai_lieu, bai_tap
            $table->string('tieu_de');
            $table->text('mo_ta')->nullable();
            $table->string('duong_dan_file')->nullable();
            $table->string('link_ngoai')->nullable();
            $table->timestamps();

            $table->foreign('lich_hoc_id')->references('id')->on('lich_hoc')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tai_nguyen_buoi_hoc');
    }
};
