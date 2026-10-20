<?php

namespace App\Http\Controllers;

use App\Models\Activation;
use App\Models\Claim;
use App\Services\ClaimWorkflowService;
use App\Services\Telegram\TelegramBotService;
use App\Services\Telegram\TelegramWebAppAuth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MiniAppController extends Controller
{
    public const NO_REASONS = [
        'Mahsulot olib kelinmadi',
        'Boshqa mahsulot olib kelindi',
        'Nuqsonli mahsulot olib kelindi',
        'Kuryer kelmadi',
        'Boshqa',
    ];

    public function show(): View
    {
        return view('miniapp.index', [
            'reasons' => ClaimFormController::REASONS,
            'noReasons' => self::NO_REASONS,
            'statuses' => [
                'new' => 'Yuborildi',
                'review' => 'Ko‘rib chiqilmoqda',
                'approved' => 'Tasdiqlandi',
                'assigned' => 'Kuryer belgilandi',
                'en_route' => 'Kuryer yo‘lda',
                'delivered' => 'Kuryer yetkazdi',
                'done' => 'Yakunlandi',
                'rejected' => 'Rad etilgan',
                'dispute' => 'Nizo',
                'unconfirmed' => 'Tasdiqsiz yopildi',
            ],
        ]);
    }

    public function boot(Request $request, TelegramWebAppAuth $auth, TelegramBotService $bot): JsonResponse
    {
        $botUser = $auth->botUserFromRequest(
            $request->input('initData'),
            $request->input('t') ?: $request->input('token')
        );
        $botUser->loadMissing('customer');
        $botUser->forceFill([
            'home_opens' => ((int) $botUser->home_opens) + 1,
            'last_home_at' => now(),
        ])->save();

        $name = trim(($botUser->first_name ?? '').' '.($botUser->last_name ?? ''))
            ?: ($botUser->customer?->name ?? 'Mijoz');
        $phone = $botUser->phone ?: ($botUser->customer?->phone ?? '—');

        $activationRows = Activation::query()
            ->with(['orderItem', 'order.shop'])
            ->where(function ($q) use ($botUser) {
                $q->where('bot_user_id', $botUser->id);
                if ($botUser->customer_id) {
                    $q->orWhere('customer_id', $botUser->customer_id);
                }
            })
            ->latest('activated_at')
            ->limit(80)
            ->get();

        $activations = $activationRows->map(function (Activation $a) {
            $item = $a->orderItem;
            $months = (int) ($item?->warranty_months ?: 12);
            $until = $a->warranty_ends_at
                ?: ($a->activated_at?->copy()->addMonths($months));

            return [
                'id' => $a->id,
                'order_id' => $a->order_id,
                'product' => $item?->name ?: ($item?->product_name ?: 'Mahsulot'),
                'qty' => (int) ($item?->quantity ?: 1),
                'chek' => $a->order?->order_number ?? '—',
                'store' => $a->order?->shop?->name ?? '—',
                'date' => $a->activated_at?->format('d.m.Y') ?? '—',
                'warranty' => $until ? ('kafolat '.$until->format('d.m.Y').' gacha') : '—',
                'sum' => (float) ($item?->total_price ?: $item?->price ?: 0),
            ];
        })->values()->all();

        // Bitta chek = bitta guruh (order_id / chek raqami)
        $cheks = $activationRows
            ->groupBy(fn (Activation $a) => (string) ($a->order_id ?: ($a->order?->order_number ?: 'a'.$a->id)))
            ->map(function ($group) {
                /** @var \Illuminate\Support\Collection<int, Activation> $group */
                $first = $group->sortByDesc(fn (Activation $a) => $a->activated_at?->timestamp ?? 0)->first();
                $items = $group->map(function (Activation $a) {
                    $item = $a->orderItem;
                    $months = (int) ($item?->warranty_months ?: 12);
                    $until = $a->warranty_ends_at
                        ?: ($a->activated_at?->copy()->addMonths($months));

                    return [
                        'id' => $a->id,
                        'product' => $item?->name ?: ($item?->product_name ?: 'Mahsulot'),
                        'qty' => (int) ($item?->quantity ?: 1),
                        'warranty' => $until ? ('kafolat '.$until->format('d.m.Y').' gacha') : '—',
                        'sum' => (float) ($item?->total_price ?: $item?->price ?: 0),
                    ];
                })->values()->all();
                $sum = array_sum(array_column($items, 'sum'));

                return [
                    'key' => (string) ($first->order_id ?: ($first->order?->order_number ?: 'a'.$first->id)),
                    'chek' => $first->order?->order_number ?? '—',
                    'store' => $first->order?->shop?->name ?? '—',
                    'date' => $first->activated_at?->format('d.m.Y') ?? '—',
                    'sort' => $first->activated_at?->timestamp ?? 0,
                    'items_n' => count($items),
                    'sum' => $sum,
                    'items' => $items,
                ];
            })
            ->sortByDesc('sort')
            ->map(function (array $c) {
                unset($c['sort']);

                return $c;
            })
            ->values()
            ->all();

        $claims = Claim::query()
            ->with(['courier', 'logs', 'items.orderItem', 'product', 'orderItem'])
            ->where('bot_user_id', $botUser->id)
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn (Claim $c) => $this->claimPayload($c))
            ->all();

        $products = Activation::query()
            ->with(['orderItem', 'order'])
            ->where(function ($q) use ($botUser) {
                $q->where('bot_user_id', $botUser->id);
                if ($botUser->customer_id) {
                    $q->orWhere('customer_id', $botUser->customer_id);
                }
            })
            ->latest('activated_at')
            ->limit(50)
            ->get()
            ->map(function (Activation $a) {
                $title = $a->orderItem?->name ?? 'Mahsulot';
                $chek = $a->order?->order_number ?? '';
                $qty = max(1, (int) round((float) ($a->orderItem?->quantity ?: 1)));
                $until = $a->warranty_ends_at?->format('d.m.Y')
                    ?? ($a->activated_at
                        ? $a->activated_at->copy()->addMonths((int) ($a->orderItem?->warranty_months ?: 12))->format('d.m.Y')
                        : null);

                return [
                    'id' => $a->id,
                    'title' => $title,
                    'qty' => $qty,
                    'chek' => $chek,
                    'order_id' => $a->order_id,
                    'sub' => implode(' · ', array_filter([
                        $chek !== '' ? 'chek '.$chek : null,
                        '×'.$qty,
                        $until ? 'kafolat '.$until : null,
                    ])),
                    'search' => mb_strtolower($title.' '.$chek.' '.($a->orderItem?->sku ?? ''), 'UTF-8'),
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'ok' => true,
            'user' => [
                'name' => $name,
                'short' => mb_substr($name, 0, 18),
                'phone' => $phone,
            ],
            'activations' => $activations,
            'cheks' => $cheks,
            'products' => $products,
            'reasons' => ClaimFormController::REASONS,
            'claims' => $claims,
            'form_url' => $bot->claimFormUrl($botUser),
            'no_reasons' => self::NO_REASONS,
        ]);
    }

    public function submitClaim(
        Request $request,
        TelegramWebAppAuth $auth,
        TelegramBotService $bot,
        ClaimFormController $forms
    ): JsonResponse {
        $botUser = $auth->botUserFromRequest(
            $request->input('initData'),
            $request->input('t') ?: $request->input('token')
        );

        try {
            $result = $forms->createClaimFromRequest(
                $request,
                $botUser,
                $bot,
                'Mini App orqali ochildi'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            $msg = collect($e->errors())->flatten()->first() ?: 'Tekshiruv xatosi';

            return response()->json(['message' => $msg, 'errors' => $e->errors()], 422);
        }

        $claim = $result['claim'];

        return response()->json([
            'ok' => true,
            'duplicate' => $result['duplicate'],
            'claim' => $this->claimPayload(
                $claim->relationLoaded('logs')
                    ? $claim
                    : $claim->fresh(['courier', 'logs', 'items.orderItem', 'product', 'orderItem'])
            ),
        ]);
    }

    public function confirm(
        Request $request,
        Claim $claim,
        TelegramWebAppAuth $auth,
        ClaimWorkflowService $workflow
    ): JsonResponse {
        $botUser = $auth->botUserFromRequest(
            $request->input('initData'),
            $request->input('t') ?: $request->input('token')
        );
        if ((int) $claim->bot_user_id !== (int) $botUser->id) {
            return response()->json(['message' => 'Bu so‘rov sizniki emas.'], 403);
        }

        $data = $request->validate([
            'answer' => 'required|in:yes,no',
            'reason' => 'nullable|string|max:200',
        ]);

        if ($data['answer'] === 'yes') {
            $claim = $workflow->customerConfirm($claim, true);
        } else {
            $reason = trim((string) ($data['reason'] ?? ''));
            if ($reason === '') {
                return response()->json(['message' => 'Sabab majburiy.'], 422);
            }
            $claim = $workflow->customerConfirm($claim, false, $reason);
        }

        return response()->json([
            'ok' => true,
            'claim' => $this->claimPayload($claim->fresh(['courier', 'logs', 'items.orderItem', 'product', 'orderItem'])),
        ]);
    }

    /** @return array<string, mixed> */
    protected function claimPayload(Claim $c): array
    {
        $logs = $c->relationLoaded('logs')
            ? $c->logs->sortBy('id')->values()->map(fn ($l) => [
                'from' => $l->from_status,
                'to' => $l->to_status,
                'note' => $l->note,
                'at' => $l->created_at?->toIso8601String(),
            ])->all()
            : [];

        $itemQty = $c->relationLoaded('items') && $c->items->isNotEmpty()
            ? (int) $c->items->sum(fn ($i) => max(1, (int) ($i->qty ?: round((float) ($i->orderItem?->quantity ?: 1)))))
            : max(1, (int) ($c->replacement_qty ?: 1));

        return [
            'id' => $c->id,
            'number' => $c->number,
            'status' => $c->status,
            'prod' => $c->productSummary(),
            'qty' => $itemQty,
            'reason' => $c->reason ?? '',
            'date' => $c->created_at?->toIso8601String(),
            'confirm' => $c->customer_confirm,
            'confirm_reason' => $c->confirm_reason,
            'reject_reason' => $c->reject_reason,
            'courier' => $c->courier?->name,
            'courier_phone' => $c->courier?->phone,
            'deadline' => $c->courier_deadline_at?->toIso8601String(),
            'assigned_at' => $c->assigned_at?->toIso8601String(),
            'en_route_at' => $c->en_route_at?->toIso8601String(),
            'delivered_at' => $c->delivered_at?->toIso8601String(),
            'logs' => $logs,
        ];
    }
}
