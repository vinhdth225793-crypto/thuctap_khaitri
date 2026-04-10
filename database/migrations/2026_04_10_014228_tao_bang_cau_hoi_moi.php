<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cau_hoi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('khoa_hoc_id')->constrained('khoa_hoc')->cascadeOnDelete();
            $table->foreignId('module_hoc_id')->nullable()->constrained('module_hoc')->nullOnDelete();
            $table->text('noi_dung');
            $table->enum('loai_cau_hoi', ['trac_nghiem', 'tu_luan'])->default('trac_nghiem');
            $table->enum('muc_do', ['de', 'trung_binh', 'kho'])->default('trung_binh');
            $table->boolean('la_cau_hoi_dung_chung')->default(false);
            $table->foreignId('nguoi_tao_id')->nullable()->constrained('nguoi_dung')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cau_hoi');
    }
};
