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

        $data = $request->validate([
            'activation_ids' => 'required|array|min:1',
            'activation_ids.*' => 'integer',
            'reason' => 'required|string|max:80',
            'description' => 'required|string|min:10|max:2000',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:51200',
        ]);

        $activationIds = array_values(array_unique(array_map('intval', $data['activation_ids'])));

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
            return back()
                ->withErrors(['activation_ids' => 'Mahsulot(lar) topilmadi yoki ruxsat yoʼq.'])
                ->withInput();
        }

        // Tanlash tartibini saqlash
        $byId = $activations->keyBy('id');
        $ordered = collect($activationIds)->map(fn (int $id) => $byId->get($id))->filter();

        $paths = [];
        foreach (array_filter($request->file('photos') ?? []) as $file) {
            if ($file && $file->isValid()) {
                $paths[] = $file->store('claims/_tmp', 'public');
            }
        }

        $claim = DB::transaction(function () use ($botUser, $ordered, $data, &$paths) {
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
                'note' => 'Web forma orqali ochildi'
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

        return redirect()
            ->route('claim.thanks', ['token' => $token, 'number' => $claim->number])
            ->with('ok', true)
            ->with('numbers', [$claim->number]);
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
