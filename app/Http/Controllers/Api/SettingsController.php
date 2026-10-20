<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IntegrationSetting;
use App\Models\NotificationRule;
use App\Services\Billz\BillzClient;
use App\Services\Billz\BillzSyncService;
use App\Services\Telegram\TelegramBotService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function telegramShow()
    {
        $token = IntegrationSetting::getEncrypted('telegram_bot_token');

        return response()->json([
            'bot_token_set' => (bool) $token,
            'bot_token_masked' => $token ? str_repeat('*', max(0, strlen($token) - 8)).substr($token, -8) : null,
            'management_group_id' => IntegrationSetting::getValue('telegram_management_group_id'),
            'service_group_id' => IntegrationSetting::getValue('telegram_service_group_id'),
            'webhook_url' => IntegrationSetting::getValue('telegram_webhook_url') ?: route('telegram.webhook'),
            'webhook_status' => IntegrationSetting::getValue('telegram_webhook_status', 'inactive'),
            'bot_username' => IntegrationSetting::getValue('telegram_bot_username'),
            'seller_rating_delay_minutes' => TelegramBotService::sellerRatingDelayMinutes(),
            'mode' => IntegrationSetting::getValue('telegram_webhook_status') === 'active'
                ? 'webhook'
                : (IntegrationSetting::getValue('telegram_mode') ?: 'polling'),
        ]);
    }

    public function telegramSave(Request $request)
    {
        $data = $request->validate([
            'bot_token' => 'nullable|string',
            'management_group_id' => 'nullable|string',
            'service_group_id' => 'nullable|string',
            'seller_rating_delay_minutes' => 'nullable|integer|min:0|max:10080',
        ]);

        if (! empty($data['bot_token'])) {
            IntegrationSetting::setEncrypted('telegram_bot_token', $data['bot_token']);
        }
        foreach (['management_group_id', 'service_group_id'] as $key) {
            if (array_key_exists($key, $data)) {
                IntegrationSetting::setValue('telegram_'.$key, $data[$key]);
            }
        }
        if (array_key_exists('seller_rating_delay_minutes', $data) && $data['seller_rating_delay_minutes'] !== null) {
            IntegrationSetting::setValue(
                'seller_rating_delay_minutes',
                (string) max(0, min(10080, (int) $data['seller_rating_delay_minutes']))
            );
        }

        return $this->telegramShow();
    }

    public function telegramWebhook(Request $request, TelegramBotService $bot)
    {
        $action = $request->validate(['action' => 'required|in:set,delete,info,test'])['action'];

        try {
            if ($action === 'set') {
                $bot->setWebhook($request->get('url'));
                $me = $bot->getMe();
                IntegrationSetting::setValue('telegram_bot_username', $me['username'] ?? null);
            } elseif ($action === 'delete') {
                $bot->deleteWebhook();
            } elseif ($action === 'test') {
                $targets = [
                    'Rahbariyat' => IntegrationSetting::getValue('telegram_management_group_id'),
                    'Kuryer' => IntegrationSetting::getValue('telegram_service_group_id'),
                ];
                $sent = [];
                $failed = [];
                foreach ($targets as $label => $chat) {
                    if (! $chat) {
                        $failed[] = $label.' (ID yoʼq)';

                        continue;
                    }
                    try {
                        $bot->sendMessage($chat, "QR Kafolat — test xabar ✅\nGuruh: <b>{$label}</b>");
                        $sent[] = $label;
                    } catch (\Throwable $e) {
                        $failed[] = $label.': '.$e->getMessage();
                    }
                }
                if ($sent === []) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'Hech qaysi guruhga yuborilmadi. '.implode('; ', $failed),
                    ], 422);
                }

                $info = null;
                try {
                    $info = $bot->getWebhookInfo();
                } catch (\Throwable) {
                }

                return response()->json([
                    'ok' => true,
                    'message' => 'Yuborildi: '.implode(', ', $sent)
                        .($failed !== [] ? '. Muammo: '.implode('; ', $failed) : ''),
                    'webhook' => $info,
                    'settings' => $this->telegramShow()->getData(true),
                ]);
            }

            $info = null;
            try {
                $info = $bot->getWebhookInfo();
            } catch (\Throwable) {
            }

            return response()->json(['ok' => true, 'webhook' => $info, 'settings' => $this->telegramShow()->getData(true)]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function billzShow()
    {
        $secret = IntegrationSetting::getEncrypted('billz_secret_token') ?: config('billz.secret_token');

        return response()->json([
            'secret_set' => (bool) $secret,
            'secret_masked' => $secret ? '********'.substr($secret, -4) : null,
            'company_id' => IntegrationSetting::getValue('billz_company_id'),
            'base_url' => config('billz.base_url'),
            'sync_status' => IntegrationSetting::getValue('billz_sync_status'),
            'last_sales_sync' => IntegrationSetting::getValue('billz_last_sales_sync'),
            'last_products_sync' => IntegrationSetting::getValue('billz_last_products_sync'),
            'sync_error' => IntegrationSetting::getValue('billz_sync_error'),
        ]);
    }

    public function billzSave(Request $request)
    {
        $data = $request->validate([
            'secret_token' => 'nullable|string',
            'company_id' => 'nullable|string',
        ]);
        if (! empty($data['secret_token'])) {
            IntegrationSetting::setEncrypted('billz_secret_token', $data['secret_token']);
        }
        if (array_key_exists('company_id', $data)) {
            IntegrationSetting::setValue('billz_company_id', $data['company_id']);
        }

        return $this->billzShow();
    }

    public function billzTest(BillzClient $client)
    {
        try {
            $token = $client->login(true);

            return response()->json(['ok' => true, 'token_preview' => substr($token, 0, 20).'...']);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function billzSync(Request $request, BillzSyncService $sync)
    {
        $job = $request->validate(['job' => 'required|in:shops,products,clients,sales,all'])['job'];
        $logs = [];
        if ($job === 'shops' || $job === 'all') {
            $logs[] = $sync->syncShops();
        }
        if ($job === 'products' || $job === 'all') {
            $logs[] = $sync->syncProducts();
        }
        if ($job === 'clients' || $job === 'all') {
            $logs[] = $sync->syncClients();
        }
        if ($job === 'sales' || $job === 'all') {
            $logs[] = $sync->syncSales();
        }

        return response()->json(['ok' => true, 'logs' => $logs]);
    }

    public function notifications()
    {
        return response()->json([
            'rules' => NotificationRule::query()->orderBy('id')->get(),
        ]);
    }

    public function notificationsSave(Request $request)
    {
        $data = $request->validate([
            'rules' => 'required|array',
            'rules.*.id' => 'required|exists:notification_rules,id',
            'rules.*.enabled' => 'required|boolean',
            'rules.*.threshold' => 'nullable|string',
        ]);

        foreach ($data['rules'] as $row) {
            $rule = NotificationRule::query()->find($row['id']);
            if (! $rule) {
                continue;
            }

            $threshold = $row['threshold'] ?? null;
            if ($rule->code === 'product_brak_3pct' && is_string($threshold) && preg_match('/(\d+(?:[.,]\d+)?)/', $threshold, $m)) {
                $threshold = (string) max(0.1, min(100, (float) str_replace(',', '.', $m[1])));
                IntegrationSetting::setValue('quality_brak_threshold', $threshold);
            }

            $rule->update([
                'enabled' => $row['enabled'],
                'threshold' => $threshold,
            ]);
        }

        return $this->notifications();
    }
}
