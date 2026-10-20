<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendSurveysCommand extends Command
{
    protected $signature = 'surveys:dispatch';

    protected $description = 'So\'rovnomalarni rejalashtirish va Telegram orqali yuborish';

    public function handle(NotificationService $notifications): int
    {
        $created = $notifications->scheduleSurveys();
        $sent = $notifications->sendPendingSurveys();
        $this->info("Yaratildi: {$created}, yuborildi: {$sent}");

        return self::SUCCESS;
    }
}
