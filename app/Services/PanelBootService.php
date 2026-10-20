<?php

namespace App\Services;

use App\Models\Activation;
use App\Models\BotUser;
use App\Models\Claim;
use App\Models\ClaimStatusLog;
use App\Models\Customer;
use App\Models\IntegrationSetting;
use App\Models\NotificationRule;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Seller;
use App\Models\Shop;
use App\Models\SurveyMessage;
use App\Models\SyncLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Original HTML panel mock-shape boot payload.
 * UI/RENDER functions stay untouched — only data source changes.
 */
class PanelBootService
{
    public function boot(int $periodDays = 30, ?int $shopId = null): array
    {
        $to = now()->endOfDay();
        $from = now()->subDays($periodDays - 1)->startOfDay();

        $shops = Shop::query()->where('is_active', true)->orderBy('name')->get();
        if ($shopId) {
            $shops = $shops->where('id', $shopId)->values();
        }
        $stores = [];
        $tot = [
            'cheks' => 0, 'units' => 0, 'summa' => 0.0, 'akt' => 0.0, 'inst' => 0.0, 'rep' => 0.0,
            'brak' => 0.0, 'resp' => 0.0, 'sat' => 0.0, 'open' => 0, 'close' => 0.0,
            'kaf' => Activation::query()
                ->where('warranty_ends_at', '>=', now())
                ->when($shopId, fn ($q) => $q->whereHas('order', fn ($o) => $o->where('shop_id', $shopId)))
                ->count(),
            'csat' => 0.0,
        ];

        foreach ($shops as $shop) {
            $oids = Order::query()
                ->where('shop_id', $shop->id)
                ->whereBetween('sold_at', [$from, $to])
                ->where('sync_error', false)
                ->pluck('id');
            $cheks = $oids->count();
            $sum = (float) Order::query()->whereIn('id', $oids)->sum('total_price');
            // QR aktivatsiya = Billz cheklaridan nechtasi bazada faol
            $aktOrders = Activation::query()->whereIn('order_id', $oids)->whereNotNull('order_id')->distinct()->count('order_id');
            // Doʼkonda: sotuvdan keyin 30 daqiqa ichida faollashtirilgan cheklar
            $instOrders = $this->inStoreActivatedCount($oids);
            $units = (float) OrderItem::query()->whereIn('order_id', $oids)->sum('quantity');
            $brakN = Claim::query()->where('shop_id', $shop->id)->whereIn('status', ['approved', 'replaced'])->whereBetween('created_at', [$from, $to])->count();
            $open = Claim::query()->where('shop_id', $shop->id)->whereNotIn('status', ['rejected', 'replaced'])->count();
            $closed = Claim::query()->where('shop_id', $shop->id)->whereNotNull('closed_at')->whereBetween('closed_at', [$from, $to])->get();
            $closeAvg = $closed->count() ? round($closed->avg(fn ($c) => $c->created_at->diffInDays($c->closed_at)), 1) : 0;

            $akt = $cheks ? round($aktOrders / $cheks * 100, 1) : 0;
            $inst = $cheks ? round($instOrders / $cheks * 100, 1) : 0;
            $brak = $units > 0 ? round($brakN / $units * 100, 2) : 0;

            // Takroriy: faqat bot orqali faollashtirilgan mijozlar (Billz customer emas)
            $rep = $this->repeatCustomerPct($oids);

            $row = [
                'id' => (string) $shop->id,
                'name' => $shop->name,
                'cheks' => $cheks,
                'units' => (int) round($units),
                'summa' => round($sum / 1_000_000, 2),
                'akt' => $akt,
                'inst' => $inst,
                'rep' => $rep,
                'brak' => $brak,
                'csat' => 0,
                'resp' => 0,
                'sat' => 0,
                'open' => $open,
                'close' => $closeAvg,
            ];
            $stores[] = $row;
            $tot['cheks'] += $cheks;
            $tot['summa'] += $row['summa'];
            $tot['open'] += $open;
        }

        // Kompaniya yoki (shop_id berilsa) bitta doʼkon kesimi
        $allOids = Order::query()
            ->whereBetween('sold_at', [$from, $to])
            ->where('sync_error', false)
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->pluck('id');
        $aktOrdersAll = Activation::query()->whereIn('order_id', $allOids)->whereNotNull('order_id')->distinct()->count('order_id');
        $unitsAll = (float) OrderItem::query()->whereIn('order_id', $allOids)->sum('quantity');
        $brakNAll = Claim::query()
            ->whereIn('status', ['approved', 'replaced'])
            ->whereBetween('created_at', [$from, $to])
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->count();
        $tot['akt'] = $tot['cheks'] ? round($aktOrdersAll / $tot['cheks'] * 100, 1) : 0;
        $tot['inst'] = $tot['cheks'] ? round($this->inStoreActivatedCount($allOids) / $tot['cheks'] * 100, 1) : 0;
        $tot['brak'] = $unitsAll > 0 ? round($brakNAll / $unitsAll * 100, 2) : 0;
        $tot['units'] = (int) round($unitsAll);
        $tot['rep'] = $this->repeatCustomerPct($allOids);
        $closedAll = Claim::query()
            ->whereNotNull('closed_at')
            ->whereBetween('closed_at', [$from, $to])
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->get();
        $tot['close'] = $closedAll->count()
            ? round($closedAll->avg(fn ($c) => $c->created_at->diffInDays($c->closed_at)), 1)
            : 0;
        $tot['csat'] = 0;

        $surveysSent = SurveyMessage::query()->whereBetween('sent_at', [$from, $to])->whereIn('status', ['sent', 'delivered', 'answered'])->count();
        $surveysAns = SurveyMessage::query()->whereBetween('answered_at', [$from, $to])->where('status', 'answered')->count();
        $good = SurveyMessage::query()->whereBetween('answered_at', [$from, $to])->where('answer', 'good')->count();
        $tot['resp'] = $surveysSent ? round($surveysAns / $surveysSent * 100, 1) : 0;
        $tot['sat'] = $surveysAns ? round($good / $surveysAns * 100, 1) : 0;

        $sellers = $this->panelSellers($from, $to, $shopId);

        $claims = Claim::query()
            ->with(['customer', 'shop', 'seller', 'product', 'order', 'orderItem', 'assignee', 'botUser', 'logs.user', 'items.orderItem', 'items.product'])
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->latest()
            ->limit(200)
            ->get()
            ->map(fn (Claim $c) => $this->panelClaimRow($c))
            ->all();

        $customersAll = Customer::query()
            ->fromBot()
            ->withCount('claims')
            ->orderByDesc('registered_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Customer $c) => $this->panelCustomerRow($c))
            ->sortByDesc(fn (array $r) => [$r['x'], $r['sum']])
            ->values();
        $customers = $customersAll->take(200)->values()->all();

        $products = $this->qualityProducts($from, $to, $periodDays, $shopId);
        $cats = collect($products)->pluck('cat')->unique()->filter()->values()
            ->map(fn ($c) => ['id' => $c, 'n' => ucfirst((string) $c)])->all();
        if ($cats === []) {
            $cats = [['id' => 'boshqa', 'n' => 'Boshqa']];
        }

        $touch = $this->surveyTouchRows($from, $to);

        $daily = [];
        for ($i = 89; $i >= 0; $i--) {
            $d = now()->subDays($i)->startOfDay();
            $cheks = Order::query()
                ->whereDate('sold_at', $d)
                ->where('sync_error', false)
                ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
                ->count();
            $akt = Activation::query()
                ->whereDate('activated_at', $d)
                ->when($shopId, fn ($q) => $q->whereHas('order', fn ($o) => $o->where('shop_id', $shopId)))
                ->count();
            $daily[] = [
                'd' => $d->toIso8601String(),
                'cheks' => $cheks,
                'akt' => $akt,
            ];
        }

        $comments = SurveyMessage::query()
            ->with(['customer', 'order.items', 'activation.orderItem'])
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->latest('answered_at')
            ->limit(20)
            ->get()
            ->map(fn ($s) => [
                'd' => optional($s->answered_at)->format('d.m') ?: '—',
                'c' => $s->customer?->name ?: '—',
                'p' => $s->activation?->orderItem?->name
                    ?? $s->order?->items?->first()?->name
                    ?? '—',
                't' => $s->comment,
            ])->all();

        $targetLabels = [
            'management' => 'Rahbariyat guruhi',
            'service' => 'Servis guruhi',
            'purchase' => 'Rahbariyat guruhi',
            'admin' => 'Rahbariyat guruhi',
        ];
        $scheduleLabels = [
            'daily_09:00' => 'Har kuni 09:00',
            'monday_09:00' => 'Dushanba 09:00',
            'month_1' => 'Oyning 1-kuni 09:00',
        ];
        $alerts = NotificationRule::query()->orderBy('id')->get()->map(fn ($r) => [
            'id' => $r->id,
            'code' => $r->code,
            'on' => (bool) $r->enabled,
            'n' => $r->name,
            'w' => $scheduleLabels[$r->schedule] ?? ($r->schedule ?: 'Darhol'),
            'to' => $targetLabels[$r->target] ?? ($r->target ?: '—'),
            'th' => $r->threshold ?: '—',
            'type' => $r->type === 'schedule' ? 'Muntazam' : 'Ogohlantirish',
        ])->all();

        $dailyReportPreview = app(NotificationService::class)->dailyReportPreview();

        $token = IntegrationSetting::getEncrypted('telegram_bot_token');

        return [
            'stores' => $stores,
            'tot' => $tot,
            'sellers' => $sellers,
            'claims' => $claims,
            'customers' => $customers,
            'products' => $products,
            'cats' => $cats,
            'touch' => $touch,
            'daily' => $daily,
            'comments' => $comments,
            'alerts' => $alerts,
            'daily_report_preview' => $dailyReportPreview,
            'updated_at' => IntegrationSetting::getValue('billz_last_sales_sync') ?: now()->toIso8601String(),
            'shop_count' => count($stores),
            'telegram' => [
                'bot_token_set' => (bool) $token,
                'bot_token_masked' => $token ? '********'.substr($token, -6) : null,
                'management_group_id' => IntegrationSetting::getValue('telegram_management_group_id'),
                'service_group_id' => IntegrationSetting::getValue('telegram_service_group_id'),
                'webhook_url' => IntegrationSetting::getValue('telegram_webhook_url') ?: url('/telegram/webhook'),
                'webhook_status' => IntegrationSetting::getValue('telegram_webhook_status', 'inactive'),
                'bot_username' => IntegrationSetting::getValue('telegram_bot_username'),
                'seller_rating_delay_minutes' => (int) IntegrationSetting::getValue('seller_rating_delay_minutes', 0),
                'mode' => IntegrationSetting::getValue('telegram_webhook_status') === 'active'
                    ? 'webhook'
                    : (IntegrationSetting::getValue('telegram_mode') ?: 'polling'),
            ],
            'billz' => [
                'secret_set' => (bool) (IntegrationSetting::getEncrypted('billz_secret_token') ?: config('billz.secret_token')),
                'company_id' => IntegrationSetting::getValue('billz_company_id'),
                'sync_status' => IntegrationSetting::getValue('billz_sync_status', 'unknown'),
                'last_sales_sync' => IntegrationSetting::getValue('billz_last_sales_sync'),
                'last_shops_sync' => IntegrationSetting::getValue('billz_last_shops_sync'),
                'last_products_sync' => IntegrationSetting::getValue('billz_last_products_sync'),
                'sync_error' => IntegrationSetting::getValue('billz_sync_error'),
                'today_orders' => Order::query()->whereDate('sold_at', today())->where('sync_error', false)->count(),
                'error_orders' => Order::query()->where('sync_error', true)->count(),
            ],
            'bot_stats' => $this->botStats($from, $to, $surveysSent),
            'funnel' => $this->funnel($from, $to, $shopId),
            'customer_stats' => $this->customerStats($from, $to, $customersAll->all()),
            'deltas' => $this->homeDeltas($from, $to, $periodDays, $tot),
            'claim_weeks' => $this->claimWeeks(12, $shopId),
            'claim_stats' => $this->claimStats($from, $to, $periodDays, $shopId),
            'brak_threshold' => $this->brakThresholdPct(),
            'survey_weeks' => $this->surveyWeeks(12),
            'auth_user' => $this->authUserPayload(),
            'sync_errors' => Order::query()->with('shop')
                ->where('sync_error', true)
                ->latest('updated_at')
                ->limit(15)
                ->get()
                ->map(fn (Order $o) => [
                    'number' => $o->order_number ?: (string) $o->id,
                    'shop' => $o->shop?->name ?? '—',
                    'at' => optional($o->updated_at)->format('d.m.Y H:i') ?: '—',
                    'message' => $o->sync_error_message ?: 'Sinxron xato',
                ])->all(),
            'audit_log' => $this->auditLogRows(25),
        ];
    }

    /** @return array<string, mixed> */
    protected function authUserPayload(): array
    {
        $user = auth()->user();
        if (! $user) {
            return ['id' => null, 'name' => '', 'pages' => [], 'can_manage_staff' => false];
        }
        $user->loadMissing(['panelRole', 'shop']);
        $pages = $user->allowedPages();
        $forcedShop = $user->forcedShopId();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'login' => $user->username ?: $user->email,
            'role_id' => $user->role_id,
            'role_name' => $user->panelRole?->name ?? ($user->role ?: '—'),
            'is_admin' => $user->isAdmin(),
            'shop_id' => $user->shop_id ? (string) $user->shop_id : null,
            'shop_name' => $user->shop?->name,
            'shop_locked' => (bool) $forcedShop,
            'pages' => $pages,
            'can_manage_staff' => in_array('xodimlar', $pages, true),
        ];
    }

    /**
     * Soʼrovnoma jadvali — T1–T4 (HTML matnlari saqlanadi).
     *
     * @return list<array<string, mixed>>
     */
    protected function surveyTouchRows(Carbon $from, Carbon $to): array
    {
        $meta = [
            'T1' => ['when' => 'Xariddan 7 kun', 'q' => 'Oʼrnatildimi, ishlayaptimi'],
            'T2' => ['when' => 'Xariddan 30 kun', 'q' => 'Foydalanishda muammo bormi'],
            'T3' => ['when' => 'Xariddan 90 kun', 'q' => 'Uzoq muddatli sifat'],
            'T4' => ['when' => 'Kafolat tugashiga 30 kun', 'q' => 'Eslatma + oxirgi tekshiruv'],
        ];

        $rows = [];
        foreach ($meta as $stage => $m) {
            $base = SurveyMessage::query()->where('stage', $stage)->whereBetween('sent_at', [$from, $to]);
            $sent = (clone $base)->count();
            $dlv = (clone $base)->where(function ($q) {
                $q->whereNotNull('delivered_at')
                    ->orWhereIn('status', ['delivered', 'answered']);
            })->count();
            $ans = (clone $base)->where(function ($q) {
                $q->where('status', 'answered')->orWhereNotNull('answered_at');
            })->count();
            $good = (clone $base)->where('answer', 'good')->count();
            $bad = (clone $base)->where('answer', 'problem')->count();

            $problemCust = (clone $base)->where('answer', 'problem')->whereNotNull('customer_id')->pluck('customer_id');
            $claims = $problemCust->isEmpty()
                ? 0
                : Claim::query()
                    ->whereIn('customer_id', $problemCust)
                    ->whereBetween('created_at', [$from, $to])
                    ->count();

            $hrsSamples = SurveyMessage::query()
                ->where('stage', $stage)
                ->whereBetween('answered_at', [$from, $to])
                ->whereNotNull('sent_at')
                ->whereNotNull('answered_at')
                ->get(['sent_at', 'answered_at']);
            $hrs = $hrsSamples->count()
                ? round($hrsSamples->avg(fn ($s) => $s->sent_at->diffInMinutes($s->answered_at) / 60), 1)
                : 0;

            $rows[] = [
                't' => $stage,
                'when' => $m['when'],
                'q' => $m['q'],
                'sent' => $sent,
                'dlv' => $dlv,
                'ans' => $ans,
                'good' => $good,
                'bad' => $bad,
                'claims' => $claims,
                'hrs' => $hrs,
            ];
        }

        return $rows;
    }

    /**
     * Haftalik «yaxshi» ulushi % — T1/T2/T3.
     *
     * @return array{labels:list<string>, T1:list<float>, T2:list<float>, T3:list<float>}
     */
    protected function surveyWeeks(int $weeks): array
    {
        $labels = [];
        $series = ['T1' => [], 'T2' => [], 'T3' => []];

        for ($i = $weeks - 1; $i >= 0; $i--) {
            $start = now()->subWeeks($i)->startOfWeek();
            $end = (clone $start)->endOfWeek();
            $labels[] = $start->format('d.m');

            foreach (array_keys($series) as $stage) {
                $ans = SurveyMessage::query()
                    ->where('stage', $stage)
                    ->whereBetween('answered_at', [$start, $end])
                    ->where(function ($q) {
                        $q->where('status', 'answered')->orWhereNotNull('answered_at');
                    })
                    ->count();
                $good = SurveyMessage::query()
                    ->where('stage', $stage)
                    ->whereBetween('answered_at', [$start, $end])
                    ->where('answer', 'good')
                    ->count();
                $series[$stage][] = $ans > 0 ? round($good / $ans * 100, 1) : 0;
            }
        }

        return [
            'labels' => $labels,
            'T1' => $series['T1'],
            'T2' => $series['T2'],
            'T3' => $series['T3'],
        ];
    }

    /**
     * Doʼkonda skanerlangan: Billz sotuv vaqtidan 30 daqiqa ichida faollashtirilgan cheklar.
     *
     * @param  \Illuminate\Support\Collection|array  $orderIds
     */
    protected function inStoreActivatedCount($orderIds): int
    {
        $ids = collect($orderIds)->filter()->values();
        if ($ids->isEmpty()) {
            return 0;
        }

        return (int) Activation::query()
            ->join('orders', 'orders.id', '=', 'activations.order_id')
            ->whereIn('activations.order_id', $ids)
            ->whereNotNull('orders.sold_at')
            ->whereNotNull('activations.activated_at')
            ->whereRaw('activations.activated_at >= orders.sold_at')
            ->whereRaw('activations.activated_at <= DATE_ADD(orders.sold_at, INTERVAL 30 MINUTE)')
            ->distinct()
            ->count('activations.order_id');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function sellersForPeriod(int $periodDays = 30): array
    {
        $to = now()->endOfDay();
        $from = now()->subDays($periodDays - 1)->startOfDay();

        return $this->panelSellers($from, $to);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function sellersBetween(Carbon $from, Carbon $to): array
    {
        return $this->panelSellers($from->copy()->startOfDay(), $to->copy()->endOfDay());
    }

    /**
     * Sotuvchilar reytingi — Billz cheklar + QR + takroriy + rad % + soʻrovnoma bahosi.
     *
     * @return list<array<string, mixed>>
     */
    protected function panelSellers(Carbon $from, Carbon $to, ?int $shopId = null): array
    {
        return Seller::query()
            ->with('shop')
            ->where('is_active', true)
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->get()
            ->map(function (Seller $s) use ($from, $to) {
            $oids = Order::query()
                ->where('seller_id', $s->id)
                ->whereBetween('sold_at', [$from, $to])
                ->where('sync_error', false)
                ->pluck('id');
            $cheks = $oids->count();
            $sum = $cheks
                ? (float) Order::query()->whereIn('id', $oids)->sum('total_price')
                : 0.0;
            $aktOrders = $cheks
                ? Activation::query()->whereIn('order_id', $oids)->whereNotNull('order_id')->distinct()->count('order_id')
                : 0;
            $instOrders = $cheks ? $this->inStoreActivatedCount($oids) : 0;
            $rep = $cheks ? $this->repeatCustomerPct($oids) : null;

            $claimsQ = Claim::query()
                ->where('seller_id', $s->id)
                ->whereBetween('created_at', [$from, $to]);
            $claimsTotal = (clone $claimsQ)->count();
            $rejected = (clone $claimsQ)->where('status', 'rejected')->count();
            // UI: rad ulushi % (15 % dan yuqori — qizil)
            $rad = $claimsTotal ? round($rejected / $claimsTotal * 100, 1) : 0.0;

            return [
                'n' => $s->name,
                'st' => (string) $s->shop_id,
                'cheks' => $cheks,
                'summa' => round($sum / 1_000_000, 2),
                'akt' => $cheks ? round($aktOrders / $cheks * 100, 1) : 0,
                'inst' => $cheks ? round($instOrders / $cheks * 100, 1) : 0,
                'rep' => $rep,
                'rad' => $rad,
                'csat' => $this->sellerCsat($oids, $from, $to),
            ];
        })->values()->all();
    }

    /**
     * Sotuvchi xizmat bahosi (1–5): mijoz botdan bergan SELLER baholari.
     * Javob yoʻq — 0 (UI da «—»).
     *
     * @param  \Illuminate\Support\Collection|array  $orderIds
     */
    protected function sellerCsat($orderIds, Carbon $from, Carbon $to): float
    {
        $ids = collect($orderIds)->filter()->values();
        if ($ids->isEmpty()) {
            return 0.0;
        }

        $answers = SurveyMessage::query()
            ->where('stage', 'SELLER')
            ->where('status', 'answered')
            ->whereBetween('answered_at', [$from, $to])
            ->whereIn('order_id', $ids)
            ->whereIn('answer', ['1', '2', '3', '4', '5'])
            ->pluck('answer');

        if ($answers->isEmpty()) {
            return 0.0;
        }

        return round($answers->map(fn ($a) => (float) $a)->avg(), 1);
    }

    /**
     * Takroriy mijoz foizi — faqat bot orqali faollashtirilgan mijozlar.
     * Shu kesimdagi bot foydalanuvchilari ichida 2+ chek faollashtirganlar ulushi.
     * Bot aktivatsiyasi yoʻq — null (UI da "—").
     *
     * @param  \Illuminate\Support\Collection|array  $orderIds
     */
    protected function repeatCustomerPct($orderIds): ?float
    {
        $ids = collect($orderIds)->filter()->values();
        if ($ids->isEmpty()) {
            return null;
        }

        $custCounts = Activation::query()
            ->whereIn('order_id', $ids)
            ->whereNotNull('bot_user_id')
            ->select('bot_user_id', DB::raw('count(distinct order_id) as c'))
            ->groupBy('bot_user_id')
            ->pluck('c');

        if ($custCounts->isEmpty()) {
            return null;
        }

        $repCust = $custCounts->filter(fn ($c) => $c >= 2)->count();

        return round($repCust / $custCounts->count() * 100, 1);
    }

    /**
     * Oldingi shu uzunlikdagi davr bilan solishtirish (real, demo emas).
     *
     * @return array<string, array{value:float|int|null, unit:?string}>
     */
    protected function homeDeltas(Carbon $from, Carbon $to, int $days, array $tot): array
    {
        $prevTo = $from->copy()->subSecond();
        $prevFrom = $prevTo->copy()->subDays($days - 1)->startOfDay();
        $prev = $this->periodTotals($prevFrom, $prevTo);

        // HTML dagi kabi har bir kartada raqam chiqadi (null emas)
        $pct = function (float $cur, float $prev): float {
            if ($prev <= 0) {
                return $cur > 0 ? 100.0 : 0.0;
            }

            return round(($cur - $prev) / $prev * 100, 1);
        };
        $pp = function (float $cur, float $prev): float {
            return round($cur - $prev, 1);
        };

        $kafNow = (int) ($tot['kaf'] ?? 0);
        $kafPrev = Activation::query()
            ->where('activated_at', '<=', $prevTo)
            ->where(function ($q) use ($prevTo) {
                $q->whereNull('warranty_ends_at')->orWhere('warranty_ends_at', '>=', $prevTo);
            })
            ->count();

        $openNow = (int) ($tot['open'] ?? 0);
        $openPrev = Claim::query()
            ->where('created_at', '<=', $prevTo)
            ->where(function ($q) use ($prevTo) {
                $q->whereNull('closed_at')->orWhere('closed_at', '>', $prevTo);
            })
            ->whereNotIn('status', ['rejected', 'replaced'])
            ->count();

        return [
            'cheks' => ['value' => $pct((float) $tot['cheks'], (float) $prev['cheks']), 'unit' => '%'],
            'akt' => ['value' => $pp((float) $tot['akt'], (float) $prev['akt']), 'unit' => 'pp'],
            'kaf' => ['value' => $kafNow - $kafPrev, 'unit' => null],
            'resp' => ['value' => $pp((float) $tot['resp'], (float) $prev['resp']), 'unit' => 'pp'],
            'sat' => ['value' => $pp((float) $tot['sat'], (float) $prev['sat']), 'unit' => 'pp'],
            'brak' => ['value' => $pp((float) $tot['brak'], (float) $prev['brak']), 'unit' => 'pp'],
            'open' => ['value' => $openNow - $openPrev, 'unit' => null],
            'close' => ['value' => $pp((float) $tot['close'], (float) $prev['close']), 'unit' => 'kun'],
        ];
    }

    protected function periodTotals(Carbon $from, Carbon $to): array
    {
        $oids = Order::query()->whereBetween('sold_at', [$from, $to])->where('sync_error', false)->pluck('id');
        $cheks = $oids->count();
        $aktOrders = Activation::query()->whereIn('order_id', $oids)->whereNotNull('order_id')->distinct()->count('order_id');
        $units = (float) OrderItem::query()->whereIn('order_id', $oids)->sum('quantity');
        $brakN = Claim::query()->whereIn('status', ['approved', 'replaced'])->whereBetween('created_at', [$from, $to])->count();
        $sent = SurveyMessage::query()->whereBetween('sent_at', [$from, $to])->whereIn('status', ['sent', 'delivered', 'answered'])->count();
        $ans = SurveyMessage::query()->whereBetween('answered_at', [$from, $to])->where('status', 'answered')->count();
        $good = SurveyMessage::query()->whereBetween('answered_at', [$from, $to])->where('answer', 'good')->count();
        $closed = Claim::query()->whereNotNull('closed_at')->whereBetween('closed_at', [$from, $to])->get();

        return [
            'cheks' => $cheks,
            'akt' => $cheks ? round($aktOrders / $cheks * 100, 1) : 0,
            'brak' => $units > 0 ? round($brakN / $units * 100, 2) : 0,
            'resp' => $sent ? round($ans / $sent * 100, 1) : 0,
            'sat' => $ans ? round($good / $ans * 100, 1) : 0,
            'close' => $closed->count() ? round($closed->avg(fn ($c) => $c->created_at->diffInDays($c->closed_at)), 1) : 0,
        ];
    }

    /**
     * @return array{
     *   labels:list<string>,
     *   in:list<int>,
     *   out:list<int>,
     *   app:list<int>,
     *   rej:list<int>,
     *   total:int
     * }
     */
    protected function claimWeeks(int $weeks, ?int $shopId = null): array
    {
        $labels = [];
        $in = [];
        $out = [];
        $app = [];
        $rej = [];
        for ($i = $weeks - 1; $i >= 0; $i--) {
            $start = now()->subWeeks($i)->startOfWeek();
            $end = (clone $start)->endOfWeek();
            $labels[] = $start->format('d.m');

            $scope = fn ($q) => $q->when($shopId, fn ($qq) => $qq->where('shop_id', $shopId));

            $in[] = Claim::query()->tap($scope)->whereBetween('created_at', [$start, $end])->count();
            $out[] = Claim::query()->tap($scope)->whereNotNull('closed_at')->whereBetween('closed_at', [$start, $end])->count();

            // Yopilgan sana (closed_at); bo‘lmasa status yangilangan sana
            $app[] = Claim::query()
                ->tap($scope)
                ->whereIn('status', ['approved', 'replaced'])
                ->where(function ($q) use ($start, $end) {
                    $q->whereBetween('closed_at', [$start, $end])
                        ->orWhere(function ($q2) use ($start, $end) {
                            $q2->whereNull('closed_at')->whereBetween('updated_at', [$start, $end]);
                        });
                })
                ->count();

            $rej[] = Claim::query()
                ->tap($scope)
                ->where('status', 'rejected')
                ->where(function ($q) use ($start, $end) {
                    $q->whereBetween('closed_at', [$start, $end])
                        ->orWhere(function ($q2) use ($start, $end) {
                            $q2->whereNull('closed_at')->whereBetween('updated_at', [$start, $end]);
                        });
                })
                ->count();
        }

        return [
            'labels' => $labels,
            'in' => $in,
            'out' => $out,
            'app' => $app,
            'rej' => $rej,
            'total' => array_sum($app) + array_sum($rej),
        ];
    }

    /** Brak darajasi chegarasi (%) — Xabarnomalar sozlamasidan (`product_brak_3pct`). */
    public function brakThresholdPct(): float
    {
        $raw = NotificationRule::query()->where('code', 'product_brak_3pct')->value('threshold')
            ?? IntegrationSetting::getValue('quality_brak_threshold', '3');

        if (preg_match('/(\d+(?:[.,]\d+)?)/', (string) $raw, $m)) {
            $v = (float) str_replace(',', '.', $m[1]);

            return max(0.1, min(100, $v));
        }

        return 3.0;
    }

    /**
     * Mahsulot sifati — faqat bot orqali chek tasdiqlab faollashtirilgan mahsulotlar.
     * Sotildi = bot aktivatsiyasi (dona); brak % = tasdiqlangan / faollashtirilgan.
     *
     * @return list<array<string, mixed>>
     */
    protected function qualityProducts(Carbon $from, Carbon $to, int $days, ?int $shopId = null): array
    {
        $prevTo = $from->copy()->subSecond();
        $prevFrom = $prevTo->copy()->subDays($days - 1)->startOfDay();

        // Faqat botda chek yuborib / tasdiqlab aktivatsiya qilinganlar
        $botActivations = Activation::query()
            ->with(['orderItem', 'order'])
            ->whereNotNull('bot_user_id')
            ->whereBetween('activated_at', [$from, $to])
            ->when($shopId, fn ($q) => $q->whereHas('order', fn ($o) => $o->where('shop_id', $shopId)))
            ->get();

        if ($botActivations->isEmpty()) {
            return [];
        }

        $groups = $botActivations->groupBy(function (Activation $a) {
            $item = $a->orderItem;

            return implode('|', [
                $item?->name ?: 'Mahsulot',
                $item?->brand ?: '—',
                $item?->supplier ?: '—',
                $item?->category ?: 'boshqa',
            ]);
        });

        return $groups->map(function ($acts) use ($from, $to, $prevFrom, $prevTo, $shopId) {
            /** @var \Illuminate\Support\Collection<int, Activation> $acts */
            $first = $acts->first()->orderItem;
            $name = $first?->name ?: 'Mahsulot';
            $brand = $first?->brand ?: '—';
            $sup = $first?->supplier ?: '—';
            $cat = $first?->category ?: 'boshqa';

            $sold = (float) $acts->sum(fn (Activation $a) => (float) ($a->orderItem?->quantity ?? 1));

            $claimScope = function ($q) use ($name, $shopId) {
                $q->where(function ($qq) use ($name) {
                    $qq->whereHas('orderItem', fn ($oi) => $oi->where('name', $name))
                        ->orWhereHas('product', fn ($p) => $p->where('name', $name))
                        ->orWhereHas('items.orderItem', fn ($oi) => $oi->where('name', $name))
                        ->orWhereHas('items.product', fn ($p) => $p->where('name', $name));
                })
                    ->whereNotNull('bot_user_id')
                    ->when($shopId, fn ($qq) => $qq->where('shop_id', $shopId));
            };

            $req = Claim::query()->whereBetween('created_at', [$from, $to])->where($claimScope)->count();
            $brak = Claim::query()
                ->whereBetween('created_at', [$from, $to])
                ->whereIn('status', ['approved', 'replaced'])
                ->where($claimScope)
                ->count();

            $costSum = (float) Claim::query()
                ->where('status', 'replaced')
                ->whereNotNull('closed_at')
                ->whereBetween('closed_at', [$from, $to])
                ->where($claimScope)
                ->sum('replacement_cost');

            $surveyBase = SurveyMessage::query()
                ->whereBetween('answered_at', [$from, $to])
                ->where('status', 'answered')
                ->whereNotNull('bot_user_id')
                ->where(function ($q) use ($name) {
                    $q->whereHas('activation.orderItem', fn ($oi) => $oi->where('name', $name))
                        ->orWhereHas('order.items', fn ($oi) => $oi->where('name', $name));
                });
            $ans = (clone $surveyBase)->count();
            $good = (clone $surveyBase)->where('answer', 'good')->count();
            $sat = $ans > 0 ? (int) round($good / $ans * 100) : 0;

            $rate = $sold > 0 ? $brak / $sold * 100 : 0.0;

            $prevSold = (float) Activation::query()
                ->whereNotNull('bot_user_id')
                ->whereBetween('activated_at', [$prevFrom, $prevTo])
                ->whereHas('orderItem', fn ($oi) => $oi->where('name', $name))
                ->when($shopId, fn ($q) => $q->whereHas('order', fn ($o) => $o->where('shop_id', $shopId)))
                ->with('orderItem')
                ->get()
                ->sum(fn (Activation $a) => (float) ($a->orderItem?->quantity ?? 1));

            $prevBrak = Claim::query()
                ->whereBetween('created_at', [$prevFrom, $prevTo])
                ->whereIn('status', ['approved', 'replaced'])
                ->where($claimScope)
                ->count();
            $prevRate = $prevSold > 0 ? $prevBrak / $prevSold * 100 : 0.0;
            $diff = $rate - $prevRate;
            $tr = abs($diff) < 0.15 ? 0 : ($diff > 0 ? 1 : -1);

            return [
                'n' => $name,
                'cat' => $cat,
                'brand' => $brand,
                'sup' => $sup,
                'sold' => (int) round($sold),
                'req' => $req,
                'brak' => $brak,
                'sat' => $sat,
                'cost' => round($costSum / 1_000_000, 3),
                'tr' => $tr,
            ];
        })
            ->sortByDesc('sold')
            ->values()
            ->all();
    }

    /**
     * Brak sahifasi KPI — davr / oldingi davr (real).
     *
     * @return array<string, mixed>
     */
    protected function claimStats(Carbon $from, Carbon $to, int $days, ?int $shopId = null): array
    {
        $scope = fn ($q) => $q->when($shopId, fn ($qq) => $qq->where('shop_id', $shopId));

        $received = Claim::query()->tap($scope)->whereBetween('created_at', [$from, $to])->count();
        $confirmed = Claim::query()->tap($scope)
            ->whereBetween('created_at', [$from, $to])
            ->whereIn('status', ['approved', 'replaced'])
            ->count();
        $approvedPct = $received > 0 ? (int) round($confirmed / $received * 100) : 0;

        $closed = Claim::query()->tap($scope)->whereNotNull('closed_at')->whereBetween('closed_at', [$from, $to])->get();
        $closeAvg = $closed->count()
            ? round($closed->avg(fn ($c) => $c->created_at->diffInDays($c->closed_at)), 1)
            : 0.0;

        $sla = Claim::query()->tap($scope)
            ->whereNotIn('status', ['rejected', 'replaced'])
            ->where(function ($q) {
                $q->whereNull('closed_at')->orWhereNotIn('status', ['rejected', 'replaced']);
            })
            ->get()
            ->filter(fn (Claim $c) => $c->daysOpen() > 7)
            ->count();

        $costSum = (float) Claim::query()->tap($scope)
            ->where('status', 'replaced')
            ->whereNotNull('closed_at')
            ->whereBetween('closed_at', [$from, $to])
            ->sum('replacement_cost');
        $costMln = round($costSum / 1_000_000, 1);

        $prevTo = $from->copy()->subSecond();
        $prevFrom = $prevTo->copy()->subDays($days - 1)->startOfDay();
        $prevReceived = Claim::query()->tap($scope)->whereBetween('created_at', [$prevFrom, $prevTo])->count();
        $prevConfirmed = Claim::query()->tap($scope)
            ->whereBetween('created_at', [$prevFrom, $prevTo])
            ->whereIn('status', ['approved', 'replaced'])
            ->count();
        $prevApprovedPct = $prevReceived > 0 ? (int) round($prevConfirmed / $prevReceived * 100) : 0;
        $prevClosed = Claim::query()->tap($scope)->whereNotNull('closed_at')->whereBetween('closed_at', [$prevFrom, $prevTo])->get();
        $prevCloseAvg = $prevClosed->count()
            ? round($prevClosed->avg(fn ($c) => $c->created_at->diffInDays($c->closed_at)), 1)
            : 0.0;
        $prevSla = Claim::query()->tap($scope)
            ->where('created_at', '<=', $prevTo)
            ->where(function ($q) use ($prevTo) {
                $q->whereNull('closed_at')->orWhere('closed_at', '>', $prevTo);
            })
            ->whereNotIn('status', ['rejected', 'replaced'])
            ->get()
            ->filter(fn (Claim $c) => (int) $c->created_at->diffInDays($prevTo) > 7)
            ->count();
        $prevCost = (float) Claim::query()->tap($scope)
            ->where('status', 'replaced')
            ->whereNotNull('closed_at')
            ->whereBetween('closed_at', [$prevFrom, $prevTo])
            ->sum('replacement_cost');
        $prevCostMln = round($prevCost / 1_000_000, 1);

        $pctDelta = function (float $cur, float $prev): float {
            if ($prev <= 0) {
                return $cur > 0 ? 100.0 : 0.0;
            }

            return round(($cur - $prev) / $prev * 100, 1);
        };

        return [
            'received' => $received,
            'approved_pct' => $approvedPct,
            'close_avg' => $closeAvg,
            'sla' => $sla,
            'cost_mln' => $costMln,
            'deltas' => [
                'received' => ['value' => $received - $prevReceived, 'unit' => null],
                'approved_pct' => ['value' => $approvedPct - $prevApprovedPct, 'unit' => 'pp'],
                'close_avg' => ['value' => round($closeAvg - $prevCloseAvg, 1), 'unit' => 'kun'],
                'sla' => ['value' => $sla - $prevSla, 'unit' => null],
                'cost_mln' => ['value' => $pctDelta($costMln, $prevCostMln), 'unit' => '%'],
            ],
        ];
    }

    public function panelClaimRow(Claim $c): array
    {
        $photos = array_values(array_filter($c->photos ?? []));
        $photoUrls = array_map(fn ($p) => asset('storage/'.$p), $photos);
        $months = (int) ($c->orderItem?->warranty_months ?: $c->product?->warranty_months ?: 12);
        $activation = $c->order_item_id
            ? Activation::query()->where('order_item_id', $c->order_item_id)->latest('activated_at')->first()
            : null;
        $warrantyActive = $activation?->warranty_ends_at
            ? $activation->warranty_ends_at->isFuture()
            : true;

        $logs = $c->relationLoaded('logs')
            ? $c->logs->sortBy('created_at')->values()->map(fn ($log) => [
                'from' => $log->from_status,
                'to' => $log->to_status,
                'note' => $log->note,
                'user' => $log->user?->name ?? '',
                'at' => $log->created_at?->toIso8601String(),
            ])->all()
            : [];

        return [
            'id' => $c->number,
            'db_id' => $c->id,
            'date' => $c->created_at->toIso8601String(),
            'cust' => $c->customer?->name
                ?: trim(($c->botUser?->first_name ?? '').' '.($c->botUser?->last_name ?? ''))
                ?: '—',
            'cust_id' => $c->customer_id,
            'ph' => $c->customer?->phone ?? $c->botUser?->phone ?? '—',
            'prod' => $c->productSummary(),
            'prods' => $c->productLabels(),
            'brand' => $c->product?->brand ?? $c->orderItem?->brand ?? '—',
            'sn' => $c->orderItem?->serial_number ?: '—',
            'st' => (string) ($c->shop_id ?? ''),
            'seller' => $c->seller?->name ?? '—',
            'chek' => $c->order?->order_number ?? '—',
            'reason' => $c->reason ?? '',
            'text' => $c->description ?? '',
            'status' => $c->status,
            'assignee' => $c->assignee?->name ?? '',
            'assignee_id' => $c->assignee_id,
            'photos' => count($photos),
            'photo_urls' => $photoUrls,
            'closed' => $c->closed_at?->toIso8601String(),
            'rej' => $c->reject_reason,
            'cost' => $c->replacement_cost ? round((float) $c->replacement_cost / 1_000_000, 3) : null,
            'warranty_months' => $months,
            'warranty_ok' => $warrantyActive,
            'logs' => $logs,
        ];
    }

    protected function botStats(Carbon $from, Carbon $to, int $surveysSent): array
    {
        $started = BotUser::query()->whereBetween('started_at', [$from, $to])->count();
        $phoneOk = BotUser::query()->whereBetween('phone_verified_at', [$from, $to])->count();
        // Telefon tasdiqlashda uzilganlar: /start qilgan, lekin telefon yoʻq
        $startedNoPhone = BotUser::query()
            ->whereBetween('started_at', [$from, $to])
            ->whereNull('phone_verified_at')
            ->count();
        $phoneDrop = $started ? round($startedNoPhone / $started * 100, 1) : 0.0;

        // Marketing — faqat bot mijozlari (umumiy holat)
        $consentYes = Customer::query()->fromBot()->where('marketing_consent', true)->count();
        $consentNo = Customer::query()->fromBot()->where('marketing_consent', false)->count();

        $dlv = SurveyMessage::query()->whereBetween('sent_at', [$from, $to])->whereNotNull('delivered_at')->count();
        $fail = SurveyMessage::query()->whereBetween('sent_at', [$from, $to])->where('status', 'failed')->count();
        $mau = BotUser::query()
            ->where(function ($q) {
                $q->whereBetween('last_seen_at', [now()->subDays(30)->startOfDay(), now()])
                    ->orWhereBetween('last_home_at', [now()->subDays(30)->startOfDay(), now()]);
            })
            ->count();

        $homeOpens = (int) BotUser::query()->whereBetween('last_home_at', [$from, $to])->sum('home_opens');
        if ($homeOpens === 0) {
            $homeOpens = BotUser::query()->whereBetween('last_home_at', [$from, $to])->count();
        }

        $byStage = [];
        foreach (['T1', 'T2', 'T3', 'T4', 'SELLER'] as $stage) {
            $q = SurveyMessage::query()->where('stage', $stage)->whereBetween('sent_at', [$from, $to]);
            $sent = (clone $q)->count();
            $d = (clone $q)->whereNotNull('delivered_at')->count();
            $err = (clone $q)->where('status', 'failed')->count();
            $byStage[] = ['stage' => $stage, 'sent' => $sent, 'dlv' => $d, 'err' => $err];
        }

        // Oldingi davr — KPI ↑/↓
        $days = max(1, (int) $from->diffInDays($to) + 1);
        $prevTo = $from->copy()->subSecond();
        $prevFrom = $prevTo->copy()->subDays($days - 1)->startOfDay();
        $prevSent = SurveyMessage::query()->whereBetween('sent_at', [$prevFrom, $prevTo])->whereIn('status', ['sent', 'delivered', 'answered'])->count();
        $prevDlv = SurveyMessage::query()->whereBetween('sent_at', [$prevFrom, $prevTo])->whereNotNull('delivered_at')->count();
        $prevFail = SurveyMessage::query()->whereBetween('sent_at', [$prevFrom, $prevTo])->where('status', 'failed')->count();
        $prevDeliveryPct = $prevSent ? round($prevDlv / $prevSent * 100, 1) : 0.0;
        $prevMau = BotUser::query()
            ->where(function ($q) use ($prevTo) {
                $q->whereBetween('last_seen_at', [$prevTo->copy()->subDays(30)->startOfDay(), $prevTo])
                    ->orWhereBetween('last_home_at', [$prevTo->copy()->subDays(30)->startOfDay(), $prevTo]);
            })
            ->count();
        $deliveryPct = $surveysSent ? round($dlv / $surveysSent * 100, 1) : 0.0;

        $pctDelta = function (float $cur, float $prev): float {
            if ($prev <= 0) {
                return $cur > 0 ? 100.0 : 0.0;
            }

            return round(($cur - $prev) / $prev * 100, 1);
        };

        return [
            'starts' => $started,
            'phones' => $phoneOk,
            'phone_drop' => $phoneDrop,
            'consent_yes' => $consentYes,
            'consent_no' => $consentNo,
            'home' => $homeOpens,
            'home_users' => BotUser::query()->whereBetween('last_home_at', [$from, $to])->count(),
            'blocked' => BotUser::query()->where('is_blocked', true)->count(),
            'errors' => $fail,
            'sent' => $surveysSent,
            'dlv' => $dlv,
            'fail' => $fail,
            'delivery_pct' => $deliveryPct,
            'mau' => $mau,
            'by_stage' => $byStage,
            'deltas' => [
                'sent' => ['value' => $pctDelta((float) $surveysSent, (float) $prevSent), 'unit' => '%'],
                'delivery_pct' => ['value' => round($deliveryPct - $prevDeliveryPct, 1), 'unit' => 'pp'],
                'fail' => ['value' => $fail - $prevFail, 'unit' => null],
                'mau' => ['value' => $pctDelta((float) $mau, (float) $prevMau), 'unit' => '%'],
            ],
        ];
    }

    /**
     * Audit jurnali — brak holat oʻzgarishlari + Billz sync loglari.
     *
     * @return list<array{at:string,who:string,action:string,ts:int}>
     */
    protected function auditLogRows(int $limit = 25): array
    {
        $statusUz = [
            'new' => 'Yangi',
            'review' => 'Koʻrib chiqilmoqda',
            'approved' => 'Tasdiqlangan',
            'rejected' => 'Rad etilgan',
            'replaced' => 'Almashtirildi',
        ];
        $jobUz = [
            'sync-sales' => 'Billz sotuvlar sinxroni',
            'sync-shops' => 'Billz doʻkonlar sinxroni',
            'sync-products' => 'Billz mahsulotlar sinxroni',
            'sync-clients' => 'Billz mijozlar sinxroni',
        ];

        $claimRows = ClaimStatusLog::query()
            ->with(['user', 'claim'])
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(function (ClaimStatusLog $log) use ($statusUz) {
                $who = $log->user?->name ?: 'Tizim';
                $num = $log->claim?->number ?: '#'.$log->claim_id;
                $from = $statusUz[$log->from_status] ?? ($log->from_status ?: '—');
                $to = $statusUz[$log->to_status] ?? $log->to_status;
                $note = $log->note ? ' · '.$log->note : '';

                return [
                    'at' => optional($log->created_at)->format('d.m.Y H:i') ?: '—',
                    'who' => $who,
                    'action' => "Brak {$num}: {$from} → {$to}{$note}",
                    'ts' => optional($log->created_at)->getTimestamp() ?: 0,
                ];
            });

        $syncRows = SyncLog::query()
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(function (SyncLog $log) use ($jobUz) {
                $job = $jobUz[$log->job] ?? $log->job;
                $st = match ($log->status) {
                    'success' => 'OK',
                    'failed' => 'XATO',
                    'running' => 'jarayonda',
                    default => $log->status,
                };
                $msg = $log->message && $log->message !== 'OK' ? ' · '.$log->message : '';

                return [
                    'at' => optional($log->finished_at ?? $log->started_at ?? $log->created_at)->format('d.m.Y H:i') ?: '—',
                    'who' => 'Billz sync',
                    'action' => "{$job}: {$st} ({$log->records} yozuv){$msg}",
                    'ts' => optional($log->finished_at ?? $log->started_at ?? $log->created_at)->getTimestamp() ?: 0,
                ];
            });

        return $claimRows->concat($syncRows)
            ->sortByDesc('ts')
            ->take($limit)
            ->values()
            ->map(fn (array $r) => [
                'at' => $r['at'],
                'who' => $r['who'],
                'action' => $r['action'],
            ])
            ->all();
    }

    protected function funnel(Carbon $from, Carbon $to, ?int $shopId = null): array
    {
        $orderScope = fn ($q) => $q->whereBetween('sold_at', [$from, $to])
            ->where('sync_error', false)
            ->when($shopId, fn ($qq) => $qq->where('shop_id', $shopId));

        $cheks = Order::query()->whereBetween('sold_at', [$from, $to])->where('sync_error', false)
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->count();

        $scanned = \App\Models\QrToken::query()
            ->whereNotNull('scanned_at')
            ->whereHas('order', $orderScope)
            ->count();

        // Bot bosqichlari kompaniya boʼyicha (doʼkonga bogʼlanmagan)
        $starts = BotUser::query()->whereBetween('started_at', [$from, $to])->count();
        $consent = BotUser::query()->whereBetween('consent_answered_at', [$from, $to])->count();
        $phones = BotUser::query()->whereBetween('phone_verified_at', [$from, $to])->count();
        $homes = BotUser::query()->whereBetween('last_home_at', [$from, $to])->count();
        if ($homes === 0) {
            $homes = Activation::query()
                ->whereBetween('activated_at', [$from, $to])
                ->whereNotNull('bot_user_id')
                ->when($shopId, fn ($q) => $q->whereHas('order', fn ($o) => $o->where('shop_id', $shopId)))
                ->distinct()
                ->count('bot_user_id');
        }

        return [
            'cheks' => $cheks,
            'qr_scanned' => $scanned,
            'starts' => $starts,
            'consent' => $consent,
            'phones' => $phones,
            'homes' => $homes,
        ];
    }

    /**
     * Panel «Mijozlar bazasi» qatori — faqat bot orqali faollashtirilgan cheklar.
     */
    protected function panelCustomerRow(Customer $c): array
    {
        $orderIds = $this->botCustomerOrderIds($c);

        $orders = $orderIds->isEmpty()
            ? collect()
            : Order::query()
                ->with(['items', 'seller', 'shop'])
                ->whereIn('id', $orderIds)
                ->orderByDesc('sold_at')
                ->get();

        $x = $orders->count();
        $sum = (float) $orders->sum('total_price');
        $last = $orders->first();
        $days = $last?->sold_at ? $last->sold_at->diffInDays(now()) : 999;
        $seg = $x <= 1 ? 'Yangi' : ($days > 90 ? 'Uxlagan' : 'Doimiy');
        $claimsN = (int) ($c->claims_count ?? $c->claims()->count());

        $buys = $orders->take(50)->map(function (Order $o) {
            $lines = $o->items->map(function ($i) {
                $qty = (float) $i->quantity;
                $name = $i->name ?: 'Mahsulot';

                return [
                    'n' => $name,
                    'q' => $qty > 0 ? $qty : 1,
                ];
            })->values()->all();

            return [
                'd' => optional($o->sold_at)->format('d.m.Y') ?: '—',
                'st' => (string) ($o->shop_id ?? ''),
                'chek' => $o->order_number ?: (string) $o->id,
                'lines' => $lines,
                'sum' => round(((float) $o->total_price) / 1_000_000, 2),
                'seller' => $o->seller?->name ?: '—',
            ];
        })->values()->all();

        $t1 = SurveyMessage::query()
            ->where('stage', 'T1')
            ->where(function ($q) use ($c) {
                $q->where('customer_id', $c->id)
                    ->orWhereIn('bot_user_id', BotUser::query()->where('customer_id', $c->id)->select('id'));
            })
            ->latest('sent_at')
            ->latest('id')
            ->first();
        $t2 = SurveyMessage::query()
            ->where('stage', 'T2')
            ->where(function ($q) use ($c) {
                $q->where('customer_id', $c->id)
                    ->orWhereIn('bot_user_id', BotUser::query()->where('customer_id', $c->id)->select('id'));
            })
            ->latest('sent_at')
            ->latest('id')
            ->first();

        return [
            'id' => $c->id,
            'n' => $c->name ?: 'Mijoz',
            'ph' => $c->phone ?: '—',
            'reg' => optional($c->registered_at)->format('d.m.Y') ?: '—',
            'x' => $x,
            'sum' => round($sum / 1_000_000, 2),
            'last' => optional($last?->sold_at)->format('d.m.Y') ?: '—',
            'lastSt' => (string) ($last?->shop_id ?? ''),
            'kaf' => Activation::query()->where('customer_id', $c->id)->where('warranty_ends_at', '>=', now())->count(),
            'claims' => $claimsN,
            'seg' => $seg,
            'consent' => (bool) $c->marketing_consent,
            'buys' => $buys,
            't1' => $this->surveyStageLabel($t1),
            't2' => $this->surveyStageLabel($t2, true),
        ];
    }

    /**
     * Drawer SMS qatori — HTML dagi iboralar, real holat boʻyicha.
     */
    protected function surveyStageLabel(?SurveyMessage $s, bool $defaultPlanned = false): string
    {
        if (! $s) {
            return $defaultPlanned ? 'rejalashtirilgan' : '—';
        }
        if (($s->status === 'scheduled' || ! $s->sent_at) && ! $s->answered_at) {
            return 'rejalashtirilgan';
        }
        if ($s->answer === 'good') {
            return 'yuborildi · javob: «yaxshi»';
        }
        if ($s->answer === 'problem') {
            return 'javob: «muammo bor» → soʼrov ochildi';
        }
        if ($s->sent_at || in_array($s->status, ['sent', 'delivered', 'failed'], true)) {
            return 'javob yoʼq';
        }

        return $defaultPlanned ? 'rejalashtirilgan' : '—';
    }

    protected function customerStats(Carbon $from, Carbon $to, array $customers): array
    {
        $total = Customer::query()->fromBot()->count();
        $newInPeriod = Customer::query()->fromBot()->whereBetween('registered_at', [$from, $to])->count();
        $col = collect($customers);
        $y = $col->where('seg', 'Yangi')->count();
        $d = $col->where('seg', 'Doimiy')->count();
        $u = $col->where('seg', 'Uxlagan')->count();
        $withOrders = $col->where('x', '>', 0);
        $avgPurchase = $withOrders->count()
            ? (int) round($withOrders->avg(fn ($c) => ($c['sum'] ?? 0) * 1000))
            : 0;
        $repeatPct = $col->count()
            ? round(($d + $u) / max(1, $col->count()) * 100, 1)
            : 0;

        $monthNames = ['Yan', 'Fev', 'Mar', 'Apr', 'May', 'Iyn', 'Iyl', 'Avg', 'Sen', 'Okt', 'Noy', 'Dek'];
        $months = [];
        $monthlyNew = [];
        for ($i = 8; $i >= 0; $i--) {
            $start = now()->startOfMonth()->subMonths($i);
            $end = $start->copy()->endOfMonth();
            $months[] = $monthNames[(int) $start->format('n') - 1];
            $monthlyNew[] = Customer::query()->fromBot()->whereBetween('registered_at', [$start, $end])->count();
        }

        // Oldingi shu uzunlikdagi davr — KPI pastidagi ↑/↓ foizlar
        $days = max(1, (int) $from->diffInDays($to) + 1);
        $prevTo = $from->copy()->subSecond();
        $prevFrom = $prevTo->copy()->subDays($days - 1)->startOfDay();
        $newPrev = Customer::query()->fromBot()->whereBetween('registered_at', [$prevFrom, $prevTo])->count();
        $prevSnap = $this->customerMetricsAsOf($prevTo);

        $pctDelta = function (float $cur, float $prev): float {
            if ($prev <= 0) {
                return $cur > 0 ? 100.0 : 0.0;
            }

            return round(($cur - $prev) / $prev * 100, 1);
        };

        return [
            'total' => $total,
            'new' => $newInPeriod,
            'repeat_pct' => $repeatPct,
            'avg_purchase_ming' => $avgPurchase,
            'new_delta' => $pctDelta((float) $newInPeriod, (float) $newPrev),
            'repeat_delta' => round($repeatPct - (float) $prevSnap['repeat_pct'], 1),
            'avg_delta' => $pctDelta((float) $avgPurchase, (float) $prevSnap['avg_purchase_ming']),
            'seg' => ['yangi' => $y, 'doimiy' => $d, 'uxlagan' => $u],
            'months' => $months,
            'monthly_new' => $monthlyNew,
            'consent_yes' => Customer::query()->fromBot()->where('marketing_consent', true)->count(),
            'consent_no' => Customer::query()->fromBot()->where('marketing_consent', false)->count(),
        ];
    }

    /**
     * Berilgan sanagacha bot mijozlari boʻyicha takroriy % va oʻrtacha xarid (ming).
     *
     * @return array{repeat_pct:float, avg_purchase_ming:int}
     */
    protected function customerMetricsAsOf(Carbon $asOf): array
    {
        $rows = Customer::query()->fromBot()
            ->where(function ($q) use ($asOf) {
                $q->whereNull('registered_at')->orWhere('registered_at', '<=', $asOf);
            })
            ->get();

        if ($rows->isEmpty()) {
            return ['repeat_pct' => 0.0, 'avg_purchase_ming' => 0];
        }

        $doimiy = 0;
        $uxlagan = 0;
        $spendMing = [];

        foreach ($rows as $c) {
            $orderIds = $this->botCustomerOrderIds($c);

            $orders = $orderIds->isEmpty()
                ? collect()
                : Order::query()
                    ->whereIn('id', $orderIds)
                    ->where('sold_at', '<=', $asOf)
                    ->orderByDesc('sold_at')
                    ->get();

            $x = $orders->count();
            if ($x > 0) {
                $spendMing[] = ((float) $orders->sum('total_price')) / 1000;
            }
            $last = $orders->first();
            $days = $last?->sold_at ? $last->sold_at->diffInDays($asOf) : 999;
            if ($x <= 1) {
                // Yangi
            } elseif ($days > 90) {
                $uxlagan++;
            } else {
                $doimiy++;
            }
        }

        $n = $rows->count();

        return [
            'repeat_pct' => $n ? round(($doimiy + $uxlagan) / $n * 100, 1) : 0.0,
            'avg_purchase_ming' => $spendMing === [] ? 0 : (int) round(array_sum($spendMing) / count($spendMing)),
        ];
    }

    /**
     * Bot mijozining faqat bot orqali faollashtirilgan chek ID lari (Billz customer_id emas).
     *
     * @return \Illuminate\Support\Collection<int, int|string>
     */
    protected function botCustomerOrderIds(Customer $c)
    {
        $botUserIds = BotUser::query()->where('customer_id', $c->id)->pluck('id');

        return Activation::query()
            ->whereNotNull('order_id')
            ->where(function ($q) use ($c, $botUserIds) {
                $q->where('customer_id', $c->id);
                if ($botUserIds->isNotEmpty()) {
                    $q->orWhereIn('bot_user_id', $botUserIds);
                }
            })
            ->pluck('order_id')
            ->unique()
            ->filter()
            ->values();
    }
}
