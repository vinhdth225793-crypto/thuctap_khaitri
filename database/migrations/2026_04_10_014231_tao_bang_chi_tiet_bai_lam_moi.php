<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chi_tiet_bai_lam', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bai_lam_id')->constrained('bai_lam')->cascadeOnDelete();
            $table->foreignId('cau_hoi_id')->constrained('cau_hoi')->cascadeOnDelete();
            $table->foreignId('dap_an_chon_id')->nullable()->constrained('dap_an_cau_hoi')->nullOnDelete();
            $table->text('noi_dung_tu_luan')->nullable();
            $table->decimal('diem_dat_duoc', 5, 2)->nullable();
            $table->boolean('la_cau_dung')->nullable();
            $table->timestamps();

            $table->unique(['bai_lam_id', 'cau_hoi_id'], 'uq_chi_tiet_bai_lam');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_bai_lam');
    }
};
