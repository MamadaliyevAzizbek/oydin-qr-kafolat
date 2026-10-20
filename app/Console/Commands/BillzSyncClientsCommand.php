<?php

namespace App\Console\Commands;

use App\Services\Billz\BillzSyncService;
use Illuminate\Console\Command;

class BillzSyncClientsCommand extends Command
{
    protected $signature = 'billz:sync-clients {--pages=20}';

    protected $description = 'Billz mijozlarini sinxronlash';

    public function handle(BillzSyncService $sync): int
    {
        $log = $sync->syncClients((int) $this->option('pages'));
        $this->line("{$log->status}: {$log->records} ta — ".($log->message ?? ''));

        return $log->status === 'success' ? self::SUCCESS : self::FAILURE;
    }
}
