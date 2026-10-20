<?php

namespace App\Console\Commands;

use App\Services\Billz\BillzSyncService;
use Illuminate\Console\Command;

class BillzSyncProductsCommand extends Command
{
    protected $signature = 'billz:sync-products {--pages=20}';

    protected $description = 'Billz mahsulotlarini sinxronlash';

    public function handle(BillzSyncService $sync): int
    {
        $log = $sync->syncProducts((int) $this->option('pages'));
        $this->line("{$log->status}: {$log->records} ta — ".($log->message ?? ''));

        return $log->status === 'success' ? self::SUCCESS : self::FAILURE;
    }
}
