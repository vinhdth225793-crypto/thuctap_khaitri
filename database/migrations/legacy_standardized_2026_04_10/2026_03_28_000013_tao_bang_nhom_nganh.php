<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nhom_nganh', function (Blueprint $table) {
            $table->id();
            $table->string('ma_nhom_nganh', 50)->unique();
            $table->string('ten_nhom_nganh', 150)->unique();
            $table->text('mo_ta')->nullable();
            $table->string('hinh_anh', 255)->nullable();
            $table->boolean('trang_thai')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nhom_nganh');
    }
};