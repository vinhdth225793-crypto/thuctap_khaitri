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
        Schema::create('module_hoc', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('khoa_hoc_id');
            $table->string('ma_module', 50)->unique();
            $table->string('ten_module', 200);
            $table->text('mo_ta')->nullable();
            $table->integer('thu_tu_module');
            $table->integer('thoi_luong_du_kien')->nullable();
            $table->boolean('trang_thai')->default(1);
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('khoa_hoc_id')->references('id')->on('khoa_hoc')->onDelete('cascade');
            $table->unique(['khoa_hoc_id', 'thu_tu_module']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_hoc');
    }
};
