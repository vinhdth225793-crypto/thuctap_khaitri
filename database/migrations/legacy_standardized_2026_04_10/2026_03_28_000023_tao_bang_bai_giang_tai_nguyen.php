<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bai_giang_tai_nguyen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bai_giang_id');
            $table->unsignedBigInteger('tai_nguyen_id');
            $table->string('vai_tro_tai_nguyen', 50)->default('phu');
            $table->integer('thu_tu_hien_thi')->default(0);
            $table->timestamps();

            $table->foreign('bai_giang_id')->references('id')->on('bai_giangs')->cascadeOnDelete();
            $table->foreign('tai_nguyen_id')->references('id')->on('tai_nguyen_buoi_hoc')->cascadeOnDelete();
            $table->unique(['bai_giang_id', 'tai_nguyen_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bai_giang_tai_nguyen');
    }
};