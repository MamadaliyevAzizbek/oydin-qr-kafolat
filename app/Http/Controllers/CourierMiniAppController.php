<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Services\ClaimWorkflowService;
use App\Services\Telegram\TelegramWebAppAuth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourierMiniAppController extends Controller
{
    public function show(): View
    {
        return view('courier-app.index', [
            'statuses' => [
                'assigned' => 'Belgilandi',
                'en_route' => 'Yo‘lda',
                'delivered' => 'Yetkazildi',
                'done' => 'Yakunlandi',
                'dispute' => 'Nizo',
                'unconfirmed' => 'Tasdiqsiz',
            ],
        ]);
    }

    public function boot(Request $request, TelegramWebAppAuth $auth): JsonResponse
    {
        $courier = $auth->courierFromInitData($request->input('initData'));
        $claimId = (int) $request->input('claim_id', 0);
        if ($claimId <= 0) {
            $start = (string) $request->input('start_param', '');
            if (preg_match('/^c(\d+)$/i', $start, $m)) {
                $claimId = (int) $m[1];
            }
        }

        $open = Claim::query()
            ->with(['customer', 'botUser', 'pickupShop', 'shop', 'items.orderItem', 'items.product', 'orderItem', 'product', 'courier'])
            ->where('courier_id', $courier->id)
            ->whereIn('status', ['assigned', 'en_route', 'delivered', 'dispute'])
            ->latest('assigned_at')
            ->limit(30)
            ->get()
            ->map(fn (Claim $c) => $this->payload($c))
            ->values()
            ->all();

        $current = null;
        if ($claimId > 0) {
            $claim = Claim::query()
                ->with(['customer', 'botUser', 'pickupShop', 'shop', 'items.orderItem', 'items.product', 'orderItem', 'product', 'courier'])
                ->where('id', $claimId)
                ->where('courier_id', $courier->id)
                ->first();
            if ($claim) {
                $current = $this->payload($claim);
            }
        }

        return response()->json([
            'ok' => true,
            'courier' => [
                'id' => $courier->id,
                'name' => $courier->name,
                'short' => mb_substr($courier->name, 0, 18),
            ],
            'claim' => $current,
            'claims' => $open,
        ]);
    }

    public function action(
        Request $request,
        Claim $claim,
        TelegramWebAppAuth $auth,
        ClaimWorkflowService $workflow
    ): JsonResponse {
        $courier = $auth->courierFromInitData($request->input('initData'));
        if ((int) $claim->courier_id !== (int) $courier->id) {
            return response()->json(['message' => 'Bu topshiriq sizniki emas.'], 403);
        }

        $data = $request->validate([
            'action' => 'required|in:en_route,deliver,problem',
            'note' => 'nullable|string|max:500',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        try {
            if ($data['action'] === 'en_route') {
                $claim = $workflow->enRoute($claim, $courier, 'courier_miniapp');
            } elseif ($data['action'] === 'deliver') {
                $file = $request->file('photo');
                if (! $file && ! $claim->delivery_photo) {
                    return response()->json(['message' => 'Topshirish fotosi majburiy.'], 422);
                }
                $path = $claim->delivery_photo;
                if ($file) {
                    $path = $file->store('claims/'.$claim->id.'/delivery', 'public');
                }
                $claim = $workflow->delivered($claim, $courier, (string) $path, 'courier_miniapp');
            } else {
                $note = trim((string) ($data['note'] ?? ''));
                if ($note === '') {
                    return response()->json(['message' => 'Sabab majburiy.'], 422);
                }
                $claim = $workflow->reportProblem($claim, $courier, $note);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => collect($e->errors())->flatten()->first() ?: $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }

        $claim->load(['customer', 'botUser', 'pickupShop', 'shop', 'items.orderItem', 'items.product', 'orderItem', 'product', 'courier']);

        return response()->json([
            'ok' => true,
            'claim' => $this->payload($claim),
        ]);
    }

    /** @return array<string, mixed> */
    protected function payload(Claim $c): array
    {
        $cust = $c->customer?->name
            ?: trim(($c->botUser?->first_name ?? '').' '.($c->botUser?->last_name ?? ''))
            ?: 'Mijoz';
        $phone = $c->customer?->phone ?: ($c->botUser?->phone ?? '—');
        $products = [];
        if ($c->items->isNotEmpty()) {
            foreach ($c->items as $item) {
                $products[] = [
                    'name' => $item->displayName(),
                    'qty' => (int) ($item->orderItem?->quantity ?: 1),
                ];
            }
        } else {
            $products[] = [
                'name' => $c->productSummary(),
                'qty' => (int) ($c->replacement_qty ?: $c->orderItem?->quantity ?: 1),
            ];
        }

        return [
            'id' => $c->id,
            'number' => $c->number,
            'status' => $c->status,
            'cust' => $cust,
            'ph' => $phone,
            'address' => $c->address ?: '',
            'reason' => $c->reason ?: '',
            'pickup' => $c->pickupShop?->name ?? $c->shop?->name ?? '—',
            'deadline' => $c->courier_deadline_at?->toIso8601String(),
            'products' => $products,
            'delivery_photo' => $c->delivery_photo ? asset('storage/'.$c->delivery_photo) : null,
            'photo_urls' => collect($c->photos ?? [])->map(fn ($p) => asset('storage/'.$p))->values()->all(),
        ];
    }
}
