<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kirish — QR Kafolat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root{
            --ink:#141820; --ink-2:#5a6170; --ink-3:#8b919c;
            --line:#e4e7ec; --line-2:#d0d5dd;
            --side:#0f1b33; --gold:#f2a900;
            --accent:#1b3f8f; --surface:#fff; --page:#e8ecf2;
            --err:#c62828; --err-bg:#fdeaea;
            --radius:12px;
        }
        *{box-sizing:border-box}
        html,body{height:100%;margin:0}
        body{
            font-family:"IBM Plex Sans",system-ui,sans-serif;
            color:var(--ink);
            min-height:100%;
            display:grid;
            place-items:center;
            padding:24px 16px;
            background:
                radial-gradient(1200px 600px at 10% -10%, rgba(242,169,0,.18), transparent 55%),
                radial-gradient(900px 500px at 100% 0%, rgba(27,63,143,.22), transparent 50%),
                linear-gradient(165deg, #0f1b33 0%, #1a2a4a 42%, #e8ecf2 42.1%, #eef1f6 100%);
            -webkit-font-smoothing:antialiased;
        }
        .shell{
            width:min(920px,100%);
            display:grid;
            grid-template-columns:1.05fr .95fr;
            background:var(--surface);
            border-radius:18px;
            overflow:hidden;
            box-shadow:0 24px 60px rgba(15,27,51,.28);
            min-height:480px;
            animation:rise .55s ease both;
        }
        @keyframes rise{
            from{opacity:0;transform:translateY(14px)}
            to{opacity:1;transform:none}
        }
        .hero{
            background:
                linear-gradient(160deg, rgba(15,27,51,.92), rgba(27,63,143,.75)),
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            color:#fff;
            padding:40px 36px;
            display:flex;
            flex-direction:column;
            justify-content:space-between;
            position:relative;
        }
        .hero::after{
            content:"";
            position:absolute;
            right:-40px;bottom:-40px;
            width:180px;height:180px;
            border-radius:40px;
            border:18px solid rgba(242,169,0,.35);
            transform:rotate(18deg);
        }
        .brand{display:flex;align-items:center;gap:12px;position:relative;z-index:1}
        .mark{
            width:44px;height:44px;border-radius:11px;background:var(--gold);
            display:grid;place-items:center;color:var(--side);flex:none;
        }
        .brand b{display:block;font-size:18px;font-weight:700;letter-spacing:-.02em}
        .brand span{font-size:12.5px;color:rgba(255,255,255,.72)}
        .hero h1{
            margin:48px 0 10px;font-size:clamp(26px,3.2vw,34px);font-weight:700;
            letter-spacing:-.03em;line-height:1.15;position:relative;z-index:1;max-width:14ch;
        }
        .hero p{margin:0;color:rgba(255,255,255,.78);font-size:14.5px;line-height:1.55;max-width:32ch;position:relative;z-index:1}
        .hero-foot{font-size:12px;color:rgba(255,255,255,.55);position:relative;z-index:1}

        .panel{padding:40px 36px;display:flex;flex-direction:column;justify-content:center}
        .panel h2{margin:0 0 6px;font-size:22px;font-weight:650;letter-spacing:-.02em}
        .panel .sub{margin:0 0 28px;color:var(--ink-2);font-size:13.5px}

        label.field{display:flex;flex-direction:column;gap:6px;margin-bottom:14px}
        label.field span{font-size:11px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-3)}
        label.field input{
            border:1px solid var(--line-2);border-radius:10px;padding:11px 13px;
            font-size:14.5px;background:#fafbfc;color:var(--ink);outline:none;
            transition:border-color .15s, box-shadow .15s, background .15s;
        }
        label.field input:focus{
            border-color:var(--accent);background:#fff;
            box-shadow:0 0 0 3px rgba(27,63,143,.12);
        }
        .row{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:4px 0 22px;font-size:13px;color:var(--ink-2)}
        .row label{display:inline-flex;align-items:center;gap:8px;cursor:pointer}
        .row input{accent-color:var(--accent)}

        .btn{
            width:100%;border:0;border-radius:10px;padding:12px 16px;
            background:var(--side);color:#fff;font-weight:600;font-size:14.5px;
            cursor:pointer;transition:transform .12s, filter .12s, background .12s;
        }
        .btn:hover{filter:brightness(1.08)}
        .btn:active{transform:translateY(1px)}
        .btn .gold{color:var(--gold)}

        .err{
            background:var(--err-bg);color:var(--err);border-radius:8px;
            padding:9px 12px;font-size:13px;margin-bottom:14px;
        }
        .hint{
            margin-top:18px;padding:12px 14px;border-radius:10px;
            background:#f7f8fa;border:1px solid var(--line);
            font-size:12.5px;color:var(--ink-2);line-height:1.45;
        }
        .hint b{color:var(--ink);font-weight:600}

        @media (max-width:820px){
            body{background:linear-gradient(180deg,#0f1b33 0%,#1a2a4a 38%,#eef1f6 38%)}
            .shell{grid-template-columns:1fr}
            .hero{min-height:220px;padding:28px 24px}
            .hero h1{margin-top:28px;font-size:26px}
            .panel{padding:28px 24px 32px}
            .hero::after{width:120px;height:120px;border-width:12px}
        }
        @media (prefers-reduced-motion:reduce){.shell{animation:none}}
    </style>
</head>
<body>
    <div class="shell">
        <aside class="hero">
            <div class="brand">
                <div class="mark" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 20 20" fill="currentColor"><path d="M11.5 1 4 11h5l-.5 8L16 8h-5z"/></svg>
                </div>
                <div>
                    <b>QR Kafolat</b>
                    <span>Oydin Electric</span>
                </div>
            </div>
            <div>
                <h1>Ma'lumotlar paneli</h1>
                <p>Billz sotuvlari, QR aktivatsiya va Telegram bot — bitta joyda.</p>
            </div>
            <div class="hero-foot">Admin kirish · xavfsiz sessiya</div>
        </aside>

        <main class="panel">
            <h2>Tizimga kirish</h2>
            <p class="sub">Hisobotlar va sozlamalarni ochish uchun akkauntingizga kiring.</p>

            @if (session('status'))
                <div class="hint" style="margin-bottom:14px">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="err" role="alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <label class="field">
                    <span>Login</span>
                    <input id="login" type="text" name="login" value="{{ old('login') }}" required autofocus autocomplete="username" placeholder="Login">
                </label>
                <label class="field">
                    <span>Parol</span>
                    <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Parol">
                </label>
                <div class="row">
                    <label>
                        <input type="checkbox" name="remember">
                        Eslab qolish
                    </label>
                </div>
                <button class="btn" type="submit">Kirish <span class="gold">→</span></button>
            </form>
        </main>
    </div>
</body>
</html>
