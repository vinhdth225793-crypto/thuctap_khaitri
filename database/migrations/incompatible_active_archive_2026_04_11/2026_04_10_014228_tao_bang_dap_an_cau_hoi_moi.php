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
            $table->foreignId('cau_hoi_id')->constrained('cau_hoi')->cascadeOnDelete();
            $table->text('noi_dung_dap_an');
            $table->boolean('la_dap_an_dung')->default(false);
            $table->text('giai_thich')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dap_an_cau_hoi');
    }
};
