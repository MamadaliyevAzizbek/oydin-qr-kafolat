<?php

namespace App\Services;

use App\Models\Shop;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;

class SellerRatingReportService
{
    public function __construct(protected PanelBootService $panel) {}

    /**
     * @return array{from:Carbon,to:Carbon,label:string,rows:list<array<string,mixed>>,high_rad:list<array<string,mixed>>,avg_akt:float}
     */
    public function build(?Carbon $from = null, ?Carbon $to = null): array
    {
        if (! $from || ! $to) {
            // Odatiy: o‘tgan to‘liq kalendar oy
            $to = now()->startOfMonth()->subSecond();
            $from = $to->copy()->startOfMonth();
        }

        $from = $from->copy()->startOfDay();
        $to = $to->copy()->endOfDay();

        $shops = Shop::query()->pluck('name', 'id');
        $raw = $this->panel->sellersBetween($from, $to);
        $sums = array_map(fn ($s) => (float) ($s['summa'] ?? 0), $raw);
        $maxSum = max(1, $sums === [] ? 0 : max($sums));

        $rows = collect($raw)->map(function (array $s) use ($maxSum, $shops) {
            $rep = is_null($s['rep'] ?? null) ? 0.0 : (float) $s['rep'];
            $csat = ((float) ($s['csat'] ?? 0)) ?: 4.5;
            $ball = (int) round(
                40 * ((float) ($s['summa'] ?? 0)) / $maxSum
                + 25 * ((float) ($s['akt'] ?? 0)) / 100
                + 20 * ($rep / 40)
                + 15 * ($csat / 5)
            );

            return [
                'n' => $s['n'] ?? '—',
                'shop' => $shops[(int) ($s['st'] ?? 0)] ?? '—',
                'st' => (string) ($s['st'] ?? ''),
                'cheks' => (int) ($s['cheks'] ?? 0),
                'summa' => (float) ($s['summa'] ?? 0),
                'akt' => (float) ($s['akt'] ?? 0),
                'inst' => (float) ($s['inst'] ?? 0),
                'rep' => $s['rep'] ?? null,
                'rad' => (float) ($s['rad'] ?? 0),
                'csat' => (float) ($s['csat'] ?? 0),
                'ball' => $ball,
            ];
        })
            ->sortByDesc('ball')
            ->values()
            ->all();

        $cheksAll = array_sum(array_column($rows, 'cheks'));
        $avgAkt = $cheksAll
            ? array_sum(array_map(fn ($r) => $r['akt'] * $r['cheks'], $rows)) / $cheksAll
            : 0.0;

        $highRad = array_values(array_filter($rows, fn ($r) => ($r['rad'] ?? 0) >= 15 && ($r['cheks'] ?? 0) > 0));

        return [
            'from' => $from,
            'to' => $to,
            'label' => $from->format('d.m.Y').' — '.$to->format('d.m.Y'),
            'month_key' => $from->format('Y-m'),
            'rows' => $rows,
            'high_rad' => $highRad,
            'avg_akt' => round($avgAkt, 1),
        ];
    }

    public function renderHtml(array $report): string
    {
        return view('reports.seller-rating', ['r' => $report])->render();
    }

    public function makePdf(array $report): string
    {
        $options = new Options;
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($this->renderHtml($report), 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * PDF ni diskka yozadi, yo‘lni qaytaradi.
     */
    public function storePdf(array $report): string
    {
        $name = 'seller-rating_'.$report['month_key'].'_'.now()->format('Ymd_His').'.pdf';
        $path = 'reports/'.$name;
        Storage::disk('local')->put($path, $this->makePdf($report));

        return Storage::disk('local')->path($path);
    }

    public function telegramCaption(array $report): string
    {
        $top = array_slice($report['rows'], 0, 5);
        $lines = ["<b>Sotuvchilar oylik reytingi</b>", $report['label'], ''];
        foreach ($top as $i => $row) {
            $n = $i + 1;
            $lines[] = "{$n}. <b>{$row['n']}</b> — {$row['ball']} ball · akt {$row['akt']}%";
        }
        if ($report['high_rad'] !== []) {
            $lines[] = '';
            $lines[] = '⚠ Rad ≥15%:';
            foreach ($report['high_rad'] as $row) {
                $lines[] = "• {$row['n']} ({$row['shop']}) — {$row['rad']}%";
            }
        }
        $lines[] = '';
        $lines[] = '<a href="'.url('/panel#sotuvchilar').'">Panelni ochish</a>';

        return implode("\n", $lines);
    }
}
