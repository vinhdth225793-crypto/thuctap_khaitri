<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('thong_bao', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nguoi_nhan_id');
            $table->string('tieu_de', 255);
            $table->text('noi_dung');
            $table->enum('loai', ['phan_cong','xac_nhan_gv','mo_lop','he_thong'])
                  ->default('he_thong');
            $table->string('url', 500)->nullable();   // redirect khi click
            $table->boolean('da_doc')->default(0);
            $table->timestamps();

            $table->foreign('nguoi_nhan_id')
                  ->references('ma_nguoi_dung')
                  ->on('nguoi_dung')
                  ->onDelete('cascade');

            $table->index(['nguoi_nhan_id', 'da_doc']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thong_bao');
    }
};
