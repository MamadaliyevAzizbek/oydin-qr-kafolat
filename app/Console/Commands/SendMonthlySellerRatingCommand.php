<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendMonthlySellerRatingCommand extends Command
{
    protected $signature = 'notify:monthly-sellers {--force : Qayta yuborish}';

    protected $description = 'Oylik sotuvchilar reytingini PDF qilib Telegram rahbariyat guruhiga yuborish';

    public function handle(NotificationService $notifications): int
    {
        $result = $notifications->sendMonthlySellerRating((bool) $this->option('force'));
        if ($result['ok']) {
            $this->info($result['message'].' (rad≥15: '.($result['high_rad'] ?? 0).')');
        } else {
            $this->warn($result['message']);
        }

        return self::SUCCESS;
    }
}
