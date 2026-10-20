<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no,viewport-fit=cover">
<title>QR Kafolat</title>
<script src="https://telegram.org/js/telegram-web-app.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<style>
:root{--bg:#f4f6fa;--surface:#fff;--ink:#12151c;--ink-2:#4a5160;--ink-3:#8b93a7;--line:#e6e9f0;--line-2:#d5dae6;--accent:#2f6fed;--accent-bg:#e8f0fc;--good:#1a9a55;--good-bg:#e6f7ee;--warn:#c47f00;--warn-bg:#fff4de;--crit:#d64545;--crit-bg:#fdeaea;--s3:#5b6abf;--amber:#f2a900;--radius:12px}
@media (prefers-color-scheme: dark){:root{--bg:#0f1218;--surface:#181c24;--ink:#eef1f7;--ink-2:#aeb6c8;--ink-3:#7a8499;--line:#2a3140;--line-2:#3a4356;--accent-bg:#172a4a;--good-bg:#143226;--warn-bg:#3a2e14;--crit-bg:#3a1a1a}}
*{box-sizing:border-box}
html,body{height:100%;height:100dvh;margin:0;overflow:hidden;overscroll-behavior:none;-webkit-text-size-adjust:100%}
body{font:14px/1.45 system-ui,-apple-system,Segoe UI,sans-serif;background:var(--bg);color:var(--ink)}
.app{max-width:480px;margin:0 auto;height:100%;height:100dvh;max-height:100dvh;display:flex;flex-direction:column;background:var(--surface);overflow:hidden;position:relative}
.bar{flex:0 0 auto;padding:max(12px,calc(var(--tg-safe-top,0px) + var(--tg-content-top,0px))) 16px 12px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;gap:8px;background:var(--surface);z-index:2}
.bar b{font-size:16px;font-weight:650}.hint{color:var(--ink-3);font-size:12.5px}
.body{flex:1 1 auto;min-height:0;overflow-x:hidden;overflow-y:auto;-webkit-overflow-scrolling:touch;overscroll-behavior:contain;padding:12px 14px 16px}
.foot-act{flex:0 0 auto;padding:10px 14px;border-top:1px solid var(--line);background:var(--surface);z-index:4}
.foot-act .btn{margin:0}
.pnav{flex:0 0 auto;display:flex;border-top:1px solid var(--line);background:var(--surface);padding:4px 0 max(6px,calc(var(--tg-safe-bottom,0px) + env(safe-area-inset-bottom,0px)));z-index:3;box-shadow:0 -4px 16px rgba(0,0,0,.04)}
.pnav button{flex:1;border:0;background:none;padding:8px 4px;font-size:11px;color:var(--ink-3);cursor:pointer;display:flex;flex-direction:column;align-items:center;gap:3px;-webkit-tap-highlight-color:transparent;touch-action:manipulation}
.pnav button[aria-current=page]{color:var(--accent);font-weight:600}.pnav svg{width:18px;height:18px;pointer-events:none}
.titem{border:1px solid var(--line);border-radius:10px;padding:10px 12px;margin-bottom:8px;cursor:pointer}.titem .r{display:flex;justify-content:space-between;gap:8px}.titem small{display:block;color:var(--ink-2);font-size:12px;margin-top:2px}
.pill{display:inline-flex;align-items:center;gap:5px;font-size:11.5px;font-weight:600;padding:2px 8px;border-radius:999px;background:var(--bg);border:1px solid var(--line)}.pill i{width:6px;height:6px;border-radius:50%;background:var(--ink-3)}.pill.new i{background:var(--accent)}.pill.review i{background:var(--warn)}.pill.approved i,.pill.assigned i,.pill.en_route i{background:var(--s3)}.pill.delivered i{background:var(--amber)}.pill.done i{background:var(--good)}.pill.rejected i,.pill.unconfirmed i{background:var(--ink-3)}.pill.dispute i{background:var(--crit)}
.btn{border:1px solid var(--line-2);background:var(--surface);border-radius:10px;padding:10px 12px;font:inherit;font-weight:600;cursor:pointer;color:var(--ink)}.btn.primary{background:var(--accent);border-color:var(--accent);color:#fff}.btn.good{background:var(--good);border-color:var(--good);color:#fff}.btn.danger{color:var(--crit);border-color:var(--crit)}.btn.big{width:100%;justify-content:center;display:block;text-align:center;padding:12px;border-radius:12px;font-size:15px}.btn:disabled{opacity:.45}
.big2{display:grid;gap:10px;margin-top:12px}.alert{border-radius:8px;padding:9px 12px;font-size:13px;margin-top:10px;border:1px solid transparent}.alert.info{background:var(--accent-bg)}.alert.warn{background:var(--warn-bg);color:var(--warn)}.alert.ok{background:var(--good-bg);color:var(--good)}.alert.crit{background:var(--crit-bg);color:var(--crit)}
.psec{margin-top:14px;padding-top:12px;border-top:1px solid var(--line)}.psec h4{margin:0 0 8px;font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-3)}
.tl{list-style:none;margin:0;padding:0}.tl li{position:relative;padding:0 0 12px 16px;font-size:13px}.tl li::before{content:"";position:absolute;left:0;top:6px;width:8px;height:8px;border-radius:50%;background:var(--line-2)}.tl li.done::before{background:var(--good)}.tl li.now::before{background:var(--amber)}.tl li small{display:block;color:var(--ink-3);font-size:11.5px;margin-top:2px}
.sel{width:100%;font:inherit;padding:9px 10px;border-radius:8px;border:1px solid var(--line-2);background:var(--surface);color:var(--ink);margin:8px 0}
.empty{padding:24px 8px;text-align:center;color:var(--ink-3)}.banner{background:var(--accent-bg);border-radius:10px;padding:9px 12px;font-size:12.5px;margin-bottom:12px}.amb{color:var(--warn);font-weight:500}
.actions{margin-top:8px}.err{padding:24px;text-align:center;color:var(--crit)}
/* Shikoyat forma — sodda */
.ffield{display:grid;gap:6px;margin-bottom:12px}
.ffield .lbl{font-size:10.5px;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-3);font-weight:600}
.ffield .txt,.ffield textarea,.ffield .file,.ffield .sel{width:100%;border:1px solid var(--line-2);border-radius:10px;padding:10px 12px;font:inherit;font-size:14px;color:var(--ink);background:var(--bg)}
.ffield textarea{min-height:88px;resize:vertical}
.psug{position:relative}
.psug input{width:100%;border:1px solid var(--line-2);border-radius:10px;padding:11px 12px;font:inherit;font-size:14px;color:var(--ink);background:var(--bg)}
.psug-list{display:none;margin-top:6px;border:1px solid var(--line);border-radius:12px;background:var(--surface);max-height:220px;overflow-y:auto;box-shadow:0 8px 20px rgba(0,0,0,.12)}
.psug-list.on{display:block}
.psug-item{display:block;width:100%;text-align:left;border:0;border-bottom:1px solid var(--line);background:none;padding:11px 12px;font:inherit;cursor:pointer;color:var(--ink)}
.psug-item:last-child{border-bottom:0}
.psug-item:active{background:var(--accent-bg)}
.psug-item b{display:block;font-size:13.5px;font-weight:600;line-height:1.3}
.psug-item small{display:block;margin-top:2px;color:var(--ink-3);font-size:11.5px}
.picked{display:grid;gap:8px;margin:0}
.pcard{display:flex;align-items:center;gap:12px;padding:12px;border:1px solid var(--line);border-radius:12px;background:var(--bg)}
.pcard .meta{flex:1;min-width:0}
.pcard .meta b{display:block;font-size:14px;font-weight:650;line-height:1.3}
.pcard .meta small{display:block;margin-top:3px;color:var(--ink-3);font-size:12px}
.step{flex:none;display:inline-flex;align-items:center;border:1px solid var(--line-2);border-radius:999px;background:var(--surface);overflow:hidden;height:36px}
.step button{width:36px;height:36px;border:0;background:none;color:var(--ink);font:inherit;font-size:18px;font-weight:500;cursor:pointer;line-height:1;-webkit-tap-highlight-color:transparent}
.step button:disabled{opacity:.35;cursor:default}
.step .n{min-width:36px;text-align:center;font-variant-numeric:tabular-nums;font-weight:650;font-size:14px;color:var(--ink);background:var(--bg);height:100%;display:grid;place-items:center;border-left:1px solid var(--line);border-right:1px solid var(--line)}
.chek-pick .titem{cursor:pointer}
.chek-pick .titem:active{background:var(--accent-bg)}
.phgrid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px}
.phcard{aspect-ratio:1;border-radius:12px;border:1px solid var(--line);background:var(--bg);position:relative;overflow:hidden;padding:0;cursor:pointer;-webkit-tap-highlight-color:transparent}
.phcard.add{border-style:dashed;border-color:var(--line-2);display:grid;place-items:center;color:var(--ink-3);font-size:28px;font-weight:300;line-height:1}
.phcard.add:active{background:var(--accent-bg);color:var(--accent)}
.phcard img{width:100%;height:100%;object-fit:cover;display:block;pointer-events:none}
.phcard .rm{position:absolute;top:5px;right:5px;width:24px;height:24px;border:0;border-radius:50%;background:rgba(15,20,30,.72);color:#fff;font:inherit;font-size:15px;line-height:1;cursor:pointer;display:grid;place-items:center;z-index:1}
.phview{position:fixed;inset:0;z-index:50;background:rgba(10,14,24,.88);display:grid;place-items:center;padding:20px;cursor:zoom-out}
.phview img{max-width:100%;max-height:100%;border-radius:10px;object-fit:contain}
.phview .pvx{position:absolute;top:max(12px,calc(var(--tg-safe-top,0px) + 8px));right:12px;width:36px;height:36px;border:0;border-radius:10px;background:rgba(15,20,30,.7);color:#fff;font-size:20px;cursor:pointer}
.pcard-wrap{border:1px solid var(--line);border-radius:12px;background:var(--bg);overflow:hidden}
.pcard-wrap .pcard{border:0;border-radius:0;background:transparent}
.pcard-wrap .drop{border-top:1px solid var(--line);padding:12px;display:grid;gap:10px;background:var(--surface)}
.pcard-wrap.open{border-color:var(--accent)}
.addr-row{display:grid;gap:8px}
.btn.geo-big{width:100%;padding:14px 16px;font-size:15.5px;font-weight:650;border-radius:12px;display:flex;align-items:center;justify-content:center;gap:8px;background:var(--accent);border-color:var(--accent);color:#fff}
.btn.geo-big:disabled{opacity:.55}
.btn.geo-big.ok{background:var(--good);border-color:var(--good)}
.geomap{border:1px solid var(--line);border-radius:12px;overflow:hidden;background:#1a1f28;height:280px;z-index:1;touch-action:none}
.geomap .leaflet-control-attribution{font-size:9px;background:rgba(255,255,255,.75)!important}
.geomap-tip{margin:0;font-size:12px;color:var(--ink-3)}
.geomap-meta{display:flex;justify-content:space-between;align-items:center;gap:8px;margin-top:6px}
.geomap-meta .hint{margin:0}
.geomap-meta .btn-sm{border:1px solid var(--line-2);background:var(--surface);border-radius:8px;padding:6px 10px;font:inherit;font-size:12px;font-weight:600;cursor:pointer;color:var(--ink)}
.geo-actions{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.okbox{text-align:center;padding:28px 12px}.okbox b{display:block;font-size:18px;margin:8px 0 4px}
.pline{display:flex;justify-content:space-between;gap:10px;align-items:flex-start;padding:10px 0;border-bottom:1px dashed var(--line);font-size:13.5px}
.pline:last-child{border-bottom:0}
.pline .pn{min-width:0;font-weight:600;line-height:1.3}
.pline .pq{flex:none;color:var(--ink-3);font-variant-numeric:tabular-nums}
.pline .pw{display:block;margin-top:3px;font-size:11.5px;font-weight:400;color:var(--ink-3)}
</style>
</head>
<body>
<div class="app" id="app"><div class="body empty">Yuklanmoqda…</div></div>
<script>
const STATUS=@json($statuses);
const NO_REASONS=@json($noReasons);
const REASONS=@json($reasons);
const qs=new URLSearchParams(location.search);
const AUTH_T=qs.get('t')||'';
const _tab=qs.get('tab');
function tgApp(){return (window.Telegram&&Telegram.WebApp)||null;}
function applyTgSafe(w){
  try{
    const s=w.safeAreaInset||{};
    const c=w.contentSafeAreaInset||{};
    const root=document.documentElement;
    root.style.setProperty('--tg-safe-top',(s.top||0)+'px');
    root.style.setProperty('--tg-safe-bottom',(s.bottom||0)+'px');
    root.style.setProperty('--tg-content-top',(c.top||0)+'px');
    root.style.setProperty('--tg-content-bottom',(c.bottom||0)+'px');
    if(w.viewportHeight){
      document.documentElement.style.height=w.viewportHeight+'px';
      document.body.style.height=w.viewportHeight+'px';
      const app=document.getElementById('app');
      if(app){app.style.height=w.viewportHeight+'px';app.style.maxHeight=w.viewportHeight+'px';}
    }
  }catch(e){}
}
function bootTelegramShell(){
  const w=tgApp();if(!w) return null;
  try{
    w.ready();
    w.expand();
    if(typeof w.requestFullscreen==='function'){
      try{w.requestFullscreen();}catch(e){}
    }
    if(w.disableVerticalSwipes) w.disableVerticalSwipes();
    if(w.setHeaderColor) w.setHeaderColor('secondary_bg_color');
    if(w.setBackgroundColor) w.setBackgroundColor(w.themeParams?.bg_color||'#ffffff');
    if(w.setBottomBarColor) w.setBottomBarColor(w.themeParams?.bg_color||'#ffffff');
    applyTgSafe(w);
    w.onEvent?.('viewportChanged',()=>{w.expand();applyTgSafe(w);});
    w.onEvent?.('fullscreenChanged',()=>applyTgSafe(w));
    w.onEvent?.('safeAreaChanged',()=>applyTgSafe(w));
    w.onEvent?.('contentSafeAreaChanged',()=>applyTgSafe(w));
    // Baʼzi klientlarda fullscreen biroz kechikadi
    setTimeout(()=>{try{w.expand();if(typeof w.requestFullscreen==='function'&&!w.isFullscreen)w.requestFullscreen();}catch(e){}applyTgSafe(w);},120);
    setTimeout(()=>{try{w.expand();if(typeof w.requestFullscreen==='function'&&!w.isFullscreen)w.requestFullscreen();}catch(e){}applyTgSafe(w);},600);
  }catch(e){}
  return w;
}
const tg=bootTelegramShell();
// Guruhdan startapp=c{id} → kuryer Mini App
(function(){
  const w=tgApp();
  const sp=(w&&w.initDataUnsafe&&w.initDataUnsafe.start_param)||qs.get('startapp')||'';
  const m=String(sp).match(/^c(\d+)$/i);
  if(m){
    const u=new URL('/courier-app',location.origin);
    u.searchParams.set('claim',m[1]);
    location.replace(u.toString());
  }
})();
const PHOTO_MAX=20;
const state={tab:['home','claims','form'].includes(_tab)?_tab:'home',sel:null,chek:null,noask:false,reason:'',boot:null,err:null,formOk:null,formSending:false,formStep:'cheks',formChek:null,openId:null,picked:[],photoView:null,draft:{address:'',lat:null,lng:null}};
function clearAllItemPhotos(){
  (state.picked||[]).forEach(p=>{(p.photos||[]).forEach(ph=>{try{URL.revokeObjectURL(ph.url);}catch(e){}});p.photos=[];});
  state.photoView=null;
}
function addItemPhotos(pid, list){
  const p=(state.picked||[]).find(x=>Number(x.id)===Number(pid));
  if(!p||!list||!list.length) return;
  if(!p.photos) p.photos=[];
  for(const f of list){
    if(p.photos.length>=PHOTO_MAX) break;
    if(!f||!(f.type||'').startsWith('image/')) continue;
    p.photos.push({key:String(Date.now())+Math.random().toString(16).slice(2),file:f,url:URL.createObjectURL(f)});
  }
}
function removeItemPhoto(pid, key){
  const p=(state.picked||[]).find(x=>Number(x.id)===Number(pid));
  if(!p) return;
  const i=(p.photos||[]).findIndex(ph=>String(ph.key)===String(key));
  if(i<0) return;
  try{URL.revokeObjectURL(p.photos[i].url);}catch(e){}
  p.photos.splice(i,1);
  if(state.photoView&&String(state.photoView.key)===String(key)) state.photoView=null;
}
function openProduct(id){
  state.openId=id==null?null:Number(id);
  (state.picked||[]).forEach(p=>{p.open=Number(p.id)===Number(state.openId);});
}
function itemPhotoGrid(p){
  const cards=(p.photos||[]).map(ph=>`<div class="phcard" data-phview="${ph.key}" data-pid="${p.id}" title="Ochish">
      <img src="${ph.url}" alt="">
      <button type="button" class="rm" data-phrm="${ph.key}" data-pid="${p.id}" aria-label="oʻchirish">×</button>
    </div>`).join('');
  const add=(p.photos||[]).length<PHOTO_MAX
    ?`<button type="button" class="phcard add" data-phadd="${p.id}" aria-label="Rasm qoʻshish">+</button>`:'';
  return `<div class="phgrid">${cards}${add}</div>
    <input type="file" class="fPhotosItem" data-pid="${p.id}" accept="image/*" multiple hidden>`;
}
const $=s=>document.querySelector(s);
const pill=(st,l)=>`<span class="pill ${st}"><i></i>${l||STATUS[st]||st}</span>`;
const fdt=s=>{if(!s)return '—';const d=new Date(s);return isNaN(d)?s:d.toLocaleString('uz-UZ',{day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'});};
const qtyN=n=>Math.max(1,Math.round(Number(n)||1));
const icons={home:'<path d="M2.5 8 8 3l5.5 5M4 7v6h8V7"/>',claims:'<path d="M3 2h10v12H3zM5.5 5h5M5.5 8h5M5.5 11h3"/>',form:'<path d="M8 3v10M3 8h10"/>'};

function authPayload(extra){
  const w=tgApp();
  return {initData:(w&&w.initData)||'',t:AUTH_T,...(extra||{})};
}
async function api(path,body){
  const r=await fetch(path,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify(authPayload(body))});
  const d=await r.json().catch(()=>({}));
  if(!r.ok) throw new Error(d.message||(d.errors&&Object.values(d.errors).flat()[0])||'Xato');
  return d;
}
async function apiForm(path,fd){
  const w=tgApp();
  fd.append('initData',(w&&w.initData)||'');
  fd.append('t',AUTH_T);
  const r=await fetch(path,{method:'POST',headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:fd});
  const d=await r.json().catch(()=>({}));
  if(!r.ok) throw new Error(d.message||(d.errors&&Object.values(d.errors).flat()[0])||'Xato');
  return d;
}
function money(n){return Math.round(Number(n)||0).toLocaleString('uz-UZ')+' soʼm';}
function chekList(b){
  if(Array.isArray(b.cheks)&&b.cheks.length) return b.cheks;
  // eski boot — mahsulotlarni chek boʼyicha guruhlash
  const map=new Map();
  (b.activations||[]).forEach(a=>{
    const k=String(a.order_id||a.chek||a.id);
    if(!map.has(k)) map.set(k,{key:k,chek:a.chek,store:a.store,date:a.date,items_n:0,sum:0,items:[]});
    const g=map.get(k);
    g.items.push({id:a.id,product:a.product,qty:qtyN(a.qty),warranty:a.warranty,sum:a.sum});
    g.items_n=g.items.length;g.sum+=Number(a.sum)||0;
  });
  return [...map.values()];
}
function pickChekForForm(key){
  const b=state.boot;if(!b) return false;
  const c=chekList(b).find(x=>String(x.key)===String(key));
  if(!c||!(c.items||[]).length) return false;
  clearAllItemPhotos();
  state.picked=(c.items||[]).map(it=>({
    id:it.id,
    title:it.product,
    maxQty:qtyN(it.qty),
    brakQty:0,
    reason:'',
    desc:'',
    photos:[],
    open:false,
    chek:c.chek
  }));
  state.formChek=String(c.key);
  state.formStep='details';
  state.openId=null;
  state.draft={address:'',lat:null,lng:null};
  return true;
}
function setBrakQty(id, next){
  const p=(state.picked||[]).find(x=>Number(x.id)===Number(id));
  if(!p) return;
  const max=qtyN(p.maxQty);
  const prev=Math.max(0, Math.round(Number(p.brakQty)||0));
  p.brakQty=Math.max(0, Math.min(max, Math.round(Number(next)||0)));
  // + bosilib 0→1: shu mahsulot ochiladi, eski yopiladi
  if(prev===0 && p.brakQty>0) openProduct(p.id);
  if(p.brakQty===0){
    p.reason='';p.desc='';
    (p.photos||[]).forEach(ph=>{try{URL.revokeObjectURL(ph.url);}catch(e){}});
    p.photos=[];
    if(Number(state.openId)===Number(p.id)) openProduct(null);
  }
}
function goForm(preselectChek){
  state.tab='form';state.sel=null;state.chek=null;state.formOk=null;state.noask=false;
  clearAllItemPhotos();
  state.picked=[];state.draft={address:'',lat:null,lng:null};state.formChek=null;state.formStep='cheks';state.openId=null;
  if(preselectChek) pickChekForForm(preselectChek);
  render();
}

function render(){
  const root=$('#app');
  if(state.err){root.innerHTML=`<div class="err"><b>Kirish mumkin emas</b><p>${state.err}</p><p class="hint">Botdagi «Mening uyim» yoki «Shikoyat» tugmasidan oching.</p></div>`;return;}
  const b=state.boot;if(!b){root.innerHTML=`<div class="body empty">Yuklanmoqda…</div>`;return;}
  let body='';
  if(state.tab==='home'){
    const list=chekList(b);
    const open=state.chek?list.find(x=>String(x.key)===String(state.chek)):null;
    if(open){
      body=`<button class="btn" type="button" data-chekback="1" style="margin-bottom:10px">← Cheklar</button>
        <div class="r" style="display:flex;justify-content:space-between;gap:8px;align-items:baseline"><b style="font-size:15px">Chek ${open.chek}</b>${pill('done','Kafolat faol')}</div>
        <p class="hint" style="margin:4px 0 12px">${open.date} · ${open.store}${open.sum?` · ${money(open.sum)}`:''} · ${open.items_n} ta mahsulot</p>
        <div class="psec" style="margin-top:0;padding-top:0;border:0">
          ${(open.items||[]).map(it=>`<div class="pline"><div class="pn">${it.product}<span class="pw">${it.warranty||''}${it.sum?` · ${money(it.sum)}`:''}</span></div><span class="pq">×${it.qty||1}</span></div>`).join('')||'<div class="empty">Mahsulot yoʼq</div>'}
        </div>
        <div class="big2" style="margin-top:14px"><button class="btn primary big" type="button" data-new="1" data-chekform="${open.key}">Brak boʼyicha soʼrov</button></div>`;
    }else{
      const units=c=>(c.items||[]).reduce((s,it)=>s+qtyN(it.qty),0);
      body=`<p class="hint" style="margin:0 0 8px">Cheklar — telefoningizga bogʼlangan (real)</p>`+
        (list.length?list.map(c=>`<div class="titem" data-chek="${c.key}"><div class="r"><b>Chek ${c.chek}</b>${pill('done','Kafolat faol')}</div><small>${c.date} · ${c.store}${c.sum?` · ${money(c.sum)}`:''}</small><small>${c.items_n} qator · ${units(c)} dona · ochish →</small></div>`).join(''):'<div class="empty">Faol kafolat yoʼq — avval botda chekni tasdiqlang</div>');
    }
  }else if(state.tab==='claims'){
    const c=state.sel?b.claims.find(x=>x.id===state.sel):null;
    if(c){
      const order=['new','review','approved','assigned','en_route','delivered','done'];
      const labels={new:'Yuborildi',review:'Koʼrib chiqilmoqda',approved:'Tasdiqlandi',assigned:'Kuryer belgilandi',en_route:'Kuryer yoʼlda',delivered:'Kuryer yetkazdi',done:'Yakunlandi'};
      const reached=s=> (c.logs||[]).some(e=>e.to===s)||c.status===s;
      let confirm='';
      if(c.status==='delivered'&&!c.confirm){
        confirm=state.noask
          ?`<div class="psec"><h4>Tasdiqlang</h4><p style="margin:0 0 6px;font-weight:600">Nima boʼldi?</p><select class="sel" id="noReason"><option value="">Tanlang</option>${NO_REASONS.map(r=>`<option ${state.reason===r?'selected':''}>${r}</option>`).join('')}</select><div class="big2"><button class="btn danger big" type="button" data-ans="no">Yuborish</button></div></div>`
          :`<div class="psec"><h4>Tasdiqlang</h4><p style="margin:0 0 6px;font-weight:600">Kuryer ${c.courier||''} brak mahsulotni almashtirib berdimi?</p><div class="big2"><button class="btn good big" type="button" data-ans="yes">Ha, almashtirildi</button><button class="btn danger big" type="button" data-ans="noask">Yoʼq, almashtirilmadi</button></div><p class="hint" style="margin:8px 0 0">Javob bir marta beriladi.</p></div>`;
      }
      body=`<button class="btn" type="button" data-back="1" style="margin-bottom:10px">← Soʼrovlarim</button>
        <div style="display:flex;justify-content:space-between;gap:8px;align-items:baseline"><b style="font-size:15px">${c.number}</b>${pill(c.status,STATUS[c.status])}</div>
        <p class="hint" style="margin:0">${c.prod} · ${c.reason}</p>
        ${c.status==='rejected'?`<div class="alert warn">Rad etildi: ${c.reject_reason||'—'}</div>`:''}
        ${c.status==='dispute'?`<div class="alert crit">Javobingiz: almashtirilmadi (${c.confirm_reason||'—'}). Admin bogʼlanadi.</div>`:''}
        ${c.courier&&['assigned','en_route','delivered','done','dispute'].includes(c.status)?`<div class="alert info">Kuryer: <b>${c.courier}</b>${c.courier_phone?` · ${c.courier_phone}`:''}${c.deadline&&['assigned','en_route'].includes(c.status)?` · eng kech ${fdt(c.deadline)}`:''}</div>`:''}
        ${confirm}
        <div class="psec"><h4>Holat</h4><ul class="tl">${order.filter(s=>!(s==='done'&&['rejected','dispute','unconfirmed'].includes(c.status))).map(s=>{const ev=(c.logs||[]).find(e=>e.to===s);const r=reached(s);return `<li class="${r?(c.status===s&&s!=='done'?'now':'done'):''}">${labels[s]}<small>${ev?fdt(ev.at):(r?'':'—')}</small></li>`;}).join('')}</ul></div>`;
    }else{
      body=b.claims.map(x=>`<div class="titem" data-sel="${x.id}"><div class="r"><b>${x.number}</b>${pill(x.status,STATUS[x.status])}</div><small>${x.prod} · ${fdt(x.date)}</small>${x.status==='delivered'&&!x.confirm?'<small class="amb">Tasdiqlashingiz kutilmoqda →</small>':''}</div>`).join('')||'<div class="empty">Soʼrov yoʼq</div>';
    }
  }else if(state.tab==='form'){
    if(state.formOk){
      body=`<div class="okbox"><div class="alert ok" style="margin:0">Qabul qilindi</div><b>${state.formOk}</b>
        <div class="big2"><button class="btn primary big" type="button" data-tab="claims">Soʼrovlarim</button><button class="btn big" type="button" data-newform="1">Yana</button></div></div>`;
    }else{
      const list=chekList(b);
      const reasons=b.reasons||REASONS||[];
      const d=state.draft||{};
      const escAttr=s=>String(s||'').replace(/"/g,'&quot;');
      const escHtml=s=>String(s||'').replace(/</g,'&lt;');
      if(!list.length){
        body='<div class="empty">Avval botda chekni tasdiqlang</div>';
      }else if(state.formStep!=='details'||!(state.picked||[]).length){
        const units=c=>(c.items||[]).reduce((s,it)=>s+qtyN(it.qty),0);
        body=`<div class="chek-pick">
          <p class="hint" style="margin:0 0 10px">Shikoyat uchun <b>chekni tanlang</b> — mahsulotlar avtomatik yigʻiladi</p>
          ${list.map(c=>`<div class="titem" data-formchek="${c.key}"><div class="r"><b>Chek ${c.chek}</b>${pill('done','Kafolat faol')}</div><small>${c.date} · ${c.store}${c.sum?` · ${money(c.sum)}`:''}</small><small>${c.items_n} qator · ${units(c)} dona · tanlash →</small></div>`).join('')}
        </div>`;
      }else{
        const open=list.find(x=>String(x.key)===String(state.formChek));
        const cardHtml=(state.picked||[]).map(p=>{
          const max=qtyN(p.maxQty);
          const n=Math.max(0, Math.min(max, Math.round(Number(p.brakQty)||0)));
          const isOpen=!!p.open && n>0;
          const drop=isOpen?`<div class="drop">
              <div class="ffield" style="margin:0"><span class="lbl">Sabab</span>
                <select class="sel" data-ireason="${p.id}"><option value="">Tanlang</option>${reasons.map(r=>`<option value="${r}" ${p.reason===r?'selected':''}>${r}</option>`).join('')}</select>
              </div>
              <div class="ffield" style="margin:0"><span class="lbl">Muammo</span>
                <textarea data-idesc="${p.id}" maxlength="2000" placeholder="Qisqa yozing…">${escHtml(p.desc||'')}</textarea>
              </div>
              <div class="ffield" style="margin:0"><span class="lbl">Foto</span>${itemPhotoGrid(p)}</div>
            </div>`:'';
          return `<div class="pcard-wrap ${isOpen?'open':''}" data-pid="${p.id}">
            <div class="pcard">
              <div class="meta"><b>${escHtml(p.title)}</b><small>Chekda: ${max} dona · brak miqdori</small></div>
              <div class="step">
                <button type="button" data-step="-" data-pid="${p.id}" ${n<=0?'disabled':''} aria-label="kamaytirish">−</button>
                <span class="n">${n}</span>
                <button type="button" data-step="+" data-pid="${p.id}" ${n>=max?'disabled':''} aria-label="oshirish">+</button>
              </div>
            </div>${drop}
          </div>`;
        }).join('');
        const hasGeo=d.lat!=null&&d.lng!=null&&!isNaN(Number(d.lat))&&!isNaN(Number(d.lng));
        const showMap=!!d.mapOpen||hasGeo;
        // Default: Toshkent markazi — foydalanuvchi xaritada boshqa joy belgilaydi
        const latN=hasGeo?Number(d.lat):(Number(d.mapLat)||41.3111);
        const lngN=hasGeo?Number(d.lng):(Number(d.mapLng)||69.2797);
        const geoBlock=showMap
          ?`<p class="geomap-tip">Xaritada bosing yoki 📍 belgini sudrab kerakli joyni tanlang</p>
            <div class="geomap" id="geoMap" data-lat="${latN}" data-lng="${lngN}" data-picked="${hasGeo?1:0}"></div>
            <div class="geomap-meta">
              <span class="hint" id="geoCoords">${hasGeo?`📍 ${latN.toFixed(5)}, ${lngN.toFixed(5)} · tanlangan`:'📍 Xaritada joy belgilang'}</span>
              <button type="button" class="btn-sm" id="btnGeoClear">Yopish</button>
            </div>`
          :'';
        body=`<button class="btn" type="button" data-formchekback="1" style="margin-bottom:10px">← Cheklar</button>
          <p class="hint" style="margin:0 0 8px">Chek <b>${open?open.chek:(state.picked[0]&&state.picked[0].chek)||'—'}</b> — + bosib brak miqdorini belgilang, sabab/muammo/rasm ochiladi</p>
          <form id="claimForm">
            <div class="ffield"><span class="lbl">Mahsulotlar</span>
              <div class="picked" id="pickedList">${cardHtml}</div>
            </div>
            <div class="ffield"><span class="lbl">Manzil</span>
              <div class="addr-row">
                <input class="txt" type="text" id="fAddress" maxlength="500" placeholder="Tuman, koʼcha, uy — yoki pastdan xarita" value="${escAttr(d.address||'')}">
                <div class="geo-actions">
                  <button class="btn geo-big ${hasGeo?'ok':''}" type="button" id="btnGeo">${hasGeo?'📍 GPS yangilash':'📍 GPS aniqlash'}</button>
                  <button class="btn geo-big" type="button" id="btnMapPick" style="background:var(--s3);border-color:var(--s3)">${showMap?'🗺 Xarita':'🗺 Xaritadan belgilash'}</button>
                </div>
                ${geoBlock}
              </div>
            </div>
          </form>`;
      }
    }
  }
  const showSend=state.tab==='form'&&!state.formOk&&state.formStep==='details'&&(state.picked||[]).length>0;
  const foot=showSend?`<div class="foot-act"><button class="btn primary big" type="submit" form="claimForm" id="btnSubmit">Yuborish</button></div>`:'';
  const viewer=state.photoView?`<div class="phview" data-phclose="1"><button type="button" class="pvx" data-phclose="1" aria-label="yopish">×</button><img src="${state.photoView.url}" alt=""></div>`:'';
  root.innerHTML=`<div class="bar"><b>QR Kafolat</b><span class="hint">${b.user.short}</span></div>
    <div class="body">${body}</div>
    ${foot}
    <div class="pnav">${[['home','Mening uyim'],['claims','Soʼrovlarim'],['form','Yangi soʼrov']].map(([k,l])=>`<button type="button" data-tab="${k}" aria-current="${state.tab===k?'page':'false'}"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">${icons[k]}</svg>${l}</button>`).join('')}</div>
    ${viewer}`;

  function syncFormFields(){
    const d=state.draft||{address:'',lat:null,lng:null};
    const addr=root.querySelector('#fAddress');
    if(addr) d.address=addr.value||'';
    (state.picked||[]).forEach(p=>{
      const r=root.querySelector(`[data-ireason="${p.id}"]`);
      const t=root.querySelector(`[data-idesc="${p.id}"]`);
      if(r) p.reason=r.value||'';
      if(t) p.desc=t.value||'';
    });
    state.draft=d;
  }

  root.querySelectorAll('[data-tab]').forEach(el=>el.onclick=()=>{
    const t=el.dataset.tab;
    state.tab=t;state.sel=null;state.chek=null;state.noask=false;state.photoView=null;
    if(t==='form'){
      state.formOk=null;state.formStep='cheks';state.formChek=null;state.openId=null;
      clearAllItemPhotos();state.picked=[];state.draft={address:'',lat:null,lng:null};
    }
    render();
  });
  root.querySelectorAll('[data-sel]').forEach(el=>el.onclick=()=>{state.sel=Number(el.dataset.sel);render();});
  root.querySelectorAll('[data-chek]').forEach(el=>el.onclick=()=>{state.chek=el.dataset.chek;render();});
  root.querySelector('[data-chekback]')?.addEventListener('click',()=>{state.chek=null;render();});
  root.querySelector('[data-back]')?.addEventListener('click',()=>{state.sel=null;state.noask=false;render();});
  root.querySelectorAll('[data-new]').forEach(el=>el.onclick=()=>goForm(el.dataset.chekform||null));
  root.querySelector('[data-newform]')?.addEventListener('click',()=>goForm());
  root.querySelectorAll('[data-formchek]').forEach(el=>el.onclick=()=>{
    if(pickChekForForm(el.dataset.formchek)){
      if(tg&&tg.HapticFeedback) tg.HapticFeedback.selectionChanged();
      render();
    }
  });
  root.querySelector('[data-formchekback]')?.addEventListener('click',()=>{
    clearAllItemPhotos();
    state.formStep='cheks';state.formChek=null;state.picked=[];state.openId=null;
    state.draft={address:'',lat:null,lng:null};render();
  });
  const noSel=root.querySelector('#noReason');
  if(noSel) noSel.onchange=()=>{state.reason=noSel.value;};
  root.querySelectorAll('[data-ans]').forEach(el=>el.onclick=async()=>{
    const a=el.dataset.ans;
    if(a==='noask'){state.noask=true;render();return;}
    el.disabled=true;
    try{
      const body={answer:a==='yes'?'yes':'no'};
      if(body.answer==='no'){body.reason=state.reason||($('#noReason')&&$('#noReason').value)||'';if(!body.reason)throw new Error('Sabab tanlang');}
      const r=await api('/miniapp/api/claims/'+state.sel+'/confirm',body);
      const i=b.claims.findIndex(x=>x.id===state.sel);
      if(i>=0) b.claims[i]=r.claim;
      state.noask=false;state.reason='';
      if(tg&&tg.HapticFeedback) tg.HapticFeedback.notificationOccurred('success');
      render();
    }catch(e){alert(e.message);el.disabled=false;}
  });

  // Shikoyat — har mahsulot: miqdor + accordion sabab/muammo/rasm + umumiy manzil/geo
  const form=root.querySelector('#claimForm');
  if(form){
    root.querySelectorAll('[data-step]').forEach(btn=>{
      btn.onclick=e=>{
        e.stopPropagation();
        syncFormFields();
        const id=btn.dataset.pid;
        const p=(state.picked||[]).find(x=>Number(x.id)===Number(id));
        if(!p) return;
        const cur=Math.max(0, Math.round(Number(p.brakQty)||0));
        setBrakQty(id, btn.dataset.step==='+' ? cur+1 : cur-1);
        if(btn.dataset.step==='+' && p.brakQty>0) openProduct(p.id);
        if(tg&&tg.HapticFeedback) tg.HapticFeedback.selectionChanged();
        render();
      };
    });
    // Card ustiga bosilsa — miqdor > 0 boʻlsa dropdown ochiladi/yopiladi
    root.querySelectorAll('.pcard-wrap .pcard .meta').forEach(el=>{
      el.style.cursor='pointer';
      el.onclick=()=>{
        syncFormFields();
        const wrap=el.closest('.pcard-wrap');
        const id=wrap?.dataset?.pid;
        const p=(state.picked||[]).find(x=>Number(x.id)===Number(id));
        if(!p||!(Math.round(Number(p.brakQty)||0)>0)) return;
        openProduct(Number(state.openId)===Number(p.id)?null:p.id);
        render();
      };
    });
    root.querySelectorAll('[data-ireason]').forEach(el=>{
      el.addEventListener('change',()=>{
        const p=(state.picked||[]).find(x=>Number(x.id)===Number(el.dataset.ireason));
        if(p) p.reason=el.value||'';
      });
    });
    root.querySelectorAll('[data-idesc]').forEach(el=>{
      el.addEventListener('input',()=>{
        const p=(state.picked||[]).find(x=>Number(x.id)===Number(el.dataset.idesc));
        if(p) p.desc=el.value||'';
      });
    });
    const addrEl=root.querySelector('#fAddress');
    if(addrEl) addrEl.addEventListener('input',()=>{
      state.draft=state.draft||{};
      state.draft.address=addrEl.value||'';
    });
    const geoBtn=root.querySelector('#btnGeo');
    const applyGeo=(lat,lng,opts)=>{
      const o=opts||{};
      state.draft=state.draft||{};
      state.draft.lat=lat;state.draft.lng=lng;
      state.draft.mapOpen=true;
      state.draft.mapLat=lat;state.draft.mapLng=lng;
      if(!(state.draft.address||'').trim()||/^Geo:\s*/i.test(state.draft.address||'')){
        state.draft.address=`Geo: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
        const addrInp=root.querySelector('#fAddress');
        if(addrInp) addrInp.value=state.draft.address;
      }
      const coordsEl=root.querySelector('#geoCoords');
      if(coordsEl) coordsEl.textContent=`📍 ${lat.toFixed(5)}, ${lng.toFixed(5)} · tanlangan`;
      if(geoBtn){geoBtn.disabled=false;geoBtn.classList.add('ok');geoBtn.textContent='📍 GPS yangilash';}
      if(tg&&tg.HapticFeedback) tg.HapticFeedback.selectionChanged();
      if(o.rerender!==false){
        render();
        setTimeout(()=>{
          const map=document.getElementById('geoMap');
          if(map) map.scrollIntoView({behavior:'smooth',block:'nearest'});
        },80);
      }
    };
    const geoFail=(msg)=>{
      if(geoBtn){geoBtn.disabled=false;geoBtn.textContent='📍 GPS aniqlash';}
      alert(msg||'Geolokatsiya olinmadi — xaritadan belgilang yoki manzil yozing');
    };
    const viaNavigator=()=>{
      if(!navigator.geolocation){geoFail('Brauzer geolokatsiyani qoʻllab-quvvatlamaydi');return;}
      navigator.geolocation.getCurrentPosition(
        pos=>applyGeo(pos.coords.latitude,pos.coords.longitude),
        err=>{
          const code=err&&err.code;
          if(code===1) geoFail('Joylashuvga ruxsat berilmadi. «Xaritadan belgilash»ni bosing.');
          else if(code===3) geoFail('Vaqt tugadi — qayta urinib koʻring yoki xaritadan belgilang.');
          else geoFail('Joylashuv aniqlanmadi — xaritadan belgilang.');
        },
        {enableHighAccuracy:true,timeout:15000,maximumAge:0}
      );
    };
    geoBtn?.addEventListener('click',()=>{
      syncFormFields();
      geoBtn.disabled=true;
      geoBtn.textContent='⏳ Aniqlanmoqda…';
      const w=tgApp();
      try{
        const lm=w&&w.LocationManager;
        if(lm&&typeof lm.getLocation==='function'){
          if(typeof lm.init==='function') lm.init();
          let done=false;
          const finish=loc=>{
            if(done) return; done=true;
            if(loc&&(loc.latitude!=null||loc.lat!=null)){
              applyGeo(Number(loc.latitude??loc.lat),Number(loc.longitude??loc.lng??loc.lon));
            }else viaNavigator();
          };
          lm.getLocation(finish);
          setTimeout(()=>{if(!done) viaNavigator();},2500);
          return;
        }
      }catch(e){}
      viaNavigator();
    });
    root.querySelector('#btnMapPick')?.addEventListener('click',()=>{
      syncFormFields();
      state.draft=state.draft||{};
      state.draft.mapOpen=true;
      if(state.draft.lat==null){
        state.draft.mapLat=state.draft.mapLat||41.3111;
        state.draft.mapLng=state.draft.mapLng||69.2797;
      }
      render();
      setTimeout(()=>{
        const map=document.getElementById('geoMap');
        if(map) map.scrollIntoView({behavior:'smooth',block:'nearest'});
      },80);
    });
    root.querySelector('#btnGeoClear')?.addEventListener('click',()=>{
      syncFormFields();
      state.draft=state.draft||{};
      state.draft.lat=null;state.draft.lng=null;
      state.draft.mapOpen=false;
      if(/^Geo:\s*/i.test(state.draft.address||'')) state.draft.address='';
      render();
    });
    root.querySelectorAll('[data-phadd]').forEach(btn=>{
      btn.addEventListener('click',()=>{
        const pid=btn.dataset.phadd;
        const inp=root.querySelector(`.fPhotosItem[data-pid="${pid}"]`);
        inp?.click();
      });
    });
    root.querySelectorAll('.fPhotosItem').forEach(inp=>{
      inp.addEventListener('change',()=>{
        syncFormFields();
        addItemPhotos(inp.dataset.pid, inp.files);
        inp.value='';
        render();
      });
    });
    root.querySelectorAll('[data-phview]').forEach(el=>{
      el.addEventListener('click',e=>{
        if(e.target.closest('[data-phrm]')) return;
        const pid=el.dataset.pid;
        const p=(state.picked||[]).find(x=>Number(x.id)===Number(pid));
        const ph=(p?.photos||[]).find(x=>String(x.key)===String(el.dataset.phview));
        if(!ph) return;
        state.photoView=ph;
        render();
      });
    });
    root.querySelectorAll('[data-phrm]').forEach(btn=>{
      btn.addEventListener('click',e=>{
        e.stopPropagation();
        syncFormFields();
        removeItemPhoto(btn.dataset.pid, btn.dataset.phrm);
        render();
      });
    });
    form.onsubmit=async e=>{
      e.preventDefault();
      if(state.formSending) return;
      syncFormFields();
      const chosen=(state.picked||[]).filter(p=>Math.round(Number(p.brakQty)||0)>0);
      if(!chosen.length){alert('Kamida bitta mahsulot uchun brak miqdorini belgilang (+)');return;}
      for(const p of chosen){
        if(!(p.reason||'').trim()){alert(`«${p.title}» uchun sabab tanlang`);openProduct(p.id);render();return;}
        if((p.desc||'').trim().length<10){alert(`«${p.title}» muammosi kamida 10 belgi`);openProduct(p.id);render();return;}
      }
      const fd=new FormData();
      chosen.forEach((p,i)=>{
        fd.append(`items[${i}][activation_id]`, String(p.id));
        fd.append(`items[${i}][qty]`, String(Math.round(Number(p.brakQty)||0)));
        fd.append(`items[${i}][reason]`, p.reason||'');
        fd.append(`items[${i}][description]`, (p.desc||'').trim());
        (p.photos||[]).forEach(ph=>{
          if(ph.file) fd.append(`items[${i}][photos][]`, ph.file, ph.file.name||'photo.jpg');
        });
      });
      const d=state.draft||{};
      fd.append('address', d.address||'');
      if(d.lat!=null&&d.lng!=null){
        fd.append('address_lat', String(d.lat));
        fd.append('address_lng', String(d.lng));
      }
      const btn=root.querySelector('#btnSubmit');
      state.formSending=true;
      if(btn){btn.disabled=true;btn.textContent='…';}
      try{
        const r=await apiForm('/miniapp/api/claims',fd);
        if(r.claim){
          const idx=b.claims.findIndex(x=>x.id===r.claim.id);
          if(idx>=0) b.claims[idx]=r.claim; else b.claims.unshift(r.claim);
        }
        state.formOk=r.claim?.number||'OK';
        clearAllItemPhotos();
        state.picked=[];state.draft={address:'',lat:null,lng:null};
        state.formStep='cheks';state.formChek=null;state.openId=null;
        if(tg&&tg.HapticFeedback) tg.HapticFeedback.notificationOccurred('success');
        render();
      }catch(err){
        alert(err.message||'Yuborilmadi');
        if(btn){btn.disabled=false;btn.textContent='Yuborish';}
      }finally{state.formSending=false;}
    };
  }
  root.querySelectorAll('[data-phclose]').forEach(el=>{
    el.addEventListener('click',e=>{e.stopPropagation();state.photoView=null;render();});
  });

  // Sputnik xarita — bosib / sudrab joy belgilash
  const mapEl=root.querySelector('#geoMap');
  if(mapEl&&window.L){
    try{
      const lat=Number(mapEl.dataset.lat),lng=Number(mapEl.dataset.lng);
      const picked=mapEl.dataset.picked==='1';
      const map=L.map(mapEl,{zoomControl:true,attributionControl:true,tapTolerance:20}).setView([lat,lng],picked?18:14);
      L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',{
        maxZoom:19,
        attribution:'Esri · sputnik'
      }).addTo(map);
      const marker=L.marker([lat,lng],{draggable:true,autoPan:true}).addTo(map);
      const setPin=(la,ln,zoomIn)=>{
        marker.setLatLng([la,ln]);
        if(zoomIn) map.setView([la,ln],Math.max(map.getZoom(),17));
        state.draft=state.draft||{};
        state.draft.lat=la;state.draft.lng=ln;
        state.draft.mapOpen=true;
        state.draft.mapLat=la;state.draft.mapLng=ln;
        if(!(state.draft.address||'').trim()||/^Geo:\s*/i.test(state.draft.address||'')){
          state.draft.address=`Geo: ${la.toFixed(5)}, ${ln.toFixed(5)}`;
          const addrInp=root.querySelector('#fAddress');
          if(addrInp) addrInp.value=state.draft.address;
        }
        const coordsEl=root.querySelector('#geoCoords');
        if(coordsEl) coordsEl.textContent=`📍 ${la.toFixed(5)}, ${ln.toFixed(5)} · tanlangan`;
        const gBtn=root.querySelector('#btnGeo');
        if(gBtn){gBtn.classList.add('ok');gBtn.textContent='📍 GPS yangilash';}
        mapEl.dataset.picked='1';
        if(tg&&tg.HapticFeedback) tg.HapticFeedback.selectionChanged();
      };
      map.on('click',e=>setPin(e.latlng.lat,e.latlng.lng,true));
      marker.on('dragend',()=>{
        const p=marker.getLatLng();
        setPin(p.lat,p.lng,false);
      });
      // GPS kelganda marker yangilanishi uchun
      mapEl._setPin=setPin;
      setTimeout(()=>map.invalidateSize(),120);
    }catch(e){}
  }
}

(async()=>{
  try{
    // initData biroz kechikishi mumkin — kutamiz; bo‘lmasa URL dagi t (bot tokeni) ishlaydi
    for(let i=0;i<8;i++){
      const w=tgApp();
      if((w&&w.initData)||AUTH_T) break;
      await new Promise(r=>setTimeout(r,80));
    }
    if(!(tgApp()&&tgApp().initData)&&!AUTH_T){
      state.err='Botdagi «Mening uyim» yoki «Shikoyat» orqali oching.';
      render();return;
    }
    state.boot=await api('/miniapp/api/boot');
    render();
  }catch(e){state.err=e.message;render();}
})();
</script>
</body>
</html>
