<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('giang_vien', function (Blueprint $table) {
            if (! Schema::hasColumn('giang_vien', 'so_gio_day')) {
                $table->string('so_gio_day')->nullable()->after('hoc_vi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('giang_vien', function (Blueprint $table) {
            if (Schema::hasColumn('giang_vien', 'so_gio_day')) {
                $table->dropColumn('so_gio_day');
            }
        });
    }
};
