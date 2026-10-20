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
        $data = $request->validate([
            'activation_ids' => 'required|array|min:1',
            'activation_ids.*' => 'integer',
            'reason' => 'required|string|max:80',
            'description' => 'required|string|min:10|max:2000',
            'address' => 'nullable|string|max:500',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:51200',
        ]);

        $activationIds = array_values(array_unique(array_map('intval', $data['activation_ids'])));
        sort($activationIds);

        $activations = Activation::query()
            ->with(['order', 'orderItem', 'order.shop', 'order.seller'])
            ->whereIn('id', $activationIds)
            ->where(function ($q) use ($botUser) {
                $q->where('bot_user_id', $botUser->id);
                if ($botUser->customer_id) {
                    $q->orWhere('customer_id', $botUser->customer_id);
                }
            })
            ->get();

        if ($activations->isEmpty() || $activations->count() !== count($activationIds)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'activation_ids' => 'Mahsulot(lar) topilmadi yoki ruxsat yoʼq.',
            ]);
        }

        // Ikki marta bosish — oxirgi 45 soniyadagi bir xil so‘rovni qayta yaratmaslik
        $recent = Claim::query()
            ->with('items')
            ->where('bot_user_id', $botUser->id)
            ->where('created_at', '>=', now()->subSeconds(45))
            ->latest('id')
            ->limit(5)
            ->get();
        foreach ($recent as $dup) {
            $dupIds = $dup->items->pluck('activation_id')->filter()->map(fn ($id) => (int) $id)->sort()->values()->all();
            if ($dupIds === [] && $dup->order_item_id) {
                $act = Activation::query()->where('order_item_id', $dup->order_item_id)->where('bot_user_id', $botUser->id)->value('id');
                $dupIds = $act ? [(int) $act] : [];
            }
            sort($dupIds);
            if ($dupIds === $activationIds && (string) $dup->reason === (string) $data['reason']) {
                return ['claim' => $dup, 'duplicate' => true];
            }
        }

        $byId = $activations->keyBy('id');
        $ordered = collect($activationIds)->map(fn (int $id) => $byId->get($id))->filter();

        $paths = [];
        foreach (array_filter($request->file('photos') ?? []) as $file) {
            if ($file && $file->isValid()) {
                $paths[] = $file->store('claims/_tmp', 'public');
            }
        }

        $claim = DB::transaction(function () use ($botUser, $ordered, $data, &$paths, $logNote) {
            /** @var Activation $primary */
            $primary = $ordered->first();
            $number = 'QK-'.now()->format('ymd').'-'.Str::upper(Str::random(4));

            $claim = Claim::query()->create([
                'number' => $number,
                'customer_id' => $botUser->customer_id,
                'bot_user_id' => $botUser->id,
                'order_id' => $primary->order_id,
                'order_item_id' => $primary->order_item_id,
                'product_id' => $primary->orderItem?->product_id,
                'shop_id' => $primary->order?->shop_id,
                'seller_id' => $primary->order?->seller_id,
                'reason' => $data['reason'],
                'description' => $data['description'],
                'address' => $data['address'] ?? null,
                'status' => 'new',
                'photos' => [],
            ]);

            $finalPhotos = [];
            foreach ($paths as $src) {
                $ext = pathinfo($src, PATHINFO_EXTENSION);
                $dest = "claims/{$claim->id}/".Str::random(10).($ext ? '.'.$ext : '');
                try {
                    Storage::disk('public')->move($src, $dest);
                    $finalPhotos[] = $dest;
                } catch (\Throwable) {
                    try {
                        Storage::disk('public')->copy($src, $dest);
                        $finalPhotos[] = $dest;
                    } catch (\Throwable) {
                    }
                }
            }
            $paths = [];
            if ($finalPhotos !== []) {
                $claim->update(['photos' => $finalPhotos]);
            }

            foreach ($ordered as $activation) {
                ClaimItem::query()->create([
                    'claim_id' => $claim->id,
                    'activation_id' => $activation->id,
                    'order_id' => $activation->order_id,
                    'order_item_id' => $activation->order_item_id,
                    'product_id' => $activation->orderItem?->product_id,
                ]);
            }

            ClaimStatusLog::query()->create([
                'claim_id' => $claim->id,
                'from_status' => null,
                'to_status' => 'new',
                'note' => $logNote
                    .($ordered->count() > 1 ? ' ('.$ordered->count().' ta mahsulot)' : ''),
            ]);

            return $claim;
        });

        foreach ($paths as $src) {
            try {
                Storage::disk('public')->delete($src);
            } catch (\Throwable) {
            }
        }

        $bot->notifyClaimOpened($claim->fresh([
            'orderItem',
            'product',
            'botUser',
            'customer',
            'items.orderItem',
            'items.product',
        ]));

        return ['claim' => $claim->fresh([
            'courier', 'logs', 'items.orderItem', 'product', 'orderItem',
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
