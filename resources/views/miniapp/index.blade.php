<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no,viewport-fit=cover">
<title>QR Kafolat</title>
<script src="https://telegram.org/js/telegram-web-app.js"></script>
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
.picked{display:grid;gap:6px;margin-top:8px}
.picked:empty{display:none}
.picked .prow{display:flex;align-items:center;gap:8px;padding:9px 10px;border:1px solid var(--line);border-radius:10px;background:var(--bg)}
.picked .prow b{flex:1;min-width:0;font-size:13px;font-weight:600;line-height:1.3}
.picked .prow button{border:0;background:none;color:var(--crit);font:inherit;font-size:18px;line-height:1;padding:0 4px;cursor:pointer}
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
const state={tab:['home','claims','form'].includes(_tab)?_tab:'home',sel:null,chek:null,noask:false,reason:'',boot:null,err:null,formOk:null,formSending:false,picked:[],draft:{reason:'',address:'',desc:''}};
const $=s=>document.querySelector(s);
const pill=(st,l)=>`<span class="pill ${st}"><i></i>${l||STATUS[st]||st}</span>`;
const fdt=s=>{if(!s)return '—';const d=new Date(s);return isNaN(d)?s:d.toLocaleString('uz-UZ',{day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'});};
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
function goForm(){state.tab='form';state.sel=null;state.chek=null;state.formOk=null;state.noask=false;state.picked=[];state.draft={reason:'',address:'',desc:''};render();}
function money(n){return Math.round(Number(n)||0).toLocaleString('uz-UZ')+' soʼm';}
function chekList(b){
  if(Array.isArray(b.cheks)&&b.cheks.length) return b.cheks;
  // eski boot — mahsulotlarni chek boʼyicha guruhlash
  const map=new Map();
  (b.activations||[]).forEach(a=>{
    const k=String(a.order_id||a.chek||a.id);
    if(!map.has(k)) map.set(k,{key:k,chek:a.chek,store:a.store,date:a.date,items_n:0,sum:0,items:[]});
    const g=map.get(k);
    g.items.push({id:a.id,product:a.product,qty:a.qty,warranty:a.warranty,sum:a.sum});
    g.items_n=g.items.length;g.sum+=Number(a.sum)||0;
  });
  return [...map.values()];
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
        <div class="big2" style="margin-top:14px"><button class="btn primary big" type="button" data-new="1">Brak boʼyicha soʼrov</button></div>`;
    }else{
      body=`<p class="hint" style="margin:0 0 8px">Cheklar — telefoningizga bogʼlangan (real)</p>`+
        (list.length?list.map(c=>`<div class="titem" data-chek="${c.key}"><div class="r"><b>Chek ${c.chek}</b>${pill('done','Kafolat faol')}</div><small>${c.date} · ${c.store}${c.sum?` · ${money(c.sum)}`:''}</small><small>${c.items_n} ta mahsulot · ochish →</small></div>`).join(''):'<div class="empty">Faol kafolat yoʼq — avval botda chekni tasdiqlang</div>');
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
        <p class="hint" style="margin:0">${c.prod} × ${c.qty} · ${c.reason}</p>
        ${c.status==='rejected'?`<div class="alert warn">Rad etildi: ${c.reject_reason||'—'}</div>`:''}
        ${c.status==='dispute'?`<div class="alert crit">Javobingiz: almashtirilmadi (${c.confirm_reason||'—'}). Admin bogʼlanadi.</div>`:''}
        ${c.courier&&['assigned','en_route','delivered','done','dispute'].includes(c.status)?`<div class="alert info">Kuryer: <b>${c.courier}</b>${c.courier_phone?` · ${c.courier_phone}`:''}${c.deadline&&['assigned','en_route'].includes(c.status)?` · eng kech ${fdt(c.deadline)}`:''}</div>`:''}
        ${confirm}
        <div class="psec"><h4>Holat</h4><ul class="tl">${order.filter(s=>!(s==='done'&&['rejected','dispute','unconfirmed'].includes(c.status))).map(s=>{const ev=(c.logs||[]).find(e=>e.to===s);const r=reached(s);return `<li class="${r?(c.status===s&&s!=='done'?'now':'done'):''}">${labels[s]}<small>${ev?fdt(ev.at):(r?'':'—')}</small></li>`;}).join('')}</ul></div>`;
    }else{
      body=b.claims.map(x=>`<div class="titem" data-sel="${x.id}"><div class="r"><b>${x.number}</b>${pill(x.status,STATUS[x.status])}</div><small>${x.prod} × ${x.qty} · ${fdt(x.date)}</small>${x.status==='delivered'&&!x.confirm?'<small class="amb">Tasdiqlashingiz kutilmoqda →</small>':''}</div>`).join('')||'<div class="empty">Soʼrov yoʼq</div>';
    }
  }else if(state.tab==='form'){
    if(state.formOk){
      body=`<div class="okbox"><div class="alert ok" style="margin:0">Qabul qilindi</div><b>${state.formOk}</b>
        <div class="big2"><button class="btn primary big" type="button" data-tab="claims">Soʼrovlarim</button><button class="btn big" type="button" data-newform="1">Yana</button></div></div>`;
    }else{
      const prods=b.products||[];
      const reasons=b.reasons||REASONS||[];
      const d=state.draft||{};
      body=prods.length?`<form id="claimForm">
          <div class="ffield"><span class="lbl">Mahsulot</span>
            <div class="psug">
              <input type="search" id="prodSearch" placeholder="Mahsulot nomini yozing…" autocomplete="off">
              <div class="psug-list" id="prodSug"></div>
            </div>
            <div class="picked" id="pickedList">${(state.picked||[]).map(p=>`<div class="prow" data-pid="${p.id}"><b>${p.title}</b><button type="button" data-rm="${p.id}" aria-label="olib tashlash">×</button></div>`).join('')}</div>
          </div>
          <div class="ffield"><span class="lbl">Sabab</span>
            <select class="sel" id="fReason"><option value="">Tanlang</option>${reasons.map(r=>`<option value="${r}" ${d.reason===r?'selected':''}>${r}</option>`).join('')}</select>
          </div>
          <div class="ffield"><span class="lbl">Manzil</span>
            <input class="txt" type="text" id="fAddress" maxlength="500" placeholder="Tuman, koʼcha, uy" value="${(d.address||'').replace(/"/g,'&quot;')}">
          </div>
          <div class="ffield"><span class="lbl">Muammo</span>
            <textarea id="fDesc" maxlength="2000" placeholder="Qisqa yozing…">${(d.desc||'').replace(/</g,'&lt;')}</textarea>
          </div>
          <div class="ffield"><span class="lbl">Foto</span>
            <input class="file" type="file" id="fPhotos" accept="image/*" multiple>
          </div>
        </form>`:'<div class="empty">Avval botda chekni tasdiqlang</div>';
    }
  }
  const showSend=state.tab==='form'&&!state.formOk&&(b.products||[]).length>0;
  const foot=showSend?`<div class="foot-act"><button class="btn primary big" type="submit" form="claimForm" id="btnSubmit">Yuborish</button></div>`:'';
  root.innerHTML=`<div class="bar"><b>QR Kafolat</b><span class="hint">${b.user.short}</span></div>
    <div class="body">${body}</div>
    ${foot}
    <div class="pnav">${[['home','Mening uyim'],['claims','Soʼrovlarim'],['form','Yangi soʼrov']].map(([k,l])=>`<button type="button" data-tab="${k}" aria-current="${state.tab===k?'page':'false'}"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">${icons[k]}</svg>${l}</button>`).join('')}</div>`;

  root.querySelectorAll('[data-tab]').forEach(el=>el.onclick=()=>{
    const t=el.dataset.tab;
    state.tab=t;state.sel=null;state.chek=null;state.noask=false;
    if(t==='form'){state.formOk=null;if(!state.picked)state.picked=[];}
    render();
  });
  root.querySelectorAll('[data-sel]').forEach(el=>el.onclick=()=>{state.sel=Number(el.dataset.sel);render();});
  root.querySelectorAll('[data-chek]').forEach(el=>el.onclick=()=>{state.chek=el.dataset.chek;render();});
  root.querySelector('[data-chekback]')?.addEventListener('click',()=>{state.chek=null;render();});
  root.querySelector('[data-back]')?.addEventListener('click',()=>{state.sel=null;state.noask=false;render();});
  root.querySelectorAll('[data-new]').forEach(el=>el.onclick=()=>goForm());
  root.querySelector('[data-newform]')?.addEventListener('click',()=>goForm());
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

  // Shikoyat — qidiruv + tanlanganlar
  const form=root.querySelector('#claimForm');
  if(form){
    const prods=b.products||[];
    const search=root.querySelector('#prodSearch');
    const sug=root.querySelector('#prodSug');
    const pickedEl=root.querySelector('#pickedList');
    const esc=s=>String(s||'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const paintPicked=()=>{
      if(!pickedEl) return;
      pickedEl.innerHTML=(state.picked||[]).map(p=>`<div class="prow"><b>${esc(p.title)}</b><button type="button" data-rm="${p.id}" aria-label="olib tashlash">×</button></div>`).join('');
      pickedEl.querySelectorAll('[data-rm]').forEach(btn=>btn.onclick=()=>{
        state.picked=state.picked.filter(x=>Number(x.id)!==Number(btn.dataset.rm));
        paintPicked();
        if(search) showSug(search.value);
      });
    };
    const showSug=q=>{
      if(!sug) return;
      const needle=(q||'').trim().toLowerCase();
      if(needle.length<1){sug.classList.remove('on');sug.innerHTML='';return;}
      const taken=new Set((state.picked||[]).map(p=>Number(p.id)));
      const hits=prods.filter(p=>!taken.has(Number(p.id))&&(p.search||'').includes(needle)).slice(0,12);
      if(!hits.length){sug.classList.remove('on');sug.innerHTML='';return;}
      sug.innerHTML=hits.map(p=>`<button type="button" class="psug-item" data-add="${p.id}"><b>${esc(p.title)}</b>${p.sub?`<small>${esc(p.sub)}</small>`:''}</button>`).join('');
      sug.classList.add('on');
      sug.querySelectorAll('[data-add]').forEach(btn=>btn.onclick=()=>{
        const p=prods.find(x=>Number(x.id)===Number(btn.dataset.add));
        if(!p) return;
        if(!(state.picked||[]).some(x=>Number(x.id)===Number(p.id))) state.picked=[...(state.picked||[]),p];
        if(search){search.value='';search.focus();}
        sug.classList.remove('on');sug.innerHTML='';
        paintPicked();
      });
    };
    search?.addEventListener('input',()=>showSug(search.value));
    search?.addEventListener('focus',()=>showSug(search.value));
    ['fReason','fAddress','fDesc'].forEach(id=>{
      const el=root.querySelector('#'+id);
      if(!el) return;
      el.addEventListener('input',()=>{
        state.draft={
          reason:root.querySelector('#fReason')?.value||'',
          address:root.querySelector('#fAddress')?.value||'',
          desc:root.querySelector('#fDesc')?.value||'',
        };
      });
    });
    paintPicked();
    form.onsubmit=async e=>{
      e.preventDefault();
      if(state.formSending) return;
      const ids=(state.picked||[]).map(p=>p.id);
      const reason=root.querySelector('#fReason')?.value||'';
      const desc=(root.querySelector('#fDesc')?.value||'').trim();
      if(!ids.length){alert('Mahsulot qoʼshing');return;}
      if(!reason){alert('Sabab tanlang');return;}
      if(desc.length<10){alert('Muammo kamida 10 belgi');return;}
      const fd=new FormData();
      ids.forEach(id=>fd.append('activation_ids[]',id));
      fd.append('reason',reason);
      fd.append('description',desc);
      fd.append('address',root.querySelector('#fAddress')?.value||'');
      const files=root.querySelector('#fPhotos')?.files||[];
      for(let i=0;i<Math.min(files.length,5);i++) fd.append('photos[]',files[i]);
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
        state.picked=[];state.draft={reason:'',address:'',desc:''};
        if(tg&&tg.HapticFeedback) tg.HapticFeedback.notificationOccurred('success');
        render();
      }catch(err){
        alert(err.message||'Yuborilmadi');
        if(btn){btn.disabled=false;btn.textContent='Yuborish';}
      }finally{state.formSending=false;}
    };
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
