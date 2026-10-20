<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramBotService;
use Illuminate\Console\Command;

class TelegramSetWebhookCommand extends Command
{
    protected $signature = 'telegram:set-webhook {--url=} {--delete}';

    protected $description = 'Telegram webhook o\'rnatish yoki o\'chirish';

    public function handle(TelegramBotService $bot): int
    {
        try {
            if ($this->option('delete')) {
                $bot->deleteWebhook();
                $this->info('Webhook o\'chirildi');

                return self::SUCCESS;
            }
            $result = $bot->setWebhook($this->option('url') ?: null);
            $this->info('Webhook o\'rnatildi');
            $this->line(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
