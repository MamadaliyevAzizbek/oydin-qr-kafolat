<!doctype html><html><head><meta charset=utf8><meta name=viewport content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}"><style>:root{color-scheme:light;box-sizing:border-box;padding-top:env(safe-area-inset-top,0px);padding-bottom:env(safe-area-inset-bottom,0px)}html{scroll-padding-top:env(safe-area-inset-top,0px)}body{margin:0;padding:0;font:14px -apple-system,BlinkMacSystemFont,sans-serif;background:#faf9f5;color:#141413}img{max-width:100%}[hidden]:not([hidden=until-found i]){display:none!important}</style></head><body>
<title>QR Kafolat paneli</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&display=swap">
<style>
:root{
  --page:#f3f4f6; --surface:#ffffff; --surface-2:#f7f8fa; --ink:#141820; --ink-2:#5a6170; --ink-3:#8b919c;
  --line:#e4e7ec; --line-2:#d0d5dd;
  --accent:#1b3f8f; --accent-ink:#ffffff;
  --side:#0f1b33; --side-ink:#c3cce0; --side-dim:#7e8ba8; --side-active:#f2a900;
  --s1:#2a78d6; --s2:#eb6834; --s3:#1baf7a; --s4:#eda100;
  --o1:#1c5cab; --o2:#256abf; --o3:#2a78d6; --o4:#3987e5; --o5:#5598e7; --o6:#6da7ec;
  --good:#006300; --good-bg:#e6f4e6; --warn:#a86a00; --warn-bg:#fff3d6; --crit:#c62828; --crit-bg:#fdeaea; --info-bg:#e8f0fc;
  --grid:#e8eaee; --axis:#c9cdd4;
  --radius:10px; --shadow:0 1px 2px rgba(16,24,40,.05);
}
@media (prefers-color-scheme: dark){
  :root:not([data-theme="light"]){
    color-scheme:dark;
    --page:#0f1115; --surface:#181b21; --surface-2:#1f232a; --ink:#f2f3f5; --ink-2:#b4bac5; --ink-3:#858c98;
    --line:#2a2f38; --line-2:#3a404a;
    --accent:#6b9cf0; --accent-ink:#0b1326;
    --side:#0b1326; --side-ink:#b8c2d8; --side-dim:#6f7c99; --side-active:#f2a900;
    --s1:#3987e5; --s2:#d95926; --s3:#199e70; --s4:#c98500;
    --good:#3ccf3c; --good-bg:#12331a; --warn:#fab219; --warn-bg:#3a2c08; --crit:#f26b6b; --crit-bg:#3c1616; --info-bg:#172a4a;
    --grid:#262b34; --axis:#3a404a;
    --shadow:0 1px 2px rgba(0,0,0,.3);
  }
}
:root[data-theme="dark"]{
  color-scheme:dark;
  --page:#0f1115; --surface:#181b21; --surface-2:#1f232a; --ink:#f2f3f5; --ink-2:#b4bac5; --ink-3:#858c98;
  --line:#2a2f38; --line-2:#3a404a;
  --accent:#6b9cf0; --accent-ink:#0b1326;
  --side:#0b1326; --side-ink:#b8c2d8; --side-dim:#6f7c99; --side-active:#f2a900;
  --s1:#3987e5; --s2:#d95926; --s3:#199e70; --s4:#c98500;
  --good:#3ccf3c; --good-bg:#12331a; --warn:#fab219; --warn-bg:#3a2c08; --crit:#f26b6b; --crit-bg:#3c1616; --info-bg:#172a4a;
  --grid:#262b34; --axis:#3a404a;
  --shadow:0 1px 2px rgba(0,0,0,.3);
}
*{box-sizing:border-box}
html,body{height:100%}
body{margin:0;background:var(--page);color:var(--ink);font-family:"IBM Plex Sans",system-ui,-apple-system,"Segoe UI",sans-serif;font-size:14px;line-height:1.45;-webkit-font-smoothing:antialiased}
button,select,input{font:inherit;color:inherit}
:focus-visible{outline:2px solid var(--accent);outline-offset:2px}
@media (prefers-reduced-motion: reduce){*{transition:none!important;animation:none!important}}

.app{display:grid;grid-template-columns:236px minmax(0,1fr);min-height:100%}
.side{background:var(--side);color:var(--side-ink);padding:18px 14px calc(18px + env(safe-area-inset-bottom,0px));position:sticky;top:0;height:100vh;overflow-y:auto;display:flex;flex-direction:column;gap:14px}
.brand{display:flex;align-items:center;gap:10px;padding:2px 6px 6px}
.brand .mark{width:36px;height:36px;border-radius:9px;background:var(--side-active);display:grid;place-items:center;color:#0f1b33;flex:none}
.brand b{display:block;color:#fff;font-weight:600;font-size:15px;line-height:1.15}
.brand span{font-size:11.5px;color:var(--side-dim);letter-spacing:.02em}
.nav{display:flex;flex-direction:column;gap:1px}
.nav h6{margin:12px 8px 5px;font-size:10.5px;letter-spacing:.09em;text-transform:uppercase;color:var(--side-dim);font-weight:600}
.nav button{display:flex;align-items:center;gap:10px;width:100%;text-align:left;background:none;border:0;color:var(--side-ink);padding:8px 10px;border-radius:8px;cursor:pointer;font-size:13.5px}
.nav button svg{width:16px;height:16px;flex:none;opacity:.85}
.nav button:hover{background:rgba(255,255,255,.06);color:#fff}
.nav button[aria-current="page"]{background:rgba(255,255,255,.1);color:#fff;box-shadow:inset 3px 0 0 var(--side-active)}
.nav .n{margin-left:auto;font-size:11px;background:var(--side-active);color:#0f1b33;padding:0 7px;border-radius:999px;font-weight:600;line-height:18px}
.side .foot{margin-top:auto;font-size:11.5px;color:var(--side-dim);padding:0 8px;line-height:1.4}

.main{padding:18px 22px 48px;min-width:0}
.top{display:flex;flex-wrap:wrap;gap:12px 20px;align-items:flex-end;justify-content:space-between;margin-bottom:12px}
.top h1{margin:0;font-size:22px;font-weight:600;letter-spacing:-.01em;text-wrap:balance}
.top .sub{margin:3px 0 0;color:var(--ink-2);font-size:13px}
.filters{display:flex;flex-wrap:wrap;gap:8px;align-items:flex-end}
.filters label{display:flex;flex-direction:column;gap:3px;font-size:10.5px;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-3);font-weight:600}
.filters select{font-size:13px;font-weight:400;color:var(--ink);background:var(--surface);border:1px solid var(--line-2);border-radius:8px;padding:6px 28px 6px 10px;min-width:150px;appearance:none;-webkit-appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath d='M2.5 4.5 6 8l3.5-3.5' fill='none' stroke='%238b919c' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 9px center;text-transform:none;letter-spacing:0}
.btn{font-size:13px;font-weight:500;border-radius:8px;padding:7px 12px;border:1px solid var(--line-2);background:var(--surface);color:var(--ink);cursor:pointer;display:inline-flex;align-items:center;gap:6px;line-height:1.3}
.btn:hover{background:var(--surface-2)}
.btn.primary{background:var(--accent);border-color:var(--accent);color:var(--accent-ink)}
.btn.primary:hover{filter:brightness(1.08)}
.btn.danger{color:var(--crit);border-color:var(--crit)}
.btn svg{width:14px;height:14px}
.meta{display:flex;flex-wrap:wrap;gap:8px 14px;align-items:center;font-size:12px;color:var(--ink-3);margin-bottom:16px}
.badge{display:inline-flex;align-items:center;gap:6px;padding:2px 9px;border-radius:999px;font-size:11.5px;font-weight:500;background:var(--warn-bg);color:var(--warn)}
.badge::before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor}
.dot{display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--good);margin-right:6px;vertical-align:0}

.kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:14px}
.kpis.k5{grid-template-columns:repeat(5,minmax(0,1fr))}
@media(max-width:1180px){.kpis,.kpis.k5{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:460px){.kpis,.kpis.k5{grid-template-columns:1fr}}
.tile{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);padding:13px 16px 12px;display:grid;grid-template-columns:minmax(0,1fr) auto;grid-template-rows:auto auto auto;gap:2px 10px;box-shadow:var(--shadow);min-width:0}
.tile .lbl{grid-column:1/-1;font-size:12.5px;color:var(--ink-2);line-height:1.3}
.tile .val{grid-row:2;grid-column:1;font-size:26px;font-weight:600;letter-spacing:-.02em;line-height:1.15;white-space:nowrap}
.tile .val small{font-size:14px;font-weight:500;color:var(--ink-3);margin-left:3px;letter-spacing:0}
.tile .spark{grid-row:2/4;grid-column:2;align-self:end;width:72px;height:32px}
.tile .foot{grid-row:3;grid-column:1;display:flex;gap:6px;align-items:baseline;flex-wrap:wrap;font-size:11.5px;color:var(--ink-3);min-height:18px}
.delta{font-weight:500;color:var(--ink-2);white-space:nowrap}
.delta.good{color:var(--good)} .delta.bad{color:var(--crit)}

.grid{display:grid;gap:12px;margin-bottom:14px}
.grid.c2{grid-template-columns:repeat(2,minmax(0,1fr))}
.grid.c3{grid-template-columns:repeat(3,minmax(0,1fr))}
.grid.c21{grid-template-columns:minmax(0,3fr) minmax(0,2fr)}
@media(max-width:1000px){.grid.c2,.grid.c3,.grid.c21{grid-template-columns:1fr}}
.card{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);padding:14px 16px 12px;box-shadow:var(--shadow);min-width:0}
.card h3{margin:0;font-size:14px;font-weight:600}
.card .hd{display:flex;justify-content:space-between;gap:8px 14px;align-items:baseline;margin-bottom:10px;flex-wrap:wrap}
.hint{font-size:12px;color:var(--ink-3)}
.card p{margin:8px 0 0;font-size:13px;color:var(--ink-2)}

.chart{position:relative}
.chart svg{display:block;width:100%;height:auto;overflow:visible}
.legend{display:flex;flex-wrap:wrap;gap:4px 14px;font-size:12px;color:var(--ink-2);margin-bottom:6px}
.legend i,.sw-i{display:inline-block;width:10px;height:10px;border-radius:3px;margin-right:6px;vertical-align:-1px}
.tip{position:absolute;pointer-events:none;background:var(--ink);color:var(--page);font-size:12px;padding:6px 9px;border-radius:7px;box-shadow:0 4px 14px rgba(0,0,0,.18);white-space:nowrap;z-index:5;transform:translate(-50%,calc(-100% - 10px));line-height:1.4}
.tip b{font-weight:600}
.tip .r{display:flex;gap:12px;justify-content:space-between}
.tip i{display:inline-block;width:8px;height:8px;border-radius:2px;margin-right:5px}

.scroll{overflow-x:auto;-webkit-overflow-scrolling:touch;margin:0 -16px;padding:0 16px}
.scroll-y{overflow:auto;-webkit-overflow-scrolling:touch;overscroll-behavior:contain;margin:0 -16px;padding:0 16px}
.audit-scroll{height:280px;max-height:280px}
.audit-scroll .tbl{margin:0}
.audit-scroll .tbl thead th{position:sticky;top:0;background:var(--surface);z-index:1;box-shadow:0 1px 0 var(--line)}
table.tbl{width:100%;border-collapse:collapse;font-size:13px}
.tbl th{text-align:left;font-size:11px;letter-spacing:.05em;text-transform:uppercase;color:var(--ink-3);font-weight:600;padding:6px 10px;border-bottom:1px solid var(--line);vertical-align:bottom;line-height:1.25}
.tbl td{padding:8px 10px;border-bottom:1px solid var(--line);vertical-align:middle;min-width:80px}
.tbl tr:last-child td{border-bottom:0}
.tbl td.num,.tbl th.num{text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap}
.tbl td b{white-space:nowrap}
.tbl tr.click{cursor:pointer}
.tbl tr.click:hover td{background:var(--surface-2)}
.tbl td small{color:var(--ink-3);display:block;font-size:11.5px;line-height:1.3}
.tbl td.wrap{white-space:normal;min-width:180px}
.ibar{display:inline-flex;align-items:center;gap:8px;font-variant-numeric:tabular-nums}
.ibar b{width:72px;height:6px;border-radius:3px;background:var(--line);display:block;flex:none;overflow:hidden}
.ibar b i{display:block;height:100%;background:var(--s1);border-radius:3px}
.ibar.low b i{background:var(--crit)}
.pill{display:inline-flex;align-items:center;gap:6px;padding:2px 9px 2px 7px;border-radius:999px;font-size:11.5px;font-weight:500;white-space:nowrap;background:var(--surface-2);border:1px solid var(--line);color:var(--ink)}
.pill i{width:7px;height:7px;border-radius:50%;background:var(--ink-3);flex:none}
.pill.new i{background:var(--s1)} .pill.review i{background:var(--warn)} .pill.approved i{background:var(--accent)} .pill.replaced i,.pill.done i{background:var(--good)} .pill.rejected i{background:var(--ink-3)} .pill.assigned i,.pill.en_route i{background:var(--s3)} .pill.delivered i{background:var(--amber,#f2a900)} .pill.dispute i{background:var(--crit)} .pill.unconfirmed i{background:var(--ink-3)}
.pill.crit{background:var(--crit-bg);border-color:transparent;color:var(--crit)} .pill.crit i{background:var(--crit)}
.pill.ok{background:var(--good-bg);border-color:transparent;color:var(--good)} .pill.ok i{background:var(--good)}
.pill.warn{background:var(--warn-bg);border-color:transparent;color:var(--warn)} .pill.warn i{background:var(--warn)}
.up{color:var(--crit)} .down{color:var(--good)} .flat{color:var(--ink-3)}

.chips{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px}
.chip{font-size:12.5px;padding:5px 11px;border-radius:999px;border:1px solid var(--line-2);background:var(--surface);color:var(--ink-2);cursor:pointer;line-height:1.3}
.chip[aria-pressed="true"]{background:var(--ink);color:var(--page);border-color:var(--ink)}
.chip .n{margin-left:6px;opacity:.75;font-variant-numeric:tabular-nums}
.search{display:flex;align-items:center;gap:8px;border:1px solid var(--line-2);border-radius:8px;background:var(--surface);padding:6px 10px;min-width:220px}
.search input{border:0;background:none;outline:none;flex:1;font-size:13px;min-width:0}
.search svg{width:14px;height:14px;color:var(--ink-3);flex:none}

.list{display:flex;flex-direction:column;gap:8px}
.item{display:grid;grid-template-columns:4px minmax(0,1fr) auto;gap:12px;align-items:center;padding:0 10px 0 0;border:1px solid var(--line);border-radius:8px;background:var(--surface-2);overflow:hidden}
.item .stripe{align-self:stretch;background:var(--crit)}
.item.warn .stripe{background:var(--warn)}
.item .t{padding:8px 0;min-width:0}
.item b{font-weight:500;display:block;white-space:normal}
.item small{color:var(--ink-2);display:block;font-size:12px}
.item .btn{padding:5px 10px;font-size:12px}
.empty{padding:20px;text-align:center;color:var(--ink-3);font-size:13px}

.stat{display:grid;grid-template-columns:auto 1fr;gap:6px 14px;font-size:13px}
.stat dt{color:var(--ink-3);margin:0} .stat dd{margin:0;font-weight:500}
.tl{list-style:none;margin:0;padding:0 0 0 14px;border-left:2px solid var(--line);display:flex;flex-direction:column;gap:10px}
.tl li{position:relative;font-size:13px}
.tl li::before{content:"";position:absolute;left:-20px;top:5px;width:10px;height:10px;border-radius:50%;background:var(--line-2);border:2px solid var(--surface)}
.tl li.done::before{background:var(--good)} .tl li.now::before{background:var(--warn)}
.tl small{display:block;color:var(--ink-3);font-size:11.5px}
.photo{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}
.photo div,.photo button{aspect-ratio:4/3;border-radius:8px;background:var(--surface-2);border:1px solid var(--line-2);display:grid;place-items:center;font-size:11.5px;color:var(--ink-3);max-width:100%;overflow:hidden;padding:0;cursor:pointer;font:inherit}
.photo button:hover{border-color:var(--accent)}
.photo img{width:100%;height:100%;object-fit:cover;display:block;pointer-events:none}
.lightbox{position:fixed;inset:0;z-index:40;background:rgba(10,14,24,.72);display:grid;place-items:center;padding:24px;cursor:zoom-out}
.lightbox[hidden]{display:none!important}
.lightbox .lb-card{position:relative;max-width:min(920px,100%);max-height:min(88vh,100%);cursor:default;background:var(--surface);border-radius:12px;box-shadow:0 16px 48px rgba(0,0,0,.35);overflow:hidden;padding:10px}
.lightbox .lb-card img{display:block;max-width:100%;max-height:calc(88vh - 20px);width:auto;height:auto;margin:0 auto;border-radius:8px}
.lightbox .lb-x{position:absolute;top:12px;right:12px;border:0;background:rgba(15,20,30,.72);color:#fff;width:36px;height:36px;border-radius:8px;cursor:pointer;display:grid;place-items:center;z-index:1}
.lightbox .lb-x:hover{background:rgba(15,20,30,.9)}
.lightbox .lb-nav{position:absolute;top:50%;transform:translateY(-50%);border:0;background:rgba(15,20,30,.55);color:#fff;width:40px;height:40px;border-radius:8px;cursor:pointer;display:grid;place-items:center;font-size:20px;line-height:1}
.lightbox .lb-nav:hover{background:rgba(15,20,30,.8)}
.lightbox .lb-prev{left:12px}
.lightbox .lb-next{right:12px}
.lightbox .lb-meta{position:absolute;left:12px;bottom:12px;background:rgba(15,20,30,.65);color:#fff;font-size:12px;padding:4px 8px;border-radius:6px}
.dlg{position:fixed;inset:0;z-index:45;background:rgba(10,14,24,.55);display:grid;place-items:center;padding:20px}
.dlg[hidden]{display:none!important}
.dlg .dlg-card{width:min(420px,100%);background:var(--surface);border:1px solid var(--line);border-radius:12px;box-shadow:0 16px 40px rgba(0,0,0,.28);padding:18px 18px 16px}
.dlg h3{margin:0 0 6px;font-size:16px;font-weight:600}
.dlg .dlg-sub{margin:0 0 14px;font-size:13px;color:var(--ink-2);line-height:1.4}
.dlg label{display:grid;gap:6px;font-size:11px;letter-spacing:.05em;text-transform:uppercase;color:var(--ink-3);font-weight:600}
.dlg input,.dlg textarea{width:100%;border:1px solid var(--line-2);border-radius:8px;padding:10px 12px;font:inherit;font-size:14px;color:var(--ink);background:var(--surface);text-transform:none;letter-spacing:0;font-weight:400}
.dlg textarea{min-height:96px;resize:vertical}
.dlg input:focus,.dlg textarea:focus{outline:2px solid var(--accent);outline-offset:1px}
.dlg .dlg-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:16px}
.dlg .dlg-err{margin:8px 0 0;font-size:12.5px;color:var(--crit)}

.overlay{position:fixed;inset:0;background:rgba(10,14,24,.45);z-index:20}
.drawer{position:fixed;top:0;right:0;bottom:0;width:min(470px,100%);background:var(--surface);z-index:21;box-shadow:-8px 0 30px rgba(0,0,0,.22);padding:calc(18px + env(safe-area-inset-top,0px)) 20px calc(20px + env(safe-area-inset-bottom,0px));overflow-y:auto;display:flex;flex-direction:column;gap:16px}
.drawer .dh{display:flex;justify-content:space-between;align-items:flex-start;gap:10px}
.drawer h2{margin:0;font-size:17px;font-weight:600}
.drawer h4{margin:0 0 8px;font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-3);font-weight:600}
.drawer .x{border:0;background:var(--surface-2);width:32px;height:32px;border-radius:8px;cursor:pointer;display:grid;place-items:center;flex:none}
.drawer .actions{display:flex;gap:8px;flex-wrap:wrap;padding-top:6px;border-top:1px solid var(--line)}
.drawer .claim-items{display:grid;gap:8px}
.drawer .claim-item{border:1px solid var(--line);border-radius:10px;background:var(--surface-2);padding:0 10px 10px}
.drawer .claim-item summary{list-style:none;cursor:pointer;display:flex;justify-content:space-between;align-items:center;gap:10px;padding:10px 0;font-size:13.5px}
.drawer .claim-item summary::-webkit-details-marker{display:none}
.drawer .claim-item summary::after{content:"▾";color:var(--ink-3);font-size:12px}
.drawer .claim-item[open] summary::after{content:"▴"}
.drawer .claim-item .flat{color:var(--ink-3);font-size:12.5px;font-variant-numeric:tabular-nums}
.drawer .claim-map{margin-top:8px;border-radius:10px;overflow:hidden;border:1px solid var(--line);height:160px;background:var(--surface-2);position:relative}
.drawer .claim-map iframe{position:absolute;inset:0;width:100%;height:100%;border:0}
.survey-tpls{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
@media (max-width:900px){.survey-tpls{grid-template-columns:1fr}}
.survey-tpl{border:1px solid var(--line);border-radius:10px;padding:12px;background:var(--surface-2)}
.survey-tpl-h{display:flex;flex-direction:column;gap:2px;margin-bottom:8px}
.survey-tpl-h b{font-size:13.5px}
.survey-tpl-h small{color:var(--ink-3);font-size:12px}
.survey-tpl-ta{width:100%;min-height:88px;resize:vertical;font:inherit;font-size:13.5px;line-height:1.45;padding:10px 12px;border:1px solid var(--line-2);border-radius:8px;background:var(--surface);color:var(--ink)}
.survey-tpl-meta{display:flex;justify-content:space-between;align-items:center;gap:8px;margin-top:8px;flex-wrap:wrap}
.survey-tpl-meta .hint{margin:0;font-size:11.5px;color:var(--ink-3)}
.chek{border:1px solid var(--line);border-radius:8px;background:var(--surface-2);overflow:hidden}
.chek>summary{list-style:none;cursor:pointer;display:flex;justify-content:space-between;align-items:center;gap:12px;padding:10px 12px;user-select:none}
.chek>summary::-webkit-details-marker{display:none}
.chek>summary .meta{font-size:12.5px;color:var(--ink-2);min-width:0;line-height:1.35}
.chek>summary .sum{font-weight:600;font-variant-numeric:tabular-nums;white-space:nowrap;flex:none}
.chek>summary::after{content:'▾';color:var(--ink-3);font-size:11px;flex:none;transition:transform .15s ease}
.chek[open]>summary::after{transform:rotate(180deg)}
.chek .body{border-top:1px solid var(--line);padding:8px 12px 10px}
.chek .line{display:flex;justify-content:space-between;gap:10px;padding:5px 0;font-size:13px;border-bottom:1px dashed var(--line)}
.chek .line:last-child{border-bottom:0}
.chek .line .n{min-width:0;line-height:1.35}
.chek .line .q{color:var(--ink-3);white-space:nowrap;flex:none;font-variant-numeric:tabular-nums}
.drawer .buys-card{margin:0}
.drawer .buys-card h4{margin:0 0 10px}
.drawer .chek-scroll{max-height:calc(4 * 52px + 3 * 8px);overflow-y:auto;-webkit-overflow-scrolling:touch;display:flex;flex-direction:column;gap:8px;padding-right:2px;overscroll-behavior:contain}
.toast{position:fixed;left:50%;bottom:calc(22px + env(safe-area-inset-bottom,0px));transform:translateX(-50%);background:var(--ink);color:var(--page);padding:9px 14px;border-radius:9px;font-size:13px;box-shadow:0 6px 20px rgba(0,0,0,.2);z-index:30;max-width:calc(100% - 32px);text-align:center}

.sw{position:relative;width:36px;height:20px;appearance:none;-webkit-appearance:none;background:var(--line-2);border-radius:999px;cursor:pointer;transition:background .15s;margin:0;flex:none}
.sw::after{content:"";position:absolute;top:2px;left:2px;width:16px;height:16px;border-radius:50%;background:#fff;transition:transform .15s;box-shadow:0 1px 2px rgba(0,0,0,.25)}
.sw:checked{background:var(--good)} .sw:checked::after{transform:translateX(16px)}
.tbl input.txt{border:1px solid var(--line-2);border-radius:6px;padding:4px 8px;background:var(--surface);font-size:12.5px;width:120px}
.row-off td:not(:first-child){opacity:.45}
.staff-form{display:grid;gap:10px;max-width:640px;margin-top:10px}
.staff-form label{display:grid;gap:4px;font-size:12.5px;color:var(--ink-2)}
.staff-form input,.staff-form select{border:1px solid var(--line-2);border-radius:8px;padding:8px 10px;font:inherit;background:var(--surface);color:var(--ink)}
.staff-pages{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:6px 12px;padding:10px;border:1px solid var(--line);border-radius:10px;background:var(--surface-2)}
.staff-pages label{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--ink);cursor:pointer}
.staff-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:4px}
.btn.danger{border-color:#f1b0b0;color:#b42318}

@media(max-width:880px){
  .app{grid-template-columns:1fr}
  .side{position:sticky;top:env(safe-area-inset-top,0px);height:auto;flex-direction:row;align-items:center;gap:8px;padding:8px 12px;z-index:10}
  .side .foot{display:none}
  .brand{padding:0 4px 0 0} .brand span{display:none} .brand b{font-size:13px;white-space:nowrap}
  .nav{flex-direction:row;gap:2px;overflow-x:auto;flex:1;scrollbar-width:none;min-width:0}
  .nav::-webkit-scrollbar{display:none}
  .nav h6{display:none}
  .nav button{white-space:nowrap;width:auto;padding:6px 10px;font-size:12.5px}
  .nav button[aria-current="page"]{box-shadow:inset 0 -2px 0 var(--side-active)}
  .nav button svg{display:none}
  .nav .n{margin-left:6px}
  .main{padding:14px 16px 40px}
  .filters select{min-width:120px}
  .scroll{margin:0 -16px;padding:0 16px}
}
/* Kuryer login — faqat telefon UI, bitta sahifa scroll */
html:has(.app.courier-ui),
html:has(.app.courier-ui) body{height:auto;overflow:auto;overscroll-behavior:auto}
.app.courier-ui{grid-template-columns:1fr;min-height:100dvh;height:auto;overflow:visible}
.app.courier-ui .side,
.app.courier-ui .top,
.app.courier-ui .meta{display:none!important}
.app.courier-ui .main{padding:0;overflow:visible;min-height:100dvh}
.app.courier-ui #p-kuryer{padding:0;overflow:visible}
.app.courier-ui .phone-wrap{display:block;min-height:0;overflow:visible}
.app.courier-ui .phone-wrap .side-note{display:none!important}
.app.courier-ui .phone{width:100%;max-width:none;border:0;border-radius:0;box-shadow:none;min-height:0;height:auto;overflow:visible}
.app.courier-ui .phone .scr{display:block;height:auto!important;min-height:0;max-height:none!important;overflow:visible}
.app.courier-ui .phone .bar{position:sticky;top:0;z-index:5}
.app.courier-ui .phone .body,
.app.courier-ui .phone .body.cdet-body{flex:none;height:auto!important;max-height:none!important;overflow:visible!important;-webkit-overflow-scrolling:auto}
.app.courier-ui .phone .bar a.cout{font-size:12.5px;color:var(--ink-3);text-decoration:underline}
.app.courier-ui .cdet .cd-dock{position:sticky;bottom:0;margin:16px 0 0;padding:12px 14px calc(12px + env(safe-area-inset-bottom,0px))}
.app.courier-ui .cdet .cd-photos{overflow-x:auto;overflow-y:hidden}
</style>
<link rel="stylesheet" href="/css/jarayon.css">

<div class="app{{ auth()->user()?->isCourier() ? ' courier-ui' : '' }}">
  <aside class="side">
    <div class="brand">
      <div class="mark" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path d="M11.5 1 4 11h5l-.5 8L16 8h-5z"/></svg></div>
      <div><b>QR Kafolat</b><span>Oydin Electric · 6 doʼkon</span></div>
    </div>
    <nav class="nav" id="nav" aria-label="Boʼlimlar"></nav>
    <div class="foot">Billz · Telegram bot<br>
      <a href="{{ route('logout') }}" style="color:inherit;text-decoration:underline"
         onclick="event.preventDefault();document.getElementById('logout-form').submit();">Chiqish ({{ auth()->user()->name }})</a>
    </div>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" hidden>@csrf</form>
  </aside>

  <main class="main">
    <header class="top">
      <div><h1 id="pageTitle">Bosh sahifa</h1><p class="sub" id="pageSub"></p></div>
      <div class="filters" id="filters">
        <label>Davr<select id="fPeriod"><option value="7">Oxirgi 7 kun</option><option value="30" selected>Oxirgi 30 kun</option><option value="90">Oxirgi 90 kun</option></select></label>
        <label>Doʼkon<select id="fStore"></select></label>
        <label>Sotuvchi<select id="fSeller"></select></label>
        <label>Kategoriya<select id="fCat"></select></label>
        <button class="btn" id="btnExport" type="button"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v8m0 0 3-3M8 10 5 7M3 12v2h10v-2"/></svg>Excel</button>
      </div>
    </header>
    <div class="meta">
      <span><span class="dot"></span>Oxirgi yangilanish: <span id="updatedAt">—</span></span>
      <span class="badge" id="liveBadge">Billz · jonli</span>
      <span id="scopeNote"></span>
    </div>

    <section class="page" id="p-home"></section>
    <section class="page" id="p-sotuv" hidden></section>
    <section class="page" id="p-mijozlar" hidden></section>
    <section class="page" id="p-sorovnoma" hidden></section>
    <section class="page" id="p-brak" hidden></section>
    <section class="page" id="p-sifat" hidden></section>
    <section class="page" id="p-sotuvchilar" hidden></section>
    <section class="page" id="p-tizim" hidden></section>
    <section class="page" id="p-kuryer" hidden></section>
    <section class="page" id="p-miniapp" hidden></section>
    <section class="page" id="p-xabarnomalar" hidden></section>
    <section class="page" id="p-bot" hidden></section>
    <section class="page" id="p-xodimlar" hidden></section>
  </main>
</div>

<div class="overlay" id="overlay" hidden></div>
<aside class="drawer" id="drawer" hidden aria-label="Batafsil"></aside>
<div class="lightbox" id="lightbox" hidden role="dialog" aria-modal="true" aria-label="Rasm">
  <div class="lb-card">
    <button type="button" class="lb-x" id="lbClose" aria-label="Yopish"><svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="m2 2 10 10M12 2 2 12"/></svg></button>
    <button type="button" class="lb-nav lb-prev" id="lbPrev" aria-label="Oldingi" hidden>‹</button>
    <button type="button" class="lb-nav lb-next" id="lbNext" aria-label="Keyingi" hidden>›</button>
    <img id="lbImg" src="" alt="Rasm">
    <div class="lb-meta" id="lbMeta" hidden></div>
  </div>
</div>
<div class="dlg" id="dlg" hidden role="dialog" aria-modal="true" aria-labelledby="dlgTitle">
  <form class="dlg-card" id="dlgForm">
    <h3 id="dlgTitle">So‘rov</h3>
    <p class="dlg-sub" id="dlgSub"></p>
    <label id="dlgLabelWrap"><span id="dlgLabel">Maydon</span>
      <input id="dlgInput" type="text" autocomplete="off">
      <textarea id="dlgText" hidden></textarea>
    </label>
    <p class="dlg-err" id="dlgErr" hidden></p>
    <div class="dlg-actions">
      <button type="button" class="btn" id="dlgCancel">Bekor qilish</button>
      <button type="submit" class="btn primary" id="dlgOk">Saqlash</button>
    </div>
  </form>
</div>
<div class="toast" id="toast" hidden role="status"></div>
<script>
(function(){
'use strict';
const $=(s,r=document)=>r.querySelector(s);
const $$=(s,r=document)=>[...r.querySelectorAll(s)];
let TODAY=new Date();
const DAY=86400000;
const fmt=(n,d=0)=>Number(n).toLocaleString('ru-RU',{minimumFractionDigits:d,maximumFractionDigits:d});
const pct=(n,d=1)=>fmt(n,d)+' %';
const money=(mln)=>mln>=1000?fmt(mln/1000,2)+' mlrd soʼm':mln>=1?fmt(mln,0)+' mln soʼm':fmt(mln*1000,0)+' ming soʼm';
const dm=(d,m,y=2026)=>new Date(y,m-1,d);
const dstr=d=>String(d.getDate()).padStart(2,'0')+'.'+String(d.getMonth()+1).padStart(2,'0');
const dfull=d=>dstr(d)+'.'+d.getFullYear();
const daysBetween=(a,b)=>Math.round((b-a)/DAY);
/* ---------- Ma'lumotlar — faqat /api/panel-boot ---------- */
let STORES=[];
let TOT={cheks:0,summa:0,akt:0,inst:0,rep:null,brak:0,resp:0,sat:0,open:0,close:0,kaf:0,units:0};
const storeName=id=>(STORES.find(s=>s.id===id)||{}).name||'';
const PERIODS={
 '7':{f:1,cmp:'oʼtgan 7 kunga nisbatan',weeks:6},
 '30':{f:1,cmp:'oʼtgan oyga nisbatan',weeks:8},
 '90':{f:1,cmp:'oʼtgan chorakka nisbatan',weeks:12},
};
let CATS=[];
let PRODUCTS=[];
let THRESHOLD=3;
function parseBrakThreshold(v){
  const m=String(v??'').match(/(\d+(?:[.,]\d+)?)/);
  if(!m) return 3;
  const n=parseFloat(m[1].replace(',','.'));
  return Number.isFinite(n)?Math.max(0.1,Math.min(100,n)):3;
}
function syncBrakThresholdFromAlerts(){
  const a=ALERTS.find(x=>x.code==='product_brak_3pct');
  if(!a||a.th==='—') return;
  const i=ALERTS.indexOf(a);
  const raw=$(`#th${i}`)?.value??a.th;
  THRESHOLD=parseBrakThreshold(raw);
}
let SELLERS=[];
const STATUS={new:'Yangi',review:'Koʼrib chiqilmoqda',approved:'Tasdiqlangan',assigned:'Kuryer belgilandi',en_route:'Yoʼlda',delivered:'Kuryer yetkazdi',done:'Yakunlandi',rejected:'Rad etilgan',dispute:'Nizo',unconfirmed:'Tasdiqsiz yopildi'};
const CLOSED=new Set(['done','rejected','unconfirmed']);
const COURIER_OPEN=new Set(['assigned','en_route']);
let CLAIMS=[];
const isOpen=c=>!CLOSED.has(c.status);
const isCourierOpen=c=>COURIER_OPEN.has(c.status);
const daysOpen=c=>daysBetween(c.date,c.closed||TODAY);
let CUSTOMERS=[];
let TOUCH=[
 {t:'T1',when:'Xariddan 7 kun',q:'Oʼrnatildimi, ishlayaptimi',sent:0,dlv:0,ans:0,good:0,bad:0,claims:0,hrs:0},
 {t:'T2',when:'Xariddan 30 kun',q:'Foydalanishda muammo bormi',sent:0,dlv:0,ans:0,good:0,bad:0,claims:0,hrs:0},
 {t:'T3',when:'Xariddan 90 kun',q:'Uzoq muddatli sifat',sent:0,dlv:0,ans:0,good:0,bad:0,claims:0,hrs:0},
 {t:'T4',when:'Kafolat tugashiga 30 kun',q:'Eslatma + oxirgi tekshiruv',sent:0,dlv:0,ans:0,good:0,bad:0,claims:0,hrs:0},
];
let WEEKS=[];
let W_SAT={T1:[],T2:[],T3:[]};
let MONTHS=['Yan','Fev','Mar','Apr','May','Iyn','Iyl','Avg','Sen'];
let M_NEW=[0,0,0,0,0,0,0,0,0];
let COMMENTS=[];
/* kunlik seriya — faqat API (demo RNG o‘chirilgan) */
let DAILY=[];

/* ---------- Holat ---------- */
const state={page:'home',period:'30',store:'all',seller:'all',cat:'all',claimFilter:'all',qView:'product',search:'',cTab:'new',cSel:null,cour:null,tmp:{}};
let COURIERS=[];
const P=()=>PERIODS[state.period];
const curStore=()=>state.store==='all'?null:STORES.find(s=>s.id===state.store);
const share=()=>{const s=curStore();return s?s.cheks/TOT.cheks:1;};
const scope=()=>{const s=curStore();return s?s:TOT;};

/* ---------- Grafik yordamchilari ---------- */
const NS='http://www.w3.org/2000/svg';
function el(tag,attrs,parent){const e=document.createElementNS(NS,tag);for(const k in attrs)e.setAttribute(k,attrs[k]);if(parent)parent.appendChild(e);return e;}
function text(svg,x,y,str,style,anchor){const t=el('text',{x,y,'text-anchor':anchor||'start',style},svg);t.textContent=str;return t;}
function niceMax(v){if(v<=0)return 1;const p=Math.pow(10,Math.floor(Math.log10(v)));const n=v/p;const m=n<=1?1:n<=2?2:n<=2.5?2.5:n<=5?5:10;return m*p;}
function niceScale(max){let best=null;for(const n of [4,5]){const step=niceMax(max/n);const top=step*n;if(!best||top<best.top)best={top,n};}return best;}
function roundTop(x,y,w,h,r){r=Math.min(r,h,w/2);if(h<=0)return '';return `M${x} ${y+h} V${y+r} A${r} ${r} 0 0 1 ${x+r} ${y} H${x+w-r} A${r} ${r} 0 0 1 ${x+w} ${y+r} V${y+h} Z`;}
function roundRight(x,y,w,h,r){r=Math.min(r,w,h/2);if(w<=0)return '';return `M${x} ${y} H${x+w-r} A${r} ${r} 0 0 1 ${x+w} ${y+r} V${y+h-r} A${r} ${r} 0 0 1 ${x+w-r} ${y+h} H${x} Z`;}
const LBL='font-size:11px;fill:var(--ink-3)';
function mkTip(wrap){const t=document.createElement('div');t.className='tip';t.hidden=true;wrap.appendChild(t);return t;}
function placeTip(tip,wrap,W,px,py){const sc=wrap.clientWidth/W||1;const x=Math.max(70,Math.min(wrap.clientWidth-70,px*sc));tip.style.left=x+'px';tip.style.top=(py*sc)+'px';tip.hidden=false;}
function legend(host,series){if(series.length<2)return;const lg=document.createElement('div');lg.className='legend';lg.innerHTML=series.map(s=>`<span><i style="background:${s.color}"></i>${s.name}</span>`).join('');host.appendChild(lg);}

function lineChart(host,o){
  host.innerHTML='';
  const W=Math.max(280,host.clientWidth||600),H=o.height||220;
  const pad={l:46,r:o.endLabels?58:16,t:12,b:26};
  const n=o.labels.length;const all=o.series.flatMap(s=>s.values);
  let yMax=o.yMax,nT=4;const yMin=o.yMin??0;
  if(yMax==null){const s=niceScale(Math.max(...all)*1.05);yMax=s.top;nT=s.n;}
  const x=i=>pad.l+(n===1?0:(W-pad.l-pad.r)*i/(n-1));
  const y=v=>pad.t+(H-pad.t-pad.b)*(1-(v-yMin)/(yMax-yMin));
  legend(host,o.series);
  const wrap=document.createElement('div');wrap.className='chart';host.appendChild(wrap);
  const svg=el('svg',{viewBox:`0 0 ${W} ${H}`,width:W,height:H,role:'img','aria-label':o.title||''},wrap);
  const f=o.fmt||(v=>fmt(v));
  for(let i=0;i<=nT;i++){const v=yMin+(yMax-yMin)*i/nT,yy=y(v);el('line',{x1:pad.l,x2:W-pad.r,y1:yy,y2:yy,style:'stroke:var(--grid);stroke-width:1'},svg);text(svg,pad.l-8,yy+4,f(v),LBL,'end');}
  const step=Math.max(1,Math.ceil(n/(W<420?4:7)));
  o.labels.forEach((l,i)=>{if(i%step===0&&i<=n-1-step/2||i===n-1)text(svg,x(i),H-8,l,LBL,'middle');});
  o.series.forEach(s=>{
    const pts=s.values.map((v,i)=>[x(i),y(v)]);
    const d=pts.map((p,i)=>(i?'L':'M')+p[0].toFixed(1)+' '+p[1].toFixed(1)).join(' ');
    if(s.area)el('path',{d:d+` L${x(n-1).toFixed(1)} ${y(yMin)} L${x(0).toFixed(1)} ${y(yMin)} Z`,style:`fill:${s.color};opacity:.1`},svg);
    el('path',{d,style:`fill:none;stroke:${s.color};stroke-width:2;stroke-linejoin:round;stroke-linecap:round`},svg);
    const last=pts[n-1];
    el('circle',{cx:last[0],cy:last[1],r:4,style:`fill:${s.color};stroke:var(--surface);stroke-width:2`},svg);
    if(o.endLabels)text(svg,last[0]+8,last[1]+4,f(s.values[n-1]),'font-size:11px;fill:var(--ink-2);font-weight:500');
  });
  const cross=el('line',{x1:0,x2:0,y1:pad.t,y2:H-pad.b,style:'stroke:var(--axis);stroke-width:1;opacity:0'},svg);
  const dots=o.series.map(s=>el('circle',{r:4.5,style:`fill:${s.color};stroke:var(--surface);stroke-width:2;opacity:0`},svg));
  const hit=el('rect',{x:pad.l,y:pad.t,width:W-pad.l-pad.r,height:H-pad.t-pad.b,style:'fill:transparent'},svg);
  const tip=mkTip(wrap);
  const show=i=>{cross.style.opacity=1;cross.setAttribute('x1',x(i));cross.setAttribute('x2',x(i));o.series.forEach((s,k)=>{dots[k].style.opacity=1;dots[k].setAttribute('cx',x(i));dots[k].setAttribute('cy',y(s.values[i]));});tip.innerHTML=`<b>${o.labels[i]}</b>`+o.series.map(s=>`<div class="r"><span><i style="background:${s.color}"></i>${s.name}</span><b>${f(s.values[i])}</b></div>`).join('');placeTip(tip,wrap,W,x(i),Math.min(...o.series.map(s=>y(s.values[i]))));};
  const hide=()=>{cross.style.opacity=0;dots.forEach(d=>d.style.opacity=0);tip.hidden=true;};
  const idx=clientX=>{const r=svg.getBoundingClientRect();const px=(clientX-r.left)*(W/r.width);return Math.max(0,Math.min(n-1,Math.round((px-pad.l)/((W-pad.l-pad.r)/Math.max(1,n-1)))));};
  hit.addEventListener('mousemove',e=>show(idx(e.clientX)));
  hit.addEventListener('mouseleave',hide);
  hit.addEventListener('touchstart',e=>show(idx(e.touches[0].clientX)),{passive:true});
  hit.addEventListener('touchmove',e=>show(idx(e.touches[0].clientX)),{passive:true});
  hit.addEventListener('touchend',hide);
}

function columnChart(host,o){
  host.innerHTML='';
  const W=Math.max(280,host.clientWidth||600),H=o.height||220,pad={l:40,r:12,t:14,b:26};
  const n=o.labels.length;const totals=o.labels.map((_,i)=>o.series.reduce((a,s)=>a+s.values[i],0));
  let yMax=o.yMax,nT=4;if(yMax==null){const s=niceScale(Math.max(...totals,o.ref||0)*1.12);yMax=s.top;nT=s.n;}
  const y=v=>pad.t+(H-pad.t-pad.b)*(1-v/yMax);
  const band=(W-pad.l-pad.r)/n,bw=Math.min(24,band*0.62);
  const f=o.fmt||(v=>fmt(v));
  legend(host,o.series);
  const wrap=document.createElement('div');wrap.className='chart';host.appendChild(wrap);
  const svg=el('svg',{viewBox:`0 0 ${W} ${H}`,width:W,height:H,role:'img','aria-label':o.title||''},wrap);
  for(let i=0;i<=nT;i++){const v=yMax*i/nT,yy=y(v);el('line',{x1:pad.l,x2:W-pad.r,y1:yy,y2:yy,style:'stroke:var(--grid);stroke-width:1'},svg);text(svg,pad.l-8,yy+4,f(v),LBL,'end');}
  const lstep=band<40?2:1;
  o.labels.forEach((l,i)=>{if(i%lstep===0)text(svg,pad.l+band*i+band/2,H-8,l,LBL,'middle');});
  o.labels.forEach((_,i)=>{
    let cum=0;const x0=pad.l+band*i+(band-bw)/2;
    const lastK=o.series.map((s,k)=>s.values[i]>0?k:-1).filter(k=>k>=0).pop();
    o.series.forEach((s,k)=>{const v=s.values[i];if(!v)return;const y0=y(cum),y1=y(cum+v);const gap=k>0?2:0;const h=Math.max(0,(y0-y1)-gap);
      if(k===lastK)el('path',{d:roundTop(x0,y1,bw,h,4),style:`fill:${s.color}`},svg);else el('rect',{x:x0,y:y1,width:bw,height:h,style:`fill:${s.color}`},svg);cum+=v;});
    if(o.labelTop)text(svg,x0+bw/2,y(cum)-6,f(cum),'font-size:11px;fill:var(--ink-2);font-weight:500','middle');
  });
  if(o.ref){const yy=y(o.ref);el('line',{x1:pad.l,x2:W-pad.r,y1:yy,y2:yy,style:'stroke:var(--ink-3);stroke-width:1'},svg);text(svg,W-pad.r,yy-5,o.refLabel||'',"font-size:11px;fill:var(--ink-2);font-weight:500",'end');}
  const tip=mkTip(wrap);
  o.labels.forEach((l,i)=>{const r=el('rect',{x:pad.l+band*i,y:pad.t,width:band,height:H-pad.t-pad.b,style:'fill:transparent'},svg);
    const show=()=>{tip.innerHTML=`<b>${o.fullLabels?o.fullLabels[i]:l}</b>`+o.series.map(s=>`<div class="r"><span><i style="background:${s.color}"></i>${s.name}</span><b>${f(s.values[i])}</b></div>`).join('')+(o.series.length>1?`<div class="r"><span>Jami</span><b>${f(totals[i])}</b></div>`:'');placeTip(tip,wrap,W,pad.l+band*i+band/2,y(totals[i]));};
    r.addEventListener('mouseenter',show);r.addEventListener('mouseleave',()=>tip.hidden=true);r.addEventListener('touchstart',show,{passive:true});});
}

function barChartH(host,o){
  host.innerHTML='';
  const W=Math.max(280,host.clientWidth||600),rowH=o.rowH||30,n=o.items.length,th=o.thick||14;
  const maxChars=Math.floor(Math.min(W*0.45,215)/6.6);
  const lab=it=>it.label.length>maxChars?it.label.slice(0,maxChars-1)+'…':it.label;
  const labelW=Math.min(W*0.45,215,Math.max(...o.items.map(it=>lab(it).length))*6.6+10);
  const pad={l:labelW+8,r:o.valW||70,t:6,b:o.ref?20:6};const H=pad.t+rowH*n+pad.b;
  const max=o.max??niceMax(Math.max(...o.items.map(i=>i.value),o.ref||0));
  const x=v=>pad.l+(W-pad.l-pad.r)*v/max;
  const f=o.fmt||(v=>fmt(v));
  const wrap=document.createElement('div');wrap.className='chart';host.appendChild(wrap);
  const svg=el('svg',{viewBox:`0 0 ${W} ${H}`,width:W,height:H,role:'img','aria-label':o.title||''},wrap);
  el('line',{x1:pad.l,x2:pad.l,y1:pad.t,y2:H-pad.b,style:'stroke:var(--axis);stroke-width:1'},svg);
  o.items.forEach((it,i)=>{const cy=pad.t+rowH*i+rowH/2;
    text(svg,pad.l-8,cy+4,lab(it),'font-size:12px;fill:var(--ink-2)','end');
    el('path',{d:roundRight(pad.l,cy-th/2,x(it.value)-pad.l,th,4),style:`fill:${it.color||'var(--s1)'}`},svg);
    text(svg,x(it.value)+8,cy+4,f(it.value)+(it.note?'  ':''),'font-size:12px;fill:var(--ink-2);font-weight:500');
    if(it.note){const t=text(svg,x(it.value)+8+f(it.value).length*7.2+6,cy+4,it.note,'font-size:11px;fill:var(--ink-3)');}
  });
  if(o.ref){const xx=x(o.ref);el('line',{x1:xx,x2:xx,y1:pad.t,y2:H-pad.b+4,style:'stroke:var(--ink-3);stroke-width:1'},svg);text(svg,xx,H-4,o.refLabel||'',"font-size:11px;fill:var(--ink-2);font-weight:500",'middle');}
  const tip=mkTip(wrap);
  o.items.forEach((it,i)=>{const r=el('rect',{x:0,y:pad.t+rowH*i,width:W,height:rowH,style:'fill:transparent'},svg);
    const show=()=>{tip.innerHTML=`<b>${it.label}</b><div class="r"><span>${o.valueName||'Qiymat'}</span><b>${f(it.value)}</b></div>`+(it.tip||'');placeTip(tip,wrap,W,x(it.value),pad.t+rowH*i+rowH/2-th/2);};
    r.addEventListener('mouseenter',show);r.addEventListener('mouseleave',()=>tip.hidden=true);r.addEventListener('touchstart',show,{passive:true});});
}

function spark(vals){const w=72,h=32,mx=Math.max(...vals),mn=Math.min(...vals);const y=v=>mx===mn?h/2:3+(h-6)*(1-(v-mn)/(mx-mn));const pts=vals.map((v,i)=>[(i/(vals.length-1))*(w-4)+2,y(v)]);const d=pts.map((p,i)=>(i?'L':'M')+p[0].toFixed(1)+' '+p[1].toFixed(1)).join(' ');const l=pts[pts.length-1];return `<svg class="spark" viewBox="0 0 ${w} ${h}" aria-hidden="true"><path d="${d}" fill="none" style="stroke:var(--line-2);stroke-width:1.5;stroke-linejoin:round"/><circle cx="${l[0]}" cy="${l[1]}" r="3" style="fill:var(--s1);stroke:var(--surface);stroke-width:1.5"/></svg>`;}

/* ---------- UI bo'laklari ---------- */
function delta(v,unit,upGood){if(v===undefined||v===null||Number.isNaN(+v))return '';v=Number(v);if(v===0)return `<span class="delta">0${unit?' '+unit:''}</span>`;const good=(v>0)===!!upGood;const d=Number.isInteger(v)?0:1;return `<span class="delta ${good?'good':'bad'}">${v>0?'↑ +':'↓ −'}${fmt(Math.abs(v),d)}${unit?' '+unit:''}</span>`;}
function tile(t){return `<div class="tile"><div class="lbl">${t.label}</div><div class="val">${t.value}${t.unit?`<small>${t.unit}</small>`:''}</div>${t.spark?spark(t.spark):''}<div class="foot">${t.delta!==undefined&&t.delta!==null?delta(t.delta,t.dUnit,t.upGood):''}<span>${t.cmp||''}</span></div></div>`;}
function realDelta(key){const d=(window.__BOOT__&&window.__BOOT__.deltas&&window.__BOOT__.deltas[key])||null;if(!d||d.value===undefined||d.value===null)return {delta:0,dUnit:d&&d.unit?d.unit:undefined};return {delta:Number(d.value),dUnit:d.unit||undefined};}
function card(title,body,hint,extra){return `<div class="card ${extra||''}"><div class="hd"><h3>${title}</h3>${hint?`<span class="hint">${hint}</span>`:''}</div>${body}</div>`;}
function ibar(v,max,low){return `<span class="ibar ${low?'low':''}"><b><i style="width:${Math.min(100,v/max*100)}%"></i></b>${pct(v,0)}</span>`;}
function pill(cls,label){return `<span class="pill ${cls}"><i></i>${label}</span>`;}
function trend(tr){return tr>0?'<span class="up">↑ oʼsdi</span>':tr<0?'<span class="down">↓ kamaydi</span>':'<span class="flat">→ oʼzgarmadi</span>';}
function toast(msg){const t=$('#toast');t.textContent=msg;t.hidden=false;clearTimeout(toast.h);toast.h=setTimeout(()=>t.hidden=true,3200);}
function openDrawer(html){$('#drawer').innerHTML=html;$('#drawer').hidden=false;$('#overlay').hidden=false;$('#drawer').scrollTop=0;$('.drawer .x')?.focus();}
function closeDrawer(){$('#drawer').hidden=true;$('#overlay').hidden=true;}
$('#overlay').addEventListener('click',closeDrawer);
$('#drawer').addEventListener('click',e=>{if(e.target.closest('.x'))closeDrawer();});

let lbUrls=[],lbIdx=0;
function openLightbox(urls,idx=0){
  lbUrls=Array.isArray(urls)?urls.filter(Boolean):[];
  if(!lbUrls.length) return;
  lbIdx=Math.max(0,Math.min(idx,lbUrls.length-1));
  renderLightbox();
  $('#lightbox').hidden=false;
  $('#lbClose')?.focus();
}
function closeLightbox(){$('#lightbox').hidden=true;lbUrls=[];}
function renderLightbox(){
  const img=$('#lbImg'),meta=$('#lbMeta'),prev=$('#lbPrev'),next=$('#lbNext');
  if(!img) return;
  img.src=lbUrls[lbIdx]||'';
  img.alt='foto '+(lbIdx+1);
  if(meta){meta.hidden=lbUrls.length<2;meta.textContent=(lbIdx+1)+' / '+lbUrls.length;}
  if(prev) prev.hidden=lbUrls.length<2;
  if(next) next.hidden=lbUrls.length<2;
}
function lbStep(d){if(lbUrls.length<2)return;lbIdx=(lbIdx+d+lbUrls.length)%lbUrls.length;renderLightbox();}
$('#lightbox')?.addEventListener('click',e=>{
  if(e.target.id==='lightbox') closeLightbox();
  if(e.target.closest('#lbClose')) closeLightbox();
  if(e.target.closest('#lbPrev')){e.stopPropagation();lbStep(-1);}
  if(e.target.closest('#lbNext')){e.stopPropagation();lbStep(1);}
});
let dlgResolve=null;
const fmtSom=n=>{
  const digits=String(n??'').replace(/\D+/g,'');
  if(!digits) return '';
  return digits.replace(/\B(?=(\d{3})+(?!\d))/g,' ');
};
const parseSom=s=>String(s??'').replace(/\s+/g,'').replace(/\D+/g,'');
function askModal({title,sub='',label='Qiymat',value='',type='text',placeholder='',required=true,okText='Saqlash'}={}){
  return new Promise(resolve=>{
    const dlg=$('#dlg'), form=$('#dlgForm'),inp=$('#dlgInput'),ta=$('#dlgText'),err=$('#dlgErr');
    if(!dlg||!form){resolve(null);return;}
    if(dlgResolve){dlgResolve(null);dlgResolve=null;}
    dlgResolve=resolve;
    $('#dlgTitle').textContent=title||'So‘rov';
    $('#dlgSub').textContent=sub||'';
    $('#dlgSub').hidden=!sub;
    $('#dlgLabel').textContent=label||'Qiymat';
    $('#dlgOk').textContent=okText||'Saqlash';
    err.hidden=true;err.textContent='';
    const multi=type==='textarea';
    const money=type==='number'||type==='money';
    inp.hidden=multi;ta.hidden=!multi;
    const field=multi?ta:inp;
    inp.oninput=null;
    if(!multi){
      inp.type='text';
      inp.removeAttribute('min');
      inp.removeAttribute('step');
      inp.inputMode=money?'numeric':'text';
      field.value=money?fmtSom(value): (value??'');
      if(money){
        inp.oninput=()=>{
          const raw=parseSom(inp.value);
          const cur=inp.selectionStart;
          const before=parseSom(inp.value.slice(0,cur)).length;
          inp.value=fmtSom(raw);
          // kursor ni raqam pozitsiyasiga qaytarish
          let pos=0,seen=0;
          while(pos<inp.value.length&&seen<before){if(/\d/.test(inp.value[pos])) seen++;pos++;}
          try{inp.setSelectionRange(pos,pos);}catch(_){}
        };
      }
    }else{
      field.value=value??'';
    }
    field.placeholder=placeholder||(money?'0':'');
    dlg.hidden=false;
    setTimeout(()=>{field.focus();if(money) field.select();},0);
    form.onsubmit=e=>{
      e.preventDefault();
      let v=String(field.value??'').trim();
      if(money) v=parseSom(v);
      if(required&&!v){err.textContent='Maydonni to‘ldiring';err.hidden=false;field.focus();return;}
      if(money&&v!==''&&Number.isNaN(Number(v))){err.textContent='Raqam kiriting';err.hidden=false;field.focus();return;}
      closeModal(v);
    };
  });
}
function closeModal(val){
  const dlg=$('#dlg');
  if(dlg) dlg.hidden=true;
  const r=dlgResolve;dlgResolve=null;
  if(r) r(val);
}
$('#dlgCancel')?.addEventListener('click',()=>closeModal(null));
$('#dlg')?.addEventListener('click',e=>{if(e.target.id==='dlg') closeModal(null);});

document.addEventListener('keydown',e=>{
  if(!$('#dlg')?.hidden){
    if(e.key==='Escape'){e.preventDefault();closeModal(null);}
    return;
  }
  if(!$('#lightbox')?.hidden){
    if(e.key==='Escape'){e.preventDefault();closeLightbox();return;}
    if(e.key==='ArrowLeft') lbStep(-1);
    if(e.key==='ArrowRight') lbStep(1);
    return;
  }
  if(e.key==='Escape') closeDrawer();
});
/* Foto lightbox — photos_viewed drawer handlerida (pastda) belgilanishi bilan birga */

/* ---------- Seriya tayyorlash ---------- */
function daily(){
  // Real kunlik seriya — share/aktK bilan sunʼiy masshtablanmaydi
  if(!DAILY.length) return [];
  const p=state.period;
  if(p==='90'){
    const rows=[];
    for(let w=0;w<13;w++){
      const chunk=DAILY.slice(w*7,w*7+7);
      if(!chunk.length) continue;
      rows.push({label:dstr(chunk[0].d),cheks:chunk.reduce((a,r)=>a+r.cheks,0),akt:chunk.reduce((a,r)=>a+r.akt,0)});
    }
    return rows;
  }
  return DAILY.slice(-Number(p)).map(r=>({label:dstr(r.d),cheks:r.cheks,akt:r.akt}));
}
const weeks=()=>{
  const k=P().weeks;
  const cw=window.__BOOT__&&window.__BOOT__.claim_weeks;
  if(!(cw&&cw.labels&&cw.labels.length)) return {labels:[],in:[],out:[],app:[],rej:[],total:0,real:true};

  let labels=cw.labels.slice(-k);
  let inn=(cw.in||[]).slice(-k);
  let out=(cw.out||[]).slice(-k);
  let app=(cw.app||[]).slice(-k);
  let rej=(cw.rej||[]).slice(-k);

  // Doʼkon filtri — CLAIMS roʼyxatidan hafta boʼyicha (boot shop_id boʼlmasa)
  if(state.store!=='all'&&!(window.__BOOT__&&window.__BOOT__.claim_stats)){
    const parseLab=lab=>{const [dd,mm]=lab.split('.').map(Number);const y=TODAY.getFullYear();const start=new Date(y,mm-1,dd);start.setHours(0,0,0,0);const end=new Date(start);end.setDate(end.getDate()+6);end.setHours(23,59,59,999);return {start,end};};
    const inRange=(t,start,end)=>{if(!t)return false;const x=t instanceof Date?t:new Date(t);return x>=start&&x<=end;};
    inn=labels.map(lab=>{const {start,end}=parseLab(lab);return CLAIMS.filter(c=>c.st===state.store&&inRange(c.date,start,end)).length;});
    out=labels.map(lab=>{const {start,end}=parseLab(lab);return CLAIMS.filter(c=>c.st===state.store&&c.closed&&inRange(c.closed,start,end)).length;});
    app=labels.map(lab=>{const {start,end}=parseLab(lab);return CLAIMS.filter(c=>c.st===state.store&&['approved','done','assigned','en_route','delivered'].includes(c.status)&&inRange(c.closed||c.date,start,end)).length;});
    rej=labels.map(lab=>{const {start,end}=parseLab(lab);return CLAIMS.filter(c=>c.st===state.store&&c.status==='rejected'&&inRange(c.closed||c.date,start,end)).length;});
  }

  const total=app.reduce((a,b)=>a+b,0)+rej.reduce((a,b)=>a+b,0);
  return {labels,in:inn,out,app,rej,total,real:true};
};
function filteredProducts(){return PRODUCTS.filter(p=>state.cat==='all'||p.cat===state.cat);}
function filteredSellers(){return SELLERS.filter(s=>(state.store==='all'||s.st===state.store)&&(state.seller==='all'||s.n===state.seller));}
function filteredClaims(){return CLAIMS.filter(c=>(state.store==='all'||c.st===state.store)&&(state.seller==='all'||c.seller===state.seller)&&(state.claimFilter==='all'||c.status===state.claimFilter));}

/* ---------- Sahifalar ---------- */
const RENDER={};

RENDER.home=function(root){
  const S=scope(),sh=share(),f=P().f,cmp=P().cmp;const d=daily();
  const dCheks=realDelta('cheks'),dAkt=realDelta('akt'),dKaf=realDelta('kaf'),dResp=realDelta('resp'),dSat=realDelta('sat'),dBrak=realDelta('brak'),dOpen=realDelta('open'),dClose=realDelta('close');
  const tiles=[
    {label:'Sotuvlar soni (Billz cheklari)',value:fmt(Math.round(S.cheks*f)),delta:dCheks.delta??0,dUnit:dCheks.dUnit||'%',upGood:true,cmp,spark:d.slice(-12).map(r=>r.cheks)},
    {label:'QR aktivatsiya darajasi',value:fmt(S.akt,1),unit:'%',delta:dAkt.delta??0,dUnit:dAkt.dUnit||'pp',upGood:true,cmp,spark:d.slice(-12).map(r=>r.akt/Math.max(1,r.cheks))},
    {label:'Faol kafolatlar',value:fmt(Math.round(TOT.kaf*sh)),delta:dKaf.delta??0,dUnit:dKaf.dUnit,upGood:true,cmp},
    {label:'Soʼrovnoma javob darajasi',value:fmt(S.resp,1),unit:'%',delta:dResp.delta??0,dUnit:dResp.dUnit||'pp',upGood:true,cmp},
    {label:'Qoniqish darajasi («yaxshi» javoblar)',value:fmt(S.sat,1),unit:'%',delta:dSat.delta??0,dUnit:dSat.dUnit||'pp',upGood:true,cmp},
    {label:'Brak darajasi (tasdiqlangan / sotilgan)',value:fmt(S.brak,1),unit:'%',delta:dBrak.delta??0,dUnit:dBrak.dUnit||'pp',upGood:false,cmp},
    {label:'Ochiq soʼrovlar',value:fmt(S.open),delta:dOpen.delta??0,dUnit:dOpen.dUnit,upGood:false,cmp:'hozirgi holat'},
    {label:'Oʼrtacha yopish muddati',value:fmt(S.close,1),unit:'kun',delta:dClose.delta??0,dUnit:dClose.dUnit||'kun',upGood:false,cmp},
  ];
  // Real qoidalar (demo matn yoʼq): SLA, brak chegara, past aktivatsiya
  const attention=[];
  CLAIMS.filter(c=>isOpen(c)&&daysOpen(c)>7&&(state.store==='all'||c.st===state.store)).forEach(c=>attention.push({cls:'crit',t:`${c.id} · ${c.prod} · ${daysOpen(c)} kun ochiq`,s:`${storeName(c.st)} · masʼul: ${c.assignee||'tayinlanmagan'} · SLA 7 kun oshdi`,page:'brak',claim:c.id}));
  filteredProducts().filter(p=>(p.sold||0)>=10&&(p.brak||0)/p.sold*100>THRESHOLD).forEach(p=>attention.push({cls:'warn',t:`${p.n} — brak ${pct(p.brak/p.sold*100)} (chegara ${THRESHOLD} %)`,s:`${p.brand||'—'} · ${p.sup||'—'} · ${p.brak} ta tasdiqlangan / ${p.sold} sotilgan`,page:'sifat'}));
  // Kamida 20 chek boʼlgan doʼkonlar — past % shovqin bermasin
  STORES.filter(s=>(s.cheks||0)>=20&&s.akt<40&&(state.store==='all'||s.id===state.store)).forEach(s=>attention.push({cls:'warn',t:`${s.name} — QR aktivatsiya ${fmt(s.akt,1)} %`,s:`Davrda ${fmt(s.cheks)} chek · chegaradan past (< 40 %)`,page:'sotuv'}));
  const w=weeks();
  const claimBody=w.total>0?'<div id="c-home2"></div>':'<div class="empty">Hali yopilgan brak soʼrovi yoʼq — grafik real maʼlumotdan. Bot orqali soʼrov ochilganda shu yerda chiqadi.</div>';
  root.innerHTML=`<div class="kpis">${tiles.map(tile).join('')}</div>
  <div class="grid c21">
    ${card('Sotuvlar va QR aktivatsiya',DAILY.length?'<div id="c-home1"></div>':'<div class="empty">Maʼlumot yuklanmoqda…</div>','real · kunlar boʼyicha, dona')}
    ${card('Brak soʼrovlari, haftalar boʼyicha',claimBody,'real · yopilgan soʼrovlar')}
  </div>
  <div class="grid c21">
    ${card('Eʼtibor talab qiladi',attention.length?`<div class="list">${attention.map(a=>`<div class="item ${a.cls}"><span class="stripe"></span><div class="t"><b>${a.t}</b><small>${a.s}</small></div><button class="btn" data-go="${a.page}" ${a.claim?`data-claim="${a.claim}"`:''} type="button">Ochish</button></div>`).join('')}</div>`:'<div class="empty">Hozircha hammasi meʼyorda — SLA / brak / past aktivatsiya yoʼq</div>',`${attention.length} ta · real`)}
    ${card('Top-5 brak mahsulot','<div id="c-home3"></div>','real · tasdiqlangan brak, dona')}
  </div>`;
  if(DAILY.length&&d.length){
  lineChart($('#c-home1'),{labels:d.map(r=>r.label),series:[{name:'Cheklar',color:'var(--s1)',values:d.map(r=>r.cheks),area:true},{name:'QR aktivatsiya',color:'var(--s3)',values:d.map(r=>r.akt)}],endLabels:true});
  }
  if(w.total>0){
    columnChart($('#c-home2'),{labels:w.labels,series:[{name:'Tasdiqlangan',color:'var(--s1)',values:w.app},{name:'Rad etilgan',color:'var(--s2)',values:w.rej}]});
  }
  const top=filteredProducts()
    .filter(p=>(p.brak||0)>0)
    .map(p=>({label:p.n,value:p.brak,tip:`<div class="r"><span>Sotilgan</span><b>${fmt(p.sold)}</b></div><div class="r"><span>Tasdiqlangan brak</span><b>${fmt(p.brak)}</b></div><div class="r"><span>Brak darajasi</span><b>${pct(p.brak/Math.max(1,p.sold)*100)}</b></div>`}))
    .sort((a,b)=>b.value-a.value)
    .slice(0,5);
  if(top.length) barChartH($('#c-home3'),{items:top,valueName:'Tasdiqlangan brak'});
  else{
    const el3=$('#c-home3');
    if(el3) el3.innerHTML='<div class="empty">Hali tasdiqlangan brak yoʼq — Billz sotuvlari bor, lekin claims jadvalida tasdiqlangan soʼrov yoʼq</div>';
  }
};

RENDER.sotuv=function(root){
  const fn=(window.__BOOT__&&window.__BOOT__.funnel)||{};
  const vals=[
    ['Billz chek',fn.cheks??0],
    ['QR skanerlandi',fn.qr_scanned??0],
    ['Bot /start',fn.starts??0],
    ['Rozilik javobi',fn.consent??0],
    ['Telefon tasdiqlandi',fn.phones??0],
    ['«Mening uyim» ochildi',fn.homes??0],
  ];
  const cols=['var(--o1)','var(--o2)','var(--o3)','var(--o4)','var(--o5)','var(--o6)'];
  const base=Math.max(1,vals[0][1]||0);
  const items=vals.map((s,i)=>{const v=Math.round(Number(s[1])||0);const prev=i?Math.round(Number(vals[i-1][1])||0):v;return {label:s[0],value:v,color:cols[i],note:i&&prev?`${fmt(v/Math.max(1,prev)*100,0)} % oldingi bosqichdan`:'',tip:`<div class="r"><span>Chekdan ulushi</span><b>${pct(v/base*100,0)}</b></div>`};});
  const rows=STORES.filter(s=>state.store==='all'||s.id===state.store).slice().sort((a,b)=>b.akt-a.akt);
  // Grafik: sotuvi bor doʼkonlar (0 chek chalkashtirmasin)
  const chartRows=rows.filter(s=>(s.cheks||0)>0);
  const funnelEmpty=!vals.some(v=>(v[1]||0)>0);
  root.innerHTML=`<div class="grid c21">
    ${card('Voronka: chekdan «Mening uyim»gacha',funnelEmpty?'<div class="empty">Voronka uchun maʼlumot yoʼq</div>':'<div id="c-s1"></div>','real · Billz + bot')}
    ${card('Doʼkonlar boʼyicha QR aktivatsiya',chartRows.length?'<div id="c-s2"></div>':'<div class="empty">Davrda sotuv yoʼq</div>','kompaniya oʼrtachasi bilan')}
  </div>
  ${card('Doʼkonlar kesimida',`<div class="scroll"><table class="tbl"><thead><tr><th>Doʼkon</th><th class="num">Cheklar</th><th class="num">Sotilgan birliklar</th><th class="num">Summa</th><th>QR aktivatsiya</th><th class="num">Doʼkonda skanerlangan</th><th class="num">Takroriy mijozlar</th><th>Holat</th></tr></thead><tbody>
  ${rows.map(s=>`<tr><td><b>${s.name}</b></td><td class="num">${fmt(s.cheks||0)}</td><td class="num">${fmt(s.units||0)}</td><td class="num">${money(s.summa||0)}</td><td>${ibar(s.akt||0,100,(s.cheks||0)>0&&(s.akt||0)<40)}</td><td class="num">${pct(s.inst||0,0)}</td><td class="num">${s.rep==null||s.rep===undefined?'—':pct(s.rep,0)}</td><td>${!(s.cheks||0)?pill('new','Sotuv yoʼq'):(s.akt||0)<40?pill('crit','Past'):(s.akt||0)<55?pill('warn','Oʼrtachadan past'):pill('ok','Meʼyorda')}</td></tr>`).join('')||'<tr><td colspan="8" class="empty">Doʼkon topilmadi</td></tr>'}
  </tbody></table></div>`,'real Billz · doʼkonda ≤30 daq · takroriy faqat mijoz bogʻlanganda')}`;
  if(!funnelEmpty) barChartH($('#c-s1'),{items,rowH:34,thick:16,valW:150,valueName:'Soni'});
  if(chartRows.length) barChartH($('#c-s2'),{items:chartRows.map(s=>({
    label:s.name,
    value:s.akt||0,
    color:(s.akt||0)<40?'var(--crit)':'var(--s1)',
    tip:`<div class="r"><span>Doʼkonda skanerlangan</span><b>${pct(s.inst||0,1)}</b></div>`,
  })),fmt:v=>fmt(v,1)+' %',max:100,ref:TOT.akt,refLabel:`Oʼrtacha ${fmt(TOT.akt,1)} %`,valueName:'QR aktivatsiya',rowH:34,thick:16});
};

RENDER.mijozlar=function(root){
  const cs=(window.__BOOT__&&window.__BOOT__.customer_stats)||{};
  const seg=cs.seg||{};
  const sh=share(),f=P().f;
  const tiles=[
    {label:'Jami roʼyxatdan oʼtgan mijozlar',value:fmt(cs.total??CUSTOMERS.length),delta:cs.new??0,upGood:true,cmp:'davrda qoʼshildi'},
    {label:'Yangi mijozlar (davr)',value:fmt(cs.new??0),delta:cs.new_delta??0,dUnit:'%',upGood:true,cmp:P().cmp},
    {label:'Takroriy xarid qilganlar',value:fmt(cs.repeat_pct??scope().rep??0,1),unit:'%',delta:cs.repeat_delta??0,dUnit:'pp',upGood:true,cmp:P().cmp},
    {label:'Bir mijozga oʼrtacha xarid',value:fmt(cs.avg_purchase_ming??0),unit:'ming soʼm',delta:cs.avg_delta??0,dUnit:'%',upGood:true,cmp:P().cmp},
  ];
  const q=state.search.trim().toLowerCase();
  const rows=CUSTOMERS.filter(c=>(state.store==='all'||c.lastSt===state.store)&&(!q||c.n.toLowerCase().includes(q)||c.ph.replace(/\s/g,'').includes(q.replace(/\s/g,''))));
  root.innerHTML=`<div class="kpis">${tiles.map(tile).join('')}</div>
  <div class="grid c21">
    ${card('Yangi mijozlar, oylar boʼyicha','<div id="c-m1"></div>','2026')}
    ${card('Segmentlar','<div id="c-m2"></div>','avtomatik: 1 xarid / 2+ xarid / 90 kun jim')}
  </div>
  ${card('Mijozlar roʼyxati',`<div class="chips" style="justify-content:space-between"><label class="search"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="7" cy="7" r="4.5"/><path d="m10.5 10.5 3 3" stroke-linecap="round"/></svg><input id="custSearch" placeholder="Ism yoki telefon raqami" value="${state.search}" aria-label="Mijoz qidirish"></label><span class="hint">${rows.length} ta · qatorni bosing — kartochka ochiladi</span></div>
  <div class="scroll"><table class="tbl"><thead><tr><th>Mijoz</th><th>Roʼyxatdan oʼtgan</th><th class="num">Xaridlar</th><th class="num">Jami summa</th><th>Oxirgi xarid</th><th class="num">Faol kafolatlar</th><th class="num">Brak soʼrovlari</th><th>Segment</th></tr></thead><tbody>
  ${rows.map((c,i)=>`<tr class="click" data-cust="${c.n}"><td><b>${c.n}</b><small>${c.ph}</small></td><td>${c.reg}</td><td class="num">${c.x}</td><td class="num">${money(c.sum)}</td><td>${c.last}<small>${storeName(c.lastSt)}</small></td><td class="num">${c.kaf}</td><td class="num">${CLAIMS.filter(k=>k.cust===c.n).length}</td><td>${pill(c.seg==='Uxlagan'?'rejected':c.seg==='Yangi'?'new':'done',c.seg)}</td></tr>`).join('')||'<tr><td colspan="8" class="empty">Topilmadi</td></tr>'}
  </tbody></table></div>`)}`;
  columnChart($('#c-m1'),{labels:MONTHS,series:[{name:'Yangi mijozlar',color:'var(--s1)',values:M_NEW.map(v=>Math.round(v*sh))}],labelTop:false});
  barChartH($('#c-m2'),{items:[{label:'Yangi (1 xarid)',value:seg.yangi||0},{label:'Doimiy (2+ xarid)',value:seg.doimiy||0},{label:'Uxlagan (90 kun)',value:seg.uxlagan||0}],rowH:36,thick:16,valueName:'Mijozlar'});
  const inp=$('#custSearch');inp.addEventListener('input',()=>{state.search=inp.value;const pos=inp.selectionStart;RENDER.mijozlar(root);const n=$('#custSearch');n.focus();n.setSelectionRange(pos,pos);});
};

let SURVEY_TPL=[];
RENDER.sorovnoma=function(root){
  // Real API — sc() (demo masshtab) ishlatilmaydi
  const rows=TOUCH.map(t=>({...t}));
  const k=P().weeks;
  const tplsAll=SURVEY_TPL.length?SURVEY_TPL:(window.__BOOT__&&window.__BOOT__.survey_templates)||[];
  // Sotuvchi baholash bu yerda koʻrsatilmaydi — faqat T1–T4
  const tpls=tplsAll.filter(t=>t.id!=='SELLER');
  const escTa=s=>String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;');
  const tplCards=tpls.map(t=>`<div class="survey-tpl" data-sid="${t.id}">
      <div class="survey-tpl-h"><b>${t.label||t.id}</b><small>${t.when||''} · ${t.hint||''}${t.is_custom?' · oʼzgartirilgan':''}</small></div>
      <textarea class="survey-tpl-ta" id="stpl_${t.id}" rows="4" maxlength="4000" aria-label="${t.label||t.id}">${escTa(t.text||t.default||'')}</textarea>
      <div class="survey-tpl-meta"><span class="hint">Oʼrinbosarlar: ${(t.placeholders||[]).join(' ')}</span>
        <button type="button" class="btn" data-stpl-reset="${t.id}">Standart</button></div>
    </div>`).join('')||'<div class="empty">Shablonlar yuklanmadi</div>';
  root.innerHTML=`${card('Bosqichlar kesimida',`<div class="scroll"><table class="tbl"><thead><tr><th>Bosqich</th><th>Qachon</th><th class="num">Yuborildi</th><th class="num">Yetkazildi</th><th class="num">Javob berildi</th><th class="num">«Yaxshi»</th><th class="num">«Muammo bor»</th><th class="num">Soʼrovga aylandi</th><th class="num">Oʼrtacha javob vaqti</th></tr></thead><tbody>
  ${rows.map(t=>`<tr><td><b>${t.t}</b><small>${t.q}</small></td><td>${t.when}</td><td class="num">${fmt(t.sent||0)}</td><td class="num">${fmt(t.dlv||0)}<small>${pct((t.dlv||0)/Math.max(1,t.sent||0)*100)}</small></td><td class="num">${fmt(t.ans||0)}<small>${pct((t.ans||0)/Math.max(1,t.dlv||0)*100)}</small></td><td class="num">${fmt(t.good||0)}<small>${pct((t.good||0)/Math.max(1,t.ans||0)*100)}</small></td><td class="num">${fmt(t.bad||0)}<small class="${(t.ans||0)&&((t.bad||0)/(t.ans||1)>0.1)?'up':''}">${pct((t.bad||0)/Math.max(1,t.ans||0)*100)}</small></td><td class="num">${fmt(t.claims||0)}</td><td class="num">${fmt(t.hrs||0,1)} soat</td></tr>`).join('')}
  </tbody></table></div>`,'muddatlar sozlamalarda oʼzgartiriladi')}
  ${card('Bosqich xabarlari (botga yuboriladigan matn)',`<div class="survey-tpls">${tplCards}</div>
    <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap"><button type="button" class="btn primary" id="stplSave">Saqlash</button></div>
    <p class="hint" style="margin:8px 0 0">HTML teglar mumkin (<code>&lt;b&gt;</code>). Tugmalar (Yaxshi / Muammo bor yoki 1–5⭐) oʼzgarmaydi — faqat matn.</p>`,'har bosqich alohida · real saqlanadi')}
  <div class="grid c21">
    ${card('Qoniqish darajasi, haftalar boʼyicha','<div id="c-q1"></div>','«yaxshi» javoblar ulushi, %')}
    ${card('Mijoz izohlari',`<div class="list">${COMMENTS.map(c=>`<div class="item ${/muammo|oʼchdi|miltilla|isib|shovqin/i.test(c.t)?'warn':''}" style="grid-template-columns:4px 1fr"><span class="stripe" style="${/zoʼr|tavsiya/i.test(c.t)?'background:var(--good)':''}"></span><div class="t"><b>${c.t}</b><small>${c.c} · ${c.p} · ${c.d}</small></div></div>`).join('')}</div>`,'servis boʼlimi oʼqib chiqadi')}
  </div>`;
  const labels=WEEKS.slice(-k);
  const v1=(W_SAT.T1||[]).slice(-k),v2=(W_SAT.T2||[]).slice(-k),v3=(W_SAT.T3||[]).slice(-k);
  if(labels.length){
    const mx=Math.max(0,...v1,...v2,...v3);
    lineChart($('#c-q1'),{labels,series:[{name:'T1 (7 kun)',color:'var(--s1)',values:v1},{name:'T2 (30 kun)',color:'var(--s2)',values:v2},{name:'T3 (90 kun)',color:'var(--s3)',values:v3}],yMin:mx>0&&mx<80?0: (mx?80:0),yMax:100,fmt:v=>fmt(v,0)+' %',endLabels:true});
  }
  root.querySelectorAll('[data-stpl-reset]').forEach(btn=>{
    btn.onclick=()=>{
      const id=btn.dataset.stplReset;
      const t=(SURVEY_TPL.find(x=>x.id===id)||tpls.find(x=>x.id===id));
      const ta=root.querySelector('#stpl_'+id);
      if(ta&&t) ta.value=t.default||'';
    };
  });
  root.querySelector('#stplSave')?.addEventListener('click',async()=>{
    const templates=tpls.map(t=>{
      const ta=root.querySelector('#stpl_'+t.id);
      return {id:t.id,text:(ta?.value||t.text||'').trim()};
    }).filter(t=>t.text);
    if(!templates.length){toast('Matn boʼsh');return;}
    try{
      const r=await api('/api/settings/survey-templates',{method:'POST',body:JSON.stringify({templates})});
      SURVEY_TPL=r.templates||templates;
      if(window.__BOOT__) window.__BOOT__.survey_templates=SURVEY_TPL;
      toast('Saqlandi');
      RENDER.sorovnoma(root);
    }catch(e){toast(e.message||'Saqlanmadi');}
  });
};

RENDER.brak=function(root){
  const base=CLAIMS.filter(c=>(state.store==='all'||c.st===state.store)&&(state.seller==='all'||c.seller===state.seller));
  const counts=Object.keys(STATUS).reduce((a,k)=>(a[k]=base.filter(c=>c.status===k).length,a),{});
  const rows=filteredClaims().slice().sort((a,b)=>b.date-a.date);
  const cs=(window.__BOOT__&&window.__BOOT__.claim_stats)||{};
  const cd=cs.deltas||{};
  const dRec=cd.received||{};
  const dApp=cd.approved_pct||{};
  const dClose=cd.close_avg||{};
  const dSla=cd.sla||{};
  const dCost=cd.cost_mln||{};
  const tiles=[
    {label:'Kelgan soʼrovlar (davr)',value:fmt(cs.received||0),delta:dRec.value??0,dUnit:dRec.unit||undefined,upGood:false,cmp:P().cmp},
    {label:'Tasdiqlangan',value:fmt(cs.approved_pct??0,0),unit:'%',delta:dApp.value??0,dUnit:dApp.unit||'pp',upGood:false,cmp:'kelganlarga nisbatan'},
    {label:'Oʼrtacha yopish muddati',value:fmt(cs.close_avg??0,1),unit:'kun',delta:dClose.value??0,dUnit:dClose.unit||'kun',upGood:false,cmp:P().cmp},
    {label:'SLA buzilgan (7 kundan ortiq)',value:fmt(cs.sla??base.filter(c=>isOpen(c)&&daysOpen(c)>7).length),delta:dSla.value??0,upGood:false,cmp:'hozir ochiq'},
    {label:'Almashtirish xarajati (davr)',value:fmt(cs.cost_mln??0,1),unit:'mln soʼm',delta:dCost.value??0,dUnit:dCost.unit||'%',upGood:false,cmp:P().cmp},
  ];
  const w=weeks();
  root.innerHTML=`<div class="kpis k5">${tiles.map(tile).join('')}</div>
  <div class="grid c2">
    ${card('Kelgan va yopilgan soʼrovlar',(w.labels.length&&(w.in.some(v=>v>0)||w.out.some(v=>v>0)))?'<div id="c-b1"></div>':'<div class="empty">Hali soʼrov yoʼq — grafik real maʼlumotdan</div>','haftalar boʼyicha')}
    ${card('Holatlar oqimi',flowSVG()+'<p>Har bir holat oʼzgarishi vaqti va kim qilgani saqlanadi — SLA va bosqichlar boʼyicha vaqt tahlili shundan hisoblanadi.</p>','har bir oʼzgarish vaqt bilan yoziladi')}
  </div>
  ${card('Soʼrovlar roʼyxati',`<div class="chips">${[['all','Hammasi',base.length],...Object.keys(STATUS).map(k=>[k,STATUS[k],counts[k]])].map(([k,l,n])=>`<button class="chip" type="button" data-cf="${k}" aria-pressed="${state.claimFilter===k}">${l}<span class="n">${n}</span></button>`).join('')}</div>
  <div class="scroll"><table class="tbl"><thead><tr><th>№ / sana</th><th>Mijoz</th><th>Mahsulot</th><th>Doʼkon / sotuvchi</th><th>Sabab</th><th>Holat</th><th class="num">Ochiq kunlar</th><th>Masʼul</th></tr></thead><tbody>
  ${rows.map(c=>{const d=daysOpen(c);const over=isOpen(c)&&d>7;const nItems=c.items_n||(c.items&&c.items.length)||0;const sabab=nItems>1?`${nItems} mahsulot`:(c.reason||'—');return `<tr class="click" data-claim="${c.id}"><td><b>${c.id}</b><small>${dfull(c.date)}</small></td><td>${c.cust}<small>${c.ph}</small></td><td>${c.prod}<small>Chek ${c.chek||'—'} · ${c.qty||1} dona brak</small></td><td>${storeName(c.st)}<small>${c.seller}</small></td><td>${sabab}</td><td>${pill(c.status,STATUS[c.status])}</td><td class="num ${over?'up':''}">${isOpen(c)?`<b>${d}</b> kun`:`yopildi ${dstr(c.closed)}`}</td><td>${c.assignee||'<span class="flat">tayinlanmagan</span>'}</td></tr>`;}).join('')||'<tr><td colspan="8" class="empty">Bu filtrda soʼrov yoʼq</td></tr>'}
  </tbody></table></div>`,'qatorni bosing — kartochka ochiladi')}
  ${card('Kuryerlar reytingi',`<div class="scroll"><table class="tbl"><thead><tr><th>Kuryer</th><th>Hudud</th><th class="num">Ochiq</th><th class="num">Yetkazilgan</th><th class="num">Mijoz Ha</th><th class="num">Nizo</th><th class="num">⭐</th><th class="num">Muddat</th><th class="num">Chora</th></tr></thead><tbody>${COURIERS.map(k=>`<tr><td><b>${k.name}</b><small>${k.tg||'—'} · ${k.phone||'—'}</small></td><td>${k.region||'—'}</td><td class="num">${k.open}</td><td class="num">${k.delivered}</td><td class="num">${k.confirmed}</td><td class="num ${k.dispute?'up':''}">${k.dispute}</td><td class="num">${k.rating_avg!=null?`${k.rating_avg} (${k.rating_n||0})`:'—'}</td><td class="num ${k.overdue?'up':''}">${k.overdue}</td><td class="num">${k.sanctions||0}</td></tr>`).join('')||'<tr><td colspan="9" class="empty">Kuryer yoʼq — Xodimlarda yarating</td></tr>'}</tbody></table></div>`,'real · panel-boot')}`;
  const el=$('#c-b1');
  if(el) lineChart(el,{labels:w.labels,series:[{name:'Kelgan',color:'var(--s2)',values:w.in},{name:'Yopilgan',color:'var(--s1)',values:w.out}],endLabels:true});
};
function courierSteps(c){
  const s=[['assigned','Rahbar belgiladi',c.assigned_at],['en_route','Yoʼlda',c.en_route_at],['delivered','Yetkazdi',c.delivered_at]];
  const order=['assigned','en_route','delivered','done','dispute','unconfirmed'];
  const idx=order.indexOf(c.status);
  return `<div class="steps">${s.map((x,i)=>{const done=x[2]&&(idx>i||idx>=3);const now=idx===i;return `<span class="${done?'done':now?'now':''}"><b>${x[1]}</b>${x[2]?dfull(new Date(x[2])):(now?'kutilmoqda':'—')}</span>`;}).join('')}</div>`;
}
function flowSVG(){
  const box=(x,y,w,label,color)=>`<rect x="${x}" y="${y}" width="${w}" height="36" rx="8" style="fill:var(--surface-2);stroke:var(--line-2)"/><circle cx="${x+14}" cy="${y+18}" r="4" style="fill:${color}"/><text x="${x+24}" y="${y+22}" style="font-size:12px;fill:var(--ink)">${label}</text>`;
  const arrow=d=>`<path d="${d}" style="fill:none;stroke:var(--ink-3);stroke-width:1.5" marker-end="url(#arr)"/>`;
  const note=(x,y,s)=>`<text x="${x}" y="${y}" style="font-size:10.5px;fill:var(--ink-3)">${s}</text>`;
  return `<svg viewBox="0 0 430 232" width="430" height="232" style="display:block;width:100%;max-width:430px;height:auto" role="img" aria-label="Holatlar oqimi"><defs><marker id="arr" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto"><path d="M0 0 10 5 0 10z" style="fill:var(--ink-3)"/></marker></defs>
  ${box(0,2,150,'Yangi','var(--s1)')}${box(0,50,150,'Koʼrib chiqilmoqda','var(--warn)')}${box(0,98,150,'Tasdiqlangan','var(--accent)')}${box(0,146,150,'Kuryer / Yoʼlda','var(--s3)')}${box(0,194,150,'Yakunlandi','var(--good)')}${box(260,98,150,'Rad / Nizo','var(--crit)')}
  ${arrow('M75 38 V48')}${arrow('M75 86 V96')}${arrow('M75 134 V144')}${arrow('M75 182 V192')}${arrow('M150 66 H335 V98')}
  ${note(180,40,'2 soat · admin')}${note(180,160,'72 soat · kuryer')}${note(196,88,'rad / nizo')}
  </svg>`;
}

function claimDrawer(c){
  if(!c) return;
  const logs=Array.isArray(c.logs)?c.logs:[];
  const tl=[];
  if(logs.length){
    logs.forEach(l=>{
      const to=STATUS[l.to]||l.to||'—';
      const who=l.user?` — ${l.user}`:'';
      const note=l.note?`: ${l.note}`:'';
      const when=l.at?dfull(new Date(l.at))+(l.at.includes('T')?', '+new Date(l.at).toLocaleTimeString('uz-UZ',{hour:'2-digit',minute:'2-digit'}):''):'—';
      tl.push({t:(l.from?`${STATUS[l.from]||l.from} → ${to}`:to)+who+note,d:when,done:true});
    });
  }else{
    tl.push({t:'Kelib tushdi (bot)',d:dfull(c.date),done:true});
  }
  const nextHint={new:'servis qabul qilishi',review:'tasdiqlash yoki rad',approved:'kuryer belgilash',assigned:'kuryer yoʼlga chiqishi',en_route:'yetkazish + foto',delivered:'mijoz tasdigʼi',dispute:'nizo yechimi'}[c.status]||'—';
  if(isOpen(c))tl.push({t:'Keyingi qadam: '+nextHint,d:'kutilmoqda · '+daysOpen(c)+' kun ochiq',now:true});
  const courOpts=COURIERS.map(k=>`<option value="${k.id}" ${Number(c.courier_id)===Number(k.id)?'selected':''}>${k.name} — ${k.region} · ochiq ${k.open}</option>`).join('');
  let acts='';
  if(c.status==='new') acts=`<button class="btn primary" data-act="accept" type="button">Qabul qilish</button>`;
  else if(c.status==='review'){
    const needPhoto=!c.photos_viewed&&(c.photos||0)>0;
    acts=(needPhoto?'<div class="alert warn" style="width:100%;margin:0 0 8px">Avval yuqoridagi mijoz fotosini bosing (koʼrilgan deb belgilanadi)</div>':'')
      +`<button class="btn primary" data-act="approve" type="button" ${needPhoto?'disabled':''}>Brakni tasdiqlash</button><button class="btn danger" data-act="reject" type="button">Rad etish</button>`;
  }
  else if(c.status==='approved') acts=`<label class="lbl">Kuryer</label><select class="sel" id="dwCour">${courOpts||'<option value="">Kuryer yoʼq — Xodimlarda yarating</option>'}</select><button class="btn primary" data-act="assign" type="button">Kuryerni belgilash</button>`;
  else if(c.status==='assigned') acts=`${courierSteps(c)}<label class="lbl">Kuryerni almashtirish</label><select class="sel" id="dwCour">${courOpts}</select><button class="btn" data-act="assign" type="button">Qayta belgilash</button>`;
  else if(c.status==='en_route') acts=`${courierSteps(c)}`;
  else if(c.status==='delivered') acts=`${courierSteps(c)}${c.delivery_photo?`<div class="photos" style="margin-top:8px"><a class="ph" href="${c.delivery_photo}" target="_blank" rel="noopener">topshirish fotosi</a></div>`:''}<div class="alert warn">Mijoz tasdigʼi kutilmoqda — bot / Mini App</div>`;
  else if(c.status==='dispute') acts=`<button class="btn primary" data-act="dispute_retry" type="button">Kuryer qayta boradi</button><button class="btn" data-act="dispute_close" type="button">Kelishildi — yopish</button><button class="btn danger" data-act="dispute_sanction" type="button">Kuryerga chora</button>`;
  const urls=Array.isArray(c.photo_urls)?c.photo_urls:[];
  const kaf=c.warranty_ok===false?pill('rejected','Tugagan'):pill('ok','Faol');
  const kafM=c.warranty_months||12;
  const dl=c.deadline?new Date(c.deadline):null;
  const items=Array.isArray(c.items)&&c.items.length?c.items:[{name:c.prod,qty:c.qty||1,reason:c.reason||'',text:c.text||'',photo_urls:urls}];
  const itemCards=items.map((it,i)=>{
    const pu=Array.isArray(it.photo_urls)?it.photo_urls:[];
    const lb=JSON.stringify(pu).replace(/'/g,"&#39;");
    return `<details class="claim-item" ${i===0?'open':''}>
      <summary><b>${it.name||'Mahsulot'}</b><span class="flat">×${it.qty||1} brak</span></summary>
      <dl class="stat" style="margin-top:8px"><dt>Sabab</dt><dd>${it.reason||'—'}</dd><dt>Muammo</dt><dd style="font-weight:400">«${it.text||'—'}»</dd></dl>
      ${pu.length?`<div class="photo" data-claim-photos="${c.db_id}-i${i}" data-lb-urls='${lb}'>${pu.map((u,j)=>`<button type="button" data-lb="${j}" aria-label="foto ${j+1}"><img src="${u}" alt="foto ${j+1}"></button>`).join('')}</div>`:'<div class="hint">Rasm yoʼq</div>'}
    </details>`;
  }).join('');
  openDrawer(`<div class="dh"><div><h2>${c.id}</h2><div style="margin-top:6px">${pill(c.status,STATUS[c.status])} ${isOpen(c)&&daysOpen(c)>7?pill('crit','SLA buzilgan'):''}${c.confirm==='yes'?pill('ok','Mijoz Ha'):c.confirm==='no'?pill('crit','Mijoz Yoʼq'):''}</div><p class="hint" style="margin:6px 0 0">Chek ${c.chek||'—'} · ${c.qty||1} dona brak · ${(c.items_n||items.length)} mahsulot</p></div><button class="x" type="button" aria-label="Yopish"><svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="m2 2 10 10M12 2 2 12"/></svg></button></div>
  <div><h4>Mijoz va xarid</h4><dl class="stat"><dt>Mijoz</dt><dd>${c.cust}</dd><dt>Telefon</dt><dd>${c.ph}</dd><dt>Manzil</dt><dd>${c.address||'—'}${c.address_lat&&c.address_lng?` <a href="https://maps.google.com/?q=${c.address_lat},${c.address_lng}" target="_blank" rel="noopener">xarita</a>`:''}</dd>${c.address_lat&&c.address_lng?`<dt></dt><dd><div class="claim-map"><iframe title="Manzil" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://maps.google.com/maps?q=${c.address_lat},${c.address_lng}&ll=${c.address_lat},${c.address_lng}&z=17&t=k&hl=uz&output=embed" allowfullscreen></iframe></div></dd>`:''}<dt>Chek</dt><dd>${c.chek} · ${storeName(c.st)}</dd><dt>Sotuvchi</dt><dd>${c.seller}</dd><dt>Kafolat</dt><dd>${kaf} ${kafM} oy</dd></dl></div>
  <div><h4>Soʼrov</h4>
    <div class="claim-items">${itemCards}</div>
    <dl class="stat" style="margin-top:12px"><dt>Kuryer</dt><dd>${c.courier||'—'} ${c.courier_phone||''}</dd><dt>Muddat</dt><dd>${dl?dfull(dl):'—'}</dd>${c.rej?`<dt>Rad sababi</dt><dd>${c.rej}</dd>`:''}${c.confirm_reason?`<dt>Nizo sababi</dt><dd>${c.confirm_reason}</dd>`:''}</dl>
  </div>
  ${c.delivery_photo?`<div><h4>Topshirish fotosi</h4><img src="${c.delivery_photo}" alt="delivery" style="max-width:100%;border-radius:8px"></div>`:''}
  <div><h4>Holatlar tarixi</h4><ul class="tl">${tl.map(e=>`<li class="${e.done?'done':''} ${e.now?'now':''}">${e.t}<small>${e.d}</small></li>`).join('')}</ul></div>
  ${acts?`<div class="actions" style="flex-wrap:wrap" data-claim="${c.id}" data-db="${c.db_id||''}">${acts}</div>`:''}`);
}
function mergeClaimRow(row,prev){
  if(!row) return prev;
  return {
    ...prev,
    ...row,
    date:row.date?new Date(row.date):(prev&&prev.date),
    closed:row.closed?new Date(row.closed):(prev&&prev.closed)||undefined,
    photo_urls:row.photo_urls||(prev&&prev.photo_urls)||[],
    logs:row.logs||(prev&&prev.logs)||[],
    photos_viewed:row.photos_viewed!=null?!!row.photos_viewed:!!(prev&&prev.photos_viewed),
  };
}
async function postCourierAction(cid,action,extraFd){
  if(!cid||cid==='undefined') throw new Error('Soʼrov ID topilmadi — sahifani yangilang');
  const fd=extraFd||new FormData();
  if(!fd.has('action')) fd.append('action',action);
  const r=await fetch('/api/claims/'+cid+'/courier',{method:'POST',credentials:'same-origin',headers:{'X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest',Accept:'application/json'},body:fd});
  const d=await r.json().catch(()=>({}));
  if(!r.ok){
    const errMsg=d.message||(d.errors&&Object.values(d.errors).flat()[0])||('HTTP '+r.status);
    throw new Error(errMsg);
  }
  return d;
}
$('#drawer').addEventListener('click',async e=>{
  const lb=e.target.closest('[data-lb]');
  if(lb){
    e.preventDefault();
    const box=lb.closest('[data-claim-photos]');
    let urls=[];
    try{urls=JSON.parse(box?.getAttribute('data-lb-urls')||'[]');}catch(_){urls=[];}
    openLightbox(urls,Number(lb.dataset.lb)||0);
    const dbId=box&&box.dataset.claimPhotos;
    if(dbId){
      try{
        const res=await api('/api/claims/'+dbId,{method:'PATCH',body:JSON.stringify({action:'photos_viewed'})});
        const prev=CLAIMS.find(k=>String(k.db_id)===String(dbId));
        const mapped=mergeClaimRow(res.claim,prev||{});
        const idx=CLAIMS.findIndex(k=>String(k.db_id)===String(dbId));
        if(idx>=0) CLAIMS[idx]=mapped; else if(mapped.id) CLAIMS.push(mapped);
        claimDrawer(mapped);
      }catch(err){toast(err.message||'Foto koʼrilgan deb saqlanmadi');}
    }
    return;
  }
  const cact=e.target.closest('[data-cact]');
  if(cact){
    const cid=cact.dataset.cid, action=cact.dataset.cact;
    cact.disabled=true;
    try{
      const res=await postCourierAction(cid,action);
      const prev=CLAIMS.find(k=>String(k.db_id)===String(cid));
      const mapped=mergeClaimRow(res.claim,prev||{});
      const idx=CLAIMS.findIndex(k=>String(k.db_id)===String(cid));
      if(idx>=0) CLAIMS[idx]=mapped;
      toast((mapped.id||'')+': «'+(STATUS[mapped.status]||mapped.status)+'»');
      await reloadBoot();
      claimDrawer(CLAIMS.find(k=>String(k.db_id)===String(cid))||mapped);
    }catch(err){toast(err.message||'Xato');}
    finally{cact.disabled=false;}
    return;
  }
  const b=e.target.closest('[data-act]');if(!b)return;
  const wrap=b.closest('[data-claim]');
  const id=wrap.dataset.claim;
  const c=CLAIMS.find(k=>k.id===id);if(!c||!c.db_id){toast('Soʼrov topilmadi');return;}
  const action=b.dataset.act;
  const body={action};
  if(action==='reject'){
    const reason=await askModal({title:'Rad etish',sub:`${c.id} · ${c.prod}`,label:'Rad sababi',type:'textarea',value:'Kafolat shartiga kirmaydi',required:true,okText:'Rad etish'});
    if(reason==null) return;
    body.reject_reason=reason;body.note=reason;
  }
  if(action==='assign'){
    const sel=$('#dwCour');const cid=sel&&sel.value;if(!cid){toast('Kuryer tanlang');return;}
    body.courier_id=Number(cid);
  }
  if(action==='dispute_close'||action==='dispute_sanction'){
    const note=await askModal({title:'Izoh',sub:c.id,label:'Izoh',type:'textarea',value:'',required:true,okText:'Saqlash'});
    if(note==null) return;body.note=note;
  }
  b.disabled=true;
  try{
    const res=await api('/api/claims/'+c.db_id,{method:'PATCH',body:JSON.stringify(body)});
    const row=res.claim||{};
    const mapped={...row,date:row.date?new Date(row.date):c.date,closed:row.closed?new Date(row.closed):undefined};
    toast(`${mapped.id}: «${STATUS[mapped.status]}»`);
    await reloadBoot();
    claimDrawer(CLAIMS.find(k=>k.id===id)||mapped);
  }catch(err){toast(err.message||'Saqlashda xato');}
  finally{b.disabled=false;}
});

RENDER.kuryer=function(root){
  if(!COURIERS.length){
    root.innerHTML=card('Kuryer paneli','<div class="empty">Hali kuryer yoʼq. <b>Xodimlar</b> boʼlimida «Kuryer» roli bilan xodim yarating (Telegram ID majburiy — guruh tugmalari uchun).</div>');
    return;
  }
  const au=(window.__BOOT__&&window.__BOOT__.auth_user)||{};
  const iAmCourier=!!au.is_courier;
  if(iAmCourier&&au.id){
    state.cour=Number(au.id);
  }else if(!state.cour||!COURIERS.some(k=>Number(k.id)===Number(state.cour))){
    state.cour=COURIERS[0].id;
  }
  const me=COURIERS.find(k=>Number(k.id)===Number(state.cour))||COURIERS[0];
  const mine=CLAIMS.filter(c=>Number(c.courier_id)===Number(me.id));
  const tabs={new:c=>c.status==='assigned',work:c=>c.status==='en_route',done:c=>['delivered','done','dispute','unconfirmed'].includes(c.status)};
  const names={new:'Yangi',work:'Jarayonda',done:'Bajarilgan',stats:'Natijalarim'};
  let body='';
  const c=state.cSel?CLAIMS.find(x=>x.id===state.cSel):null;
  const durMs=ms=>{ms=Math.max(0,ms);const d=Math.floor(ms/864e5),h=Math.floor(ms%864e5/36e5);if(d)return d+' kun '+h+' soat';if(h)return h+' soat';return Math.floor(ms/6e4)+' daq';};
  const canAct=iAmCourier&&Number(me.id)===Number(au.id);
  const inDetail=!!(c&&Number(c.courier_id)===Number(me.id));
  if(inDetail){
    const left=c.deadline?new Date(c.deadline)-Date.now():0;
    const deadCls=left<=0?'crit':left<864e5?'warn':'ok';
    const tel=isCourierOpen(c)&&c.ph?`<a href="tel:${String(c.ph).replace(/[^\d+]/g,'')}">${c.ph}</a>`:(isCourierOpen(c)?(c.ph||'—'):'yopilgach yashirin');
    const addr=(c.address&&String(c.address).trim())||'';
    let dock='';
    if(canAct&&c.status==='assigned'){
      dock=`<div class="cd-dock"><div class="alert info" style="margin:0">Mahsulotni ${c.pickup_shop||storeName(c.st)} dan oling</div><button class="btn primary big" data-cact="en_route" data-cid="${c.db_id}" type="button">Yoʼldaman</button></div>`;
    }else if(canAct&&c.status==='en_route'){
      dock=`<div class="cd-dock">
        <div class="alert warn" id="cPhotoNeed" style="margin:0"${c.delivery_photo?' hidden':''}>Yetkazish fotosi majburiy</div>
        <div id="cPhotoPrev">${c.delivery_photo?`<img class="cd-prev" src="${c.delivery_photo}" alt="">`:''}</div>
        <div class="big2"><label class="btn big" style="cursor:pointer">Foto olish<input type="file" accept="image/*" capture="environment" id="cPhoto" hidden></label>
        <button class="btn good big" data-cact="deliver" data-cid="${c.db_id}" type="button" id="cDeliver" ${c.delivery_photo?'':'disabled'}>Yetkazdim</button></div></div>`;
    }else if(c.status==='delivered') dock=`<div class="cd-dock"><div class="alert warn" style="margin:0">Mijoz tasdigʼi kutilmoqda</div></div>`;
    else if(c.status==='done') dock=`<div class="cd-dock"><div class="alert ok" style="margin:0">Yakunlandi</div></div>`;
    else if(c.status==='dispute') dock=`<div class="cd-dock"><div class="alert crit" style="margin:0">Nizo — admin bilan bogʼlaning</div></div>`;
    const prob=canAct&&isCourierOpen(c)?`<details class="cd-prob"><summary>Muammo bormi?</summary>
      <select class="sel" id="cProb"><option value="">Sabab…</option><option>Mijoz javob bermayapti</option><option>Manzil notoʼgʼri</option><option>Mahsulot omborda yoʼq</option></select>
      <div class="actions"><button class="btn" data-cact="problem" data-cid="${c.db_id}" type="button">Adminga xabar</button></div></details>`:'';
    body=`<div class="cdet">
      <div class="cd-head"><b>${c.id}</b>${pill(c.status,STATUS[c.status])}</div>
      ${isCourierOpen(c)&&c.deadline?`<div class="cd-dead ${deadCls}">Muddat ${dfull(new Date(c.deadline))} · ${left>0?durMs(left)+' qoldi':durMs(-left)+' oʼtdi'}</div>`:''}
      <div class="cd-card"><h4>Mijoz</h4>
        <div class="cd-row"><span>Ism</span><b>${c.cust||'—'}</b></div>
        <div class="cd-row"><span>Telefon</span><b>${tel}</b></div>
        <div class="cd-addr">${addr||'Manzil kiritilmagan'}</div>
      </div>
      <div class="cd-card"><h4>Olib borish</h4>
        <div class="cd-row"><span>Mahsulot</span><b>${c.prod||'—'}</b></div>
        <div class="cd-row"><span>Dona</span><b>${c.qty||1}</b></div>
        <div class="cd-row"><span>Qayerdan</span><b>${c.pickup_shop||storeName(c.st)}</b></div>
        ${(c.photo_urls||[]).length?`<div class="cd-photos">${c.photo_urls.map(u=>`<img src="${u}" alt="">`).join('')}</div>`:''}
      </div>
      ${prob}${dock}
    </div>`;
  }else if(state.cTab==='stats'){
    body=`<div class="stiles" style="display:grid;grid-template-columns:1fr 1fr;gap:8px"><div class="stile card"><div class="v" style="font-size:22px;font-weight:600">${me.delivered}</div><div class="l hint">Yetkazilgan</div></div><div class="stile card"><div class="v" style="font-size:22px;font-weight:600">${me.confirmed}</div><div class="l hint">Mijoz Ha</div></div><div class="stile card"><div class="v" style="font-size:22px;font-weight:600">${me.dispute}</div><div class="l hint">Nizo</div></div><div class="stile card"><div class="v" style="font-size:22px;font-weight:600">${me.overdue}</div><div class="l hint">Muddat buzilgan</div></div></div><p class="hint" style="margin-top:12px">Faqat oʼz natijalari (real).</p>`;
  }else{
    const list=mine.filter(tabs[state.cTab]||(()=>false)).sort((a,b)=>new Date(a.deadline||a.date)-new Date(b.deadline||b.date));
    body=list.map(x=>{const left=x.deadline?new Date(x.deadline)-Date.now():0;return `<div class="titem" data-csel="${x.id}" style="padding:10px;border:1px solid var(--line);border-radius:10px;margin-bottom:8px;cursor:pointer"><div style="display:flex;justify-content:space-between;gap:8px"><b>${x.id} · ${x.cust.split(' ')[0]}</b>${pill(x.status,STATUS[x.status])}</div><small class="hint">${x.prod} · ${(x.address||'').split(',')[0]||'—'}</small>${isCourierOpen(x)&&x.deadline?`<small class="${left<=0?'up':''}">${left>0?'muddat: '+durMs(left):'muddat oʼtdi'}</small>`:''}</div>`;}).join('')||'<div class="empty">Bu boʼlimda topshiriq yoʼq</div>';
  }
  const barLeft=inDetail
    ? `<button type="button" class="cback" data-cback="1">← Orqaga</button>`
    : `<b>Salom, ${me.name.split(' ')[0]}</b>`;
  const barRight=iAmCourier
    ? `<a href="{{ route('logout') }}" class="cout" onclick="event.preventDefault();document.getElementById('logout-form').submit();">Chiqish</a>`
    : `<select class="sel" style="width:auto" id="courPick">${COURIERS.map(k=>`<option value="${k.id}" ${Number(k.id)===Number(me.id)?'selected':''}>${k.name}</option>`).join('')}</select>`;
  const rating=iAmCourier?'':`<div class="side-note" style="flex:1;min-width:240px">${card('Kuryerlar reytingi',`<div class="scroll"><table class="tbl"><thead><tr><th>Kuryer</th><th class="num">Ochiq</th><th class="num">Yetkazilgan</th><th class="num">Ha</th><th class="num">Nizo</th></tr></thead><tbody>${COURIERS.map(k=>`<tr><td><b>${k.name}</b><small>${k.region}</small></td><td class="num">${k.open}</td><td class="num">${k.delivered}</td><td class="num">${k.confirmed}</td><td class="num">${k.dispute}</td></tr>`).join('')}</tbody></table></div>`,'real · panel-boot')}</div>`;
  root.innerHTML=`<div class="phone-wrap"><div class="phone"><div class="scr"><div class="bar">${barLeft}${barRight}</div><div class="body${inDetail?' cdet-body':''}">${inDetail?'':`<div class="tabs">${Object.keys(names).map(k=>`<button type="button" data-ctab="${k}" aria-pressed="${state.cTab===k}">${names[k]}${k!=='stats'?` (${mine.filter(tabs[k]).length})`:''}</button>`).join('')}</div>`}${body}</div></div></div>${rating}`;
  const pick=$('#courPick');if(pick) pick.onchange=()=>{state.cour=Number(pick.value);state.cSel=null;render();};
  root.querySelectorAll('[data-ctab]').forEach(b=>b.onclick=()=>{state.cTab=b.dataset.ctab;state.cSel=null;render();});
  root.querySelectorAll('[data-csel]').forEach(b=>b.onclick=()=>{state.cSel=b.dataset.csel;render();});
  const back=root.querySelector('[data-cback]');if(back) back.onclick=()=>{state.cSel=null;render();};
  const photoInp=root.querySelector('#cPhoto');
  const deliverBtn=root.querySelector('#cDeliver');
  const photoPrev=root.querySelector('#cPhotoPrev');
  const photoNeed=root.querySelector('#cPhotoNeed');
  if(photoInp){
    photoInp.onchange=()=>{
      const f=photoInp.files&&photoInp.files[0];
      const has=!!(f||(c&&c.delivery_photo));
      if(deliverBtn) deliverBtn.disabled=!has;
      if(photoNeed) photoNeed.hidden=has;
      if(photoPrev){
        if(f){
          const url=URL.createObjectURL(f);
          photoPrev.innerHTML=`<img class="cd-prev" src="${url}" alt="preview">`;
        }else if(c&&c.delivery_photo){
          photoPrev.innerHTML=`<img class="cd-prev" src="${c.delivery_photo}" alt="">`;
        }else{
          photoPrev.innerHTML='';
        }
      }
    };
  }
  root.querySelectorAll('[data-cact]').forEach(btn=>btn.onclick=async()=>{
    const cid=btn.dataset.cid, action=btn.dataset.cact;
    const fd=new FormData();fd.append('action',action);
    if(action==='deliver'){
      const inp=root.querySelector('#cPhoto');
      const f=inp&&inp.files&&inp.files[0];
      if(!f&&!c.delivery_photo){toast('Avval foto oling');return;}
      if(f) fd.append('photo',f);
    }
    if(action==='problem'){
      const n=root.querySelector('#cProb')?.value;if(!n){toast('Sabab tanlang');return;}fd.append('note',n);
    }
    btn.disabled=true;
    try{
      const res=await postCourierAction(cid,action,fd);
      toast((res.claim&&STATUS[res.claim.status])?('«'+STATUS[res.claim.status]+'»'):'Saqlandi');
      const keep=state.cSel;
      await reloadBoot();
      if(action==='en_route'||action==='deliver') state.cSel=keep;
      if(action==='deliver') state.cSel=null;
      render();
    }catch(err){toast(err.message||'Xato');}
    finally{btn.disabled=false;}
  });
};

const NO_REASONS=['Mahsulot olib kelinmadi','Boshqa mahsulot olib kelindi','Nuqsonli mahsulot olib kelindi','Kuryer kelmadi','Boshqa'];
RENDER.miniapp=function(root){
  if(!state.mTab) state.mTab='claims';
  const wait=CLAIMS.filter(c=>c.status==='delivered'&&!c.confirm);
  const mine=CLAIMS.slice().sort((a,b)=>new Date(b.date)-new Date(a.date));
  const icons={home:'<path d="M2.5 8 8 3l5.5 5M4 7v6h8V7"/>',claims:'<path d="M3 2h10v12H3zM5.5 5h5M5.5 8h5M5.5 11h3"/>',form:'<path d="M8 3v10M3 8h10"/>'};
  let body='';
  if(state.mTab==='home'){
    body=`<p class="hint" style="margin:0 0 8px">Panel koʼrinishi — mijoz Mini Appida cheklar bot orqali keladi (real).</p>
      <div class="alert info">Tasdiq kutilmoqda: <b>${wait.length}</b> ta. Toʼliq Mini App: <code>/miniapp</code> (Telegram initData).</div>
      ${wait.map(c=>`<div class="titem" data-msel="${c.id}"><div class="r"><b>${c.id}</b>${pill(c.status,STATUS[c.status])}</div><small>${c.cust} · ${c.prod}</small><small class="amb">Tasdiq kutilmoqda →</small></div>`).join('')||'<div class="empty">Hozir tasdiq kutayotgan soʼrov yoʼq</div>'}`;
  }else if(state.mTab==='claims'){
    const c=state.mSel?CLAIMS.find(x=>x.id===state.mSel):null;
    if(c){
      const order=['new','review','approved','assigned','en_route','delivered','done'];
      const labels={new:'Yuborildi',review:'Koʼrib chiqilmoqda',approved:'Tasdiqlandi',assigned:'Kuryer belgilandi',en_route:'Kuryer yoʼlda',delivered:'Kuryer yetkazdi',done:'Yakunlandi'};
      const reached=s=>(c.logs||[]).some(e=>e.to===s)||c.status===s;
      body=`<button class="btn" data-mback="1" type="button" style="margin-bottom:10px">← Soʼrovlarim</button>
        <div style="display:flex;justify-content:space-between;gap:8px;align-items:baseline"><b style="font-size:15px">${c.id}</b>${pill(c.status,STATUS[c.status])}</div>
        <p class="hint" style="margin:0">${c.prod} × ${c.qty||1} · ${c.reason}</p>
        ${c.status==='rejected'?`<div class="alert warn">Rad: ${c.rej||'—'}</div>`:''}
        ${c.status==='dispute'?`<div class="alert crit">Nizo: ${c.confirm_reason||'—'}</div>`:''}
        ${c.courier?`<div class="alert info">Kuryer: <b>${c.courier}</b> ${c.courier_phone||''}</div>`:''}
        ${c.status==='delivered'&&!c.confirm?`<div class="alert warn">Mijoz Ha/Yoʼq ni botda yoki Telegram Mini App (<code>/miniapp</code>) da beradi — panel faqat kuzatuv.</div>`:''}
        ${['assigned','en_route','delivered','done','dispute'].includes(c.status)?courierSteps(c):''}
        <div class="psec"><h4>Holat</h4><ul class="tl">${order.filter(s=>!(s==='done'&&['rejected','dispute','unconfirmed'].includes(c.status))).map(s=>{const ev=(c.logs||[]).find(e=>e.to===s);const r=reached(s);return `<li class="${r?(c.status===s&&s!=='done'?'now':'done'):''}">${labels[s]}<small>${ev&&ev.at?dfull(new Date(ev.at)):(r?'':'—')}</small></li>`;}).join('')}</ul></div>`;
    }else{
      body=mine.map(x=>`<div class="titem" data-msel="${x.id}"><div class="r"><b>${x.id}</b>${pill(x.status,STATUS[x.status])}</div><small>${x.cust} · ${x.prod} · ${dfull(x.date)}</small>${x.status==='delivered'&&!x.confirm?'<small class="amb">Tasdiq kutilmoqda →</small>':''}</div>`).join('')||'<div class="empty">Soʼrov yoʼq</div>';
    }
  }else{
    body=`<div class="banner">Yangi soʼrov mijoz Mini Appida yoki bot «Shikoyat» → forma orqali yuboriladi (real <code>/shikoyat/{token}</code>).</div>
      <p class="hint">Panelda forma yaratilmaydi — mijoz Telegram orqali ochadi. URL: <code>${location.origin}/miniapp</code></p>`;
  }
  const short=(mine[0]&&mine[0].cust)||'Mijoz';
  root.innerHTML=`<div class="phone-wrap"><div class="phone"><div class="scr"><div class="bar"><b>QR Kafolat</b><span class="hint">${short.split(' ')[0]}</span></div><div class="body">${body}</div><div class="pnav">${[['home','Mening uyim'],['claims','Soʼrovlarim'],['form','Yangi soʼrov']].map(([k,l])=>`<button type="button" data-mtab="${k}" aria-current="${state.mTab===k?'page':'false'}"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">${icons[k]}</svg>${l}</button>`).join('')}</div></div></div>
  <div class="side-note">${card('Mini App','<p class="hint" style="margin:0">Botdagi web_app tugmasi <code>/miniapp</code> ni ochadi. initData HMAC tekshiriladi — demolarsiz. Soʼrov holati va Ha/Yoʼq tasdiq real claim jadvalidan.</p>')}
  ${card('Hozir',`<dl class="stat"><dt>Ochiq soʼrovlar</dt><dd>${CLAIMS.filter(isOpen).length}</dd><dt>Tasdiq kutilmoqda</dt><dd class="${wait.length?'amb':''}">${wait.length}</dd></dl>`)}
  ${card('Bot tasdiq','<p class="hint" style="margin:0">«Yetkazdim» → mijozga Ha/Yoʼq. Javob: done yoki dispute. 24 soat eslatma, 72 soat → unconfirmed.</p>')}</div>`;
  $$('[data-mtab]').forEach(b=>b.onclick=()=>{state.mTab=b.dataset.mtab;state.mSel=null;render();});
  $$('[data-msel]').forEach(b=>b.onclick=()=>{state.mSel=b.dataset.msel;state.mTab='claims';render();});
  $$('[data-mback]').forEach(b=>b.onclick=()=>{state.mSel=null;render();});
};

function custDrawer(c){
  if(!c) return;
  const cl=CLAIMS.filter(k=>k.cust_id===c.id||(!c.id&&k.cust===c.n));
  const buys=Array.isArray(c.buys)?c.buys:[];
  const t1=c.t1!=null?c.t1:'—';
  const t2=c.t2!=null?c.t2:'—';
  openDrawer(`<div class="dh"><div><h2>${c.n}</h2><div class="hint" style="margin-top:4px">${c.ph} · roʼyxatdan oʼtgan ${c.reg} · ${pill(c.seg==='Uxlagan'?'rejected':c.seg==='Yangi'?'new':'done',c.seg)}</div></div><button class="x" type="button" aria-label="Yopish"><svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="m2 2 10 10M12 2 2 12"/></svg></button></div>
  <div class="kpis" style="grid-template-columns:1fr 1fr;margin:0">${tile({label:'Xaridlar',value:fmt(c.x)})}${tile({label:'Jami summa',value:money(c.sum)})}</div>
  <div class="card buys-card"><h4>Xaridlar (${c.x})</h4><div class="chek-scroll">${buys.map(b=>{const lines=Array.isArray(b.lines)?b.lines:(String(b.items||'').split(' · ').filter(Boolean).map(t=>({n:t,q:1})));const meta=`${b.d} · ${storeName(b.st)} · ${b.seller||'—'}`;return `<details class="chek"><summary><span class="meta">${meta}</span><span class="sum">${money(b.sum)}</span></summary><div class="body">${lines.map(l=>`<div class="line"><span class="n">${l.n||l}</span><span class="q">×${fmt(Number(l.q)||1,0)}</span></div>`).join('')}</div></details>`;}).join('')}</div></div>
  <div><h4>Bot soʼrovnomalar</h4><dl class="stat"><dt>T1 (7 kun)</dt><dd>${t1}</dd><dt>T2 (30 kun)</dt><dd>${t2}</dd><dt>Marketing roziligi</dt><dd>${c.consent?pill('ok','Bor'):pill('rejected','Yoʼq')}</dd></dl></div>
  <div><h4>Brak soʼrovlari (${cl.length})</h4>${cl.length?`<div class="list">${cl.map(k=>`<div class="item" style="grid-template-columns:1fr auto;padding:0 10px"><div class="t"><b>${k.id} · ${k.prod}</b><small>${dfull(k.date)} · ${k.reason}</small></div>${pill(k.status,STATUS[k.status])}</div>`).join('')}</div>`:'<div class="hint">Soʼrov yoʼq</div>'}</div>`);
}

RENDER.sifat=function(root){
  const prods=filteredProducts();
  const agg=(key)=>{const m={};prods.forEach(p=>{const k=p[key];if(!k||k==='—')return;m[k]=m[k]||{n:k,sold:0,req:0,brak:0,cost:0,satW:0,tr:0,cnt:0,sub:new Set()};const a=m[k];a.sold+=p.sold||0;a.req+=p.req||0;a.brak+=p.brak||0;a.cost+=p.cost||0;a.satW+=(p.sat||0)*(p.sold||0);a.tr+=p.tr||0;a.cnt++;a.sub.add(key==='brand'?p.sup:p.brand);});return Object.values(m).map(a=>({...a,sat:a.sold?a.satW/a.sold:0,sub:[...a.sub].filter(Boolean).join(', '),tr:Math.sign(a.tr)}));};
  const rows=(state.qView==='product'?prods.map(p=>({...p,sub:`${p.brand||'—'} · ${p.sup||'—'}`})):agg(state.qView==='brand'?'brand':'sup')).map(r=>({...r,rate:r.sold?r.brak/r.sold*100:0})).sort((a,b)=>b.rate-a.rate);
  const over=rows.filter(r=>r.sold>=10&&r.rate>THRESHOLD).length;
  const empty=rows.length===0;
  root.innerHTML=`<div class="chips">${[['product','Mahsulot'],['brand','Brend'],['sup','Yetkazib beruvchi']].map(([k,l])=>`<button class="chip" type="button" data-qv="${k}" aria-pressed="${state.qView===k}">${l}</button>`).join('')}<span class="hint" style="align-self:center;margin-left:auto">${over} ta chegaradan (${THRESHOLD} %) oshgan</span></div>
  <div class="grid c21">
    ${card('Brak darajasi boʼyicha reyting',empty?'<div class="empty">Hali bot orqali faollashtirilgan mahsulot yoʼq — mijoz chek yuborib tasdiqlaganda shu yerda chiqadi</div>':`<div class="scroll"><table class="tbl"><thead><tr><th>${state.qView==='product'?'Mahsulot':state.qView==='brand'?'Brend':'Yetkazib beruvchi'}</th><th class="num">Sotildi</th><th class="num">Soʼrovlar</th><th class="num">Tasdiqlangan</th><th class="num">Brak darajasi</th><th class="num">Qoniqish</th><th class="num">Xarajat</th><th>Trend</th></tr></thead><tbody>
    ${rows.map(r=>`<tr><td><b>${r.n}</b><small>${r.sub||''}</small></td><td class="num">${fmt(r.sold||0)}</td><td class="num">${fmt(r.req||0)}</td><td class="num">${fmt(r.brak||0)}</td><td class="num">${r.rate>THRESHOLD?pill('crit',pct(r.rate)):r.rate>Math.max(0.5,THRESHOLD*2/3)?pill('warn',pct(r.rate)):pill('ok',pct(r.rate))}</td><td class="num">${r.sat?pct(r.sat,0):'—'}</td><td class="num">${money(r.cost||0)}</td><td>${trend(r.tr||0)}</td></tr>`).join('')||'<tr><td colspan="8" class="empty">Bu filtrda maʼlumot yoʼq</td></tr>'}
    </tbody></table></div>`,'xarid boʼlimi uchun — yetkazib beruvchi bilan muzokara dalili')}
    ${card('Brak darajasi, %',empty?'<div class="empty">Grafik uchun maʼlumot yoʼq</div>':'<div id="c-k1"></div>',`chegara ${THRESHOLD} %`)}
  </div>`;
  const el=$('#c-k1');
  if(el&&rows.length) barChartH(el,{items:rows.slice(0,10).map(r=>({label:r.n,value:Math.round(r.rate*10)/10,color:r.rate>THRESHOLD?'var(--crit)':'var(--s1)',tip:`<div class="r"><span>Tasdiqlangan brak</span><b>${fmt(r.brak)} / ${fmt(r.sold)}</b></div>`})),fmt:v=>fmt(v,1)+' %',ref:THRESHOLD,refLabel:`chegara ${THRESHOLD} %`,valueName:'Brak darajasi',rowH:30});
};

RENDER.sotuvchilar=function(root){
  const f=P().f;
  const rows=filteredSellers().slice().sort((a,b)=>(b.ball||0)-(a.ball||0));
  const cheksAll=SELLERS.reduce((a,s)=>a+(s.cheks||0),0);
  const avg=cheksAll?SELLERS.reduce((a,s)=>a+(s.akt||0)*(s.cheks||0),0)/cheksAll:0;
  const chartRows=rows.filter(s=>(s.cheks||0)>0);
  root.innerHTML=`${card('Sotuvchilar reytingi',`<div class="scroll"><table class="tbl"><thead><tr><th>#</th><th>Sotuvchi</th><th class="num">Cheklar</th><th class="num">Summa</th><th class="num">Oʼrtacha chek</th><th>QR aktivatsiya</th><th class="num">Doʼkonda skanerlangan</th><th class="num">Takroriy mijoz</th><th class="num">Rad etilgan soʼrov</th><th class="num">Xizmat bahosi</th><th class="num">Ball</th></tr></thead><tbody>
  ${rows.map((s,i)=>`<tr><td class="num">${i+1}</td><td><b>${s.n}</b><small>${storeName(s.st)}</small></td><td class="num">${fmt(Math.round((s.cheks||0)*f))}</td><td class="num">${money((s.summa||0)*f)}</td><td class="num">${(s.cheks||0)?fmt(Math.round(s.summa/s.cheks*1000))+' ming':'—'}</td><td>${ibar(s.akt||0,100,(s.cheks||0)>0&&(s.akt||0)<avg-10)}</td><td class="num">${pct(s.inst||0,0)}</td><td class="num">${s.rep==null||s.rep===undefined?'—':pct(s.rep,0)}</td><td class="num ${(s.rad||0)>=15?'up':''}">${pct(s.rad||0,0)}</td><td class="num">${(s.csat||0)?fmt(s.csat,1)+' / 5':'—'}</td><td class="num"><b>${s.ball||0}</b></td></tr>`).join('')||'<tr><td colspan="11" class="empty">Sotuvchi topilmadi</td></tr>'}
  </tbody></table></div>`,'ball: summa 40 % · aktivatsiya 25 % · takroriy mijoz 20 % · xizmat bahosi 15 %')}
  <div class="grid c2">
    ${card('QR aktivatsiya, sotuvchilar boʼyicha',chartRows.length?'<div id="c-t1"></div>':'<div class="empty">Davrda sotuv yoʼq</div>','kompaniya oʼrtachasi bilan')}
    ${card('Oylik reyting',`<p style="margin:0">Oy yakunida jadval avtomatik PDF qilinib rahbariyat Telegram guruhiga yuboriladi. Rad etilgan soʼrovlar ulushi 15 % dan yuqori boʼlgan sotuvchi — kafolat shartlarini notoʼgʼri tushuntirayotgan boʼlishi mumkin, bu holda doʼkon menejeriga alohida xabar ketadi.</p><div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap"><button class="btn" type="button" data-export="pdf">Reytingni PDF qilish</button><button class="btn" type="button" data-export="tg">Telegram guruhiga yuborish</button></div>`,'har oyning 1-kuni')}
  </div>`;
  if(chartRows.length) barChartH($('#c-t1'),{items:chartRows.map(s=>({label:s.n,value:s.akt||0,color:(s.akt||0)<avg-10?'var(--crit)':'var(--s1)'})),fmt:v=>fmt(v,0)+' %',max:100,ref:Math.round(avg),refLabel:`Oʼrtacha ${fmt(avg,0)} %`,valueName:'Aktivatsiya',rowH:28});
};

RENDER.tizim=function(root){
  const boot=window.__BOOT__||{};
  const bs=boot.bot_stats||{};
  const bd=bs.deltas||{};
  const bz=boot.billz||{};
  const errs=boot.sync_errors||[];
  const audit=boot.audit_log||[];
  const stages=bs.by_stage||[];
  const syncOk=!bz.sync_error&&(bz.sync_status==='ok'||bz.sync_status==='done'||bz.last_sales_sync);
  const fmtAt=v=>{if(!v)return '—';try{return new Date(v).toLocaleString('uz-UZ');}catch(e){return v;}};
  const lastSync=fmtAt(bz.last_sales_sync);
  const dVal=(k,fb=0)=>(bd[k]&&bd[k].value)!=null?bd[k].value:fb;
  const dUnit=(k,fb)=>(bd[k]&&bd[k].unit)||fb;
  const tiles=[
    {label:'Bot xabar yuborildi',value:fmt(bs.sent||0),delta:dVal('sent'),dUnit:dUnit('sent','%'),upGood:true,cmp:P().cmp},
    {label:'Yetkazildi',value:fmt(bs.delivery_pct||0,1),unit:'%',delta:dVal('delivery_pct'),dUnit:dUnit('delivery_pct','pp'),upGood:true,cmp:(bs.fail||0)+' ta xato'},
    {label:'Bot xabar xatosi',value:fmt(bs.fail||0),delta:dVal('fail'),upGood:false,cmp:'Telegram Bot API'},
    {label:'Marketing roziligi',value:fmt(bs.consent_yes||0),cmp:'rozi emas: '+(bs.consent_no||0)},
    {label:'Bot: oylik faol foydalanuvchilar',value:fmt(bs.mau||0),delta:dVal('mau'),dUnit:dUnit('mau','%'),upGood:true,cmp:'oʼtgan oyga nisbatan'},
  ];
  const stageRows=[['T1','T1 — 7 kun'],['T2','T2 — 30 kun'],['T3','T3 — 90 kun'],['T4','T4 — kafolat tugashi'],['SELLER','Sotuvchi baholash']];
  root.innerHTML=`<div class="kpis k5">${tiles.map(tile).join('')}</div>
  <div class="grid c2">
    ${card('Bot soʼrovnoma shablonlari',`<div class="scroll"><table class="tbl"><thead><tr><th>Shablon</th><th class="num">Yuborildi</th><th class="num">Yetkazildi</th><th class="num">Xato</th></tr></thead><tbody>
    ${stageRows.map(([st,n])=>{const r=stages.find(x=>x.stage===st)||{sent:0,dlv:0,err:0};const s=r.sent||0;const d=r.dlv||0;const e=r.err||0;return `<tr><td>${n}</td><td class="num">${s?fmt(s):'—'}</td><td class="num">${s?pct(d/Math.max(1,s)*100):'—'}</td><td class="num">${e?fmt(e):'—'}</td></tr>`;}).join('')}
    </tbody></table></div><p>Faqat marketing roziligi bergan mijozlarga yuboriladi.</p>`,'Telegram bot')}
    ${card('Billz sinxron holati',`<dl class="stat"><dt>Holat</dt><dd>${syncOk?`<span class="dot"></span>Ishlayapti`:`<span class="dot" style="background:var(--crit)"></span>${bz.sync_status||'nomaʼlum'}`}</dd><dt>Oxirgi sinxron</dt><dd>${lastSync}</dd><dt>Bugun oʼtkazildi</dt><dd>${fmt(bz.today_orders||0)} ta chek</dd><dt>Xato bilan tushgan</dt><dd class="${(bz.error_orders||0)>0?'up':''}">${fmt(bz.error_orders||0)} ta chek</dd>${bz.sync_error?`<dt>Xato</dt><dd class="up">${bz.sync_error}</dd>`:''}</dl>
    <div class="scroll" style="margin-top:12px"><table class="tbl"><thead><tr><th>Chek №</th><th>Doʼkon</th><th>Vaqt</th><th class="wrap">Xato</th></tr></thead><tbody>
    ${errs.length?errs.map(e=>`<tr><td>${e.number}</td><td>${e.shop}</td><td>${e.at}</td><td class="wrap">${e.message}</td></tr>`).join(''):'<tr><td colspan="4" class="empty">Xato cheklar yoʼq</td></tr>'}
    </tbody></table></div><p>Bu cheklar tizimda toʼliq emas — mijoz skanerlaganda topilmasligi mumkin.</p>`,'Billz sync')}
  </div>
  <div class="grid c2">
    ${card('Bot faoliyati',`<dl class="stat"><dt>/start bosganlar (davr)</dt><dd>${fmt(bs.starts||0)}</dd><dt>Telefon tasdiqlashda uzilganlar</dt><dd class="${(bs.phone_drop||0)>20?'up':''}">${fmt(bs.phone_drop||0,1)} %</dd><dt>«Mening uyim»ga kirishlar</dt><dd>${fmt(bs.home||0)}</dd><dt>Botni bloklaganlar</dt><dd>${fmt(bs.blocked||0)}</dd><dt>Bot xatolari</dt><dd>${fmt(bs.errors||0)}</dd></dl>`,'Telegram Bot API')}
    ${card('Audit jurnali (oxirgi)',`<div class="scroll-y audit-scroll"><table class="tbl"><thead><tr><th>Vaqt</th><th>Xodim</th><th class="wrap">Amal</th></tr></thead><tbody>
    ${audit.length?audit.map(a=>`<tr><td>${a.at||'—'}</td><td>${a.who||'—'}</td><td class="wrap">${a.action||'—'}</td></tr>`).join(''):'<tr><td colspan="3" class="empty">Hali yozuv yoʼq</td></tr>'}
    </tbody></table></div>`,'kim, qachon, nimani oʼzgartirdi')}
  </div>`;
};

let ALERTS=[];
RENDER.xabarnomalar=function(root){
  const prev=(window.__BOOT__&&window.__BOOT__.daily_report_preview)||null;
  const rows=ALERTS.length?ALERTS.map((a,i)=>`<tr class="${a.on?'':'row-off'}"><td><input type="checkbox" class="sw" id="al${i}" data-al="${i}" ${a.on?'checked':''} aria-label="${a.n}"></td><td><label for="al${i}"><b>${a.n}</b></label></td><td>${a.type==='Muntazam'?pill('new',a.type):pill('review',a.type)}</td><td>${a.w}</td><td>${a.to}</td><td>${a.th==='—'?'—':`<input class="txt" id="th${i}" value="${a.th}" aria-label="Chegara">`}</td></tr>`).join(''):'<tr><td colspan="6" class="empty">Hali qoida yoʼq</td></tr>';
  const preview=prev
    ?`<div style="background:var(--surface-2);border:1px solid var(--line);border-radius:10px;padding:12px 14px;font-size:13px;line-height:1.55;max-width:520px"><b>QR Kafolat — ${prev.date||'—'}</b><br>Sotuvlar: <b>${prev.cheks??0}</b> chek<br>QR aktivatsiya: <b>${prev.akt??0}%</b><br>Ochiq soʼrovlar: <b>${prev.open_claims??0}</b><br>Bot soʼrovnoma javob: <b>${prev.survey_resp??0}%</b><br><span style="color:var(--accent)">Panelni ochish →</span></div>`
    :`<div style="background:var(--surface-2);border:1px solid var(--line);border-radius:10px;padding:12px 14px;font-size:13px;line-height:1.55;max-width:520px" class="empty">Hali maʼlumot yoʼq</div>`;
  root.innerHTML=card('Telegram xabarnomalari',`<div class="scroll"><table class="tbl"><thead><tr><th>Yoqilgan</th><th>Xabar</th><th>Turi</th><th>Qachon</th><th>Kimga</th><th>Chegara</th></tr></thead><tbody>
  ${rows}
  </tbody></table></div><p>Har bir xabarda panelning tegishli sahifasiga havola boʼladi. Vaqt, chegara va guruhlar shu yerda oʼzgartiriladi.</p>`,`${ALERTS.filter(a=>a.on).length} / ${ALERTS.length} yoqilgan`)
  +card('Kunlik hisobot namunasi (Telegram)',preview,prev?.schedule||'har kuni 09:00')
};
/* ---------- Backend ulanish (asl UI saqlanadi) ---------- */
const CSRF=document.querySelector('meta[name="csrf-token"]')?.content||'';
async function api(path,opts={}){
  const res=await fetch(path,{headers:{Accept:'application/json','X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest',...(opts.body?{'Content-Type':'application/json'}:{})},...opts});
  const data=await res.json().catch(()=>({}));
  if(!res.ok) throw new Error(data.message||'API xato');
  return data;
}
function applyBoot(boot){
  if(!boot) return;
  // Doʼkonlar kesimida — hech qachon demo Chilonzor ga qaytmasin
  if(Array.isArray(boot.stores)) STORES=boot.stores;
  if(boot.tot) TOT=boot.tot;
  if(Array.isArray(boot.sellers)){
    const list=boot.sellers;
    const maxSum=Math.max(1,...list.map(z=>Number(z.summa)||0),1);
    SELLERS=list.map(s=>{
      const x={...s};
      const rep=x.rep==null||x.rep===undefined?0:Number(x.rep)||0;
      const csat=(Number(x.csat)||0)||4.5;
      x.ball=Math.round(40*(Number(x.summa)||0)/maxSum+25*(Number(x.akt)||0)/100+20*(rep/40)+15*csat/5);
      return x;
    });
  }
  CLAIMS=(boot.claims||[]).map(c=>({...c,date:c.date?new Date(c.date):TODAY,closed:c.closed?new Date(c.closed):undefined,photo_urls:c.photo_urls||[],logs:c.logs||[]}));
  if(Array.isArray(boot.customers)) CUSTOMERS=boot.customers;
  if(boot.customer_stats&&Array.isArray(boot.customer_stats.months)) MONTHS=boot.customer_stats.months;
  if(boot.customer_stats&&Array.isArray(boot.customer_stats.monthly_new)) M_NEW=boot.customer_stats.monthly_new;
  if(Array.isArray(boot.products)) PRODUCTS=boot.products;
  if(Array.isArray(boot.cats)&&boot.cats.length) CATS=boot.cats;
  if(Array.isArray(boot.touch)&&boot.touch.length) TOUCH=boot.touch;
  if(Array.isArray(boot.comments)) COMMENTS=boot.comments;
  if(Array.isArray(boot.survey_templates)) SURVEY_TPL=boot.survey_templates;
  if(boot.survey_weeks){
    if(Array.isArray(boot.survey_weeks.labels)) WEEKS=boot.survey_weeks.labels;
    W_SAT={
      T1:Array.isArray(boot.survey_weeks.T1)?boot.survey_weeks.T1:[],
      T2:Array.isArray(boot.survey_weeks.T2)?boot.survey_weeks.T2:[],
      T3:Array.isArray(boot.survey_weeks.T3)?boot.survey_weeks.T3:[],
    };
  }
  if(Array.isArray(boot.alerts)) ALERTS=boot.alerts;
  if(Array.isArray(boot.couriers)){COURIERS=boot.couriers;if(!state.cour&&COURIERS[0])state.cour=COURIERS[0].id;}
  if(boot.brak_threshold!=null) THRESHOLD=parseBrakThreshold(boot.brak_threshold);
  else syncBrakThresholdFromAlerts();
  DAILY=(boot.daily||[]).map(r=>({d:new Date(r.d),cheks:r.cheks,akt:r.akt}));
  TODAY=new Date();
  window.__BOOT__=boot;
  const u=$('#updatedAt'); if(u&&boot.updated_at){try{u.textContent=new Date(boot.updated_at).toLocaleString('uz-UZ');}catch(e){u.textContent=boot.updated_at;}}
  const badge=$('.badge'); if(badge) badge.textContent='Billz · '+(boot.shop_count||STORES.length)+' doʼkon · real';
  buildNav();
  const allowed=visiblePages();
  if(!allowed.some(p=>p.id===state.page)) state.page=(allowed[0]&&allowed[0].id)||'home';
  const au=boot.auth_user||{};
  if(au.shop_locked&&au.shop_id){state.store=String(au.shop_id);}
  syncCourierUi(au);
}
function syncCourierUi(au){
  const courier=!!(au&&au.is_courier);
  document.querySelector('.app')?.classList.toggle('courier-ui',courier);
  if(courier) state.page='kuryer';
}
async function reloadBoot(){
  const q=new URLSearchParams({period:state.period});
  if(state.store&&state.store!=='all') q.set('shop_id',state.store);
  const boot=await api('/api/panel-boot?'+q.toString());
  applyBoot(boot);
  initFilters();
  render();
}
RENDER.bot=function(root){
  const t=(window.__BOOT__&&window.__BOOT__.telegram)||{};
  const mode=t.mode||(t.webhook_status==='active'?'webhook':'polling');
  const modeLabel=mode==='webhook'?'Webhook (production)':'Local polling — Cloudflare kerak emas';
  root.innerHTML=card('Telegram bot sozlamalari',`<dl class="stat">
    <dt>Bot</dt><dd>${t.bot_username?'@'+t.bot_username:'—'}</dd>
    <dt>Token</dt><dd>${t.bot_token_masked||'sozlanmagan'}</dd>
    <dt>Rejim</dt><dd>${modeLabel}</dd>
    <dt>Webhook</dt><dd>${t.webhook_status||'inactive'}</dd>
    <dt>URL</dt><dd class="wrap">${t.webhook_url||'—'}</dd>
  </dl>
  <div style="display:grid;gap:10px;margin-top:14px;max-width:520px">
    <label>Bot token<input class="txt" id="tgToken" placeholder="123456:ABC..." style="width:100%;padding:8px"></label>
    <label>Rahbariyat guruh ID<input class="txt" id="tgMgmt" value="${t.management_group_id||''}" style="width:100%;padding:8px"></label>
    <label>Kuryer guruh ID<input class="txt" id="tgSvc" value="${t.service_group_id||''}" style="width:100%;padding:8px" placeholder="-100…"></label>
    <label>Sotuvchi baholash kechikishi (daqiqa)<input class="txt" id="tgSellerDelay" type="number" min="0" max="10080" step="1" value="${t.seller_rating_delay_minutes??0}" style="width:100%;padding:8px"></label>
  </div>
  <p style="font-size:12.5px;color:var(--ink-3);margin:10px 0 0;max-width:520px">Localda Cloudflare yoʼq: terminalda <code>php artisan telegram:poll</code> ishlasin. «Test xabar» guruhlarga yuboradi. Rahbariyat — yangi soʼrov/hisobot; Kuryer — faqat kuryer belgilangach (Yoʼldaman / Yetkazdim). Production: HTTPS + webhook. Sotuvchi baholash: 0 = darhol.</p>
  <div style="margin-top:14px;display:flex;flex-wrap:wrap;gap:8px">
    <button class="btn primary" type="button" id="tgSave">Saqlash</button>
    <button class="btn" type="button" id="tgSetHook">Webhook oʼrnatish</button>
    <button class="btn" type="button" id="tgDelHook">Webhook oʼchirish</button>
    <button class="btn" type="button" id="tgTest">Test xabar</button>
  </div>`,'Integratsiya');
  $('#tgSave').onclick=async()=>{try{await api('/api/settings/telegram',{method:'POST',body:JSON.stringify({bot_token:$('#tgToken').value||null,management_group_id:$('#tgMgmt').value,service_group_id:$('#tgSvc').value,seller_rating_delay_minutes:Number($('#tgSellerDelay')?.value||0)})});toast('Saqlandi');reloadBoot();}catch(e){toast(e.message);}};
  const wh=async(action)=>{try{const r=await api('/api/settings/telegram/webhook',{method:'POST',body:JSON.stringify({action})});toast(action==='test'?(r.message||'Test yuborildi'):('OK: '+action));if(action!=='test')reloadBoot();}catch(e){toast(e.message);}};
  $('#tgSetHook').onclick=()=>wh('set');
  $('#tgDelHook').onclick=()=>wh('delete');
  $('#tgTest').onclick=()=>wh('test');
};
// Patch tizim page to show Billz controls at end of original RENDER.tizim — wrap after definition
const _tizim=RENDER.tizim;
RENDER.tizim=function(root){
  _tizim(root);
  const b=(window.__BOOT__&&window.__BOOT__.billz)||{};
  const box=document.createElement('div');
  box.style.marginTop='12px';
  box.innerHTML=card('Billz sozlamalari',`<dl class="stat"><dt>Holat</dt><dd>${b.sync_status||'—'}</dd><dt>Company</dt><dd>${b.company_id||'—'}</dd><dt>Oxirgi sync</dt><dd>${b.last_sales_sync||'—'}</dd><dt>Xato</dt><dd>${b.sync_error||'—'}</dd></dl>
  <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap">
    <button class="btn" type="button" id="billzSync">Sinxron (all)</button>
    <button class="btn" type="button" id="billzTest">Login test</button>
  </div>`);
  root.appendChild(box);
  $('#billzSync').onclick=async()=>{toast('Sinxron...');try{await api('/api/settings/billz/sync',{method:'POST',body:JSON.stringify({job:'all'})});toast('Tugadi');reloadBoot();}catch(e){toast(e.message);}};
  $('#billzTest').onclick=async()=>{try{const r=await api('/api/settings/billz/test',{method:'POST',body:'{}'});toast(r.ok?'Billz OK':r.message);}catch(e){toast(e.message);}};
};

let STAFF={pages:[],roles:[],employees:[]};
let staffUi={tab:'roles',roleId:null,empId:null};
async function loadStaff(){
  const data=await api('/api/staff');
  STAFF=data;
  return data;
}
function staffPagesChecks(selected,disabled){
  const sel=new Set(selected||[]);
  const groups={};
  (STAFF.pages||[]).forEach(p=>{
    const g=p.group||'Boshqa';
    if(!groups[g]) groups[g]=[];
    groups[g].push(p);
  });
  return Object.keys(groups).map(g=>`<div style="grid-column:1/-1;font-size:11px;color:var(--ink-3);margin-top:4px">${g}</div>`+groups[g].map(p=>`<label><input type="checkbox" class="pg" value="${p.id}" ${sel.has(p.id)?'checked':''} ${disabled?'disabled':''}> ${p.label}</label>`).join('')).join('');
}
RENDER.xodimlar=function(root){
  const roles=STAFF.roles||[];
  const emps=STAFF.employees||[];
  const editRole=roles.find(r=>r.id===staffUi.roleId)||null;
  const editEmp=emps.find(e=>e.id===staffUi.empId)||null;
  const roleOpts=roles.map(r=>`<option value="${r.id}" data-slug="${r.slug||''}" ${editEmp&&editEmp.role_id===r.id?'selected':''}>${r.name}</option>`).join('');
  const isKuryerRole=id=>{const r=roles.find(x=>String(x.id)===String(id));return r&&(r.slug==='kuryer'||/kuryer|courier/i.test(r.name||''));};
  root.innerHTML=
    card('Rollar',`
      <div class="staff-actions"><button type="button" class="btn primary" id="roleNew">+ Rol qoʼshish</button></div>
      <div class="scroll"><table class="tbl"><thead><tr><th>Rol</th><th>Boʼlimlar</th><th>Xodimlar</th><th></th></tr></thead><tbody>
      ${roles.length?roles.map(r=>`<tr><td><b>${r.name}</b>${r.is_admin?' · Admin':''}</td><td>${r.pages.length} ta</td><td>${r.users_count}</td><td><button type="button" class="btn" data-role-edit="${r.id}">Tahrirlash</button> ${r.is_system?'':`<button type="button" class="btn danger" data-role-del="${r.id}">Oʼchirish</button>`}</td></tr>`).join(''):'<tr><td colspan="4" class="empty">Hali rol yoʼq</td></tr>'}
      </tbody></table></div>
      <div id="roleFormWrap" ${editRole||staffUi.roleId===0?'':'hidden'}>
        <form class="staff-form" id="roleForm">
          <label>Rol nomi<input name="name" required value="${editRole?editRole.name:''}" placeholder="Masalan: Servis"></label>
          <div><div style="font-size:12.5px;color:var(--ink-2);margin-bottom:6px">Koʼra oladigan boʼlimlar</div>
            <div class="staff-pages">${staffPagesChecks(editRole?editRole.pages:(STAFF.pages||[]).map(p=>p.id),editRole&&editRole.is_admin)}</div>
            ${editRole&&editRole.is_admin?'<p style="font-size:12.5px;color:var(--ink-3);margin:6px 0 0">Admin — barcha boʼlimlar ochiq.</p>':''}
          </div>
          <div class="staff-actions">
            <button type="submit" class="btn primary">${editRole?'Saqlash':'Yaratish'}</button>
            <button type="button" class="btn" id="roleCancel">Bekor</button>
          </div>
        </form>
      </div>`,'kim qaysi boʼlimlarni koʼradi')
    +card('Xodimlar',`
      <div class="staff-actions"><button type="button" class="btn primary" id="empNew">+ Xodim qoʼshish</button></div>
      <div class="scroll"><table class="tbl"><thead><tr><th>Ism</th><th>Login</th><th>Rol</th><th>Doʼkon</th><th>Holat</th><th></th></tr></thead><tbody>
      ${emps.length?emps.map(e=>`<tr class="${e.is_active?'':'row-off'}"><td><b>${e.name}</b><small>${e.telegram_id?('TG '+e.telegram_id):''}${e.phone?(' · '+e.phone):''}${e.region?(' · '+e.region):''}</small></td><td>${e.login}</td><td>${e.role_name||'—'}</td><td>${e.shop_name||'—'}</td><td>${e.is_active?pill('new','Faol'):pill('rejected','Oʼchiq')}</td><td><button type="button" class="btn" data-emp-edit="${e.id}">Tahrirlash</button> ${e.is_self?'':`<button type="button" class="btn danger" data-emp-del="${e.id}">Oʼchirish</button>`}</td></tr>`).join(''):'<tr><td colspan="6" class="empty">Hali xodim yoʼq</td></tr>'}
      </tbody></table></div>
      <div id="empFormWrap" ${editEmp||staffUi.empId===0?'':'hidden'}>
        <form class="staff-form" id="empForm">
          <label>Ism<input name="name" required value="${editEmp?editEmp.name:''}" placeholder="Toʼliq ism"></label>
          <label>Login<input name="login" required value="${editEmp?editEmp.login:''}" placeholder="masalan: umid yoki umid@oydin.uz" autocomplete="off"></label>
          <label>Parol${editEmp?' (boʼsh qoldirilsa oʼzgarmaydi)':''}<input name="password" type="password" ${editEmp?'':'required'} minlength="6" autocomplete="new-password"></label>
          <label>Rol<select name="role_id" id="empRole" required>${roleOpts||'<option value="">Rol yoʼq</option>'}</select></label>
          <label id="empShopWrap">Doʼkon (menejer uchun — faqat shu doʼkon)<select name="shop_id"><option value="">— barcha / yoʼq —</option>${(STAFF.shops||[]).map(s=>`<option value="${s.id}" ${editEmp&&String(editEmp.shop_id)===String(s.id)?'selected':''}>${s.name}</option>`).join('')}</select></label>
          <div id="empCourierFields">
            <label>Telegram ID (kuryer Mini App / guruh uchun)<input name="telegram_id" value="${editEmp&&editEmp.telegram_id?editEmp.telegram_id:''}" placeholder="masalan: 123456789" inputmode="numeric"></label>
            <label>Telefon<input name="phone" value="${editEmp&&editEmp.phone?editEmp.phone:''}" placeholder="+998 …"></label>
            <label>Hudud <span class="hint" style="font-weight:400;text-transform:none;letter-spacing:0">(kuryer ishlaydigan shahar — tayinlashda koʼrinadi)</span><input name="region" value="${editEmp&&editEmp.region?editEmp.region:''}" placeholder="Toshkent, Samarqand…"></label>
          </div>
          <label style="display:flex;align-items:center;gap:8px;flex-direction:row"><input type="checkbox" name="is_active" ${!editEmp||editEmp.is_active?'checked':''}> Faol (kirishi mumkin)</label>
          <div class="staff-actions">
            <button type="submit" class="btn primary">${editEmp?'Saqlash':'Yaratish'}</button>
            <button type="button" class="btn" id="empCancel">Bekor</button>
          </div>
        </form>
      </div>`,'har bir xodim oʼz login/paroli bilan kiradi');

  const showRoleForm=()=>{$('#roleFormWrap').hidden=false;};
  const hideRoleForm=()=>{staffUi.roleId=null;$('#roleFormWrap').hidden=true;};
  const showEmpForm=()=>{$('#empFormWrap').hidden=false;};
  const hideEmpForm=()=>{staffUi.empId=null;$('#empFormWrap').hidden=true;};

  $('#roleNew').onclick=()=>{staffUi.roleId=0;render();};
  $('#roleCancel')?.addEventListener('click',()=>{hideRoleForm();render();});
  $$('[data-role-edit]').forEach(b=>b.onclick=()=>{staffUi.roleId=Number(b.dataset.roleEdit);render();});
  $$('[data-role-del]').forEach(b=>b.onclick=async()=>{
    if(!confirm('Rolni oʼchirasizmi?')) return;
    try{const r=await api('/api/staff/roles/'+b.dataset.roleDel,{method:'DELETE'});STAFF=r.staff;staffUi.roleId=null;toast('Oʼchirildi');render();}catch(e){toast(e.message);}
  });
  $('#roleForm')?.addEventListener('submit',async e=>{
    e.preventDefault();
    const fd=new FormData(e.target);
    const pages=[...e.target.querySelectorAll('.pg:checked')].map(x=>x.value);
    const body={name:String(fd.get('name')||'').trim(),pages};
    try{
      if(editRole&&editRole.id){const r=await api('/api/staff/roles/'+editRole.id,{method:'PATCH',body:JSON.stringify(body)});STAFF=r.staff;}
      else{const r=await api('/api/staff/roles',{method:'POST',body:JSON.stringify(body)});STAFF=r.staff;}
      staffUi.roleId=null;toast('Rol saqlandi');render();
    }catch(err){toast(err.message);}
  });

  const syncEmpRoleFields=()=>{
    const roleSel=$('#empRole');
    const kur=roleSel?isKuryerRole(roleSel.value):false;
    const shopW=$('#empShopWrap');
    const courW=$('#empCourierFields');
    if(shopW) shopW.hidden=!!kur;
    if(courW) courW.hidden=!kur;
  };
  syncEmpRoleFields();
  $('#empRole')?.addEventListener('change',syncEmpRoleFields);
  $('#empNew').onclick=()=>{staffUi.empId=0;render();};
  $('#empCancel')?.addEventListener('click',()=>{hideEmpForm();render();});
  $$('[data-emp-edit]').forEach(b=>b.onclick=()=>{staffUi.empId=Number(b.dataset.empEdit);render();});
  $$('[data-emp-del]').forEach(b=>b.onclick=async()=>{
    if(!confirm('Xodimni oʼchirasizmi?')) return;
    try{const r=await api('/api/staff/employees/'+b.dataset.empDel,{method:'DELETE'});STAFF=r.staff;staffUi.empId=null;toast('Oʼchirildi');render();}catch(e){toast(e.message);}
  });
  $('#empForm')?.addEventListener('submit',async e=>{
    e.preventDefault();
    const fd=new FormData(e.target);
    const roleId=Number(fd.get('role_id'));
    const kur=isKuryerRole(roleId);
    const shopRaw=kur?'':String(fd.get('shop_id')||'').trim();
    const body={
      name:String(fd.get('name')||'').trim(),
      login:String(fd.get('login')||'').trim(),
      password:String(fd.get('password')||''),
      role_id:roleId,
      shop_id:shopRaw?Number(shopRaw):null,
      telegram_id:kur?(String(fd.get('telegram_id')||'').trim()||null):null,
      phone:kur?(String(fd.get('phone')||'').trim()||null):null,
      region:kur?(String(fd.get('region')||'').trim()||null):null,
      is_active:!!e.target.querySelector('[name=is_active]')?.checked,
    };
    if(editEmp&&!body.password) delete body.password;
    try{
      if(editEmp&&editEmp.id){const r=await api('/api/staff/employees/'+editEmp.id,{method:'PATCH',body:JSON.stringify(body)});STAFF=r.staff;}
      else{const r=await api('/api/staff/employees',{method:'POST',body:JSON.stringify(body)});STAFF=r.staff;}
      staffUi.empId=null;toast('Xodim saqlandi');
      await reloadBoot().catch(()=>{});
      render();
    }catch(err){toast(err.message);}
  });
};
const _xodimlar=RENDER.xodimlar;
RENDER.xodimlar=function(root){
  if(!STAFF.pages.length){
    root.innerHTML=card('Xodimlar','Yuklanmoqda…','');
    loadStaff().then(()=>render()).catch(e=>{root.innerHTML=card('Xodimlar',`<p class="empty">${e.message}</p>`,'');});
    return;
  }
  _xodimlar(root);
};

/* ---------- Navigatsiya va filtrlar ---------- */
const ICONS={home:'<path d="M2.5 8 8 3l5.5 5M4 7v6h8V7"/>',funnel:'<path d="M2 3h12l-4.5 5v4l-3 1.5V8z"/>',users:'<circle cx="5.5" cy="5" r="2.2"/><path d="M2 13c0-2.2 1.6-3.5 3.5-3.5S9 10.8 9 13"/><circle cx="11" cy="5.5" r="1.8"/><path d="M9.8 9.8c2 0 4 1.2 4 3.2"/>',msg:'<path d="M2 3h12v7H6l-3 3v-3H2z"/>',wrench:'<path d="M10.5 2.5a3 3 0 0 0-4 3.8L2.5 10.3 4.7 12.5l4-4a3 3 0 0 0 3.8-4L10.6 6.4 9.3 5.1z"/>',box:'<path d="M2 5 8 2l6 3v6l-6 3-6-3zM2 5l6 3 6-3M8 8v6"/>',star:'<path d="M8 2l1.8 3.8 4.2.6-3 2.9.7 4.2L8 11.5 4.3 13.5l.7-4.2-3-2.9 4.2-.6z"/>',cpu:'<rect x="4" y="4" width="8" height="8" rx="1"/><rect x="6.5" y="6.5" width="3" height="3"/><path d="M6 1.5V4M10 1.5V4M6 12v2.5M10 12v2.5M1.5 6H4M1.5 10H4M12 6h2.5M12 10h2.5"/>',bell:'<path d="M4 11V7a4 4 0 0 1 8 0v4l1 1.5H3zM6.5 14h3"/>',bot:'<path d="M8 1.5a2 2 0 0 1 2 2v1h2.5a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H3.5a1 1 0 0 1-1-1v-8a1 1 0 0 1 1-1H6v-1a2 2 0 0 1 2-2z"/>',truck:'<path d="M1.5 4h9v7h-9zM10.5 7H14l1.5 2.5V11h-5zM4 13.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3zM12 13.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z"/>',phone:'<rect x="4" y="1.5" width="8" height="13" rx="1.5"/><path d="M7 12.5h2"/>'};
const PAGES=[
 {id:'home',t:'Bosh sahifa',sub:'KPI paneli · 8 ta asosiy koʼrsatkich, 6 doʼkon',group:'Asosiy',icon:'home'},
 {id:'sotuv',t:'Sotuv va QR aktivatsiya',sub:'Hisobot 1 · voronka va doʼkonlar kesimi',group:'Hisobotlar',icon:'funnel'},
 {id:'mijozlar',t:'Mijozlar bazasi',sub:'Hisobot 2 · «Mening uyim» orqali roʼyxatdan oʼtganlar',icon:'users'},
 {id:'sorovnoma',t:'Soʼrovnoma (Bot)',sub:'Hisobot 3 · T1–T4 bosqichlar boʼyicha natijalar',icon:'msg'},
 {id:'brak',t:'Brak va almashtirish',sub:'Hisobot 4 · servis boʼlimining asosiy ish ekrani',icon:'wrench',badge:()=>CLAIMS.filter(c=>c.status==='new').length},
 {id:'sifat',t:'Mahsulot sifati',sub:'Hisobot 5 · mahsulot, brend va yetkazib beruvchi',icon:'box'},
 {id:'sotuvchilar',t:'Sotuvchilar reytingi',sub:'Hisobot 6 · sotuvchi va doʼkon samaradorligi',icon:'star'},
 {id:'tizim',t:'Tizim va sinxron',sub:'Hisobot 7 · Bot xabarlar, Billz sinxron',icon:'cpu'},
 {id:'kuryer',t:'Kuryer paneli',sub:'Kuryer roli · telefon: topshiriqlar, Yoʼldaman / Yetkazdim, natijalar',group:'Jarayon',icon:'truck',badge:()=>{const id=state.cour||(COURIERS[0]&&COURIERS[0].id);return CLAIMS.filter(c=>c.courier_id===id&&c.status==='assigned').length;}},
 {id:'miniapp',t:'Mijoz Mini App',sub:'Bot web tab · soʼrov holati va tasdiqlash (real)',icon:'phone',badge:()=>CLAIMS.filter(c=>c.status==='delivered'&&!c.confirm).length},
 {id:'xabarnomalar',t:'Xabarnomalar',sub:'Sozlamalar · Telegram avtomatik xabarlar va ogohlantirishlar',group:'Sozlamalar',icon:'bell'},
 {id:'bot',t:'Telegram bot',sub:'Sozlamalar · token, chat/guruh ID, webhook',icon:'bot'},
 {id:'xodimlar',t:'Xodimlar',sub:'Sozlamalar · rollar, login va boʼlim ruxsatlari',icon:'users'},
];
function visiblePages(){
  const au=window.__BOOT__&&window.__BOOT__.auth_user;
  if(!au||!Array.isArray(au.pages)) return [];
  const set=new Set(au.pages);
  return PAGES.filter(p=>set.has(p.id));
}
function buildNav(){
  const nav=$('#nav');
  const list=visiblePages();
  let lastGroup=null;
  nav.innerHTML=list.map(p=>{
    let h='';
    if(p.group&&p.group!==lastGroup){h=`<h6>${p.group}</h6>`;lastGroup=p.group;}
    return `${h}<button type="button" data-page="${p.id}"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">${ICONS[p.icon]}</svg><span>${p.t}</span>${p.badge&&p.badge()?`<span class="n">${p.badge()}</span>`:''}</button>`;
  }).join('');
}
function go(page){
  if(!visiblePages().some(p=>p.id===page)){toast('Bu boʼlimga ruxsat yoʼq');return;}
  state.page=page;try{if(location.hash!=='#'+page)history.replaceState(null,'','#'+page);}catch(e){}render();window.scrollTo({top:0});
}
$('#nav').addEventListener('click',e=>{const b=e.target.closest('[data-page]');if(b)go(b.dataset.page);});
document.addEventListener('click',e=>{
  const g=e.target.closest('[data-go]');if(g){if(g.dataset.claim){go(g.dataset.go);claimDrawer(CLAIMS.find(c=>c.id===g.dataset.claim));}else go(g.dataset.go);return;}
  const cf=e.target.closest('[data-cf]');if(cf){state.claimFilter=cf.dataset.cf;render();return;}
  const qv=e.target.closest('[data-qv]');if(qv){state.qView=qv.dataset.qv;render();return;}
  const tr=e.target.closest('tr[data-claim]');if(tr){claimDrawer(CLAIMS.find(c=>c.id===tr.dataset.claim));return;}
  const tc=e.target.closest('tr[data-cust]');if(tc){custDrawer(CUSTOMERS.find(c=>c.n===tc.dataset.cust));return;}
  const ex=e.target.closest('[data-export]');
  if(ex){
    const kind=ex.dataset.export;
    const q=new URLSearchParams({period:state.period});
    if(kind==='pdf'){
      toast('Reyting PDF yuklanmoqda…');
      location.href='/api/reports/sellers/rating.pdf?'+q.toString();
      return;
    }
    if(kind==='tg'){
      toast('Telegram guruhiga yuborilmoqda…');
      api('/api/reports/sellers/rating-send',{method:'POST',body:JSON.stringify({period:Number(state.period)})})
        .then(r=>toast(r.message||'Yuborildi'))
        .catch(err=>toast(err.message));
      return;
    }
  }
});
function fillSelect(sel,opts,val){sel.innerHTML=opts.map(o=>`<option value="${o[0]}" ${o[0]===val?'selected':''}>${o[1]}</option>`).join('');}
function initFilters(){
  const au=(window.__BOOT__&&window.__BOOT__.auth_user)||{};
  const storeSel=$('#fStore');
  if(au.shop_locked&&au.shop_id){
    const name=au.shop_name||storeName(au.shop_id)||('Doʼkon #'+au.shop_id);
    fillSelect(storeSel,[[String(au.shop_id),name]],String(au.shop_id));
    storeSel.disabled=true;
    state.store=String(au.shop_id);
  }else{
    storeSel.disabled=false;
    fillSelect(storeSel,[['all','Barcha doʼkonlar ('+STORES.length+')'],...STORES.map(s=>[s.id,s.name])],state.store);
  }
  fillSelect($('#fSeller'),[['all','Barcha sotuvchilar'],...SELLERS.filter(s=>state.store==='all'||s.st===state.store).map(s=>[s.n,s.n])],state.seller);
  fillSelect($('#fCat'),[['all','Barcha kategoriyalar'],...CATS.map(c=>[c.id,c.n])],state.cat);
}
$('#fPeriod').addEventListener('change',e=>{state.period=e.target.value;});
$('#fStore').addEventListener('change',e=>{
  const au=(window.__BOOT__&&window.__BOOT__.auth_user)||{};
  if(au.shop_locked) return;
  state.store=e.target.value;state.seller='all';reloadBoot().catch(err=>toast(err.message));
});
$('#fSeller').addEventListener('change',e=>{state.seller=e.target.value;render();});
$('#fCat').addEventListener('change',e=>{state.cat=e.target.value;render();});

function render(){
  const au=(window.__BOOT__&&window.__BOOT__.auth_user)||{};
  syncCourierUi(au);
  const allowed=visiblePages();
  if(allowed.length&&!allowed.some(p=>p.id===state.page)) state.page=allowed[0].id;
  const p=state.page;
  $$('.page').forEach(s=>s.hidden=s.id!=='p-'+p);
  $$('#nav [data-page]').forEach(b=>{if(b.dataset.page===p)b.setAttribute('aria-current','page');else b.removeAttribute('aria-current');});
  const pg=PAGES.find(x=>x.id===p)||{t:'—',sub:''};$('#pageTitle').textContent=pg.t;$('#pageSub').textContent=pg.sub;
  const parts=[];const s=curStore();if(s)parts.push('Doʼkon: '+s.name);if(state.seller!=='all')parts.push('Sotuvchi: '+state.seller);if(state.cat!=='all')parts.push('Kategoriya: '+(CATS.find(c=>c.id===state.cat)||{}).n);
  $('#scopeNote').textContent=parts.length?'Filtr — '+parts.join(' · '):'Filtr — barcha doʼkonlar';
  const badge=$('#nav [data-page="brak"] .n');const nNew=CLAIMS.filter(c=>c.status==='new').length;if(badge)badge.textContent=nNew;if(badge&&!nNew)badge.remove();
  if(typeof RENDER[p]==='function') RENDER[p]($('#p-'+p));
}
let lastW=0,rt;
new ResizeObserver(()=>{const w=$('.main').clientWidth;if(w===lastW)return;lastW=w;clearTimeout(rt);rt=setTimeout(render,120);}).observe($('.main'));
const mq=window.matchMedia('(prefers-color-scheme: dark)');mq.addEventListener?.('change',()=>render());
new MutationObserver(()=>render()).observe(document.documentElement,{attributes:true,attributeFilter:['data-theme']});

buildNav();
const h=location.hash.replace('#','');if(PAGES.some(p=>p.id===h))state.page=h;
lastW=$('.main').clientWidth;
$('#fPeriod').addEventListener('change',()=>{reloadBoot().catch(e=>toast(e.message));});
document.addEventListener('click',e=>{
  if(e.target.closest('#btnExport')){
    e.stopPropagation();
    location.href='/api/export?page='+state.page+'&period='+state.period;
  }
},true);
async function saveAlertRules(fromEl){
  if(!ALERTS.length||!ALERTS.every(a=>a.id)) return;
  if(fromEl?.matches?.('.sw[data-al]')){
    const i=Number(fromEl.dataset.al);
    if(ALERTS[i]){ALERTS[i].on=fromEl.checked;fromEl.closest('tr')?.classList.toggle('row-off',!fromEl.checked);}
  }
  if(fromEl?.matches?.('.txt[id^="th"]')){
    const i=Number(String(fromEl.id).replace('th',''));
    if(ALERTS[i]&&ALERTS[i].th!=='—') ALERTS[i].th=fromEl.value;
  }
  const rules=ALERTS.map((a,i)=>({id:a.id,enabled:$(`#al${i}`)?.checked??a.on,threshold:a.th==='—'?null:($(`#th${i}`)?.value||a.th)}));
  await api('/api/settings/notifications',{method:'POST',body:JSON.stringify({rules})});
  syncBrakThresholdFromAlerts();
  if(state.page==='sifat'||state.page==='home'||state.page==='xabarnomalar') render();
}
document.addEventListener('change',async e=>{
  if(e.target.matches('.sw[data-al], .txt[id^="th"]')){
    try{await saveAlertRules(e.target);toast(e.target.matches('.sw')?(`«${ALERTS[Number(e.target.dataset.al)]?.n||''}» ${e.target.checked?'yoqildi':'oʼchirildi'}`):'Chegara saqlandi');}
    catch(err){toast(err.message);}
  }
});
(async()=>{
  try{
    await reloadBoot();
  }catch(e){
    STORES=[];
    initFilters();
render();
    toast('Backend ulanmadi: '+e.message);
  }
})();
})();
</script>

</body></html>