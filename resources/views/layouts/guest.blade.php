<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'QR Kafolat') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body{font-family:"IBM Plex Sans",system-ui,sans-serif}</style>
</head>
<body class="text-gray-900 antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0" style="background:#f3f4f6">
        <div class="text-center">
            <div style="width:48px;height:48px;border-radius:10px;background:#f2a900;margin:0 auto;display:grid;place-items:center;color:#0f1b33;font-weight:700">Q</div>
            <div class="mt-3 font-semibold text-lg">QR Kafolat</div>
            <div class="text-sm text-gray-500">Oydin Electric</div>
        </div>
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
