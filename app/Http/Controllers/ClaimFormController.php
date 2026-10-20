<?php

namespace App\Http\Controllers;

use App\Models\Activation;
use App\Models\BotUser;
use App\Models\Claim;
use App\Models\ClaimItem;
use App\Models\ClaimStatusLog;
use App\Services\Telegram\TelegramBotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ClaimFormController extends Controller
{
    public const REASONS = [
        'Ishlamaydi',
        'Miltillaydi / qizib ketadi',
        'O‘chib ketadi',
        'Mexanik shikast',
        'Izolyatsiya nuqsoni',
        'Boshqa',
    ];

    public function show(string $token): View
    {
        $botUser = $this->resolveBotUser($token);
        $products = $this->productsFor($botUser);

        if ($products->isEmpty()) {
            return view('claim.unavailable', [
                'message' => 'Faol kafolat topilmadi. Avval botda chekni tasdiqlang.',
            ]);
        }

        return view('claim.form', [
            'botUser' => $botUser,
            'customer' => $botUser->customer,
            'products' => $products,
            'reasons' => self::REASONS,
            'token' => $token,
        ]);
    }

    public function store(Request $request, string $token, TelegramBotService $bot)
    {
        $botUser = $this->resolveBotUser($token);

        try {
            $result = $this->createClaimFromRequest($request, $botUser, $bot, 'Web forma orqali ochildi');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()
            ->route('claim.thanks', ['token' => $token, 'number' => $result['claim']->number])
            ->with('ok', true)
            ->with('numbers', [$result['claim']->number]);
    }

    /**
     * @return array{claim: Claim, duplicate: bool}
     */
    public function createClaimFromRequest(
        Request $request,
        BotUser $botUser,
        TelegramBotService $bot,
        string $logNote = 'Web forma orqali ochildi'
    ): array {
        // Yangi format: items[] — har mahsulot uchun sabab/muammo/rasm/miqdor
        // Eski format: activation_ids + umumiy reason/description (web forma)
        $hasItems = $request->has('items');

        if ($hasItems) {
            $data = $request->validate([
                'items' => 'required|array|min:1',
                'items.*.activation_id' => 'required|integer',
                'items.*.qty' => 'required|integer|min:1|max:9999',
                'items.*.reason' => 'required|string|max:80',
                'items.*.description' => 'required|string|min:10|max:2000',
                'items.*.photos' => 'nullable|array|max:20',
                'items.*.photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:51200',
                'address' => 'nullable|string|max:500',
                'address_lat' => 'nullable|numeric|between:-90,90',
                'address_lng' => 'nullable|numeric|between:-180,180',
            ]);
            $itemRows = collect($data['items'])->map(fn ($row) => [
                'activation_id' => (int) $row['activation_id'],
                'qty' => (int) $row['qty'],
                'reason' => (string) $row['reason'],
                'description' => (string) $row['description'],
            ])->values();
        } else {
            $data = $request->validate([
                'activation_ids' => 'required|array|min:1',
                'activation_ids.*' => 'integer',
                'activation_qtys' => 'nullable|array',
                'activation_qtys.*' => 'integer|min:1|max:9999',
                'reason' => 'required|string|max:80',
                'description' => 'required|string|min:10|max:2000',
                'address' => 'nullable|string|max:500',
                'address_lat' => 'nullable|numeric|between:-90,90',
                'address_lng' => 'nullable|numeric|between:-180,180',
                'photos' => 'nullable|array|max:20',
                'photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:51200',
            ]);
            $qtyMap = [];
            foreach (($data['activation_qtys'] ?? []) as $aid => $q) {
                $qtyMap[(int) $aid] = max(1, (int) $q);
            }
            $itemRows = collect($data['activation_ids'])->map(fn ($id) => [
                'activation_id' => (int) $id,
                'qty' => $qtyMap[(int) $id] ?? 1,
                'reason' => (string) $data['reason'],
                'description' => (string) $data['description'],
            ])->unique('activation_id')->values();
        }

        $activationIds = $itemRows->pluck('activation_id')->unique()->sort()->values()->all();
        $activations = Activation::query()
            ->with(['order', 'orderItem', 'order.shop', 'order.seller'])
            ->whereIn('id', $activationIds)
            ->where(function ($q) use ($botUser) {
                $q->where('bot_user_id', $botUser->id);
                if ($botUser->customer_id) {
                    $q->orWhere('customer_id', $botUser->customer_id);
                }
            })
            ->get()
            ->keyBy('id');

        if ($activations->count() !== count($activationIds)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'items' => 'Mahsulot(lar) topilmadi yoki ruxsat yoʼq.',
            ]);
        }

        foreach ($itemRows as $i => $row) {
            $activation = $activations->get($row['activation_id']);
            $max = max(1, (int) round((float) ($activation?->orderItem?->quantity ?: 1)));
            if ($row['qty'] < 1 || $row['qty'] > $max) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "items.$i.qty" => ($activation?->orderItem?->name ?? 'Mahsulot')." uchun miqdor 1–{$max} oraligʻida.",
                ]);
            }
        }

        $recent = Claim::query()
            ->with('items')
            ->where('bot_user_id', $botUser->id)
            ->where('created_at', '>=', now()->subSeconds(45))
            ->latest('id')
            ->limit(5)
            ->get();
        foreach ($recent as $dup) {
            $dupIds = $dup->items->pluck('activation_id')->filter()->map(fn ($id) => (int) $id)->sort()->values()->all();
            if ($dupIds === $activationIds) {
                return ['claim' => $dup, 'duplicate' => true];
            }
        }

        $address = trim((string) ($data['address'] ?? ''));
        $lat = isset($data['address_lat']) ? (float) $data['address_lat'] : null;
        $lng = isset($data['address_lng']) ? (float) $data['address_lng'] : null;

        $claim = DB::transaction(function () use ($botUser, $activations, $itemRows, $request, $hasItems, $data, $logNote, $address, $lat, $lng) {
            $primary = $activations->get($itemRows->first()['activation_id']);
            $totalBrak = (int) $itemRows->sum('qty');
            $firstReason = (string) $itemRows->first()['reason'];
            $summaryDesc = $itemRows->count() === 1
                ? (string) $itemRows->first()['description']
                : $itemRows->count().' ta mahsulot · jami '.$totalBrak.' dona brak';

            $claim = Claim::query()->create([
                'number' => 'QK-'.now()->format('ymd').'-'.Str::upper(Str::random(4)),
                'customer_id' => $botUser->customer_id,
                'bot_user_id' => $botUser->id,
                'order_id' => $primary->order_id,
                'order_item_id' => $primary->order_item_id,
                'product_id' => $primary->orderItem?->product_id,
                'shop_id' => $primary->order?->shop_id,
                'seller_id' => $primary->order?->seller_id,
                'reason' => $firstReason,
                'description' => $summaryDesc,
                'address' => $address !== '' ? $address : null,
                'address_lat' => $lat,
                'address_lng' => $lng,
                'replacement_qty' => $totalBrak,
                'status' => 'new',
                'photos' => [],
            ]);

            $allClaimPhotos = [];
            foreach ($itemRows->values() as $idx => $row) {
                $activation = $activations->get($row['activation_id']);
                $itemPhotos = [];
                $files = $hasItems
                    ? ($request->file("items.$idx.photos") ?? [])
                    : ($idx === 0 ? ($request->file('photos') ?? []) : []);
                foreach (array_filter(is_array($files) ? $files : [$files]) as $file) {
                    if ($file && $file->isValid()) {
                        $dest = "claims/{$claim->id}/".Str::random(10).'.'.$file->getClientOriginalExtension();
                        Storage::disk('public')->put($dest, file_get_contents($file->getRealPath()));
                        $itemPhotos[] = $dest;
                        $allClaimPhotos[] = $dest;
                    }
                }

                ClaimItem::query()->create([
                    'claim_id' => $claim->id,
                    'activation_id' => $activation->id,
                    'order_id' => $activation->order_id,
                    'order_item_id' => $activation->order_item_id,
                    'product_id' => $activation->orderItem?->product_id,
                    'qty' => $row['qty'],
                    'reason' => $row['reason'],
                    'description' => $row['description'],
                    'photos' => $itemPhotos,
                ]);
            }

            if ($allClaimPhotos !== []) {
                $claim->update(['photos' => $allClaimPhotos]);
            }

            ClaimStatusLog::query()->create([
                'claim_id' => $claim->id,
                'from_status' => null,
                'to_status' => 'new',
                'note' => $logNote
                    .' ('.$itemRows->count().' mahsulot · '.$totalBrak.' dona)',
            ]);

            return $claim;
        });

        $bot->notifyClaimOpened($claim->fresh([
            'orderItem', 'product', 'botUser', 'customer',
            'items.orderItem', 'items.product',
        ]));

        return ['claim' => $claim->fresh([
            'courier', 'logs', 'items.orderItem', 'items.product', 'product', 'orderItem',
        ]), 'duplicate' => false];
    }

    public function thanks(string $token, Request $request): View
    {
        $this->resolveBotUser($token);

        $numbers = session('numbers');
        if (! is_array($numbers) || $numbers === []) {
            $n = (string) $request->query('number', '');
            $numbers = $n !== '' ? [$n] : [];
        }

        return view('claim.thanks', [
            'number' => $numbers[0] ?? '',
            'numbers' => $numbers,
        ]);
    }

    protected function resolveBotUser(string $token): BotUser
    {
        return BotUser::query()
            ->with('customer')
            ->where('claim_token', $token)
            ->firstOrFail();
    }

    protected function productsFor(BotUser $botUser)
    {
        return Activation::query()
            ->with(['orderItem', 'order'])
            ->where(function ($q) use ($botUser) {
                $q->where('bot_user_id', $botUser->id);
                if ($botUser->customer_id) {
                    $q->orWhere('customer_id', $botUser->customer_id);
                }
            })
            ->latest('activated_at')
            ->limit(50)
            ->get();
    }
}
