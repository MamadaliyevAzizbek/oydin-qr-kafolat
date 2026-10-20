<?php

namespace App\Services;

use App\Models\Activation;
use App\Models\Claim;
use App\Models\IntegrationSetting;
use App\Models\NotificationRule;
use App\Models\SurveyMessage;
use App\Services\Telegram\TelegramBotService;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function __construct(
        protected TelegramBotService $bot,
        protected ReportService $reports,
    ) {}

    /** Kecha KPI — Telegram kunlik hisobot va panel preview uchun. */
    public function dailyReportPreview(): array
    {
        $data = $this->reports->dashboard(['period' => 1, 'shop_id' => null, 'seller_id' => null, 'category' => null]);
        $k = $data['kpis'];

        return [
            'date' => now()->subDay()->format('d.m.Y'),
            'cheks' => (int) ($k['cheks'] ?? 0),
            'akt' => (float) ($k['akt'] ?? 0),
            'open_claims' => (int) ($k['open_claims'] ?? 0),
            'survey_resp' => (float) ($k['survey_resp'] ?? 0),
            'schedule' => 'har kuni 09:00',
        ];
    }

    public function sendDailyReport(): void
    {
        if (! $this->enabled('daily_report')) {
            return;
        }

        $p = $this->dailyReportPreview();
        $panel = url('/panel');
        $text = "<b>QR Kafolat — {$p['date']}</b>\n"
            ."Sotuvlar: <b>{$p['cheks']}</b> chek\n"
            ."QR aktivatsiya: <b>{$p['akt']}%</b>\n"
            ."Ochiq so'rovlar: <b>{$p['open_claims']}</b>\n"
            ."Bot so'rovnoma javob: <b>{$p['survey_resp']}%</b>\n"
            ."<a href=\"{$panel}\">Panelni ochish</a>";

        $this->sendTo('management', $text);
    }

    /** Oxirgi 7 kun — Rahbariyatga (dushanba 09:00). */
    public function weeklyReportPreview(): array
    {
        $data = $this->reports->dashboard(['period' => 7, 'shop_id' => null, 'seller_id' => null, 'category' => null]);
        $k = $data['kpis'];
        $funnel = $this->reports->salesFunnel(['period' => 7, 'shop_id' => null, 'seller_id' => null, 'category' => null]);
        $stores = collect($funnel['stores'] ?? [])
            ->filter(fn ($s) => ((int) ($s['cheks'] ?? 0)) > 0)
            ->sortBy('akt')
            ->take(3)
            ->values()
            ->map(fn ($s) => [
                'name' => $s['name'] ?? '—',
                'akt' => (float) ($s['akt'] ?? 0),
                'cheks' => (int) ($s['cheks'] ?? 0),
            ])
            ->all();

        return [
            'from' => now()->subDays(7)->format('d.m.Y'),
            'to' => now()->subDay()->format('d.m.Y'),
            'cheks' => (int) ($k['cheks'] ?? 0),
            'akt' => (float) ($k['akt'] ?? 0),
            'open_claims' => (int) ($k['open_claims'] ?? 0),
            'survey_resp' => (float) ($k['survey_resp'] ?? 0),
            'sat' => (float) ($k['sat'] ?? 0),
            'brak' => (float) ($k['brak'] ?? 0),
            'lowest_stores' => $stores,
            'schedule' => 'dushanba 09:00',
        ];
    }

    public function sendWeeklyReport(): void
    {
        if (! $this->enabled('weekly_report')) {
            return;
        }

        $p = $this->weeklyReportPreview();
        $panel = url('/panel');
        $lines = [
            "<b>QR Kafolat — haftalik</b> ({$p['from']} – {$p['to']})",
            "Sotuvlar: <b>{$p['cheks']}</b> chek",
            "QR aktivatsiya: <b>{$p['akt']}%</b>",
            "Qoniqish: <b>{$p['sat']}%</b> · Brak: <b>{$p['brak']}%</b>",
            "Ochiq so'rovlar: <b>{$p['open_claims']}</b>",
            "Bot so'rovnoma javob: <b>{$p['survey_resp']}%</b>",
        ];
        if ($p['lowest_stores'] !== []) {
            $lines[] = "\n<b>Eng past aktivatsiya (do'kon):</b>";
            foreach ($p['lowest_stores'] as $s) {
                $lines[] = "• {$s['name']}: {$s['akt']}% ({$s['cheks']} chek)";
            }
        }
        $lines[] = "\n<a href=\"{$panel}\">Panelni ochish</a>";

        $this->sendTo('management', implode("\n", $lines));
    }

    public function checkSla(): void
    {
        if (! $this->enabled('claim_sla')) {
            return;
        }

        $panel = url('/panel#brak');

        // 5 kun ochiq — Servisga: vaqt kam qolyapti
        $warning = Claim::query()
            ->whereNotIn('status', ['rejected', 'replaced'])
            ->where('created_at', '<=', now()->subDays(5))
            ->where('created_at', '>', now()->subDays(7))
            ->whereDoesntHave('logs', fn ($q) => $q->where('note', 'SLA warning'))
            ->limit(20)
            ->get();

        foreach ($warning as $claim) {
            $days = $claim->daysOpen();
            $left = max(0, 7 - $days);
            $this->sendTo('service', "⚠ Vaqt kam: so'rov <b>{$claim->number}</b> — {$days} kun ochiq, SLA gacha ~{$left} kun.\n{$panel}");
            $claim->logs()->create([
                'from_status' => $claim->status,
                'to_status' => $claim->status,
                'note' => 'SLA warning',
            ]);
        }

        // 7+ kun — Servis + Rahbariyat (xodimlar ishlamayotgani ko‘rinsin)
        $overdue = Claim::query()
            ->whereNotIn('status', ['rejected', 'replaced'])
            ->where('created_at', '<=', now()->subDays(7))
            ->whereDoesntHave('logs', fn ($q) => $q->where('note', 'SLA notified'))
            ->limit(20)
            ->get();

        foreach ($overdue as $claim) {
            $text = "🔴 SLA buzildi: so'rov <b>{$claim->number}</b> {$claim->daysOpen()} kundan oshdi, hali yopilmagan.\n{$panel}";
            $this->sendTo('service', $text);
            $this->sendTo('management', $text."\nServis muddatida yopmagan — tekshiring.");
            $claim->logs()->create([
                'from_status' => $claim->status,
                'to_status' => $claim->status,
                'note' => 'SLA notified',
            ]);
        }
    }

    public function checkBillzSync(): void
    {
        if (! $this->enabled('billz_sync_down')) {
            return;
        }
        $last = IntegrationSetting::getValue('billz_last_sales_sync');
        if (! $last) {
            return;
        }
        if (now()->diffInMinutes($last) > 30) {
            $status = IntegrationSetting::getValue('billz_sync_status');
            if ($status !== 'ok' || now()->diffInMinutes($last) > 30) {
                $this->sendTo('management', 'Billz sinxron kechikmoqda / to\'xtagan. Oxirgi: '.$last);
            }
        }
    }

    public function checkBrakThreshold(): void
    {
        if (! $this->enabled('product_brak_3pct')) {
            return;
        }
        $threshold = app(PanelBootService::class)->brakThresholdPct();
        $quality = $this->reports->quality([
            'period' => 30,
            'shop_id' => null,
            'seller_id' => null,
            'category' => null,
            'brak_threshold' => $threshold,
        ]);
        foreach ($quality['products'] as $p) {
            if (($p['alert'] ?? false) && ($p['sold'] ?? 0) >= 10) {
                $this->sendTo('management', "Mahsulot brak ≥{$threshold}%: <b>{$p['name']}</b> — {$p['brak_pct']}%");
            }
        }
    }

    public function scheduleSurveys(): int
    {
        $created = 0;
        $map = ['T1' => 7, 'T2' => 30, 'T3' => 90];
        foreach ($map as $stage => $days) {
            $activations = Activation::query()
                ->whereNotNull('bot_user_id')
                ->whereDate('activated_at', now()->subDays($days)->toDateString())
                ->whereDoesntHave('surveys', fn ($q) => $q->where('stage', $stage))
                ->whereHas('botUser', fn ($q) => $q->where('marketing_consent', true))
                ->limit(200)
                ->get();

            foreach ($activations as $a) {
                SurveyMessage::query()->create([
                    'customer_id' => $a->customer_id,
                    'bot_user_id' => $a->bot_user_id,
                    'order_id' => $a->order_id,
                    'activation_id' => $a->id,
                    'stage' => $stage,
                    'days_after' => $days,
                    'status' => 'pending',
                    'scheduled_at' => now(),
                ]);
                $created++;
            }
        }

        // T4 — warranty ending in 30 days
        $ending = Activation::query()
            ->whereNotNull('bot_user_id')
            ->whereDate('warranty_ends_at', now()->addDays(30)->toDateString())
            ->whereHas('botUser', fn ($q) => $q->where('marketing_consent', true))
            ->limit(200)
            ->get();
        foreach ($ending as $a) {
            $exists = SurveyMessage::query()->where('activation_id', $a->id)->where('stage', 'T4')->exists();
            if ($exists) {
                continue;
            }
            SurveyMessage::query()->create([
                'customer_id' => $a->customer_id,
                'bot_user_id' => $a->bot_user_id,
                'order_id' => $a->order_id,
                'activation_id' => $a->id,
                'stage' => 'T4',
                'days_after' => 0,
                'status' => 'pending',
                'scheduled_at' => now(),
            ]);
            $created++;
        }

        $created += $this->scheduleMissedSellerRatings();

        return $created;
    }

    /**
     * Aktivatsiya boʻlgan, lekin SELLER soʻrovi yaratilmagan cheklar (catch-up).
     */
    public function scheduleMissedSellerRatings(): int
    {
        $created = 0;
        $delay = TelegramBotService::sellerRatingDelayMinutes();
        $activations = Activation::query()
            ->with(['order', 'botUser'])
            ->whereNotNull('bot_user_id')
            ->whereNotNull('order_id')
            ->where('activated_at', '<=', now()->subMinutes($delay))
            ->where('activated_at', '>=', now()->subHours(2)) // faqat yaqinda o‘tkazib yuborilganlar
            ->whereDoesntHave('surveys', fn ($q) => $q->where('stage', 'SELLER'))
            ->orderBy('id')
            ->limit(200)
            ->get()
            ->unique('order_id');

        foreach ($activations as $a) {
            if (! $a->order || ! $a->botUser) {
                continue;
            }
            $exists = SurveyMessage::query()
                ->where('order_id', $a->order_id)
                ->where('bot_user_id', $a->bot_user_id)
                ->where('stage', 'SELLER')
                ->exists();
            if ($exists) {
                continue;
            }
            SurveyMessage::query()->create([
                'customer_id' => $a->customer_id,
                'bot_user_id' => $a->bot_user_id,
                'order_id' => $a->order_id,
                'activation_id' => $a->id,
                'stage' => 'SELLER',
                'days_after' => 0,
                'status' => 'pending',
                'scheduled_at' => now(), // kechikish muddati allaqachon oʻtgan
            ]);
            $created++;
        }

        return $created;
    }

    public function sendPendingSurveys(int $limit = 50): int
    {
        $sent = 0;
        $rows = SurveyMessage::query()
            ->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('scheduled_at')->orWhere('scheduled_at', '<=', now());
            })
            ->orderBy('id')
            ->limit($limit)
            ->get();
        foreach ($rows as $row) {
            if ($this->bot->sendSurvey($row)) {
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * Oylik sotuvchilar reytingi PDF + Telegram (oyning 1-kuni).
     *
     * @return array{ok:bool,message:string,path?:string,high_rad?:int}
     */
    public function sendMonthlySellerRating(bool $force = false): array
    {
        if (! $force && ! $this->enabled('monthly_report')) {
            return ['ok' => false, 'message' => 'Oylik hisobot o‘chirilgan (Xabarnomalar).'];
        }

        $reportService = app(SellerRatingReportService::class);
        $report = $reportService->build();
        $monthKey = $report['month_key'];

        if (! $force) {
            $last = IntegrationSetting::getValue('seller_rating_last_sent_month');
            if ($last === $monthKey) {
                return ['ok' => false, 'message' => "Bu oy uchun allaqachon yuborilgan ({$monthKey})."];
            }
        }

        $path = $reportService->storePdf($report);
        $chat = $this->bot->chatId('management');
        if (! $chat) {
            return ['ok' => false, 'message' => 'Rahbariyat guruh ID sozlanmagan.', 'path' => $path];
        }

        try {
            $this->bot->sendDocument($chat, $path, $reportService->telegramCaption($report));
            IntegrationSetting::setValue('seller_rating_last_sent_month', $monthKey);
            IntegrationSetting::setValue('seller_rating_last_sent_at', now()->toIso8601String());
            IntegrationSetting::setValue('seller_rating_last_pdf', $path);

            if ($report['high_rad'] !== []) {
                $lines = ["⚠ <b>Rad etilgan so‘rov ≥15%</b> — do‘kon menejeriga e’tibor:\n"];
                foreach ($report['high_rad'] as $row) {
                    $lines[] = "• <b>{$row['n']}</b> ({$row['shop']}) — {$row['rad']}%";
                }
                $this->sendTo('management', implode("\n", $lines));
            }

            return [
                'ok' => true,
                'message' => 'Reyting PDF Telegram guruhiga yuborildi.',
                'path' => $path,
                'high_rad' => count($report['high_rad']),
            ];
        } catch (\Throwable $e) {
            Log::warning('Monthly seller rating send failed: '.$e->getMessage());

            return ['ok' => false, 'message' => $e->getMessage(), 'path' => $path];
        }
    }

    protected function enabled(string $code): bool
    {
        $rule = NotificationRule::query()->where('code', $code)->first();

        return ! $rule || $rule->enabled;
    }

    /**
     * service → faqat Servis guruhiga.
     * boshqa (management / admin / purchase) → Rahbariyat guruhiga.
     */
    protected function sendTo(string $target, string $text): void
    {
        $dest = $target === 'service' ? 'service' : 'management';
        $chat = $this->bot->chatId($dest);
        if (! $chat) {
            Log::info('Telegram notify skipped (no chat)', ['target' => $dest, 'text' => $text]);

            return;
        }
        try {
            $this->bot->sendMessage($chat, $text);
        } catch (\Throwable $e) {
            Log::warning('Telegram notify failed: '.$e->getMessage());
        }
    }
}
