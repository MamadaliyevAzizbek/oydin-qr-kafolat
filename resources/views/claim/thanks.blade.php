<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Yuborildi — QR Kafolat</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
            background: #f3f5f2;
            color: #1a211c;
            padding: 24px;
        }
        .box {
            max-width: 420px;
            background: #fff;
            border: 1px solid #d5ddd6;
            border-radius: 16px;
            padding: 28px 22px;
            text-align: center;
        }
        h1 { font-size: 1.35rem; margin: 0 0 10px; }
        p { color: #5c675f; margin: 0; line-height: 1.45; }
        .nums { margin-top: 14px; display: grid; gap: 6px; }
        .num { font-weight: 700; color: #1f6b4a; }
    </style>
</head>
<body>
@php
    $list = is_array($numbers ?? null) && $numbers !== [] ? $numbers : (($number ?? '') !== '' ? [$number] : []);
@endphp
<div class="box">
    <h1>So‘rov yuborildi ✅</h1>
    <p>Servis tez orada ko‘rib chiqadi. Holat yangilanishi Telegram botda keladi.</p>
    @if ($list !== [])
        <div class="nums">
            @foreach ($list as $n)
                <div class="num">{{ $n }}</div>
            @endforeach
        </div>
    @endif
</div>
</body>
</html>
