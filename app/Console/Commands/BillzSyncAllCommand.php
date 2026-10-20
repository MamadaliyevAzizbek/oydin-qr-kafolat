<?php

namespace App\Console\Commands;

use App\Services\Billz\BillzSyncService;
use Illuminate\Console\Command;

class BillzSyncAllCommand extends Command
{
    protected $signature = 'billz:sync-all {--from=} {--to=}';

    protected $description = 'Billz: shops + products + clients + sales (to\'liq sinxron)';

    public function handle(BillzSyncService $sync): int
    {
        $jobs = [
            'shops' => fn () => $sync->syncShops(),
            'products' => fn () => $sync->syncProducts(50),
            'clients' => fn () => $sync->syncClients(50),
            'sales' => fn () => $sync->syncSales(
                $this->option('from') ?: now()->subMonths(3)->toDateString(),
                $this->option('to') ?: now()->toDateString(),
                100
            ),
        ];

        $ok = true;
        foreach ($jobs as $name => $fn) {
            $this->info("→ {$name}...");
            $log = $fn();
            $this->line("  {$log->status}: {$log->records} — ".($log->message ?? ''));
            if ($log->status !== 'success') {
                $ok = false;
            }
        }

        return $ok ? self::SUCCESS : self::FAILURE;
    }
}
