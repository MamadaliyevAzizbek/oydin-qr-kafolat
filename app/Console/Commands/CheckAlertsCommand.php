<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class CheckAlertsCommand extends Command
{
    protected $signature = 'notify:alerts';

    protected $description = 'SLA, Billz sync va brak chegarasi ogohlantirishlari';

    public function handle(NotificationService $notifications): int
    {
        $notifications->checkSla();
        $notifications->checkBillzSync();
        $notifications->checkBrakThreshold();
        $this->info('Alert tekshiruvlari tugadi.');

        return self::SUCCESS;
    }
}
