<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramBotService;
use Illuminate\Console\Command;

class TelegramInfoCommand extends Command
{
    protected $signature = 'telegram:info';

    protected $description = 'Telegram bot va webhook holati';

    public function handle(TelegramBotService $bot): int
    {
        try {
            $me = $bot->getMe();
            $hook = $bot->getWebhookInfo();
            $this->info('Bot: @'.($me['username'] ?? '?'));
            $this->line(json_encode($hook, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
