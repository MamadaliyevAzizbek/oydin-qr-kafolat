<?php

namespace App\Console\Commands;

use App\Models\IntegrationSetting;
use App\Services\Telegram\TelegramBotService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class TelegramPollCommand extends Command
{
    protected $signature = 'telegram:poll {--once : Bitta so‘rov}';

    protected $description = 'Local: Telegram long polling (Cloudflare / webhook kerak emas)';

    public function handle(TelegramBotService $bot): int
    {
        // Polling uchun webhook o‘chirilishi shart (aks holda getUpdates ishlamaydi)
        try {
            $bot->deleteWebhook();
            IntegrationSetting::setValue('telegram_mode', 'polling');
            $this->info('Webhook o‘chirildi — local polling (Cloudflare kerak emas).');
        } catch (\Throwable $e) {
            $this->warn($e->getMessage());
        }

        $me = $bot->getMe();
        $this->info('Bot: @'.($me['username'] ?? '?'));
        $this->line('Guruhga xabar: panel → Telegram bot → Test xabar');
        $this->line('Botga yozish / chek / /chatid — shu jarayon tinglaydi. To‘xtatish: Ctrl+C');

        $offset = (int) Cache::get('telegram.poll_offset', 0);

        do {
            try {
                $updates = $bot->api('getUpdates', [
                    'offset' => $offset,
                    'timeout' => 25,
                    'allowed_updates' => ['message', 'callback_query', 'my_chat_member'],
                ]);
            } catch (\Throwable $e) {
                $this->error($e->getMessage());
                sleep(2);

                continue;
            }

            foreach ($updates as $update) {
                $offset = ((int) ($update['update_id'] ?? 0)) + 1;
                Cache::put('telegram.poll_offset', $offset, now()->addDays(7));
                $this->line('update #'.($update['update_id'] ?? '?'));
                try {
                    $bot->handleUpdate($update);
                } catch (\Throwable $e) {
                    $this->error('handle: '.$e->getMessage());
                }
            }
        } while (! $this->option('once'));

        return self::SUCCESS;
    }
}
