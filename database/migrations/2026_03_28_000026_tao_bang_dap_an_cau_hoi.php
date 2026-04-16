<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dap_an_cau_hoi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ngan_hang_cau_hoi_id');
            $table->string('ky_hieu', 10)->nullable();
            $table->text('noi_dung');
            $table->boolean('is_dap_an_dung')->default(false);
            $table->unsignedInteger('thu_tu')->default(1);
            $table->timestamps();

            $table->foreign('ngan_hang_cau_hoi_id')->references('id')->on('ngan_hang_cau_hoi')->cascadeOnDelete();
            $table->index(['ngan_hang_cau_hoi_id', 'thu_tu']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dap_an_cau_hoi');
    }
};