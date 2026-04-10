<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('don_xin_nghi_giang_vien', function (Blueprint $table) {
            $table->id();
            $table->foreignId('giang_vien_id')->constrained('giang_vien')->cascadeOnDelete();
            $table->foreignId('lop_hoc_id')->constrained('lop_hoc')->cascadeOnDelete();
            $table->foreignId('lich_hoc_id')->constrained('lich_hoc')->cascadeOnDelete();
            $table->text('ly_do');
            $table->enum('trang_thai', ['cho_duyet', 'da_duyet', 'tu_choi'])->default('cho_duyet');
            $table->foreignId('nguoi_duyet_id')->nullable()->constrained('nguoi_dung')->nullOnDelete();
            $table->text('phan_hoi_admin')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('don_xin_nghi_giang_vien');
    }
};
