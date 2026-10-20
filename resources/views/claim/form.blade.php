<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Shikoyat — QR Kafolat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&display=swap">
    <style>
        :root{
            --page:#f3f4f6; --surface:#ffffff; --surface-2:#f7f8fa; --ink:#141820; --ink-2:#5a6170; --ink-3:#8b919c;
            --line:#e4e7ec; --line-2:#d0d5dd;
            --accent:#1b3f8f; --accent-ink:#ffffff;
            --side-active:#f2a900;
            --crit:#c62828;
            --radius:10px; --shadow:0 1px 2px rgba(16,24,40,.05);
        }
        *{box-sizing:border-box}
        [hidden]{display:none!important}
        body{
            margin:0; min-height:100vh;
            background:var(--page); color:var(--ink);
            font-family:"IBM Plex Sans",system-ui,-apple-system,"Segoe UI",sans-serif;
            font-size:14px; line-height:1.45; -webkit-font-smoothing:antialiased;
        }
        .wrap{max-width:520px; width:100%; margin:0 auto; padding:28px 18px 48px; min-width:0}
        .brand{display:flex; align-items:center; gap:10px; margin-bottom:18px}
        .brand .mark{
            width:36px; height:36px; border-radius:9px; background:var(--side-active);
            display:grid; place-items:center; color:#0f1b33; font-weight:700; font-size:13px; flex:none;
        }
        .brand b{display:block; font-weight:600; font-size:15px; line-height:1.15}
        .brand span{font-size:11.5px; color:var(--ink-3)}
        h1{margin:0 0 6px; font-size:22px; font-weight:600; letter-spacing:-.01em}
        .lead{margin:0 0 18px; color:var(--ink-2); font-size:13px}
        .who{
            background:var(--surface); border:1px solid var(--line); border-radius:var(--radius);
            padding:12px 14px; margin-bottom:14px; box-shadow:var(--shadow);
        }
        .who b{display:block; font-weight:600; margin-bottom:2px}
        .who span{color:var(--ink-2); font-size:13px}
        .card{
            background:var(--surface); border:1px solid var(--line); border-radius:var(--radius);
            padding:16px; box-shadow:var(--shadow); display:grid; gap:14px;
            min-width:0; max-width:100%;
        }
        .field{display:grid; gap:6px; min-width:0}
        .field > span.lbl{
            font-size:10.5px; letter-spacing:.06em; text-transform:uppercase;
            color:var(--ink-3); font-weight:600;
        }
        .hint{font-size:12px; color:var(--ink-3); font-weight:400}
        .err{color:var(--crit); font-size:13px; margin:0 0 10px}
        textarea, .file-input, .sd-search input, .pick-search input{
            width:100%; border:1px solid var(--line-2); border-radius:8px;
            padding:10px 12px; font:inherit; color:var(--ink); background:var(--surface);
        }
        textarea{min-height:110px; resize:vertical}
        textarea:focus, .file-input:focus, .sd-trigger:focus, .pick-search input:focus{
            outline:2px solid var(--accent); outline-offset:1px;
        }
        .file-input{padding:8px 10px; background:var(--surface-2)}
        .btn{
            font-size:14px; font-weight:600; border-radius:8px; padding:12px 14px;
            border:1px solid var(--accent); background:var(--accent); color:var(--accent-ink);
            cursor:pointer; width:100%; max-width:100%;
        }
        .btn:hover{filter:brightness(1.06)}
        .btn.ghost{
            background:var(--surface); color:var(--ink); border-color:var(--line-2);
            font-size:12.5px; padding:7px 10px; width:auto;
        }
        .foot{margin-top:14px; text-align:center; color:var(--ink-3); font-size:12px}
        .prod-tools{display:flex; align-items:center; gap:8px; flex-wrap:wrap}
        .prod-meta{font-size:12.5px; color:var(--ink-2); margin-left:auto}

        /* Mahsulotlar — oddiy roʼyxat (absolute panel yoʼq, forma buzilmaydi) */
        .pick{
            border:1px solid var(--line-2); border-radius:10px; background:var(--surface);
            overflow:hidden; min-width:0;
        }
        .pick-search{
            display:flex; align-items:center; gap:8px;
            padding:8px 10px; background:var(--surface-2); border-bottom:1px solid var(--line);
        }
        .pick-search svg{width:14px; height:14px; color:var(--ink-3); flex:none}
        .pick-search input{
            border:0; background:transparent; padding:4px 0; outline:none; box-shadow:none;
        }
        .pick-list{
            max-height:min(42vh, 280px); overflow-y:auto; -webkit-overflow-scrolling:touch;
            padding:4px; overscroll-behavior:contain;
        }
        .pick-item{
            display:flex; align-items:flex-start; gap:10px;
            padding:10px; border-radius:8px; cursor:pointer; min-width:0;
        }
        .pick-item:hover{background:var(--surface-2)}
        .pick-item input{
            width:18px; height:18px; margin:2px 0 0; flex:none;
            accent-color:var(--accent);
        }
        .pick-item .txt{min-width:0; flex:1}
        .pick-item .name{
            font-size:13px; font-weight:500; color:var(--ink);
            overflow-wrap:anywhere; word-break:break-word;
        }
        .pick-item .sub{
            display:block; margin-top:2px; font-size:11.5px; color:var(--ink-3);
            overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
        }
        .pick-empty{padding:16px 10px; text-align:center; color:var(--ink-3); font-size:13px}

        /* Sabab — bitta tanlash (dropdown) */
        .sd{position:relative; min-width:0; max-width:100%}
        .sd-trigger{
            width:100%; text-align:left; display:flex; align-items:center; justify-content:space-between; gap:10px;
            font-size:13px; font-weight:400; color:var(--ink);
            background:var(--surface); border:1px solid var(--line-2); border-radius:8px;
            padding:10px 12px; cursor:pointer; min-height:42px; min-width:0;
        }
        .sd-trigger .ph{color:var(--ink-3)}
        .sd-trigger [data-label]{
            display:block; min-width:0; flex:1;
            overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
        }
        .sd-trigger .chev{width:12px; height:12px; flex:none; color:var(--ink-3)}
        .sd.open .sd-trigger .chev{transform:rotate(180deg)}
        .sd-panel{
            display:none; position:absolute; z-index:30; left:0; right:0; top:calc(100% + 4px);
            background:var(--surface); border:1px solid var(--line-2); border-radius:10px;
            box-shadow:0 8px 24px rgba(16,24,40,.12); overflow:hidden;
        }
        .sd.open .sd-panel{display:block}
        .sd-list{max-height:200px; overflow:auto; padding:4px}
        .sd-opt{
            display:block; width:100%; text-align:left; border:0; background:none;
            padding:9px 10px; border-radius:7px; cursor:pointer; font:inherit; font-size:13px;
            color:var(--ink);
        }
        .sd-opt:hover, .sd-opt[aria-selected="true"]{background:var(--surface-2)}
        .field.reason{position:relative; z-index:2}
    </style>
</head>
<body>
@php
    $oldIds = old('activation_ids', []);
    if (! is_array($oldIds)) {
        $oldIds = $oldIds !== null && $oldIds !== '' ? [(string) $oldIds] : [];
    }
    $oldIds = array_map('strval', $oldIds);
    $oldReason = (string) old('reason', '');
@endphp
<div class="wrap">
    <div class="brand">
        <div class="mark">QR</div>
        <div>
            <b>QR Kafolat</b>
            <span>Shikoyat / brak so‘rovi</span>
        </div>
    </div>

    <h1>Shikoyat yuborish</h1>
    <p class="lead">Bir yoki bir nechta mahsulotni belgilang. Har biriga alohida so‘rov ochiladi.</p>

    <div class="who">
        <b>{{ trim(($botUser->first_name ?? '').' '.($botUser->last_name ?? '')) ?: ($customer->name ?? 'Mijoz') }}</b>
        <span>{{ $botUser->phone ?? $customer->phone ?? '—' }}</span>
    </div>

    @if ($errors->any())
        <p class="err">{{ $errors->first() }}</p>
    @endif

    <form class="card" method="post" action="{{ route('claim.store', $token) }}" enctype="multipart/form-data" id="claimForm">
        @csrf

        <div class="field">
            <span class="lbl">Mahsulotlar</span>
            <span class="hint">Bir nechtasini belgilashingiz mumkin</span>
            <div class="prod-tools">
                <button type="button" class="btn ghost" id="btnAll">Barchasini</button>
                <button type="button" class="btn ghost" id="btnNone">Tozalash</button>
                <span class="prod-meta" id="prodCount">0 ta tanlandi</span>
            </div>
            <div class="pick" id="pickProducts">
                <div class="pick-search">
                    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="7" cy="7" r="4.5"/><path d="m10.5 10.5 3 3" stroke-linecap="round"/></svg>
                    <input type="search" id="prodSearch" placeholder="Mahsulot yoki chek raqami…" autocomplete="off" aria-label="Mahsulot qidirish">
                </div>
                <div class="pick-list" id="prodList">
                    @foreach ($products as $a)
                        @php
                            $title = $a->orderItem?->name ?? 'Mahsulot';
                            $sub = implode(' · ', array_filter([
                                $a->order?->order_number ? 'chek '.$a->order->order_number : null,
                                $a->warranty_ends_at ? 'kafolat '.$a->warranty_ends_at->format('d.m.Y') : null,
                            ]));
                            $search = mb_strtolower($title.' '.$a->order?->order_number.' '.$a->orderItem?->sku, 'UTF-8');
                            $checked = in_array((string) $a->id, $oldIds, true);
                        @endphp
                        <label class="pick-item" data-search="{{ $search }}">
                            <input type="checkbox" name="activation_ids[]" value="{{ $a->id }}" {{ $checked ? 'checked' : '' }}>
                            <span class="txt">
                                <span class="name">{{ $title }}</span>
                                @if ($sub)<span class="sub">{{ $sub }}</span>@endif
                            </span>
                        </label>
                    @endforeach
                    <div class="pick-empty" id="prodEmpty" hidden>Hech narsa topilmadi</div>
                </div>
            </div>
        </div>

        <div class="field reason">
            <span class="lbl">Sabab</span>
            <input type="hidden" name="reason" id="reason" value="{{ $oldReason }}">
            <div class="sd" id="sdReason">
                <button type="button" class="sd-trigger" aria-haspopup="listbox" aria-expanded="false">
                    <span class="{{ $oldReason ? 'val' : 'ph' }}" data-label>{{ $oldReason ?: 'Sababni tanlang' }}</span>
                    <svg class="chev" viewBox="0 0 12 12" fill="none" aria-hidden="true"><path d="M2.5 4.5 6 8l3.5-3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <div class="sd-panel">
                    <div class="sd-list" role="listbox">
                        @foreach ($reasons as $r)
                            <button
                                type="button"
                                class="sd-opt"
                                role="option"
                                data-value="{{ $r }}"
                                data-label="{{ $r }}"
                                aria-selected="{{ $oldReason === $r ? 'true' : 'false' }}"
                            >{{ $r }}</button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="field">
            <span class="lbl">Tavsif</span>
            <span class="hint">Nima bo‘lganini qisqa yozing (kamida 10 belgi)</span>
            <textarea name="description" required minlength="10" maxlength="2000" placeholder="Masalan: 3 kundan keyin yonmay qoldi…">{{ old('description') }}</textarea>
        </div>

        <div class="field">
            <span class="lbl">Mahsulot rasmlari</span>
            <span class="hint">Ixtiyoriy · 5 tagacha · JPG/PNG · barcha tanlanganlarga bir xil</span>
            <input class="file-input" type="file" name="photos[]" accept="image/*" multiple>
        </div>

        <button class="btn" type="submit">So‘rovni yuborish</button>
    </form>

    <p class="foot">Yuborganingizdan keyin botda xabar keladi.</p>
</div>
<script>
(function () {
    const list = document.getElementById('prodList');
    const items = [...list.querySelectorAll('.pick-item')];
    const empty = document.getElementById('prodEmpty');
    const countEl = document.getElementById('prodCount');
    const search = document.getElementById('prodSearch');

    function selectedCount() {
        return items.filter((el) => el.querySelector('input')?.checked).length;
    }
    function syncCount() {
        const n = selectedCount();
        countEl.textContent = n + ' ta tanlandi';
    }
    function filter(q) {
        const needle = (q || '').trim().toLowerCase();
        let shown = 0;
        items.forEach((el) => {
            const hay = (el.getAttribute('data-search') || '').toLowerCase();
            const ok = !needle || hay.includes(needle);
            el.hidden = !ok;
            if (ok) shown++;
        });
        empty.hidden = shown > 0;
    }

    items.forEach((el) => el.querySelector('input')?.addEventListener('change', syncCount));
    search.addEventListener('input', () => filter(search.value));
    document.getElementById('btnAll').addEventListener('click', () => {
        items.forEach((el) => {
            if (!el.hidden) {
                const inp = el.querySelector('input');
                if (inp) inp.checked = true;
            }
        });
        syncCount();
    });
    document.getElementById('btnNone').addEventListener('click', () => {
        items.forEach((el) => {
            const inp = el.querySelector('input');
            if (inp) inp.checked = false;
        });
        syncCount();
    });
    syncCount();

    // Sabab dropdown
    const root = document.getElementById('sdReason');
    const hidden = document.getElementById('reason');
    const trigger = root.querySelector('.sd-trigger');
    const labelEl = trigger.querySelector('[data-label]');
    const opts = [...root.querySelectorAll('.sd-opt')];
    function close() {
        root.classList.remove('open');
        trigger.setAttribute('aria-expanded', 'false');
    }
    trigger.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        const open = root.classList.toggle('open');
        trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    opts.forEach((opt) => opt.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        opts.forEach((o) => o.setAttribute('aria-selected', o === opt ? 'true' : 'false'));
        hidden.value = opt.getAttribute('data-value') || '';
        labelEl.textContent = opt.getAttribute('data-label') || '';
        labelEl.classList.remove('ph');
        close();
    }));
    document.addEventListener('click', (e) => { if (!root.contains(e.target)) close(); });

    document.getElementById('claimForm').addEventListener('submit', (e) => {
        if (!selectedCount()) {
            e.preventDefault();
            alert('Kamida 1 ta mahsulot tanlang');
            return;
        }
        if (!hidden.value) {
            e.preventDefault();
            alert('Sababni tanlang');
        }
    });
})();
</script>
</body>
</html>
