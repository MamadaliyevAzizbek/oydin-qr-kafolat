<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Models\IntegrationSetting;
use App\Services\Telegram\TelegramBotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __invoke(Request $request, TelegramBotService $bot)
    {
        $secret = IntegrationSetting::getValue('telegram_webhook_secret')
            ?: config('telegram.webhook_secret');

        if ($secret) {
            $header = $request->header('X-Telegram-Bot-Api-Secret-Token');
            if ($header !== $secret) {
                return response()->json(['ok' => false], 403);
            }
        }

        try {
            $bot->handleUpdate($request->all());
        } catch (\Throwable $e) {
            Log::error('Telegram webhook: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }

        return response()->json(['ok' => true]);
    }
}
