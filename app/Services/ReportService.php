<?php

namespace App\Services;

use App\Models\Activation;
use App\Models\BotUser;
use App\Models\Claim;
use App\Models\Customer;
use App\Models\IntegrationSetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\QrToken;
use App\Models\Seller;
use App\Models\Shop;
use App\Models\SurveyMessage;
use App\Models\SyncLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function period(int $days = 30): array
    {
        $to = now()->endOfDay();
        $from = now()->subDays($days - 1)->startOfDay();
        $prevTo = $from->copy()->subSecond();
        $prevFrom = $prevTo->copy()->subDays($days - 1)->startOfDay();

        return compact('from', 'to', 'prevFrom', 'prevTo', 'days');
    }

    public function filtersFromRequest(array $input): array
    {
        return [
            'period' => (int) ($input['period'] ?? 30),
            'shop_id' => ($input['shop_id'] ?? 'all') === 'all' ? null : (int) $input['shop_id'],
            'seller_id' => ($input['seller_id'] ?? 'all') === 'all' ? null : (int) $input['seller_id'],
            'category' => ($input['category'] ?? 'all') === 'all' ? null : $input['category'],
        ];
    }

    protected function orderQuery(Carbon $from, Carbon $to, array $filters): Builder
    {
        $q = Order::query()->whereBetween('sold_at', [$from, $to])->where('sync_error', false);
        if ($filters['shop_id']) {
            $q->where('shop_id', $filters['shop_id']);
        }
        if ($filters['seller_id']) {
            $q->where('seller_id', $filters['seller_id']);
        }

        return $q;
    }

    public function dashboard(array $filters): array
    {
        $p = $this->period($filters['period']);
        $orders = $this->orderQuery($p['from'], $p['to'], $filters);
        $cheks = (clone $orders)->count();
        $sum = (float) (clone $orders)->sum('total_price');
        $orderIds = (clone $orders)->pluck('id');

        $activated = Activation::query()->whereIn('order_id', $orderIds)->whereNotNull('order_id')->distinct()->count('order_id');
        $akt = $cheks > 0 ? round($activated / $cheks * 100, 1) : 0;

        $activeWarranties = Activation::query()->where('warranty_ends_at', '>=', now())->count();
        $surveysSent = SurveyMessage::query()->whereBetween('sent_at', [$p['from'], $p['to']])->whereIn('status', ['sent', 'delivered', 'answered'])->count();
        $surveysAnswered = SurveyMessage::query()->whereBetween('answered_at', [$p['from'], $p['to']])->where('status', 'answered')->count();
        $surveyResp = $surveysSent > 0 ? round($surveysAnswered / $surveysSent * 100, 1) : 0;
        $good = SurveyMessage::query()->whereBetween('answered_at', [$p['from'], $p['to']])->where('answer', 'good')->count();
        $sat = $surveysAnswered > 0 ? round($good / $surveysAnswered * 100, 1) : 0;

        $units = (float) OrderItem::query()->whereIn('order_id', $orderIds)->sum('quantity');
        $approvedClaims = Claim::query()->whereIn('status', ['approved', 'done', 'assigned', 'en_route', 'delivered'])
            ->whereBetween('created_at', [$p['from'], $p['to']])->count();
        $brak = $units > 0 ? round($approvedClaims / $units * 100, 2) : 0;

        $openClaims = Claim::query()->whereNotIn('status', Claim::CLOSED)->count();
        $closed = Claim::query()->whereNotNull('closed_at')->whereBetween('closed_at', [$p['from'], $p['to']])->get();
        $avgClose = $closed->count()
            ? round($closed->avg(fn ($c) => $c->created_at->diffInDays($c->closed_at)), 1)
            : 0;

        $series = Order::query()
            ->select(DB::raw('date(sold_at) as d'), DB::raw('count(*) as cheks'))
            ->whereBetween('sold_at', [$p['from'], $p['to']])
            ->when($filters['shop_id'], fn ($q) => $q->where('shop_id', $filters['shop_id']))
            ->groupBy('d')
            ->orderBy('d')
            ->get();

        $aktSeries = Activation::query()
            ->select(DB::raw('date(activated_at) as d'), DB::raw('count(*) as n'))
            ->whereBetween('activated_at', [$p['from'], $p['to']])
            ->groupBy('d')
            ->pluck('n', 'd');

        $chart = $series->map(fn ($r) => [
            'label' => Carbon::parse($r->d)->format('d.m'),
            'cheks' => (int) $r->cheks,
            'akt' => (int) ($aktSeries[$r->d] ?? 0),
        ])->values();

        $topBrak = Claim::query()
            ->select('order_items.name', DB::raw('count(*) as c'))
            ->leftJoin('order_items', 'order_items.id', '=', 'claims.order_item_id')
            ->whereBetween('claims.created_at', [$p['from'], $p['to']])
            ->whereIn('claims.status', ['approved', 'done', 'assigned', 'en_route', 'delivered', 'review', 'new'])
            ->groupBy('order_items.name')
            ->orderByDesc('c')
            ->limit(5)
            ->get()
            ->map(fn ($r) => ['name' => $r->name ?: 'Noma\'lum', 'count' => (int) $r->c]);

        $slaClaims = Claim::query()
            ->with(['customer', 'shop'])
            ->whereNotIn('status', Claim::CLOSED)
            ->where('created_at', '<=', now()->subDays(7))
            ->orderBy('created_at')
            ->limit(20)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->number,
                'days' => $c->daysOpen(),
                'customer' => $c->customer?->name,
                'shop' => $c->shop?->name,
                'status' => $c->status,
            ]);

        return [
            'kpis' => [
                'cheks' => $cheks,
                'sum' => $sum,
                'akt' => $akt,
                'warranties' => $activeWarranties,
                'survey_resp' => $surveyResp,
                'sat' => $sat,
                'brak' => $brak,
                'open_claims' => $openClaims,
                'avg_close' => $avgClose,
            ],
            'chart' => $chart,
            'top_brak' => $topBrak,
            'sla_claims' => $slaClaims,
            'updated_at' => IntegrationSetting::getValue('billz_last_sales_sync')
                ?: SyncLog::query()->where('status', 'success')->latest()->value('finished_at'),
        ];
    }

    public function salesFunnel(array $filters): array
    {
        $p = $this->period($filters['period']);
        $orders = $this->orderQuery($p['from'], $p['to'], $filters);
        $orderIds = (clone $orders)->pluck('id');
        $cheks = $orderIds->count();
        $scanned = QrToken::query()->whereIn('order_id', $orderIds)->whereNotNull('scanned_at')->distinct('order_id')->count('order_id');
        $botStarts = Activation::query()->whereIn('order_id', $orderIds)->whereNotNull('bot_user_id')->count();
        $phoneOk = Activation::query()->whereIn('order_id', $orderIds)
            ->whereHas('botUser', fn ($q) => $q->whereNotNull('phone_verified_at'))->count();
        $home = $phoneOk;

        $stores = Shop::query()->withCount([
            'orders as cheks' => fn ($q) => $q->whereBetween('sold_at', [$p['from'], $p['to']]),
        ])->get()->map(function (Shop $s) use ($p) {
            $oids = Order::query()->where('shop_id', $s->id)->whereBetween('sold_at', [$p['from'], $p['to']])->pluck('id');
            $cheksN = $oids->count();
            $aktOrders = Activation::query()->whereIn('order_id', $oids)->whereNotNull('order_id')->distinct()->count('order_id');
            $instOrders = $cheksN ? Activation::query()
                ->join('orders', 'orders.id', '=', 'activations.order_id')
                ->whereIn('activations.order_id', $oids)
                ->whereNotNull('orders.sold_at')
                ->whereNotNull('activations.activated_at')
                ->whereRaw('activations.activated_at >= orders.sold_at')
                ->whereRaw('activations.activated_at <= DATE_ADD(orders.sold_at, INTERVAL 30 MINUTE)')
                ->distinct()
                ->count('activations.order_id') : 0;

            return [
                'id' => $s->id,
                'name' => $s->name,
                'cheks' => $cheksN,
                'akt' => $cheksN ? round($aktOrders / $cheksN * 100, 1) : 0,
                'inst' => $cheksN ? round($instOrders / $cheksN * 100, 1) : 0,
            ];
        });

        return [
            'funnel' => [
                ['name' => 'Billz chek', 'n' => $cheks, 'pct' => 100],
                ['name' => 'QR skanerlandi', 'n' => $scanned, 'pct' => $cheks ? round($scanned / $cheks * 100, 1) : 0],
                ['name' => 'Bot /start', 'n' => $botStarts, 'pct' => $cheks ? round($botStarts / $cheks * 100, 1) : 0],
                ['name' => 'Telefon tasdiqlandi', 'n' => $phoneOk, 'pct' => $cheks ? round($phoneOk / $cheks * 100, 1) : 0],
                ['name' => 'Mening uyim', 'n' => $home, 'pct' => $cheks ? round($home / $cheks * 100, 1) : 0],
            ],
            'stores' => $stores,
        ];
    }

    public function customers(array $filters, ?string $search = null): array
    {
        $q = Customer::query()->fromBot()->withCount('orders')->withSum('orders', 'total_price');
        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        $rows = $q->orderByDesc('orders_count')->limit(200)->get()->map(function (Customer $c) {
            $last = $c->orders()->latest('sold_at')->first();
            $days = $last?->sold_at ? $last->sold_at->diffInDays(now()) : 999;
            $seg = $c->orders_count <= 1 ? 'Yangi' : ($days > 90 ? 'Uxlagan' : 'Doimiy');

            return [
                'id' => $c->id,
                'name' => $c->name,
                'phone' => $c->phone,
                'orders' => $c->orders_count,
                'sum' => (float) $c->orders_sum_total_price,
                'last' => optional($last?->sold_at)->format('d.m.Y'),
                'warranties' => Activation::query()->where('customer_id', $c->id)->where('warranty_ends_at', '>=', now())->count(),
                'claims' => $c->claims()->count(),
                'segment' => $seg,
                'registered' => optional($c->registered_at)->format('d.m.Y'),
            ];
        });

        return ['customers' => $rows];
    }

    public function surveys(array $filters): array
    {
        $p = $this->period($filters['period']);
        $stages = [];
        foreach (['T1' => 7, 'T2' => 30, 'T3' => 90, 'T4' => 0] as $stage => $days) {
            $base = SurveyMessage::query()->where('stage', $stage)->whereBetween('sent_at', [$p['from'], $p['to']]);
            $sent = (clone $base)->count();
            $dlv = (clone $base)->whereNotNull('delivered_at')->count();
            $ans = (clone $base)->where('status', 'answered')->count();
            $good = (clone $base)->where('answer', 'good')->count();
            $bad = (clone $base)->where('answer', 'problem')->count();
            $claims = Claim::query()->whereBetween('created_at', [$p['from'], $p['to']])->count();
            $stages[] = [
                'stage' => $stage,
                'days' => $days,
                'sent' => $sent,
                'delivered' => $dlv,
                'answered' => $ans,
                'good' => $good,
                'bad' => $bad,
                'resp_pct' => $sent ? round($ans / $sent * 100, 1) : 0,
                'good_pct' => $ans ? round($good / $ans * 100, 1) : 0,
            ];
        }

        $comments = SurveyMessage::query()
            ->with('customer')
            ->whereNotNull('comment')
            ->latest('answered_at')
            ->limit(50)
            ->get()
            ->map(fn ($s) => [
                'customer' => $s->customer?->name,
                'stage' => $s->stage,
                'answer' => $s->answer,
                'comment' => $s->comment,
            ]);

        return ['stages' => $stages, 'comments' => $comments];
    }

    public function claims(array $filters, ?string $status = null): array
    {
        $q = Claim::query()->with(['customer', 'shop', 'seller', 'product', 'order', 'orderItem', 'assignee', 'logs', 'items.orderItem', 'items.product']);
        if ($filters['shop_id']) {
            $q->where('shop_id', $filters['shop_id']);
        }
        if ($status && $status !== 'all') {
            $q->where('status', $status);
        }
        $rows = $q->latest()->limit(200)->get()->map(fn (Claim $c) => [
            'id' => $c->id,
            'number' => $c->number,
            'date' => $c->created_at->format('d.m.Y'),
            'customer' => $c->customer?->name,
            'phone' => $c->customer?->phone,
            'product' => $c->productSummary(),
            'brand' => $c->product?->brand,
            'serial' => $c->orderItem?->serial_number,
            'shop' => $c->shop?->name,
            'seller' => $c->seller?->name,
            'chek' => $c->order?->order_number,
            'reason' => $c->reason,
            'text' => $c->description,
            'status' => $c->status,
            'assignee' => $c->assignee?->name,
            'days' => $c->daysOpen(),
            'sla' => $c->isOpen() && $c->daysOpen() >= 7,
            'cost' => $c->replacement_cost,
            'reject' => $c->reject_reason,
            'photos' => count($c->photos ?? []),
            'closed' => optional($c->closed_at)->format('d.m.Y'),
        ]);

        $counts = [
            'all' => Claim::count(),
            'new' => Claim::where('status', 'new')->count(),
            'review' => Claim::where('status', 'review')->count(),
            'approved' => Claim::where('status', 'approved')->count(),
            'rejected' => Claim::where('status', 'rejected')->count(),
            'done' => Claim::where('status', 'done')->count(),
        ];

        return ['claims' => $rows, 'counts' => $counts];
    }

    public function quality(array $filters): array
    {
        $p = $this->period($filters['period']);
        $threshold = isset($filters['brak_threshold'])
            ? (float) $filters['brak_threshold']
            : app(PanelBootService::class)->brakThresholdPct();
        $items = OrderItem::query()
            ->select('name', 'brand', 'supplier', 'category', DB::raw('sum(quantity) as sold'))
            ->whereHas('order', fn ($q) => $q->whereBetween('sold_at', [$p['from'], $p['to']]))
            ->groupBy('name', 'brand', 'supplier', 'category')
            ->get();

        $rows = $items->map(function ($i) use ($p, $threshold) {
            $brak = (float) \App\Models\ClaimItem::query()
                ->whereHas('claim', fn ($q) => $q
                    ->whereBetween('created_at', [$p['from'], $p['to']])
                    ->whereIn('status', ['approved', 'done', 'assigned', 'en_route', 'delivered']))
                ->where(function ($q) use ($i) {
                    $q->whereHas('orderItem', fn ($oi) => $oi->where('name', $i->name))
                        ->orWhereHas('product', fn ($p) => $p->where('name', $i->name));
                })
                ->sum('qty');
            $sold = (float) $i->sold;
            $pct = $sold > 0 ? round($brak / $sold * 100, 2) : 0;

            return [
                'name' => $i->name,
                'brand' => $i->brand,
                'supplier' => $i->supplier,
                'category' => $i->category,
                'sold' => $sold,
                'brak' => $brak,
                'brak_pct' => $pct,
                'alert' => $pct >= $threshold,
            ];
        })->sortByDesc('brak_pct')->values();

        return ['products' => $rows, 'brak_threshold' => $threshold];
    }

    public function sellers(array $filters): array
    {
        $rows = collect(app(PanelBootService::class)->sellersForPeriod((int) ($filters['period'] ?? 30)))
            ->map(function (array $s) {
                $cheks = (int) ($s['cheks'] ?? 0);
                $summa = (float) ($s['summa'] ?? 0);

                return [
                    'name' => $s['n'] ?? '',
                    'shop_id' => $s['st'] ?? null,
                    'cheks' => $cheks,
                    'sum' => round($summa * 1_000_000, 0),
                    'avg' => $cheks ? round($summa * 1_000_000 / $cheks) : 0,
                    'akt' => $s['akt'] ?? 0,
                    'inst' => $s['inst'] ?? 0,
                    'rep' => $s['rep'] ?? null,
                    'rejected_pct' => $s['rad'] ?? 0,
                    'csat' => $s['csat'] ?? 0,
                ];
            })->sortByDesc('akt')->values();

        return ['sellers' => $rows];
    }

    public function system(array $filters): array
    {
        $p = $this->period($filters['period']);
        $surveys = SurveyMessage::query()->whereBetween('sent_at', [$p['from'], $p['to']]);
        $sent = (clone $surveys)->count();
        $dlv = (clone $surveys)->whereNotNull('delivered_at')->count();
        $fail = (clone $surveys)->where('status', 'failed')->count();

        $byStage = SurveyMessage::query()
            ->select('stage', DB::raw('count(*) as sent'), DB::raw("sum(case when delivered_at is not null then 1 else 0 end) as dlv"), DB::raw("sum(case when status='failed' then 1 else 0 end) as err"))
            ->whereBetween('sent_at', [$p['from'], $p['to']])
            ->groupBy('stage')
            ->get();

        $errorOrders = Order::query()->with('shop')->where('sync_error', true)->latest()->limit(20)->get()->map(fn ($o) => [
            'number' => $o->order_number,
            'shop' => $o->shop?->name,
            'message' => $o->sync_error_message,
            'at' => optional($o->sold_at)->format('d.m.Y H:i'),
        ]);

        return [
            'bot_messages' => [
                'sent' => $sent,
                'delivered' => $dlv,
                'failed' => $fail,
                'delivery_pct' => $sent ? round($dlv / $sent * 100, 1) : 0,
            ],
            'by_stage' => $byStage,
            'billz' => [
                'status' => IntegrationSetting::getValue('billz_sync_status', 'unknown'),
                'last_sales' => IntegrationSetting::getValue('billz_last_sales_sync'),
                'last_products' => IntegrationSetting::getValue('billz_last_products_sync'),
                'error' => IntegrationSetting::getValue('billz_sync_error'),
                'today_orders' => Order::query()->whereDate('created_at', today())->count(),
                'error_orders' => Order::query()->where('sync_error', true)->count(),
            ],
            'error_orders' => $errorOrders,
            'bot' => [
                'starts' => BotUser::query()->whereBetween('started_at', [$p['from'], $p['to']])->count(),
                'phone_drop' => $this->phoneDropRate($p),
                'consent_yes' => BotUser::query()->whereBetween('consent_answered_at', [$p['from'], $p['to']])->where('marketing_consent', true)->count(),
                'consent_no' => BotUser::query()->whereBetween('consent_answered_at', [$p['from'], $p['to']])->where('marketing_consent', false)->count(),
                'home_visits' => BotUser::query()->whereBetween('last_home_at', [$p['from'], $p['to']])->count()
                    ?: Activation::query()->whereBetween('activated_at', [$p['from'], $p['to']])->whereNotNull('bot_user_id')->distinct()->count('bot_user_id'),
                'blocked' => BotUser::query()->where('is_blocked', true)->count(),
            ],
            'sync_logs' => SyncLog::query()->latest()->limit(20)->get(),
        ];
    }

    protected function phoneDropRate(array $p): float
    {
        $started = BotUser::query()->whereBetween('started_at', [$p['from'], $p['to']])->count();
        $verified = BotUser::query()->whereBetween('started_at', [$p['from'], $p['to']])->whereNotNull('phone_verified_at')->count();

        return $started ? round((1 - $verified / $started) * 100, 1) : 0;
    }

    public function meta(): array
    {
        return [
            'shops' => Shop::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'sellers' => Seller::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'shop_id']),
            'categories' => OrderItem::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
        ];
    }
}
