<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class DropTempTables extends Command
{
    protected $signature = 'app:drop-temp';

    public function handle()
    {
        Schema::dropIfExists('hoc_vien');
        Schema::dropIfExists('nguoi_dung');
        $this->info('Dropped hoc_vien and nguoi_dung');
    }
}
