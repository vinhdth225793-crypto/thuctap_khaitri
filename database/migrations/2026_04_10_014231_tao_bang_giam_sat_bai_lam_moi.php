<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('giam_sat_bai_lam', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bai_lam_id')->constrained('bai_lam')->cascadeOnDelete();
            $table->string('loai_su_kien'); // vi_pham, snapshot, log
            $table->text('noi_dung')->nullable();
            $table->string('anh_snapshot')->nullable();
            $table->timestamp('thoi_gian_ghi_nhan')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('giam_sat_bai_lam');
    }
};
