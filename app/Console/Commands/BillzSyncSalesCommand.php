<?php

namespace App\Console\Commands;

use App\Services\Billz\BillzSyncService;
use Illuminate\Console\Command;

class BillzSyncSalesCommand extends Command
{
    protected $signature = 'billz:sync-sales {--from=} {--to=} {--pages=50}';

    protected $description = 'Billz sotuvlarini sinxronlash';

    public function handle(BillzSyncService $sync): int
    {
        $log = $sync->syncSales(
            $this->option('from') ?: null,
            $this->option('to') ?: null,
            (int) $this->option('pages')
        );
        $this->line("{$log->status}: {$log->records} ta — ".($log->message ?? ''));

        return $log->status === 'success' ? self::SUCCESS : self::FAILURE;
    }
}
