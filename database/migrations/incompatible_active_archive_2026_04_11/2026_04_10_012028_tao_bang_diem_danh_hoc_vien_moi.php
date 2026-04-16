<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diem_danh_hoc_vien', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lich_hoc_id')->constrained('lich_hoc')->cascadeOnDelete();
            $table->foreignId('hoc_vien_id')->constrained('hoc_vien')->cascadeOnDelete();
            $table->string('trang_thai', 50); // hien_dien, vang, tre, co_phep
            $table->string('ghi_chu')->nullable();
            $table->timestamps();

            $table->unique(['lich_hoc_id', 'hoc_vien_id'], 'uq_diem_danh_hoc_vien');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diem_danh_hoc_vien');
    }
};
