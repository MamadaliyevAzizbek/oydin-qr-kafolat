<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
  h1 { font-size: 16px; margin: 0 0 4px; }
  .sub { color: #555; margin-bottom: 12px; }
  table { width: 100%; border-collapse: collapse; }
  th, td { border: 1px solid #ccc; padding: 5px 6px; text-align: left; }
  th { background: #f0f2f5; font-weight: 600; }
  td.num, th.num { text-align: right; }
  tr.warn td { background: #fff4f0; }
  .note { margin-top: 12px; color: #444; font-size: 10px; }
</style>
</head>
<body>
  <h1>QR Kafolat — Sotuvchilar oylik reytingi</h1>
  <div class="sub">Davr: {{ $r['label'] }} · Oʻrtacha QR aktivatsiya: {{ number_format($r['avg_akt'], 1) }}% · {{ now()->format('d.m.Y H:i') }}</div>
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Sotuvchi</th>
        <th>Doʻkon</th>
        <th class="num">Cheklar</th>
        <th class="num">Summa (mln)</th>
        <th class="num">QR akt %</th>
        <th class="num">Doʻkonda %</th>
        <th class="num">Takroriy %</th>
        <th class="num">Rad %</th>
        <th class="num">Xizmat</th>
        <th class="num">Ball</th>
      </tr>
    </thead>
    <tbody>
      @forelse($r['rows'] as $i => $s)
        <tr class="{{ ($s['rad'] ?? 0) >= 15 ? 'warn' : '' }}">
          <td class="num">{{ $i + 1 }}</td>
          <td>{{ $s['n'] }}</td>
          <td>{{ $s['shop'] }}</td>
          <td class="num">{{ $s['cheks'] }}</td>
          <td class="num">{{ number_format($s['summa'], 2) }}</td>
          <td class="num">{{ number_format($s['akt'], 1) }}</td>
          <td class="num">{{ number_format($s['inst'], 1) }}</td>
          <td class="num">{{ $s['rep'] === null ? '—' : number_format((float)$s['rep'], 1) }}</td>
          <td class="num">{{ number_format($s['rad'], 1) }}</td>
          <td class="num">{{ ($s['csat'] ?? 0) > 0 ? number_format($s['csat'], 1).'/5' : '—' }}</td>
          <td class="num"><b>{{ $s['ball'] }}</b></td>
        </tr>
      @empty
        <tr><td colspan="11">Davrda sotuvchi maʼlumoti yoʻq</td></tr>
      @endforelse
    </tbody>
  </table>
  <p class="note">Ball: summa 40% · aktivatsiya 25% · takroriy mijoz 20% · xizmat bahosi 15%. Rad ≥15% — qizil qator (kafolat shartlari notoʻgʻri tushuntirilgan boʻlishi mumkin).</p>
</body>
</html>
