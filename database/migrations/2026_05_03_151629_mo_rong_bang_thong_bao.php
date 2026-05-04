<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver !== 'sqlite') {
            // MySQL/MariaDB: đổi enum sang string mở rộng
            DB::statement("ALTER TABLE thong_bao MODIFY COLUMN loai VARCHAR(60) NOT NULL DEFAULT 'he_thong'");
        }
        // SQLite không có enum thật — cột text sẵn có, không cần đổi.

        Schema::table('thong_bao', function (Blueprint $table) {
            $table->string('level', 20)->default('info')->after('loai');
            $table->string('icon', 40)->nullable()->after('level');
            $table->text('metadata')->nullable()->after('icon');
            $table->index('loai');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('thong_bao', function (Blueprint $table) {
            $table->dropIndex(['loai']);
            $table->dropIndex(['created_at']);
            $table->dropColumn(['level', 'icon', 'metadata']);
        });
    }
};
