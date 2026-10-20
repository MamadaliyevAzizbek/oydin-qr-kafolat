<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shikoyat — QR Kafolat</title>
    <style>
        body {
            margin: 0; min-height: 100vh; display: grid; place-items: center;
            font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
            background: #f3f5f2; color: #1a211c; padding: 24px;
        }
        .box {
            max-width: 420px; background: #fff; border: 1px solid #d5ddd6;
            border-radius: 16px; padding: 28px 22px; text-align: center;
        }
        p { color: #5c675f; margin: 0; line-height: 1.45; }
    </style>
</head>
<body>
<div class="box">
    <p>{{ $message }}</p>
</div>
</body>
</html>
