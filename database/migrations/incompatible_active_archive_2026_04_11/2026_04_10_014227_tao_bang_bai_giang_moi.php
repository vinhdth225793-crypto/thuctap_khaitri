<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bai_giang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lop_hoc_id')->constrained('lop_hoc')->cascadeOnDelete();
            $table->foreignId('module_hoc_id')->nullable()->constrained('module_hoc')->nullOnDelete();
            $table->foreignId('lich_hoc_id')->nullable()->constrained('lich_hoc')->nullOnDelete();
            $table->string('tieu_de');
            $table->text('noi_dung')->nullable();
            $table->string('video_url')->nullable();
            $table->enum('trang_thai', ['ban_nhap', 'cho_duyet', 'da_duyet', 'cong_bo'])->default('ban_nhap');
            $table->foreignId('nguoi_dang_id')->nullable()->constrained('nguoi_dung')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bai_giang');
    }
};
