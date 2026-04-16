<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tai_nguyen', function (Blueprint $table) {
            $table->id();
            $table->string('ten_tai_nguyen');
            $table->string('duong_dan');
            $table->string('loai_file', 50)->nullable();
            $table->unsignedBigInteger('dung_luong')->nullable();
            $table->foreignId('lop_hoc_id')->nullable()->constrained('lop_hoc')->cascadeOnDelete();
            $table->foreignId('module_hoc_id')->nullable()->constrained('module_hoc')->cascadeOnDelete();
            $table->foreignId('lich_hoc_id')->nullable()->constrained('lich_hoc')->cascadeOnDelete();
            $table->foreignId('bai_giang_id')->nullable()->constrained('bai_giang')->cascadeOnDelete();
            $table->boolean('la_tai_nguyen_he_thong')->default(false);
            $table->boolean('hien_thi')->default(true);
            $table->foreignId('nguoi_dang_id')->nullable()->constrained('nguoi_dung')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tai_nguyen');
    }
};
