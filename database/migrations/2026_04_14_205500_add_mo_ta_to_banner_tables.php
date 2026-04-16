<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['banner', 'banners'] as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'mo_ta')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->text('mo_ta')->nullable()->after('tieu_de');
                });
            }
        }
    }

    public function down(): void
    {
        // Compatibility migration: keep the column to avoid dropping data from legacy databases.
    }
};
