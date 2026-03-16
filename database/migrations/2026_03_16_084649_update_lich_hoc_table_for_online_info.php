<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lich_hoc', function (Blueprint $table) {
            $table->string('nen_tang')->nullable()->after('hinh_thuc'); // Zoom, Meet, etc.
            $table->string('meeting_id')->nullable()->after('link_online');
            $table->string('mat_khau_cuoc_hop')->nullable()->after('meeting_id');
        });
    }

    public function down(): void
    {
        Schema::table('lich_hoc', function (Blueprint $table) {
            $table->dropColumn(['nen_tang', 'meeting_id', 'mat_khau_cuoc_hop']);
        });
    }
};
