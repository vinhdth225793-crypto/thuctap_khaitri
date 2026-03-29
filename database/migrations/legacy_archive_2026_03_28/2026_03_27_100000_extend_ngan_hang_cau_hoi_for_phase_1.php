<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ngan_hang_cau_hoi', function (Blueprint $table) {
            if (!Schema::hasColumn('ngan_hang_cau_hoi', 'kieu_dap_an')) {
                $table->string('kieu_dap_an', 50)->nullable()->after('loai_cau_hoi');
            }
        });

        DB::table('ngan_hang_cau_hoi')
            ->where('loai_cau_hoi', 'trac_nghiem')
            ->whereNull('kieu_dap_an')
            ->update(['kieu_dap_an' => 'mot_dap_an']);
    }

    public function down(): void
    {
        if (!Schema::hasColumn('ngan_hang_cau_hoi', 'kieu_dap_an')) {
            return;
        }

        Schema::table('ngan_hang_cau_hoi', function (Blueprint $table) {
            $table->dropColumn('kieu_dap_an');
        });
    }
};
