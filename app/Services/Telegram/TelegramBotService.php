<?php

namespace App\Services\Telegram;

use App\Models\Activation;
use App\Models\BotUser;
use App\Models\Claim;
use App\Models\Customer;
use App\Models\IntegrationSetting;
use App\Models\Order;
use App\Models\QrToken;
use App\Models\SurveyMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class TelegramBotService
{
    public function __construct(protected ReceiptActivationService $receipts) {}

    public function token(): ?string
    {
        return IntegrationSetting::getEncrypted('telegram_bot_token')
            ?: config('telegram.bot_token');
    }

    /**
     * Faqat 2 guruh: Rahbariyat (management) va Servis (service).
     * Eski admin/purchase so‘rovlari → Rahbariyatga.
     */
    public function chatId(string $target = 'management'): ?string
    {
        return match ($target) {
            // service = Kuryer guruhi (eski kalit nomi saqlanadi)
            'service', 'courier' => IntegrationSetting::getValue('telegram_service_group_id'),
            default => IntegrationSetting::getValue('telegram_management_group_id'),
        };
    }

    public function api(string $method, array $params = []): array
    {
        $token = $this->token();
        if (! $token) {
            throw new RuntimeException('Telegram bot token sozlanmagan.');
        }

        $response = Http::asJson()
            ->timeout(60)
            ->post("https://api.telegram.org/bot{$token}/{$method}", $params);

        $json = $response->json() ?? [];
        if (! ($json['ok'] ?? false)) {
            throw new RuntimeException('Telegram API xato: '.($json['description'] ?? $response->body()));
        }

        $result = $json['result'] ?? [];

        // Telegram sometimes returns true/false instead of object
        return is_array($result) ? $result : ['ok' => (bool) $result];
    }

    public function sendMessage(string|int $chatId, string $text, array $extra = []): array
    {
        try {
            return $this->api('sendMessage', array_merge([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ], $extra));
        } catch (\Throwable $e) {
            if ($this->isBlockedByUserError($e->getMessage())) {
                $this->markBlocked($chatId, true);
            }
            throw $e;
        }
    }

    public function sendDocument(string|int $chatId, string $filePath, ?string $caption = null): array
    {
        $token = $this->token();
        if (! $token) {
            throw new RuntimeException('Telegram bot token sozlanmagan.');
        }
        if (! is_file($filePath)) {
            throw new RuntimeException('Fayl topilmadi: '.$filePath);
        }

        $request = Http::attach(
            'document',
            file_get_contents($filePath),
            basename($filePath)
        )->timeout(120);

        $payload = [
            'chat_id' => $chatId,
        ];
        if ($caption) {
            $payload['caption'] = mb_substr($caption, 0, 1024);
            $payload['parse_mode'] = 'HTML';
        }

        $response = $request->post("https://api.telegram.org/bot{$token}/sendDocument", $payload);
        $json = $response->json() ?? [];
        if (! ($json['ok'] ?? false)) {
            $desc = (string) ($json['description'] ?? $response->body());
            if ($this->isBlockedByUserError($desc)) {
                $this->markBlocked($chatId, true);
            }
            throw new RuntimeException('Telegram sendDocument xato: '.$desc);
        }

        $result = $json['result'] ?? [];

        return is_array($result) ? $result : ['ok' => true];
    }

    public function deleteMessage(string|int $chatId, int $messageId): void
    {
        try {
            $this->api('deleteMessage', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ]);
        } catch (\Throwable) {
            // ignore
        }
    }

    public function setWebhook(?string $url = null): array
    {
        $url = $url ?: route('telegram.webhook');
        $secret = IntegrationSetting::getValue('telegram_webhook_secret')
            ?: config('telegram.webhook_secret')
            ?: Str::random(32);

        IntegrationSetting::setValue('telegram_webhook_secret', $secret);
        IntegrationSetting::setValue('telegram_webhook_url', $url);

        $result = $this->api('setWebhook', [
            'url' => $url,
            'secret_token' => $secret,
            'allowed_updates' => ['message', 'callback_query', 'my_chat_member'],
        ]);

        IntegrationSetting::setValue('telegram_webhook_status', 'active');
        IntegrationSetting::setValue('telegram_mode', 'webhook');

        return $result;
    }

    public function deleteWebhook(): array
    {
        $result = $this->api('deleteWebhook', ['drop_pending_updates' => false]);
        IntegrationSetting::setValue('telegram_webhook_status', 'inactive');
        IntegrationSetting::setValue('telegram_mode', 'polling');

        return $result;
    }

    public function getWebhookInfo(): array
    {
        return $this->api('getWebhookInfo');
    }

    public function getMe(): array
    {
        return $this->api('getMe');
    }

    public function handleUpdate(array $update): void
    {
        if (isset($update['my_chat_member'])) {
            $this->handleMyChatMember($update['my_chat_member']);

            return;
        }

        if (isset($update['callback_query'])) {
            $this->handleCallback($update['callback_query']);

            return;
        }

        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        }
    }

    /**
     * Mijoz botni bloklaganda / qayta ochganda Telegram yuboradi.
     * Guruhga qo‘shilganda — chat ID haqida qisqa yo‘riqnoma.
     */
    protected function handleMyChatMember(array $member): void
    {
        $chat = $member['chat'] ?? [];
        $chatType = (string) ($chat['type'] ?? '');
        $chatId = (string) ($chat['id'] ?? '');
        if ($chatId === '') {
            return;
        }

        $status = (string) ($member['new_chat_member']['status'] ?? '');

        // Guruhga qo‘shilganda — mijoz oqimi emas, faqat ID ko‘rsatish
        if (in_array($chatType, ['group', 'supergroup'], true)) {
            if (in_array($status, ['member', 'administrator'], true)) {
                $title = $chat['title'] ?? 'Guruh';
                try {
                    $this->sendMessage(
                        $chatId,
                        "QR Kafolat bot guruhga qo‘shildi.\n"
                        ."<b>{$title}</b>\n"
                        ."Guruh ID: <code>{$chatId}</code>\n\n"
                        ."Panel → Telegram bot sozlamalarida:\n"
                        ."• Rahbariyat guruh ID yoki\n"
                        ."• Kuryer guruh ID\n"
                        ."deb yozing.\n\n"
                        ."Qayta ko‘rish: /chatid"
                    );
                } catch (\Throwable $e) {
                    Log::warning('Group join notify failed: '.$e->getMessage());
                }
            }

            return;
        }

        if ($chatType !== 'private') {
            return;
        }

        $blocked = in_array($status, ['kicked', 'left'], true);
        $active = in_array($status, ['member', 'restricted'], true);

        $data = [
            'is_blocked' => $blocked,
            'username' => $member['from']['username'] ?? null,
            'first_name' => $member['from']['first_name'] ?? null,
            'last_name' => $member['from']['last_name'] ?? null,
        ];
        if ($active) {
            $data['is_blocked'] = false;
            $data['last_seen_at'] = now();
        }

        BotUser::query()->updateOrCreate(['telegram_id' => $chatId], $data);

        if ($blocked) {
            Log::info('Telegram bot blocked by user', ['telegram_id' => $chatId, 'status' => $status]);
        }
    }

    /** Rahbariyat / Kuryer guruhlari — faqat /chatid (mijoz menyusi yo‘q). */
    protected function handleGroupMessage(array $message, string $chatId, array $chat): void
    {
        $text = trim((string) ($message['text'] ?? ''));
        // /chatid@BotUsername
        $cmd = preg_replace('/@\w+$/', '', explode(' ', $text)[0] ?? '');
        if (! in_array($cmd, ['/chatid', '/id', '/start'], true)) {
            return;
        }

        $title = $chat['title'] ?? 'Guruh';
        $type = $chat['type'] ?? 'group';
        $this->sendMessage(
            $chatId,
            "<b>{$title}</b> ({$type})\n"
            ."Chat ID: <code>{$chatId}</code>\n\n"
            ."Shu ID ni panel → <b>Telegram bot</b> da:\n"
            ."• Rahbariyat guruh ID\n"
            ."• Kuryer guruh ID\n"
            ."maydonlariga yozing."
        );
    }

    public function markBlocked(string|int $telegramId, bool $blocked = true): void
    {
        BotUser::query()->where('telegram_id', (string) $telegramId)->update([
            'is_blocked' => $blocked,
        ]);
    }

    protected function isBlockedByUserError(string $message): bool
    {
        $m = mb_strtolower($message);

        return str_contains($m, 'blocked')
            || str_contains($m, 'bot was blocked')
            || str_contains($m, 'user is deactivated')
            || str_contains($m, 'chat not found')
            || str_contains($m, 'forbidden: bot');
    }

    protected function handleMessage(array $message): void
    {
        $chat = $message['chat'] ?? [];
        $chatId = (string) ($chat['id'] ?? '');
        $chatType = (string) ($chat['type'] ?? 'private');
        $from = $message['from'] ?? [];
        if ($chatId === '') {
            return;
        }

        // Guruh / kanal — mijoz oqimi yo‘q; faqat /chatid
        if (in_array($chatType, ['group', 'supergroup', 'channel'], true)) {
            $this->handleGroupMessage($message, $chatId, $chat);

            return;
        }

        $botUser = BotUser::query()->updateOrCreate(
            ['telegram_id' => $chatId],
            [
                'username' => $from['username'] ?? null,
                'first_name' => $from['first_name'] ?? null,
                'last_name' => $from['last_name'] ?? null,
                'last_seen_at' => now(),
                'is_blocked' => false,
            ]
        );

        if (isset($message['contact']['phone_number'])) {
            if (! $botUser->consent_answered_at) {
                $this->askConsent($botUser);

                return;
            }
            $this->verifyPhone($botUser, $message['contact']['phone_number']);

            return;
        }

        // Photo of Billz receipt
        if (! empty($message['photo']) && is_array($message['photo'])) {
            $this->handleReceiptPhoto($botUser, $message);

            return;
        }

        // Document image
        if (! empty($message['document']['mime_type']) && str_starts_with($message['document']['mime_type'], 'image/')) {
            $this->handleReceiptPhoto($botUser, $message, $message['document']['file_id'] ?? null);

            return;
        }

        $text = trim((string) ($message['text'] ?? ''));
        if (str_starts_with($text, '/start')) {
            $payload = trim(substr($text, 6));
            $this->start($botUser, $payload !== '' ? $payload : null);

            return;
        }

        if (in_array($text, ['/home', 'Mening uyim', '🏠 Mening uyim'], true)) {
            if (! $this->canUseMainMenu($botUser)) {
                $this->sendMessage(
                    $botUser->telegram_id,
                    'Avval Billz chekini yuboring va tasdiqlang — keyin <b>Mening uyim</b> ochiladi.',
                    ['reply_markup' => $this->mainKeyboard($botUser)]
                );

                return;
            }
            $this->showHome($botUser);

            return;
        }

        if (in_array($text, ['Shikoyat', '📝 Shikoyat', '/claim', '📱 Mini App', 'Mini App'], true)) {
            $this->startClaim($botUser);

            return;
        }

        if ($botUser->state === 'awaiting_name') {
            $this->saveFullName($botUser, $text);

            return;
        }

        if ($botUser->state === 'awaiting_claim_text') {
            $botUser->update(['state' => null]);
            $this->startClaim($botUser);

            return;
        }

        if ($botUser->state === 'awaiting_order_number' || preg_match('/^\d{6,14}$/', preg_replace('/\D+/', '', $text))) {
            $this->handleOrderNumberText($botUser, $text);

            return;
        }

        if ($this->canUseMainMenu($botUser)) {
            $this->sendMessage(
                $botUser->telegram_id,
                'Billz <b>chek rasmini</b> yuboring yoki chek raqamini yozing.',
                ['reply_markup' => $this->mainKeyboard($botUser)]
            );

            return;
        }

        $this->sendMessage(
            $botUser->telegram_id,
            'Ro‘yxatdan o‘ting (/start), so‘ng Billz chek rasmini yuboring.',
            ['reply_markup' => $this->mainKeyboard($botUser)]
        );
    }

    /** Reply tugmalar: faqat chek tasdiqlangandan keyin. */
    protected function canUseMainMenu(BotUser $botUser): bool
    {
        if (! $botUser->phone_verified_at || $this->needsName($botUser) || ! $botUser->consent_answered_at) {
            return false;
        }

        return Activation::query()
            ->where(function ($q) use ($botUser) {
                $q->where('bot_user_id', $botUser->id);
                if ($botUser->customer_id) {
                    $q->orWhere('customer_id', $botUser->customer_id);
                }
            })
            ->exists();
    }

    protected function mainKeyboard(BotUser $botUser): array
    {
        if (! $this->canUseMainMenu($botUser)) {
            return ['remove_keyboard' => true];
        }

        // Faqat 2 tugma — ikkalasi ham Mini App ochadi (alohida «Mini App» tugma yo‘q).
        $https = str_starts_with($this->publicAppBase(), 'https://');
        $homeBtn = $https
            ? ['text' => '🏠 Mening uyim', 'web_app' => ['url' => $this->miniAppUrl('home', $botUser)]]
            : ['text' => '🏠 Mening uyim'];
        $claimBtn = $https
            ? ['text' => '📝 Shikoyat', 'web_app' => ['url' => $this->miniAppUrl('form', $botUser)]]
            : ['text' => '📝 Shikoyat'];

        return [
            'keyboard' => [[$homeBtn, $claimBtn]],
            'resize_keyboard' => true,
            'is_persistent' => true,
        ];
    }

    protected function startClaim(BotUser $botUser): void
    {
        if (! $this->canUseMainMenu($botUser)) {
            $this->sendMessage(
                $botUser->telegram_id,
                'Shikoyat uchun avval chekni tasdiqlang.',
                ['reply_markup' => $this->mainKeyboard($botUser)]
            );

            return;
        }

        $this->openMiniAppGuide(
            $botUser,
            "Shikoyatni <b>Mini App</b> orqali yuboring.\nPastdagi tugmani bosing.",
            'form',
            '📱 Mini App ochish'
        );
    }

    /** Qisqa yo‘l-yo‘riq + Mini App tugmasi (yoki HTTPS yo‘q bo‘lsa forma). */
    protected function openMiniAppGuide(BotUser $botUser, string $text, string $tab, string $btnLabel): void
    {
        $mini = $this->miniAppUrl($tab, $botUser);
        if (str_starts_with($mini, 'https://')) {
            $this->sendMessage(
                $botUser->telegram_id,
                $text,
                [
                    'reply_markup' => [
                        'inline_keyboard' => [[
                            ['text' => $btnLabel, 'web_app' => ['url' => $mini]],
                        ]],
                    ],
                ]
            );

            return;
        }

        $form = $this->claimFormUrl($botUser);
        $this->sendMessage(
            $botUser->telegram_id,
            'Ochish uchun tugmani bosing.',
            [
                'reply_markup' => [
                    'inline_keyboard' => [[
                        ['text' => '📝 Ochish', 'url' => $tab === 'form' ? $form : $mini],
                    ]],
                ],
            ]
        );
    }

    public function publicAppBase(): string
    {
        return rtrim(
            (string) (IntegrationSetting::getValue('public_app_url') ?: config('app.url')),
            '/'
        );
    }

    public function miniAppUrl(?string $tab = null, ?BotUser $botUser = null): string
    {
        $url = $this->publicAppBase().'/miniapp';
        $q = [];
        if ($tab !== null && $tab !== '') {
            $q['tab'] = $tab;
        }
        if ($botUser) {
            $q['t'] = $botUser->ensureClaimToken();
        }
        if ($q !== []) {
            $url .= '?'.http_build_query($q);
        }

        return $url;
    }

    /** Kuryer Mini App (HTTPS) — guruhda url / shaxsiyda web_app. */
    public function courierAppUrl(int $claimId): string
    {
        return $this->publicAppBase().'/courier-app?claim='.$claimId;
    }

    /**
     * Guruhda Mini App ochish: t.me/bot?startapp=c{id}
     * (BotFather’da Mini App URL bo‘lsa to‘liq Mini App; bo‘lmasa HTTPS url).
     */
    public function courierAppOpenButton(int $claimId): array
    {
        $https = $this->courierAppUrl($claimId);
        $username = trim((string) IntegrationSetting::getValue('telegram_bot_username', ''));
        $username = ltrim($username, '@');
        if ($username === '') {
            try {
                $me = $this->getMe();
                $username = (string) ($me['username'] ?? '');
                if ($username !== '') {
                    IntegrationSetting::setValue('telegram_bot_username', $username);
                }
            } catch (\Throwable) {
            }
        }
        if ($username !== '') {
            return [
                'text' => '📱 Topshiriqni ochish',
                'url' => 'https://t.me/'.$username.'?startapp=c'.$claimId,
            ];
        }

        return [
            'text' => '📱 Topshiriqni ochish',
            'url' => $https,
        ];
    }

    /** Tayinlangan kuryerga shaxsiy Mini App (web_app — eng ishonchli). */
    public function notifyCourierPersonalMiniApp(Claim $claim): void
    {
        $claim->loadMissing('courier');
        $tg = trim((string) ($claim->courier?->telegram_id ?? ''));
        if ($tg === '' || ! $claim->id) {
            return;
        }
        $url = $this->courierAppUrl((int) $claim->id);
        try {
            $this->sendMessage(
                $tg,
                "📦 Yangi topshiriq: <b>{$claim->number}</b>\nPastdagi tugmadan oching.",
                [
                    'reply_markup' => [
                        'inline_keyboard' => [[
                            ['text' => '📱 Topshiriqni ochish', 'web_app' => ['url' => $url]],
                        ]],
                    ],
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Courier personal miniapp: '.$e->getMessage());
        }
    }

    public function claimFormUrl(BotUser $botUser): string
    {
        $token = $botUser->ensureClaimToken();

        return $this->publicAppBase().'/shikoyat/'.$token;
    }

    public function notifyClaimOpened(Claim $claim): void
    {
        $claim->loadMissing(['botUser', 'orderItem', 'product', 'customer', 'items.orderItem', 'items.product']);
        $number = $claim->number;
        $labels = $claim->productLabels();
        $productBlock = count($labels) > 1
            ? "Mahsulotlar (".count($labels)." ta):\n".collect($labels)->map(fn ($n, $i) => ($i + 1).'. '.$n)->implode("\n")
            : 'Mahsulot: '.($labels[0] ?? 'Mahsulot');
        $reason = $claim->reason ?? '';
        $photos = count($claim->photos ?? []);

        if ($claim->botUser?->telegram_id) {
            try {
                $this->sendMessage(
                    $claim->botUser->telegram_id,
                    "So‘rov ochildi: <b>{$number}</b>\n"
                    ."{$productBlock}\n"
                    ."Sabab: {$reason}"
                    .($photos ? "\nRasmlar: {$photos} ta" : '')
                    ."\n\nTez orada ko‘rib chiqiladi. Holat yangilanishi shu botda keladi.",
                    ['reply_markup' => $this->mainKeyboard($claim->botUser)]
                );
            } catch (\Throwable $e) {
                Log::warning('Claim bot notify failed: '.$e->getMessage());
            }
        }

        $cust = $claim->customer?->name
            ?: trim(($claim->botUser?->first_name ?? '').' '.($claim->botUser?->last_name ?? ''))
            ?: 'Mijoz';
        $phone = $claim->customer?->phone ?: ($claim->botUser?->phone ?? '—');
        $panel = url('/panel#brak');
        $text = "Yangi brak so‘rovi <b>{$number}</b>\n"
            ."Mijoz: {$cust} · {$phone}\n"
            ."{$productBlock}\n"
            ."{$reason}\n"
            .Str::limit((string) $claim->description, 200)."\n"
            ."<a href=\"{$panel}\">Panelni ochish</a>";

        // Yangi so‘rov — faqat Rahbariyat. Kuryer guruhi faqat kuryer belgilangach.
        $chat = $this->chatId('management');
        if ($chat) {
            try {
                $this->sendMessage($chat, $text);
            } catch (\Throwable $e) {
                Log::warning('Claim notify (management) failed: '.$e->getMessage());
            }
        }
    }

    protected function start(BotUser $botUser, ?string $qrToken = null): void
    {
        if (! $botUser->started_at) {
            $botUser->started_at = now();
            $botUser->save();
        }

        if ($qrToken) {
            $token = QrToken::query()->where('token', $qrToken)->first();
            if ($token) {
                $token->update(['scanned_at' => $token->scanned_at ?? now()]);
                $botUser->update([
                    'state' => 'pending_qr',
                    'state_data' => array_merge($botUser->state_data ?? [], ['qr_token_id' => $token->id]),
                ]);
            }
        }

        // TZ: ro‘yxatdan o‘tishda marketing / shaxsiy ma’lumot roziligi
        if (! $botUser->consent_answered_at) {
            $this->askConsent($botUser);

            return;
        }

        if (! $botUser->phone_verified_at) {
            $this->askPhone($botUser);

            return;
        }

        if ($this->needsName($botUser)) {
            $this->askName($botUser);

            return;
        }

        $this->activatePendingQr($botUser);
        $this->activatePendingOrder($botUser);

        $this->sendMessage($botUser->telegram_id,
            "Xush kelibsiz!\n\nBillz <b>chek rasmini</b> yuboring yoki chek raqamini yozing — mahsulotlar <b>Mening uyim</b>ga tushadi.",
            ['reply_markup' => $this->mainKeyboard($botUser)]
        );
    }

    protected function askConsent(BotUser $botUser): void
    {
        $botUser->update(['state' => 'awaiting_consent']);
        $this->sendMessage(
            $botUser->telegram_id,
            "Assalomu alaykum! <b>QR Kafolat</b> botiga xush kelibsiz.\n\n"
            ."Kafolat va servis uchun telefon raqamingiz va xarid ma’lumotlaringiz qayta ishlanadi.\n\n"
            ."Aksiya va so‘rovnoma xabarlarini (marketing) olishga <b>rozimisiz</b>?\n"
            ."«Rozi emasman» — faqat kafolat ishlaydi, marketing xabarlar yuborilmaydi.",
            [
                'reply_markup' => [
                    'inline_keyboard' => [[
                        ['text' => '✅ Roziman', 'callback_data' => 'consent:yes'],
                        ['text' => '❌ Rozi emasman', 'callback_data' => 'consent:no'],
                    ]],
                ],
            ]
        );
    }

    protected function askPhone(BotUser $botUser): void
    {
        $this->sendMessage($botUser->telegram_id,
            "Kafolatni ochish uchun telefon raqamingizni yuboring:",
            [
                'reply_markup' => [
                    'keyboard' => [[
                        ['text' => '📱 Telefonni yuborish', 'request_contact' => true],
                    ]],
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ],
            ]
        );
    }

    protected function saveConsent(BotUser $botUser, bool $accepted): void
    {
        $botUser->update([
            'marketing_consent' => $accepted,
            'consent_answered_at' => now(),
            'state' => null,
        ]);

        if ($botUser->customer_id) {
            Customer::query()->whereKey($botUser->customer_id)->update([
                'marketing_consent' => $accepted,
            ]);
        }

        $this->sendMessage(
            $botUser->telegram_id,
            $accepted
                ? 'Rahmat! Marketing va so‘rovnoma xabarlariga rozilik qabul qilindi.'
                : 'Tushundik. Marketing xabarlar yuborilmaydi — kafolat ishlayveradi.'
        );

        if (! $botUser->phone_verified_at) {
            $this->askPhone($botUser);

            return;
        }

        if ($this->needsName($botUser)) {
            $this->askName($botUser);

            return;
        }

        $this->finishRegistration($botUser);
    }

    protected function verifyPhone(BotUser $botUser, string $phone): void
    {
        $phone = preg_replace('/\D+/', '', $phone);
        if (! str_starts_with($phone, '998') && strlen($phone) === 9) {
            $phone = '998'.$phone;
        }

        // Faqat bot mijozlari: Billz importidan kelgan yozuvni telefon boʻyicha qayta ishlatmaymiz
        $customer = Customer::query()
            ->fromBot()
            ->where('phone', 'like', '%'.substr($phone, -9))
            ->first();
        if (! $customer) {
            $customer = Customer::query()->create([
                'name' => null,
                'phone' => '+'.$phone,
                'telegram_id' => $botUser->telegram_id,
                'registered_at' => now(),
            ]);
        } else {
            $customer->update(['telegram_id' => $botUser->telegram_id]);
        }

        $customer->update([
            'marketing_consent' => (bool) $botUser->marketing_consent,
        ]);

        $botUser->update([
            'phone' => '+'.$phone,
            'customer_id' => $customer->id,
            'phone_verified_at' => now(),
        ]);
        $botUser->setRelation('customer', $customer->fresh());

        $this->sendMessage($botUser->telegram_id, "Telefon tasdiqlandi: <b>+{$phone}</b>");

        if ($this->needsName($botUser)) {
            $this->askName($botUser);

            return;
        }

        $this->finishRegistration($botUser);
    }

    protected function needsName(BotUser $botUser): bool
    {
        $botUser->loadMissing('customer');
        $name = trim((string) ($botUser->customer?->name ?? ''));
        if ($name === '' || mb_strtolower($name) === 'mijoz') {
            return true;
        }

        // Kamida ism + familiya
        return count(preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY)) < 2;
    }

    protected function askName(BotUser $botUser): void
    {
        $botUser->update(['state' => 'awaiting_name']);
        $this->sendMessage(
            $botUser->telegram_id,
            "Ism va familiyangizni yozing (masalan: <b>Jamshid Xasanov</b>):",
            [
                'reply_markup' => [
                    'remove_keyboard' => true,
                ],
            ]
        );
    }

    protected function saveFullName(BotUser $botUser, string $text): void
    {
        $name = trim(preg_replace('/\s+/u', ' ', $text) ?? '');
        $name = trim($name, " \t\n\r\0\x0B.,;");

        if (mb_strlen($name) < 3 || count(preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY)) < 2) {
            $this->sendMessage($botUser->telegram_id, 'Iltimos, ism va familiyani to‘liq yozing (kamida 2 so‘z).');
            $this->askName($botUser);

            return;
        }

        if (preg_match('/^\d+$/', $name) || str_starts_with($name, '/')) {
            $this->sendMessage($botUser->telegram_id, 'Bu ism emas. Ism va familiyangizni yozing.');
            $this->askName($botUser);

            return;
        }

        $parts = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY);
        $first = $parts[0] ?? null;
        $last = isset($parts[1]) ? implode(' ', array_slice($parts, 1)) : null;

        $botUser->update([
            'first_name' => $first,
            'last_name' => $last,
            'state' => null,
        ]);

        if ($botUser->customer_id) {
            Customer::query()->whereKey($botUser->customer_id)->update(['name' => $name]);
        }

        $this->sendMessage($botUser->telegram_id, "Rahmat, <b>{$name}</b>!");
        $this->finishRegistration($botUser);
    }

    protected function finishRegistration(BotUser $botUser): void
    {
        $this->activatePendingQr($botUser);
        $this->activatePendingOrder($botUser);
        $this->sendMessage(
            $botUser->telegram_id,
            "Ro‘yxatdan o‘tdingiz ✅\n\nYangi chek qo‘shish uchun: rasm yuboring (yoki chek raqamini yozing).",
            ['reply_markup' => $this->mainKeyboard($botUser)]
        );
    }

    protected function handleReceiptPhoto(BotUser $botUser, array $message, ?string $fileId = null): void
    {
        if (! $botUser->consent_answered_at) {
            $this->askConsent($botUser);

            return;
        }
        if (! $botUser->phone_verified_at) {
            $this->sendMessage($botUser->telegram_id, "Avval telefonni tasdiqlang — /start");
            $this->askPhone($botUser);

            return;
        }
        if ($this->needsName($botUser)) {
            $this->askName($botUser);

            return;
        }

        if (! $fileId) {
            $photos = $message['photo'];
            $best = end($photos);
            $fileId = $best['file_id'] ?? null;
        }

        if (! $fileId) {
            $this->sendMessage($botUser->telegram_id, 'Rasm o‘qilmadi. Qayta yuboring.');

            return;
        }

        $loading = $this->sendMessage($botUser->telegram_id, '🔎');
        $loadingId = $loading['message_id'] ?? null;

        try {
            $path = $this->receipts->downloadTelegramFile($this, $fileId);
            if (! $path) {
                throw new RuntimeException('Fayl yuklanmadi');
            }

            $numbers = [];
            $source = '';

            // 1) Pastdagi barcode (haqiqiy foto uchun eng oson)
            $barcode = $this->receipts->decodeBarcode($path);
            if ($barcode) {
                $numbers = $this->receipts->extractOrderNumbers($barcode);
                if ($numbers === [] && preg_match('/^\d{9,14}$/', $barcode)) {
                    $numbers = [$barcode];
                }
                $source = 'barcode';
            }

            // 2) OCR — Billz «Продажа: #...»
            if ($numbers === []) {
                $ocrText = $this->receipts->ocrImage($path);
                $caption = (string) ($message['caption'] ?? '');
                $numbers = $this->receipts->extractOrderNumbers($ocrText."\n".$caption);
                $source = 'ocr';
            }

            if ($numbers === []) {
                $botUser->update(['state' => 'awaiting_order_number']);
                $this->sendMessage($botUser->telegram_id,
                    "Chekdan raqam topilmadi.\nIltimos, chek raqamini qo‘lda yozing (masalan <code>010001090266</code>).");

                return;
            }

            $order = null;
            foreach ($numbers as $num) {
                $order = $this->receipts->findOrderByNumber($num);
                if ($order) {
                    break;
                }
            }

            if (! $order) {
                $botUser->update([
                    'state' => 'awaiting_order_number',
                    'state_data' => ['candidates' => $numbers],
                ]);
                $list = implode(', ', array_slice($numbers, 0, 5));
                $this->sendMessage($botUser->telegram_id,
                    "Topilgan raqamlar ({$source}): <code>{$list}</code>\nLekin Billz bazada chek yo‘q (yoki hali sinxron bo‘lmagan).\nChek raqamini qayta yozing.");

                return;
            }

            Log::info('Receipt matched', ['source' => $source, 'order' => $order->order_number]);
            $this->finalizeOrderActivation($botUser, $order);
        } catch (\Throwable $e) {
            Log::error('Receipt photo: '.$e->getMessage());
            $botUser->update(['state' => 'awaiting_order_number']);
            $this->sendMessage($botUser->telegram_id,
                "Chekni avtomatik o‘qib bo‘lmadi.\nChek raqamini yozing (masalan <code>010001090266</code>).");
        } finally {
            if ($loadingId) {
                $this->deleteMessage($botUser->telegram_id, (int) $loadingId);
            }
        }
    }

    protected function handleOrderNumberText(BotUser $botUser, string $text): void
    {
        if (! $botUser->consent_answered_at) {
            $botUser->update([
                'state' => 'pending_order',
                'state_data' => ['order_number' => preg_replace('/\D+/', '', $text)],
            ]);
            $this->askConsent($botUser);

            return;
        }

        if (! $botUser->phone_verified_at) {
            $botUser->update([
                'state' => 'pending_order',
                'state_data' => ['order_number' => preg_replace('/\D+/', '', $text)],
            ]);
            $this->sendMessage($botUser->telegram_id, 'Avval telefonni tasdiqlang.');
            $this->askPhone($botUser);

            return;
        }

        if ($this->needsName($botUser)) {
            $botUser->update([
                'state_data' => array_merge($botUser->state_data ?? [], [
                    'order_number' => preg_replace('/\D+/', '', $text),
                ]),
            ]);
            $this->askName($botUser);

            return;
        }

        $number = preg_replace('/\D+/', '', $text);
        $order = $this->receipts->findOrderByNumber($number);
        if (! $order) {
            $this->sendMessage($botUser->telegram_id,
                "Chek <code>{$number}</code> topilmadi.\nRaqamni tekshiring yoki Billz sinxronini kuting.");

            return;
        }

        $this->finalizeOrderActivation($botUser, $order);
    }

    protected function finalizeOrderActivation(BotUser $botUser, Order $order): void
    {
        $result = $this->receipts->activateOrder($botUser, $order);
        $botUser->update(['state' => null, 'state_data' => null]);

        $shop = $order->shop?->name ?? '—';
        $sum = number_format((float) $order->total_price, 0, '.', ' ');
        $header = "✅ Chek topildi va tasdiqlandi\n\n"
            ."Chek: <b>{$order->order_number}</b>\n"
            ."Do‘kon: {$shop}\n"
            ."Summa: {$sum} so‘m\n\n";
        $footer = "\n\nYangi chek qo‘shish uchun: rasm yuboring.";

        if (($result['status'] ?? '') === 'taken_by_other') {
            $this->sendMessage(
                $botUser->telegram_id,
                "Chek <b>{$order->order_number}</b>\nDo‘kon: {$shop}\nSumma: {$sum} so‘m\n\n"
                ."❌ Bu chek <b>allaqachon boshqa mijoz</b> tomonidan faollashtirilgan.\nQayta urib bo‘lmaydi — har bir chek faqat 1 marta."
                .$footer,
                ['reply_markup' => $this->mainKeyboard($botUser)]
            );

            return;
        }

        if (($result['status'] ?? '') === 'already_yours') {
            $this->sendMessage(
                $botUser->telegram_id,
                $header."ℹ️ Bu chek sizda allaqachon faol.\nQayta qo‘shilmaydi.\n\n"
                .implode("\n", $result['items'])
                .$footer,
                ['reply_markup' => $this->mainKeyboard($botUser)]
            );

            return;
        }

        if ($result['activated'] === 0 && $result['items'] === []) {
            $this->sendMessage(
                $botUser->telegram_id,
                $header.'Bu chekda mahsulot qatorlari yo‘q.'.$footer,
                ['reply_markup' => $this->mainKeyboard($botUser)]
            );

            return;
        }

        $this->sendMessage(
            $botUser->telegram_id,
            $header."<b>Mahsulotlar:</b>\n".implode("\n", $result['items']).$footer,
            ['reply_markup' => $this->mainKeyboard($botUser)]
        );

        if (($result['status'] ?? '') === 'ok' && ($result['activated'] ?? 0) > 0) {
            $activation = Activation::query()
                ->where('order_id', $order->id)
                ->where('bot_user_id', $botUser->id)
                ->latest('id')
                ->first();
            $this->scheduleSellerRating($botUser, $order, $activation);
        }
    }

    protected function activatePendingOrder(BotUser $botUser): void
    {
        $number = $botUser->state_data['order_number'] ?? null;
        if (! $number) {
            return;
        }
        $order = $this->receipts->findOrderByNumber((string) $number);
        if ($order) {
            $this->finalizeOrderActivation($botUser, $order);
        }
    }

    protected function activatePendingQr(BotUser $botUser): void
    {
        $qrId = $botUser->state_data['qr_token_id'] ?? null;
        if (! $qrId) {
            return;
        }

        $token = QrToken::query()->with(['orderItem', 'order', 'activation'])->find($qrId);
        if (! $token) {
            return;
        }

        $botUser->update(['state' => null, 'state_data' => null]);

        if ($token->activation) {
            $same = (int) $token->activation->bot_user_id === (int) $botUser->id;
            $this->sendMessage(
                $botUser->telegram_id,
                $same
                    ? 'Bu QR sizda allaqachon faol.'
                    : '❌ Bu mahsulot boshqa mijozda faollashtirilgan. Qayta urib bo‘lmaydi.'
            );

            return;
        }

        if ($token->order_id && Activation::query()
            ->where('order_id', $token->order_id)
            ->where('bot_user_id', '!=', $botUser->id)
            ->exists()) {
            $this->sendMessage(
                $botUser->telegram_id,
                '❌ Bu chek boshqa mijoz tomonidan faollashtirilgan. Qayta urib bo‘lmaydi.'
            );

            return;
        }

        $months = $token->orderItem?->warranty_months ?? 12;
        try {
            $activation = Activation::query()->create([
                'qr_token_id' => $token->id,
                'order_id' => $token->order_id,
                'order_item_id' => $token->order_item_id,
                'customer_id' => $botUser->customer_id,
                'bot_user_id' => $botUser->id,
                'activated_at' => now(),
                'warranty_ends_at' => now()->addMonths($months),
            ]);
        } catch (\Throwable) {
            $this->sendMessage($botUser->telegram_id, '❌ Bu mahsulot allaqachon faollashtirilgan.');

            return;
        }

        $this->sendMessage(
            $botUser->telegram_id,
            'Kafolat faollashtirildi ('.$months." oy).\nMahsulot: ".($token->orderItem?->name ?? 'Xarid')
            ."\n\nYangi chek qo‘shish uchun: rasm yuboring.",
            ['reply_markup' => $this->mainKeyboard($botUser->fresh())]
        );

        if ($token->order) {
            $this->scheduleSellerRating($botUser, $token->order, $activation);
        }
    }

    /**
     * Chek roʻyxatdan oʻtgach sotuvchi baholash soʻrovi.
     * Kechikish: IntegrationSetting `seller_rating_delay_minutes` (default 0 = darhol).
     */
    public function scheduleSellerRating(BotUser $botUser, Order $order, ?Activation $activation = null): void
    {
        $exists = SurveyMessage::query()
            ->where('order_id', $order->id)
            ->where('bot_user_id', $botUser->id)
            ->where('stage', 'SELLER')
            ->exists();
        if ($exists) {
            return;
        }

        $delay = self::sellerRatingDelayMinutes();
        $survey = SurveyMessage::query()->create([
            'customer_id' => $botUser->customer_id,
            'bot_user_id' => $botUser->id,
            'order_id' => $order->id,
            'activation_id' => $activation?->id,
            'stage' => 'SELLER',
            'days_after' => 0,
            'status' => 'pending',
            'scheduled_at' => now()->addMinutes($delay),
        ]);

        // 0 daqiqa — chek tasdiqlanishi bilan birga yuboriladi (cron kutilmaydi)
        if ($delay === 0) {
            $this->sendSurvey($survey);
        }
    }

    /** Sotuvchi baholash kechikishi (daqiqa). 0 = darhol. */
    public static function sellerRatingDelayMinutes(): int
    {
        return max(0, min(10080, (int) IntegrationSetting::getValue('seller_rating_delay_minutes', 0)));
    }

    protected function showHome(BotUser $botUser): void
    {
        $botUser->update([
            'home_opens' => ((int) $botUser->home_opens) + 1,
            'last_home_at' => now(),
        ]);

        if (! $this->canUseMainMenu($botUser)) {
            $this->sendMessage(
                $botUser->telegram_id,
                'Avval chek yuboring va tasdiqlang.',
                ['reply_markup' => $this->mainKeyboard($botUser)]
            );

            return;
        }

        // Ro‘yxat Mini Appda — bot faqat yo‘l ko‘rsatadi
        $this->openMiniAppGuide(
            $botUser,
            "Cheklaringiz <b>Mini App</b>da.\nPastdagi tugmani bosing.",
            'home',
            '🏠 Mening uyimni ochish'
        );
    }

    protected function handleCallback(array $callback): void
    {
        $data = (string) ($callback['data'] ?? '');
        $fromId = (string) ($callback['from']['id'] ?? '');
        $chatId = (string) ($callback['message']['chat']['id'] ?? $fromId);
        $callbackId = $callback['id'] ?? null;

        $botUser = null;
        if ($fromId !== '') {
            $botUser = BotUser::query()->where('telegram_id', $fromId)->first();
        }
        if (! $botUser && $chatId !== '') {
            $botUser = BotUser::query()->where('telegram_id', $chatId)->first();
        }

        $ack = function (?string $text = null, bool $alert = false) use ($callbackId): void {
            if (! $callbackId) {
                return;
            }
            try {
                $payload = ['callback_query_id' => $callbackId];
                if ($text !== null && $text !== '') {
                    $payload['text'] = $text;
                    $payload['show_alert'] = $alert;
                }
                $this->api('answerCallbackQuery', $payload);
            } catch (\Throwable) {
            }
        };

        $messageId = isset($callback['message']['message_id']) ? (int) $callback['message']['message_id'] : null;

        // Kuryer guruh tugmalari — BotUser bo‘lishi shart emas
        if (str_starts_with($data, 'cclaim:')) {
            $parts = explode(':', $data);
            $claimId = (int) ($parts[1] ?? 0);
            $action = (string) ($parts[2] ?? '');
            $claim = Claim::query()->with('courier')->find($claimId);
            if (! $claim) {
                $ack('So‘rov topilmadi.', true);

                return;
            }
            $res = app(ClaimNotifyService::class)->handleCourierCallback(
                $claim,
                $action,
                $fromId,
                $messageId,
                $chatId !== '' ? $chatId : null
            );
            $ack($res['message'], ! $res['ok']);

            return;
        }

        if (str_starts_with($data, 'cconfirm:')) {
            $parts = explode(':', $data);
            $claimId = (int) ($parts[1] ?? 0);
            $answer = (string) ($parts[2] ?? '');
            $claim = Claim::query()->with('botUser')->find($claimId);
            if (! $claim) {
                $ack('So‘rov topilmadi.', true);

                return;
            }
            $res = app(ClaimNotifyService::class)->handleCustomerConfirmCallback($claim, $answer, $fromId);
            $ack($res['message'], ! $res['ok']);

            return;
        }

        if (str_starts_with($data, 'crate:')) {
            $parts = explode(':', $data);
            $claimId = (int) ($parts[1] ?? 0);
            $stars = (string) ($parts[2] ?? '');
            $claim = Claim::query()->with(['botUser', 'courier'])->find($claimId);
            if (! $claim) {
                $ack('So‘rov topilmadi.', true);

                return;
            }
            $res = app(ClaimNotifyService::class)->handleCourierRatingCallback($claim, $stars, $fromId);
            $ack($res['message'], ! $res['ok']);
            if ($res['ok'] && $messageId && $chatId !== '') {
                try {
                    $this->api('editMessageReplyMarkup', [
                        'chat_id' => $chatId,
                        'message_id' => $messageId,
                        'reply_markup' => json_encode(['inline_keyboard' => []]),
                    ]);
                } catch (\Throwable) {
                }
            }

            return;
        }

        if (! $botUser) {
            $ack('Foydalanuvchi topilmadi. /start bosing.', true);

            return;
        }

        if ($data === 'consent:yes' || $data === 'consent:no') {
            $ack();
            $this->saveConsent($botUser, $data === 'consent:yes');

            return;
        }

        if ($data === 'claim:new') {
            $ack();
            $this->startClaim($botUser);

            return;
        }

        if (str_starts_with($data, 'survey:')) {
            $parts = explode(':', $data);
            $surveyId = (int) ($parts[1] ?? 0);
            $answer = (string) ($parts[2] ?? '');
            $survey = SurveyMessage::query()->with('order.seller')->find($surveyId);

            if (! $survey) {
                $ack('So‘rov topilmadi.', true);

                return;
            }
            if ($survey->status === 'answered') {
                $ack('Allaqachon baholagansiz.', false);

                return;
            }
            if ((int) $survey->bot_user_id !== (int) $botUser->id) {
                $ack('Bu so‘rov sizniki emas.', true);

                return;
            }

            if ($survey->stage === 'SELLER' && preg_match('/^[1-5]$/', $answer)) {
                $survey->update([
                    'status' => 'answered',
                    'answer' => $answer,
                    'answered_at' => now(),
                    'delivered_at' => $survey->delivered_at ?? now(),
                ]);
                $ack("{$answer}/5 qabul qilindi ✓");
                $stars = str_repeat('⭐', (int) $answer);
                $this->sendMessage(
                    $chatId !== '' ? $chatId : $botUser->telegram_id,
                    "Rahmat! Baholangiz qabul qilindi: <b>{$answer}/5</b> {$stars}",
                    ['reply_markup' => $this->mainKeyboard($botUser)]
                );

                return;
            }

            if (in_array($answer, ['good', 'problem'], true)) {
                $survey->update([
                    'status' => 'answered',
                    'answer' => $answer,
                    'answered_at' => now(),
                    'delivered_at' => $survey->delivered_at ?? now(),
                ]);
                $ack('Javob qabul qilindi');
                $this->sendMessage($chatId !== '' ? $chatId : $botUser->telegram_id, $answer === 'good'
                    ? 'Rahmat! Javobingiz qabul qilindi.'
                    : 'Tushundik. Pastdagi <b>Shikoyat</b> tugmasidan so‘rov oching.');

                return;
            }

            $ack('Noto‘g‘ri javob.', true);

            return;
        }

        $ack();
    }

    public function sendSurvey(SurveyMessage $survey): bool
    {
        $botUser = $survey->botUser ?: BotUser::query()->find($survey->bot_user_id);
        if (! $botUser?->telegram_id) {
            $survey->update(['status' => 'failed', 'error_message' => 'Bot user yo\'q']);

            return false;
        }

        if ($survey->stage === 'SELLER') {
            return $this->sendSellerRatingSurvey($survey, $botUser);
        }

        $stageLabel = match ($survey->stage) {
            'T1' => '7 kun',
            'T2' => '30 kun',
            'T3' => '90 kun',
            'T4' => 'kafolat tugashi',
            default => $survey->stage,
        };

        try {
            $result = $this->sendMessage($botUser->telegram_id, "So'rovnoma ({$stageLabel}): mahsulotdan qoniqdingizmi?", [
                'reply_markup' => [
                    'inline_keyboard' => [[
                        ['text' => 'Yaxshi', 'callback_data' => "survey:{$survey->id}:good"],
                        ['text' => 'Muammo bor', 'callback_data' => "survey:{$survey->id}:problem"],
                    ]],
                ],
            ]);
            $survey->update([
                'status' => 'sent',
                'sent_at' => now(),
                'delivered_at' => now(),
                'telegram_message_id' => (string) ($result['message_id'] ?? ''),
            ]);

            return true;
        } catch (\Throwable $e) {
            $survey->update(['status' => 'failed', 'error_message' => $e->getMessage()]);

            return false;
        }
    }

    protected function sendSellerRatingSurvey(SurveyMessage $survey, BotUser $botUser): bool
    {
        $survey->loadMissing(['order.seller', 'order.shop']);
        $order = $survey->order;
        $seller = $order?->seller?->name;
        $shop = $order?->shop?->name ?? '—';
        $chek = $order?->order_number ?? '—';

        $text = "⭐ <b>Sotuvchi xizmatini baholang</b>\n\n"
            ."Chek: <b>{$chek}</b>\n"
            ."Do‘kon: {$shop}\n"
            .($seller ? "Sotuvchi: <b>{$seller}</b>\n" : '')
            ."\nXizmat sifatini 1–5 ball bilan belgilang.";

        $row = [];
        for ($i = 1; $i <= 5; $i++) {
            $row[] = [
                'text' => (string) $i.'⭐',
                'callback_data' => "survey:{$survey->id}:{$i}",
            ];
        }

        try {
            $result = $this->sendMessage($botUser->telegram_id, $text, [
                'reply_markup' => [
                    'inline_keyboard' => [$row],
                ],
            ]);
            $survey->update([
                'status' => 'sent',
                'sent_at' => now(),
                'delivered_at' => now(),
                'telegram_message_id' => (string) ($result['message_id'] ?? ''),
            ]);

            return true;
        } catch (\Throwable $e) {
            $survey->update(['status' => 'failed', 'error_message' => $e->getMessage()]);

            return false;
        }
    }
}
