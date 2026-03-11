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
        Schema::create('mon_hoc', function (Blueprint $table) {
            $table->id();
            $table->string('ma_mon_hoc', 50)->unique(); // Mã môn học tự động tạo, unique
            $table->string('ten_mon_hoc', 150)->unique(); // Tên môn học unique
            $table->text('mo_ta')->nullable();
            $table->string('hinh_anh', 255)->nullable();
            $table->boolean('trang_thai')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mon_hoc');
    }
};
