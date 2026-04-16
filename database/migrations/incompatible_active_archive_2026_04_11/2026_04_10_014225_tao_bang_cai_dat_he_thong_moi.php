<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cai_dat_he_thong', function (Blueprint $table) {
            $table->id();
            $table->string('khoa')->unique();
            $table->text('gia_tri')->nullable();
            $table->string('nhom', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cai_dat_he_thong');
    }
};
