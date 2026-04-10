<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chi_tiet_bai_kiem_tra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bai_kiem_tra_id')->constrained('bai_kiem_tra')->cascadeOnDelete();
            $table->foreignId('cau_hoi_id')->constrained('cau_hoi')->cascadeOnDelete();
            $table->decimal('diem_toi_da', 5, 2)->default(1.00);
            $table->unsignedInteger('thu_tu')->nullable();
            $table->timestamps();

            $table->unique(['bai_kiem_tra_id', 'cau_hoi_id'], 'uq_chi_tiet_bai_kiem_tra');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_bai_kiem_tra');
    }
};
