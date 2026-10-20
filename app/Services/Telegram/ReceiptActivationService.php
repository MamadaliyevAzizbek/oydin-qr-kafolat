<?php

namespace App\Services\Telegram;

use App\Models\Activation;
use App\Models\BotUser;
use App\Models\Order;
use App\Models\QrToken;
use App\Services\Billz\BillzSyncService;
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
        // OCR baʼzan kirill/lotin aralashtiradi
        $norm = str_replace(["\r", "\n", "\t"], ' ', $raw);
        $norm = preg_replace('/\s+/u', ' ', $norm) ?? $norm;
        $priority = [];
        $found = [];

        // 1) Eng ishonchli: Продажа: #010001090266 (+ OCR xatoliklari)
        $salePatterns = [
            '/Продажа\s*[:\.]?\s*#?\s*(\d{9,14})/ui',
            '/Пpoдaжa\s*[:\.]?\s*#?\s*(\d{9,14})/ui', // lotin o/a aralash
            '/Prodazh?a\s*[:\.]?\s*#?\s*(\d{9,14})/ui',
            '/(?:Sale|Check|Chek|Order|Чек)\s*[:\.]?\s*#?\s*(\d{9,14})/ui',
        ];
        foreach ($salePatterns as $re) {
            if (preg_match_all($re, $raw, $m) || preg_match_all($re, $norm, $m)) {
                foreach ($m[1] as $num) {
                    $priority[] = $num;
                }
            }
        }
        // #010001090266 alone
        if (preg_match_all('/#\s*(\d{9,14})\b/', $raw, $m)) {
            foreach ($m[1] as $num) {
                $priority[] = $num;
            }
        }

        // 2) Barcode / general digits (9–14) — Billz odatda 12 xonali, 01… bilan
        if (preg_match_all('/\b(\d{9,14})\b/', $norm, $m)) {
            foreach ($m[1] as $num) {
                $found[] = $num;
            }
        }
        // Boʻshliqsiz: 010002001256 (OCR biriktirib yuborsa)
        if (preg_match_all('/(?<!\d)(01\d{10})(?!\d)/', preg_replace('/\D+/', '', $raw) ?? '', $m)) {
            foreach ($m[1] as $num) {
                $found[] = $num;
            }
        }

        $all = array_values(array_unique(array_filter(array_merge($priority, $found))));

        // Prefer: Продажа / # match → Billz 01… → DB da bor
        usort($all, function ($a, $b) use ($priority) {
            $score = function (string $n) use ($priority): int {
                $s = 0;
                if (in_array($n, $priority, true)) {
                    $s -= 100;
                }
                if (str_starts_with($n, '01') && strlen($n) === 12) {
                    $s -= 50;
                }
                if (Order::query()->where('order_number', $n)->exists()) {
                    $s -= 20;
                }

                return $s;
            };

            return $score($a) <=> $score($b);
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
        if ($number === '') {
            return null;
        }

        return Order::query()
            ->with(['items', 'shop', 'customer'])
            ->where(function ($q) use ($number) {
                $q->where('order_number', $number)
                    ->orWhere('order_number', 'like', '%'.$number);
            })
            ->first();
    }

    /**
     * Lokalda yo‘q bo‘lsa — Billz dan oxirgi kunlar sinxroni + qayta qidiruv.
     */
    public function findOrImportOrder(string $number): ?Order
    {
        $order = $this->findOrderByNumber($number);
        if ($order) {
            return $order;
        }

        try {
            /** @var BillzSyncService $sync */
            $sync = app(BillzSyncService::class);
            if (Order::query()->count() === 0) {
                $sync->syncShops();
            }
            $sync->syncSales(
                now()->subDays(30)->toDateString(),
                now()->toDateString(),
                30
            );
        } catch (\Throwable $e) {
            Log::warning('Billz import on receipt failed: '.$e->getMessage());
        }

        return $this->findOrderByNumber($number);
    }

    /**
     * OCR receipt image via OCR.space (no local tesseract required).
     * Billz cheklari ruscha («Продажа») — avval rus, keyin eng.
     */
    /**
     * OCR.space free limiti ~1MB — katta Telegram fotoni kichraytiramiz.
     */
    public function prepareImageForOcr(string $absolutePath): string
    {
        $size = @filesize($absolutePath) ?: 0;
        if ($size > 0 && $size < 900_000 && function_exists('getimagesize')) {
            return $absolutePath;
        }
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagejpeg')) {
            return $absolutePath;
        }

        $bytes = @file_get_contents($absolutePath);
        if ($bytes === false || $bytes === '') {
            return $absolutePath;
        }

        try {
            $img = @imagecreatefromstring($bytes);
            if ($img === false) {
                return $absolutePath;
            }
            $w = imagesx($img);
            $h = imagesy($img);
            $maxW = 1600;
            if ($w > $maxW) {
                $nw = $maxW;
                $nh = (int) round($h * ($maxW / $w));
                $resized = imagecreatetruecolor($nw, $nh);
                imagecopyresampled($resized, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
                imagedestroy($img);
                $img = $resized;
            }
            $tmp = Storage::disk('local')->path('receipts/ocr-'.Str::uuid().'.jpg');
            @mkdir(dirname($tmp), 0775, true);
            imagejpeg($img, $tmp, 82);
            imagedestroy($img);
            if (is_file($tmp) && filesize($tmp) > 0) {
                Log::info('OCR image prepared', ['from' => $size, 'to' => filesize($tmp)]);

                return $tmp;
            }
        } catch (\Throwable $e) {
            Log::warning('OCR prepare failed: '.$e->getMessage());
        }

        return $absolutePath;
    }

    public function ocrImage(string $absolutePath): string
    {
        $path = $this->prepareImageForOcr($absolutePath);
        $bytes = @file_get_contents($path);
        if ($bytes === false || $bytes === '') {
            Log::warning('OCR: empty image file', ['path' => $path]);

            return '';
        }

        // rus/eng × engine 2/1 — raqam topilganini afzal koʻramiz
        $last = '';
        foreach ([2, 1] as $engine) {
            foreach (['rus', 'eng'] as $lang) {
                $text = $this->ocrImageWithLanguage($path, $bytes, $lang, $engine);
                if ($text !== '' && $this->extractOrderNumbers($text) !== []) {
                    return $text;
                }
                if ($text !== '') {
                    $last = $text;
                }
            }
        }

        return $last;
    }

    protected function ocrImageWithLanguage(string $absolutePath, string $bytes, string $language, int $engine = 2): string
    {
        try {
            $response = Http::timeout(90)
                ->attach('file', $bytes, basename($absolutePath))
                ->post('https://api.ocr.space/parse/image', [
                    'apikey' => config('services.ocrspace.key', 'helloworld'),
                    'language' => $language,
                    'isOverlayRequired' => 'false',
                    'OCREngine' => (string) $engine,
                    'scale' => 'true',
                    'detectOrientation' => 'true',
                    'isTable' => 'true',
                ]);

            $json = $response->json() ?? [];
            if (! empty($json['IsErroredOnProcessing'])) {
                $err = $json['ErrorMessage'] ?? $response->body();
                if (is_array($err)) {
                    $err = implode('; ', $err);
                }
                Log::warning('OCR API error', ['lang' => $language, 'error' => $err]);

                return '';
            }

            $text = trim((string) ($json['ParsedResults'][0]['ParsedText'] ?? ''));
            if ($text !== '') {
                Log::info('OCR ok', ['lang' => $language, 'len' => mb_strlen($text)]);

                return $text;
            }

            Log::warning('OCR empty', ['lang' => $language, 'body' => mb_substr($response->body(), 0, 500)]);
        } catch (\Throwable $e) {
            Log::warning('OCR failed: '.$e->getMessage(), ['lang' => $language]);
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
                $qty = max(1, (int) round((float) ($item->quantity ?: 1)));

                return "• {$item->name} ×{$qty}\n  Kafolat: {$until} (allaqachon faol)";
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
            $qty = max(1, (int) round((float) ($item->quantity ?: 1)));
            $lines[] = "• {$item->name} ×{$qty}\n  Kafolat: {$until} ({$months} oy)";
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
