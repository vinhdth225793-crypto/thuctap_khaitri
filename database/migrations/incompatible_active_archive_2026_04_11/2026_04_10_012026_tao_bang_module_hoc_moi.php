<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_hoc', function (Blueprint $table) {
            $table->id();
            $table->foreignId('khoa_hoc_id')->constrained('khoa_hoc')->cascadeOnDelete();
            $table->string('ten_module', 200);
            $table->unsignedInteger('so_buoi')->default(0);
            $table->text('mo_ta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_hoc');
    }
};
