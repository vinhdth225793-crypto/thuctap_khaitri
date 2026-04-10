<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ShowCreateTable extends Command
{
    protected $signature = 'app:show-create';

    public function handle()
    {
        $res = DB::select("SHOW CREATE TABLE nguoi_dung");
        $this->info(json_encode($res, JSON_PRETTY_PRINT));
    }
}
