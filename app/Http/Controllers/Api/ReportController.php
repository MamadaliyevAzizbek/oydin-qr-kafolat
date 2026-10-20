<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PanelBootService;
use App\Services\ReportService;
use App\Services\SellerRatingReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reports,
        protected PanelBootService $panelBoot,
    ) {}

    public function panelBoot(Request $request)
    {
        $period = (int) $request->get('period', 30);
        $shop = $request->get('shop_id', 'all');
        $shopId = ($shop === 'all' || $shop === null || $shop === '') ? null : (int) $shop;

        $forced = $request->user()?->forcedShopId();
        if ($forced) {
            $shopId = $forced;
        }

        return response()->json($this->panelBoot->boot($period, $shopId));
    }

    protected function filters(Request $request): array
    {
        $filters = $this->reports->filtersFromRequest($request->all());
        $forced = $request->user()?->forcedShopId();
        if ($forced) {
            $filters['shop_id'] = $forced;
        }

        return $filters;
    }

    public function meta()
    {
        return response()->json($this->reports->meta());
    }

    public function dashboard(Request $request)
    {
        return response()->json($this->reports->dashboard($this->filters($request)));
    }

    public function sales(Request $request)
    {
        return response()->json($this->reports->salesFunnel($this->filters($request)));
    }

    public function customers(Request $request)
    {
        return response()->json($this->reports->customers($this->filters($request), $request->get('q')));
    }

    public function surveys(Request $request)
    {
        return response()->json($this->reports->surveys($this->filters($request)));
    }

    public function claims(Request $request)
    {
        return response()->json($this->reports->claims($this->filters($request), $request->get('status')));
    }

    public function quality(Request $request)
    {
        return response()->json($this->reports->quality($this->filters($request)));
    }

    public function sellers(Request $request)
    {
        return response()->json($this->reports->sellers($this->filters($request)));
    }

    /** Sotuvchilar oylik reytingi PDF yuklab olish. */
    public function sellersRatingPdf(Request $request, SellerRatingReportService $rating)
    {
        [$from, $to] = $this->ratingRange($request);
        $report = $rating->build($from, $to);
        $pdf = $rating->makePdf($report);
        $name = 'sotuvchilar_reyting_'.$report['month_key'].'.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$name.'"',
        ]);
    }

    /** Sotuvchilar reytingini Telegram rahbariyat guruhiga yuborish. */
    public function sellersRatingSend(Request $request, SellerRatingReportService $rating)
    {
        [$from, $to] = $this->ratingRange($request);
        $report = $rating->build($from, $to);
        $path = $rating->storePdf($report);

        $bot = app(\App\Services\Telegram\TelegramBotService::class);
        $chat = $bot->chatId('management');
        if (! $chat) {
            return response()->json(['message' => 'Rahbariyat guruh ID sozlanmagan.'], 422);
        }

        try {
            $bot->sendDocument($chat, $path, $rating->telegramCaption($report));
            if ($report['high_rad'] !== []) {
                $lines = ["⚠ <b>Rad etilgan so‘rov ≥15%</b>\n"];
                foreach ($report['high_rad'] as $row) {
                    $lines[] = "• <b>{$row['n']}</b> ({$row['shop']}) — {$row['rad']}%";
                }
                $bot->sendMessage($chat, implode("\n", $lines));
            }

            return response()->json([
                'ok' => true,
                'message' => 'Reyting PDF Telegram guruhiga yuborildi.',
                'rows' => count($report['rows']),
                'high_rad' => count($report['high_rad']),
                'label' => $report['label'],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * @return array{0:Carbon,1:Carbon}
     */
    protected function ratingRange(Request $request): array
    {
        if ($request->filled('from') && $request->filled('to')) {
            return [Carbon::parse($request->get('from')), Carbon::parse($request->get('to'))];
        }
        // Panel davri (7/30/90) — joriy oyning emas, tanlangan kunlar
        $period = (int) $request->get('period', 0);
        if (in_array($period, [7, 30, 90], true)) {
            $to = now()->endOfDay();
            $from = now()->subDays($period - 1)->startOfDay();

            return [$from, $to];
        }
        // Odatiy: o‘tgan kalendar oy
        $to = now()->startOfMonth()->subSecond();
        $from = $to->copy()->startOfMonth();

        return [$from, $to];
    }

    public function system(Request $request)
    {
        return response()->json($this->reports->system($this->filters($request)));
    }

    public function export(Request $request): StreamedResponse
    {
        $page = $request->get('page', 'home');
        $filters = $this->filters($request);
        $data = match ($page) {
            'sotuv' => $this->reports->salesFunnel($filters),
            'mijozlar' => $this->reports->customers($filters, $request->get('q')),
            'sorovnoma' => $this->reports->surveys($filters),
            'brak' => $this->reports->claims($filters, $request->get('status')),
            'sifat' => $this->reports->quality($filters),
            'sotuvchilar' => $this->reports->sellers($filters),
            default => $this->reports->dashboard($filters),
        };

        $filename = 'hisobot_'.$page.'_'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            $this->writeCsv($out, $data);
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function writeCsv($out, array $data): void
    {
        foreach ($data as $key => $value) {
            if (! is_array($value)) {
                fputcsv($out, [$key, $value]);

                continue;
            }
            if ($value === [] || ! array_is_list($value)) {
                fputcsv($out, [$key]);
                if (! array_is_list($value)) {
                    foreach ($value as $k => $v) {
                        fputcsv($out, [$k, is_scalar($v) ? $v : json_encode($v, JSON_UNESCAPED_UNICODE)]);
                    }
                }

                continue;
            }
            fputcsv($out, [$key]);
            $first = $value[0] ?? null;
            if (is_array($first)) {
                fputcsv($out, array_keys($first));
                foreach ($value as $row) {
                    fputcsv($out, array_map(fn ($v) => is_scalar($v) ? $v : json_encode($v, JSON_UNESCAPED_UNICODE), array_values($row)));
                }
            }
        }
    }
}
