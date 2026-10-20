<?php

namespace App\Console\Commands;

use App\Services\Billz\BillzSyncService;
use Illuminate\Console\Command;

class BillzSyncShopsCommand extends Command
{
    protected $signature = 'billz:sync-shops';

    protected $description = 'Billz do\'konlarini sinxronlash';

    public function handle(BillzSyncService $sync): int
    {
        $log = $sync->syncShops();
        $this->line("{$log->status}: {$log->records} ta — ".($log->message ?? ''));

        return $log->status === 'success' ? self::SUCCESS : self::FAILURE;
    }
}
