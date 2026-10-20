<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendWeeklyReportCommand extends Command
{
    protected $signature = 'notify:weekly';

    protected $description = 'Haftalik Telegram hisobot (Rahbariyat)';

    public function handle(NotificationService $notifications): int
    {
        $notifications->sendWeeklyReport();
        $this->info('Haftalik hisobot yuborildi (yoki o\'tkazib yuborildi).');

        return self::SUCCESS;
    }
}
