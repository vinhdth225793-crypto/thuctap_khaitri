<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lich_hoc', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lop_hoc_id')->constrained('lop_hoc')->cascadeOnDelete();
            $table->foreignId('module_hoc_id')->constrained('module_hoc')->cascadeOnDelete();
            $table->foreignId('giang_vien_id')->constrained('giang_vien')->cascadeOnDelete();
            $table->date('ngay_hoc');
            $table->time('gio_bat_dau')->nullable();
            $table->time('gio_ket_thuc')->nullable();
            $table->unsignedInteger('tiet_bat_dau')->nullable();
            $table->unsignedInteger('tiet_ket_thuc')->nullable();
            $table->string('buoi_hoc', 20)->nullable(); // sang, chieu, toi
            $table->unsignedInteger('thu_trong_tuan')->nullable();
            $table->unsignedInteger('buoi_so')->nullable();
            $table->string('phong_hoc')->nullable();
            $table->enum('hinh_thuc', ['offline', 'online'])->default('offline');
            $table->string('link_online')->nullable();
            $table->enum('trang_thai', ['cho', 'dang_hoc', 'hoan_thanh', 'huy'])->default('cho');
            $table->text('ghi_chu')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lich_hoc');
    }
};
