<?php

namespace App\Services\Telegram;

use App\Models\Claim;
use App\Models\User;
use App\Services\ClaimWorkflowService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ClaimNotifyService
{
    public function __construct(protected TelegramBotService $bot) {}

    public function afterTransition(Claim $claim, string $from, string $to): void
    {
        $claim->loadMissing([
            'customer', 'botUser', 'courier', 'orderItem', 'product',
            'items.orderItem', 'shop', 'pickupShop',
        ]);

        match ($to) {
            'review' => $this->toCustomer($claim, "Shikoyatingiz ko‘rib chiqilmoqda 🔎\nSo‘rov: <b>{$claim->number}</b>"),
            'rejected' => $this->rejected($claim),
            'approved' => $this->toCustomer(
                $claim,
                "Brak tasdiqlandi ✅\nSo‘rov: <b>{$claim->number}</b>\nKuryer tez orada tayinlanadi."
            ),
            'assigned' => $this->assigned($claim),
            'en_route' => $this->enRoute($claim),
            'delivered' => $this->delivered($claim),
            'done' => $this->done($claim, $from),
            'dispute' => $this->dispute($claim),
            'unconfirmed' => $this->groupsOnly(
                $claim,
                "⚠️ <b>{$claim->number}</b> · Tasdiqsiz yopildi\nMijoz 72 soat javob bermadi."
            ),
            default => null,
        };
    }

    protected function rejected(Claim $claim): void
    {
        $reason = $claim->reject_reason ?: '—';
        $this->toCustomer(
            $claim,
            "So‘rov rad etildi\nSo‘rov: <b>{$claim->number}</b>\nSabab: {$reason}"
        );
        $this->groupsOnly(
            $claim,
            "So‘rov <b>{$claim->number}</b> rad etildi\nSabab: {$reason}"
        );
    }

    protected function assigned(Claim $claim): void
    {
        $c = $claim->courier;
        $name = $c?->name ?? 'Kuryer';
        $phone = $c?->phone ?? '—';
        $deadline = $claim->courier_deadline_at?->format('d.m.Y H:i') ?? '—';
        $this->toCustomer(
            $claim,
            "Kuryer <b>{$name}</b> ({$phone}) 3 kun ichida olib boradi.\nEng kech: {$deadline}\nSo‘rov: <b>{$claim->number}</b>"
        );

        $body = $this->taskBody($claim);
        $open = $this->bot->courierAppOpenButton((int) $claim->id);
        // Faqat Mini App — Yo‘ldaman / Yetkazdim guruhda yo‘q
        $btns = [[$open]];
        $this->sendCourierGroup(
            $claim,
            "📦 <b>Yangi topshiriq</b> · {$claim->number}\n\n".$body
            ."\n\nℹ️ Amallar faqat Mini Appda («Topshiriqni ochish»).",
            $btns
        );
        $this->sendClaimLocationToCourier($claim);
        $this->bot->notifyCourierPersonalMiniApp($claim);
    }

    protected function enRoute(Claim $claim): void
    {
        $c = $claim->courier;
        $this->toCustomer(
            $claim,
            'Kuryer yo‘lda'
            .($c ? ": <b>{$c->name}</b>, {$c->phone}" : '')
            ."\nSo‘rov: <b>{$claim->number}</b>"
        );
        $who = $c?->name ?? 'Kuryer';
        $open = $this->bot->courierAppOpenButton((int) $claim->id);
        $this->sendCourierGroup(
            $claim,
            "🚚 <b>{$claim->number}</b> · {$who} yo‘lda\nFoto bilan yakunlash — Mini Appda.",
            [[$open]]
        );
    }

    protected function delivered(Claim $claim): void
    {
        $cname = $claim->courier?->name ?? 'Kuryer';
        $this->askCustomerConfirm($claim);
        $this->sendCourierGroup(
            $claim,
            "✅ <b>{$claim->number}</b> · {$cname} yetkazdi\nMijoz tasdig‘i kutilmoqda"
            .($claim->delivery_photo ? "\nFoto: ".asset('storage/'.$claim->delivery_photo) : ''),
            []
        );
        $this->sendMgmt(
            $claim,
            "✅ <b>{$claim->number}</b> · Yetkazildi, mijoz tasdig‘i kutilmoqda"
        );
    }

    protected function done(Claim $claim, string $from): void
    {
        $this->toCustomer($claim, "Rahmat! So‘rov <b>{$claim->number}</b> yopildi.");
        if ($from === 'dispute') {
            $this->groupsOnly(
                $claim,
                "🟢 <b>{$claim->number}</b> · Nizo yopildi"
            );
            $this->askCourierRating($claim);

            return;
        }
        $hours = ($claim->assigned_at && $claim->delivered_at)
            ? round($claim->assigned_at->diffInMinutes($claim->delivered_at) / 60, 1)
            : null;
        $this->groupsOnly(
            $claim,
            "🟢 <b>{$claim->number}</b> · Mijoz tasdiqladi"
            .($claim->courier ? "\nKuryer: ".$claim->courier->name : '')
            .($hours !== null ? "\n{$hours} soatda" : '')
        );
        $this->askCourierRating($claim);
    }

    /** Mijozga kuryerni 1–5 yulduz bilan baholash. */
    public function askCourierRating(Claim $claim): void
    {
        $claim->loadMissing(['courier', 'botUser']);
        if (! $claim->courier_id || $claim->courier_rating !== null) {
            return;
        }
        $cname = $claim->courier?->name ?? 'Kuryer';
        $row = [];
        for ($i = 1; $i <= 5; $i++) {
            $row[] = [
                'text' => (string) $i.'⭐',
                'callback_data' => "crate:{$claim->id}:{$i}",
            ];
        }
        $this->toCustomer(
            $claim,
            "⭐ <b>Kuryerni baholang</b>\n"
            ."So‘rov: <b>{$claim->number}</b>\n"
            ."Kuryer: <b>{$cname}</b>\n\n"
            .'Xizmat sifatini 1–5 yulduz bilan belgilang.',
            [
                'reply_markup' => [
                    'inline_keyboard' => [$row],
                ],
            ]
        );
    }

    /**
     * @return array{ok:bool,message:string}
     */
    public function handleCourierRatingCallback(Claim $claim, string $stars, string $fromTelegramId): array
    {
        $claim->loadMissing('botUser');
        $owner = trim((string) ($claim->botUser?->telegram_id ?? ''));
        $from = trim((string) $fromTelegramId);
        $same = $owner !== '' && $from !== '' && (
            $owner === $from
            || (ctype_digit($owner) && ctype_digit($from) && (string) (int) $owner === (string) (int) $from)
        );
        if (! $same) {
            return ['ok' => false, 'message' => 'Bu so‘rov sizniki emas.'];
        }
        if (! preg_match('/^[1-5]$/', $stars)) {
            return ['ok' => false, 'message' => 'Noto‘g‘ri baho.'];
        }
        try {
            $claim = app(ClaimWorkflowService::class)->rateCourier($claim, (int) $stars);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $msg = collect($e->errors())->flatten()->first() ?: $e->getMessage();

            return ['ok' => false, 'message' => Str::limit((string) $msg, 120)];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => Str::limit($e->getMessage(), 120)];
        }
        $n = (int) $stars;
        $bar = str_repeat('⭐', $n);
        $this->toCustomer(
            $claim,
            "Rahmat! Baholangiz qabul qilindi: <b>{$n}/5</b> {$bar}"
        );
        if ($claim->courier) {
            $this->sendMgmt(
                $claim,
                "⭐ <b>{$claim->number}</b> · Kuryer bahosi {$n}/5\n"
                .($claim->courier->name ?? 'Kuryer')
            );
        }

        return ['ok' => true, 'message' => "{$n}/5 ✓"];
    }

    public function dispute(Claim $claim): void
    {
        $this->toCustomer($claim, "Javobingiz qabul qilindi. Admin siz bilan bog‘lanadi.\nSo‘rov: <b>{$claim->number}</b>");
        $reason = $claim->confirm_reason ?: '—';
        $c = $claim->courier?->name ?? '—';
        $text = "🔴 <b>NIZO</b> {$claim->number}\nKuryer: {$c}\nMijoz: almashtirilmadi\nSabab: {$reason}\nAdmin 24 soat ichida hal qilsin\n".$this->panelLink();
        $this->sendCourierGroup($claim, $text, []);
        $this->sendMgmt($claim, $text);
    }

    public function courierProblem(Claim $claim, string $note): void
    {
        $c = $claim->courier?->name ?? 'Kuryer';
        $text = "⚠️ <b>{$claim->number}</b> · Muammo\nKuryer: {$c}\n{$note}\n".$this->panelLink();
        $this->sendCourierGroup($claim, $text, []);
        $this->sendMgmt($claim, $text);
    }

    public function courierDeadlineWarning(Claim $claim): void
    {
        $when = $claim->courier_deadline_at?->format('d.m.Y H:i') ?? '—';
        $c = $claim->courier?->name ?? 'Kuryer';
        $this->groupsOnly($claim, "⏰ <b>{$claim->number}</b> · Muddat yaqin\n{$c} · {$when}");
    }

    public function courierDeadlineOverdue(Claim $claim): void
    {
        $text = "🔴 MUDDAT O‘TDI <b>{$claim->number}</b>\nKuryer: ".($claim->courier?->name ?? '—')."\n".$this->panelLink();
        $this->groupsOnly($claim, $text);
    }

    public function adminSlaAlert(Claim $claim): void
    {
        $text = "🔴 <b>{$claim->number}</b> 2 soatdan beri ko‘rib chiqilmagan\n".$this->panelLink();
        $this->sendMgmt($claim, $text);
        // Kuryer guruhi — faqat topshiriq berilgach; SLA ogohlantirish faqat rahbariyatga
    }

    public function askCustomerConfirm(Claim $claim): void
    {
        $cname = $claim->courier?->name ?? 'Kuryer';
        $this->toCustomer(
            $claim,
            "⭐ <b>Kuryer {$cname} brak mahsulotni almashtirib berdimi?</b>\nSo‘rov: <b>{$claim->number}</b>",
            [
                'reply_markup' => [
                    'inline_keyboard' => [[
                        ['text' => 'Ha, almashtirildi', 'callback_data' => "cconfirm:{$claim->id}:yes"],
                        ['text' => 'Yo‘q', 'callback_data' => "cconfirm:{$claim->id}:no"],
                    ]],
                ],
            ]
        );
    }

    public function confirmReminder(Claim $claim): void
    {
        $this->toCustomer(
            $claim,
            "Eslatma: kuryer almashtirib berdimi?\nSo‘rov: <b>{$claim->number}</b>",
            [
                'reply_markup' => [
                    'inline_keyboard' => [[
                        ['text' => 'Ha', 'callback_data' => "cconfirm:{$claim->id}:yes"],
                        ['text' => 'Yo‘q', 'callback_data' => "cconfirm:{$claim->id}:no"],
                    ]],
                ],
            ]
        );
    }

    protected function taskBody(Claim $claim): string
    {
        $claim->loadMissing([
            'courier', 'customer', 'botUser', 'pickupShop', 'shop',
            'items.orderItem', 'items.product', 'orderItem', 'product',
        ]);

        $c = $claim->courier;
        $deadline = $claim->courier_deadline_at?->format('d.m.Y H:i') ?? '—';
        $cust = $claim->customer?->name
            ?: trim(($claim->botUser?->first_name ?? '').' '.($claim->botUser?->last_name ?? ''))
            ?: 'Mijoz';
        $phone = $claim->customer?->phone ?: ($claim->botUser?->phone ?? '—');
        $addr = trim((string) ($claim->address ?: '')) ?: 'kiritilmagan';
        $store = $claim->pickupShop?->name ?? $claim->shop?->name ?? '—';
        $courierName = $c?->name ?: 'Belgilanmagan';
        $courierPhone = $c?->phone ? ' · '.$c->phone : '';
        $reason = trim((string) ($claim->reason ?: '')) ?: '—';

        $lines = $this->productLines($claim);
        $prodBlock = $lines === []
            ? "• Mahsulot — 1 dona"
            : implode("\n\n", $lines);

        return "👤 <b>Mijoz</b>\n{$cust}\n{$phone}\n\n"
            ."📍 <b>Manzil</b>\n{$addr}\n\n"
            ."🏪 <b>Olish joyi</b>\n{$store}\n\n"
            ."📦 <b>Mahsulotlar</b>\n{$prodBlock}\n\n"
            ."❗️ <b>Sabab</b>\n{$reason}\n\n"
            ."🚴 <b>Kuryer</b>\n{$courierName}{$courierPhone}\n\n"
            ."⏰ <b>Muddat</b>\n{$deadline}\n\n"
            .$this->panelLink();
    }

    /** @return list<string> */
    protected function productLines(Claim $claim): array
    {
        $claim->loadMissing(['items.orderItem', 'items.product', 'orderItem', 'product']);
        if ($claim->items->isNotEmpty()) {
            return $claim->items->values()->map(function ($item, $i) {
                $name = e($item->product?->name ?? $item->orderItem?->name ?? 'Mahsulot');
                $qty = method_exists($item, 'brakQty') ? $item->brakQty() : max(1, (int) ($item->qty ?: 1));
                $reason = e(trim((string) ($item->reason ?: '')));

                return '• '.($i + 1).'. <b>'.$name.' ×'.$qty.'</b>'
                    .($reason !== '' ? "\n  Sabab: {$reason}" : '');
            })->all();
        }

        $one = $claim->product?->name ?? $claim->orderItem?->name;
        if (! $one) {
            return [];
        }
        $qty = (int) ($claim->replacement_qty ?: $claim->orderItem?->quantity ?: 1);

        return ['• 1. <b>'.e($one).' ×'.$qty.'</b>'];
    }

    protected function panelLink(): string
    {
        return '<a href="'.url('/panel#brak').'">Panelni ochish</a>';
    }

    protected function toCustomer(Claim $claim, string $text, array $opts = []): void
    {
        $tid = $claim->botUser?->telegram_id;
        if (! $tid) {
            return;
        }
        try {
            $this->bot->sendMessage($tid, $text, $opts);
        } catch (\Throwable $e) {
            Log::warning('Claim customer notify: '.$e->getMessage());
        }
    }

    protected function groupsOnly(Claim $claim, string $text): void
    {
        $this->sendCourierGroup($claim, $text, []);
        $this->sendMgmt($claim, $text);
    }

    protected function sendCourierGroup(Claim $claim, string $text, array $keyboard): void
    {
        // Kuryer guruhi faqat kuryer tayinlangandan keyin
        if (! $claim->courier_id) {
            return;
        }
        $chat = $this->bot->chatId('courier');
        if (! $chat) {
            return;
        }
        try {
            $opts = $keyboard !== [] ? ['reply_markup' => ['inline_keyboard' => $keyboard]] : [];
            $res = $this->bot->sendMessage($chat, $text, $opts);
            if (! empty($res['message_id'])) {
                $claim->update(['tg_courier_message_id' => (string) $res['message_id']]);
            }
        } catch (\Throwable $e) {
            Log::warning('Claim courier group: '.$e->getMessage());
        }
    }

    /** Mijoz geo-lokatsiyasi — kuryer guruhiga + kuryer shaxsiy chatiga. */
    protected function sendClaimLocationToCourier(Claim $claim): void
    {
        if ($claim->address_lat === null || $claim->address_lng === null) {
            return;
        }
        $lat = (float) $claim->address_lat;
        $lng = (float) $claim->address_lng;
        if (abs($lat) > 90 || abs($lng) > 180) {
            return;
        }

        $chat = $this->bot->chatId('courier');
        if ($chat) {
            try {
                $this->bot->sendLocation($chat, $lat, $lng);
            } catch (\Throwable $e) {
                Log::warning('Claim location → courier group: '.$e->getMessage());
            }
        }

        $tg = trim((string) ($claim->courier?->telegram_id ?? ''));
        if ($tg !== '') {
            try {
                $this->bot->sendLocation($tg, $lat, $lng);
            } catch (\Throwable $e) {
                Log::warning('Claim location → courier personal: '.$e->getMessage());
            }
        }
    }

    protected function sendMgmt(Claim $claim, string $text): void
    {
        $chat = $this->bot->chatId('management');
        if (! $chat) {
            return;
        }
        try {
            $res = $this->bot->sendMessage($chat, $text);
            if (! empty($res['message_id'])) {
                $claim->update(['tg_mgmt_message_id' => (string) $res['message_id']]);
            }
        } catch (\Throwable $e) {
            Log::warning('Claim mgmt group: '.$e->getMessage());
        }
    }

    /**
     * @return array{ok:bool,message:string}
     */
    public function handleCourierCallback(Claim $claim, string $action, string $fromTelegramId, ?int $messageId = null, ?string $chatId = null): array
    {
        $tg = trim((string) $fromTelegramId);
        $courier = User::query()
            ->where(function ($q) use ($tg) {
                $q->where('telegram_id', $tg);
                if (ctype_digit($tg)) {
                    $q->orWhere('telegram_id', (string) (int) $tg);
                }
            })
            ->first();

        if (! $courier || ! $courier->isCourier()) {
            return ['ok' => false, 'message' => 'Siz kuryer sifatida ro‘yxatdan o‘tmagansiz (Telegram ID).'];
        }
        if ((int) $claim->courier_id !== (int) $courier->id) {
            $who = $claim->courier?->name ?? 'kuryer';

            return ['ok' => false, 'message' => "Bu topshiriq {$who}ga tayinlangan."];
        }

        $workflow = app(ClaimWorkflowService::class);
        try {
            if ($action === 'en_route') {
                if ($claim->status === 'en_route') {
                    return ['ok' => true, 'message' => 'Allaqachon yo‘ldasiz.'];
                }
                if ($claim->status !== 'assigned') {
                    return ['ok' => false, 'message' => 'Holat: '.$claim->status.' — Yo‘ldaman mumkin emas.'];
                }
                $workflow->enRoute($claim, $courier, 'telegram_group');
            } elseif ($action === 'deliver') {
                return ['ok' => false, 'message' => 'Yetkazdim uchun Kuryer panelida foto yuklang.'];
            } else {
                return ['ok' => false, 'message' => 'Nomaʼlum amal.'];
            }
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => Str::limit($e->getMessage(), 160)];
        }

        if ($chatId && $messageId) {
            try {
                $this->bot->api('editMessageReplyMarkup', [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'reply_markup' => json_encode(['inline_keyboard' => []]),
                ]);
            } catch (\Throwable) {
            }
        }

        return ['ok' => true, 'message' => 'Qabul qilindi.'];
    }

    public function handleCustomerConfirmCallback(Claim $claim, string $answer, string $fromTelegramId): array
    {
        if ((string) $claim->botUser?->telegram_id !== (string) $fromTelegramId) {
            return ['ok' => false, 'message' => 'Bu so‘rov sizniki emas.'];
        }
        try {
            if ($answer === 'yes') {
                app(ClaimWorkflowService::class)->customerConfirm($claim, true);
            } elseif ($answer === 'no') {
                // sabab keyin so‘raladi — default
                app(ClaimWorkflowService::class)->customerConfirm($claim, false, 'Boshqa');
            } else {
                return ['ok' => false, 'message' => 'Nomaʼlum javob.'];
            }
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => Str::limit($e->getMessage(), 120)];
        }

        return ['ok' => true, 'message' => 'Rahmat!'];
    }
}
