<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GetTableNames extends Command
{
    protected $signature = 'app:get-tables';

    public function handle()
    {
        $tables = Schema::getTables();
        $this->info(implode(',', array_column($tables, 'name')));
    }
}
