<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DropTablesHard extends Command
{
    protected $signature = 'app:drop-hard';

    public function handle()
    {
        $tables = ['hoc_vien', 'nguoi_dung', 'giang_vien', 'khoa_hoc', 'lop_hoc', 'module_hoc', 'nhom_nganh'];
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($tables as $table) {
            DB::statement("DROP TABLE IF EXISTS $table");
            $this->info("Dropped $table");
        }
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
