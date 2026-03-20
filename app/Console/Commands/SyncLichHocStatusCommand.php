<?php

namespace App\Console\Commands;

use App\Models\LichHoc;
use Illuminate\Console\Command;

class SyncLichHocStatusCommand extends Command
{
    protected $signature = 'lich-hoc:sync-status {--dry-run : Chi hien thi so lieu ma khong ghi vao DB}';

    protected $description = 'Dong bo trang thai lich hoc theo thoi gian thuc te';

    public function handle(): int
    {
        $now = now();
        $dryRun = (bool) $this->option('dry-run');
        $toDangHoc = 0;
        $toHoanThanh = 0;

        LichHoc::query()
            ->whereIn('trang_thai', ['cho', 'dang_hoc'])
            ->where('trang_thai', '!=', 'huy')
            ->orderBy('id')
            ->chunkById(200, function ($lichHocs) use ($now, $dryRun, &$toDangHoc, &$toHoanThanh) {
                foreach ($lichHocs as $lichHoc) {
                    $startsAt = $lichHoc->starts_at;
                    $endsAt = $lichHoc->ends_at;

                    if (!$startsAt || !$endsAt) {
                        continue;
                    }

                    if ($now->greaterThan($endsAt)) {
                        if ($lichHoc->trang_thai !== 'hoan_thanh') {
                            $toHoanThanh++;
                            if (!$dryRun) {
                                $lichHoc->update(['trang_thai' => 'hoan_thanh']);
                            }
                        }

                        continue;
                    }

                    if ($now->greaterThanOrEqualTo($startsAt) && $now->lessThanOrEqualTo($endsAt) && $lichHoc->trang_thai !== 'dang_hoc') {
                        $toDangHoc++;
                        if (!$dryRun) {
                            $lichHoc->update(['trang_thai' => 'dang_hoc']);
                        }
                    }
                }
            });

        $modeLabel = $dryRun ? 'dry-run' : 'apply';

        $this->info("Sync lich hoc [$modeLabel] hoan tat.");
        $this->line("Cho -> Dang hoc: $toDangHoc");
        $this->line("Cho/Dang hoc -> Hoan thanh: $toHoanThanh");

        return self::SUCCESS;
    }
}
