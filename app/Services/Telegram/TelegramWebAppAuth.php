<?php

namespace App\Services\Telegram;

use App\Models\BotUser;
use App\Models\IntegrationSetting;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class TelegramWebAppAuth
{
    /**
     * Avval Telegram initData, bo‘lmasa botdan kelgan claim_token (`t`).
     */
    public function botUserFromRequest(?string $initData, ?string $token = null, int $maxAgeSeconds = 86400): BotUser
    {
        if ($initData && trim($initData) !== '') {
            try {
                return $this->botUserFromInitData($initData, $maxAgeSeconds);
            } catch (ValidationException $e) {
                // initData buzilgan bo‘lsa token bilan davom
                if (! $token) {
                    throw $e;
                }
            }
        }

        return $this->botUserFromToken($token);
    }

    public function botUserFromToken(?string $token): BotUser
    {
        $token = trim((string) $token);
        if ($token === '' || strlen($token) < 20) {
            throw ValidationException::withMessages([
                'initData' => 'Kirish uchun botdagi «Mening uyim» yoki «Shikoyat» tugmasidan oching.',
            ]);
        }

        $botUser = BotUser::query()->where('claim_token', $token)->first();
        if (! $botUser) {
            throw ValidationException::withMessages([
                'initData' => 'Sessiya topilmadi. Botdan qayta oching.',
            ]);
        }

        return $botUser;
    }

    /**
     * Telegram WebApp initData ni tekshiradi va BotUser qaytaradi.
     *
     * @see https://core.telegram.org/bots/webapps#validating-data-received-via-the-mini-app
     */
    public function botUserFromInitData(?string $initData, int $maxAgeSeconds = 86400): BotUser
    {
        if (! $initData || trim($initData) === '') {
            throw ValidationException::withMessages(['initData' => 'Telegram initData yo‘q. Botdan oching.']);
        }

        $token = IntegrationSetting::getEncrypted('telegram_bot_token')
            ?: config('telegram.bot_token');
        if (! $token) {
            throw ValidationException::withMessages(['initData' => 'Bot token sozlanmagan.']);
        }

        parse_str($initData, $data);
        $hash = (string) ($data['hash'] ?? '');
        if ($hash === '') {
            throw ValidationException::withMessages(['initData' => 'initData hash yo‘q.']);
        }
        unset($data['hash']);

        ksort($data);
        $check = [];
        foreach ($data as $k => $v) {
            $check[] = $k.'='.$v;
        }
        $dataCheckString = implode("\n", $check);

        $secretKey = hash_hmac('sha256', $token, 'WebAppData', true);
        $calc = hash_hmac('sha256', $dataCheckString, $secretKey);

        if (! hash_equals($calc, $hash)) {
            throw ValidationException::withMessages(['initData' => 'initData imzo noto‘g‘ri.']);
        }

        $authDate = (int) ($data['auth_date'] ?? 0);
        if ($authDate <= 0 || (time() - $authDate) > $maxAgeSeconds) {
            throw ValidationException::withMessages(['initData' => 'initData muddati o‘tgan — Mini Appni qayta oching.']);
        }

        $userJson = $data['user'] ?? '';
        $user = is_string($userJson) ? json_decode($userJson, true) : null;
        $tgId = isset($user['id']) ? (string) $user['id'] : '';
        if ($tgId === '') {
            throw ValidationException::withMessages(['initData' => 'Foydalanuvchi aniqlanmadi.']);
        }

        $botUser = BotUser::query()->where('telegram_id', $tgId)->first();
        if (! $botUser) {
            throw ValidationException::withMessages(['initData' => 'Avval botda /start bosing.']);
        }

        return $botUser;
    }

    /**
     * initData imzosini tekshirib Telegram user id qaytaradi.
     *
     * @return array{tg_id: string, start_param: string, user: array<string, mixed>}
     */
    public function parseInitData(?string $initData, int $maxAgeSeconds = 86400): array
    {
        if (! $initData || trim($initData) === '') {
            throw ValidationException::withMessages(['initData' => 'Telegram initData yo‘q. Botdan oching.']);
        }

        $token = IntegrationSetting::getEncrypted('telegram_bot_token')
            ?: config('telegram.bot_token');
        if (! $token) {
            throw ValidationException::withMessages(['initData' => 'Bot token sozlanmagan.']);
        }

        parse_str($initData, $data);
        $hash = (string) ($data['hash'] ?? '');
        if ($hash === '') {
            throw ValidationException::withMessages(['initData' => 'initData hash yo‘q.']);
        }
        unset($data['hash']);

        ksort($data);
        $check = [];
        foreach ($data as $k => $v) {
            $check[] = $k.'='.$v;
        }
        $dataCheckString = implode("\n", $check);

        $secretKey = hash_hmac('sha256', $token, 'WebAppData', true);
        $calc = hash_hmac('sha256', $dataCheckString, $secretKey);

        if (! hash_equals($calc, $hash)) {
            throw ValidationException::withMessages(['initData' => 'initData imzo noto‘g‘ri.']);
        }

        $authDate = (int) ($data['auth_date'] ?? 0);
        if ($authDate <= 0 || (time() - $authDate) > $maxAgeSeconds) {
            throw ValidationException::withMessages(['initData' => 'initData muddati o‘tgan — Mini Appni qayta oching.']);
        }

        $userJson = $data['user'] ?? '';
        $user = is_string($userJson) ? json_decode($userJson, true) : null;
        $tgId = isset($user['id']) ? (string) $user['id'] : '';
        if ($tgId === '') {
            throw ValidationException::withMessages(['initData' => 'Foydalanuvchi aniqlanmadi.']);
        }

        return [
            'tg_id' => $tgId,
            'start_param' => (string) ($data['start_param'] ?? ''),
            'user' => is_array($user) ? $user : [],
        ];
    }

    /** Kuryer xodim (User) — Telegram ID bo‘yicha. */
    public function courierFromInitData(?string $initData, int $maxAgeSeconds = 86400): User
    {
        $parsed = $this->parseInitData($initData, $maxAgeSeconds);
        $tg = $parsed['tg_id'];

        $user = User::query()
            ->where(function ($q) use ($tg) {
                $q->where('telegram_id', $tg);
                if (ctype_digit($tg)) {
                    $q->orWhere('telegram_id', (string) (int) $tg);
                }
            })
            ->first();

        if (! $user || ! $user->isCourier()) {
            throw ValidationException::withMessages([
                'initData' => 'Siz kuryer sifatida ro‘yxatdan o‘tmagansiz (Telegram ID).',
            ]);
        }

        return $user;
    }
}
