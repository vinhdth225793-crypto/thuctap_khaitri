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
        Schema::create('hoc_vien', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nguoi_dung_id')->unique();
            // Trường riêng của học viên
            $table->string('lop')->nullable();
            $table->string('nganh')->nullable();
            $table->decimal('diem_trung_binh', 3, 2)->nullable();
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
        Schema::dropIfExists('hoc_vien');
    }
};