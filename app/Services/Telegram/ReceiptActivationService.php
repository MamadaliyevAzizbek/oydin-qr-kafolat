<?php

namespace App\Services\Telegram;

use App\Models\Activation;
use App\Models\BotUser;
use App\Models\Order;
use App\Models\QrToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReceiptActivationService
{
    /**
     * Extract likely Billz order numbers from OCR / barcode / plain text.
     *
     * Billz chek: yuqorida «Продажа: #010001090266», pastda shu raqamli barcode.
     *
     * @return list<string>
     */
    public function extractOrderNumbers(string $text): array
    {
        $raw = $text;
        $text = str_replace(["\r", "\n", "\t"], ' ', $text);
        $priority = [];
        $found = [];

        // 1) Eng ishonchli: Продажа: #010001090266
        if (preg_match_all('/Продажа\s*[:\.]?\s*#?\s*(\d{9,14})/ui', $raw, $m)) {
            foreach ($m[1] as $num) {
                $priority[] = $num;
            }
        }
        if (preg_match_all('/(?:Sale|Check|Chek|Order)\s*[:\.]?\s*#?\s*(\d{9,14})/ui', $raw, $m)) {
            foreach ($m[1] as $num) {
                $priority[] = $num;
            }
        }
        // #010001090266 alone
        if (preg_match_all('/#\s*(\d{9,14})\b/', $raw, $m)) {
            foreach ($m[1] as $num) {
                $priority[] = $num;
            }
        }

        // 2) Barcode / general digits (9–14)
        if (preg_match_all('/\b(\d{9,14})\b/', $text, $m)) {
            foreach ($m[1] as $num) {
                $found[] = $num;
            }
        }

        $all = array_values(array_unique(array_filter(array_merge($priority, $found))));

        // Prefer: explicit Продажа match, then numbers that exist in DB
        usort($all, function ($a, $b) use ($priority) {
            $pa = in_array($a, $priority, true) ? 0 : 1;
            $pb = in_array($b, $priority, true) ? 0 : 1;
            if ($pa !== $pb) {
                return $pa <=> $pb;
            }
            $oa = Order::query()->where('order_number', $a)->exists() ? 0 : 1;
            $ob = Order::query()->where('order_number', $b)->exists() ? 0 : 1;

            return $oa <=> $ob;
        });

        return $all;
    }

    /**
     * Try decode 1D barcode from image (bottom of Billz receipt).
     * Returns digits / code text or null.
     */
    public function decodeBarcode(string $absolutePath): ?string
    {
        $script = base_path('scripts/decode-barcode.js');
        if (! is_file($script)) {
            return null;
        }

        $node = trim((string) shell_exec('command -v node 2>/dev/null'));
        if ($node === '') {
            return null;
        }

        $cmd = escapeshellarg($node).' '.escapeshellarg($script).' '.escapeshellarg($absolutePath).' 2>/dev/null';
        $out = trim((string) shell_exec($cmd));
        if ($out === '') {
            return null;
        }

        // Keep digits if barcode is numeric sale id
        $digits = preg_replace('/\D+/', '', $out);

        return $digits !== '' ? $digits : $out;
    }

    public function findOrderByNumber(string $number): ?Order
    {
        $number = preg_replace('/\D+/', '', $number);

        return Order::query()
            ->with(['items', 'shop', 'customer'])
            ->where(function ($q) use ($number) {
                $q->where('order_number', $number)
                    ->orWhere('order_number', 'like', '%'.$number);
            })
            ->first();
    }

    /**
     * OCR receipt image via OCR.space (no local tesseract required).
     */
    public function ocrImage(string $absolutePath): string
    {
        try {
            $response = Http::timeout(60)
                ->attach('file', file_get_contents($absolutePath), basename($absolutePath))
                ->post('https://api.ocr.space/parse/image', [
                    'apikey' => config('services.ocrspace.key', 'helloworld'),
                    'language' => 'eng',
                    'isOverlayRequired' => 'false',
                    'OCREngine' => '2',
                    'scale' => 'true',
                ]);

            $json = $response->json() ?? [];
            if (! empty($json['ParsedResults'][0]['ParsedText'])) {
                return (string) $json['ParsedResults'][0]['ParsedText'];
            }

            Log::warning('OCR empty', ['body' => $response->body()]);
        } catch (\Throwable $e) {
            Log::warning('OCR failed: '.$e->getMessage());
        }

        return '';
    }

    public function downloadTelegramFile(TelegramBotService $bot, string $fileId): ?string
    {
        $file = $bot->api('getFile', ['file_id' => $fileId]);
        $path = $file['file_path'] ?? null;
        if (! $path) {
            return null;
        }

        $token = $bot->token();
        $bytes = Http::timeout(60)->get("https://api.telegram.org/file/bot{$token}/{$path}")->body();
        if ($bytes === '' || $bytes === null) {
            return null;
        }

        $ext = pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg';
        $name = 'receipts/'.now()->format('Ymd').'/'.Str::uuid().'.'.$ext;
        Storage::disk('local')->put($name, $bytes);

        return Storage::disk('local')->path($name);
    }

    /**
     * Activate all line items of an order for this bot user.
     * Bir chek — bir marta: boshqa mijoz qayta faollashtira olmaydi.
     *
     * @return array{status:string, activated:int, order:Order, items:array}
     */
    public function activateOrder(BotUser $botUser, Order $order): array
    {
        $order->loadMissing('items');

        $existing = Activation::query()
            ->where('order_id', $order->id)
            ->orderBy('id')
            ->get();

        if ($existing->isNotEmpty()) {
            $ownerIds = $existing->pluck('bot_user_id')->filter()->unique();
            $ownedByOther = $ownerIds->contains(fn ($id) => (int) $id !== (int) $botUser->id);

            if ($ownedByOther) {
                return [
                    'status' => 'taken_by_other',
                    'activated' => 0,
                    'order' => $order,
                    'items' => [],
                ];
            }

            // Shu mijoz allaqachon urgan — qayta yaratmaymiz
            $lines = $order->items->map(function ($item) use ($existing) {
                $a = $existing->firstWhere('order_item_id', $item->id);
                $until = optional($a?->warranty_ends_at)->format('d.m.Y') ?? '—';

                return "• {$item->name}\n  Kafolat: {$until} (allaqachon faol)";
            })->all();

            return [
                'status' => 'already_yours',
                'activated' => 0,
                'order' => $order,
                'items' => $lines,
            ];
        }

        $activated = 0;
        $lines = [];

        foreach ($order->items as $item) {
            // Race: boshqa mijoz shu orada urgan bo‘lishi mumkin
            if (Activation::query()->where('order_item_id', $item->id)->exists()) {
                return [
                    'status' => 'taken_by_other',
                    'activated' => 0,
                    'order' => $order,
                    'items' => [],
                ];
            }

            $inStore = $order->sold_at && now()->lte($order->sold_at->copy()->addMinutes(30));

            $qr = QrToken::query()->firstOrCreate(
                [
                    'order_id' => $order->id,
                    'order_item_id' => $item->id,
                ],
                [
                    'token' => Str::lower(Str::random(24)),
                    'printed' => true,
                    'scanned_at' => now(),
                    'scanned_in_store' => $inStore,
                ]
            );

            $qrPatch = [];
            if (! $qr->scanned_at) {
                $qrPatch['scanned_at'] = now();
            }
            if ($inStore && ! $qr->scanned_in_store) {
                $qrPatch['scanned_in_store'] = true;
            }
            if ($qrPatch !== []) {
                $qr->update($qrPatch);
            }

            $months = (int) ($item->warranty_months ?: 12);
            try {
                Activation::query()->create([
                    'qr_token_id' => $qr->id,
                    'order_id' => $order->id,
                    'order_item_id' => $item->id,
                    'customer_id' => $botUser->customer_id,
                    'bot_user_id' => $botUser->id,
                    'activated_at' => now(),
                    'warranty_ends_at' => now()->addMonths($months),
                ]);
            } catch (\Throwable) {
                return [
                    'status' => 'taken_by_other',
                    'activated' => 0,
                    'order' => $order,
                    'items' => [],
                ];
            }

            $activated++;
            $until = now()->addMonths($months)->format('d.m.Y');
            $lines[] = "• {$item->name}\n  Kafolat: {$until} ({$months} oy)";
        }

        if (! $order->customer_id && $botUser->customer_id) {
            $order->update(['customer_id' => $botUser->customer_id]);
        }

        return [
            'status' => $activated > 0 ? 'ok' : 'empty',
            'activated' => $activated,
            'order' => $order,
            'items' => $lines,
        ];
    }
}
