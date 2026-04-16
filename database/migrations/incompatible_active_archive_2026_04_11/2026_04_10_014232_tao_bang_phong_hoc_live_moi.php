<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phong_hoc_live', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lop_hoc_id')->constrained('lop_hoc')->cascadeOnDelete();
            $table->foreignId('lich_hoc_id')->constrained('lich_hoc')->cascadeOnDelete();
            $table->foreignId('giang_vien_id')->constrained('giang_vien')->cascadeOnDelete();
            $table->string('tieu_de');
            $table->string('nen_tang')->default('zoom');
            $table->string('phong_id')->nullable();
            $table->string('mat_khau')->nullable();
            $table->dateTime('bat_dau_du_kien')->nullable();
            $table->dateTime('ket_thuc_du_kien')->nullable();
            $table->dateTime('bat_dau_thuc_te')->nullable();
            $table->dateTime('ket_thuc_thuc_te')->nullable();
            $table->enum('trang_thai', ['cho', 'dang_dien_ra', 'da_ket_thuc', 'huy'])->default('cho');
            $table->json('du_lieu_nen_tang')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phong_hoc_live');
    }
};
