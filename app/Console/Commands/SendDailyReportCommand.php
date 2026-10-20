<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendDailyReportCommand extends Command
{
    protected $signature = 'notify:daily';

    protected $description = 'Kunlik Telegram hisobot';

    public function handle(NotificationService $notifications): int
    {
        $notifications->sendDailyReport();
        $this->info('Kunlik hisobot yuborildi (yoki o\'tkazib yuborildi).');

        return self::SUCCESS;
    }
}
