<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no,viewport-fit=cover">
<title>Kuryer</title>
<script src="https://telegram.org/js/telegram-web-app.js"></script>
<style>
:root{
  --bg:var(--tg-theme-bg-color,#f4f6fa);
  --surface:var(--tg-theme-secondary-bg-color,#fff);
  --ink:var(--tg-theme-text-color,#12151c);
  --ink-2:var(--tg-theme-hint-color,#8b93a7);
  --line:rgba(127,127,127,.22);
  --accent:var(--tg-theme-button-color,#2f6fed);
  --accent-ink:var(--tg-theme-button-text-color,#fff);
  --good:#1a9a55;--warn:#c47f00;--warn-bg:#fff4de;--info-bg:#e8f0fc;
  --pad-top:0px;--pad-bot:0px;--vh:100dvh;
}
*{box-sizing:border-box}
html,body{height:var(--vh);margin:0;overflow:hidden;font:14px/1.45 system-ui,-apple-system,Segoe UI,sans-serif;background:var(--bg);color:var(--ink)}
.app{height:var(--vh);max-height:var(--vh);display:flex;flex-direction:column;background:var(--bg);overflow:hidden;padding-top:var(--pad-top);padding-bottom:var(--pad-bot)}
.bar{flex:0 0 auto;display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 14px;background:var(--surface);border-bottom:1px solid var(--line)}
.bar .title{font-size:15px;font-weight:650;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.bar .link{border:0;background:none;color:var(--accent);font:inherit;font-size:14px;font-weight:600;padding:0;cursor:pointer;flex:none}
.body{flex:1 1 auto;min-height:0;overflow-y:auto;-webkit-overflow-scrolling:touch;padding:12px 14px 16px}
.foot{flex:0 0 auto;padding:10px 14px;background:var(--surface);border-top:1px solid var(--line)}
.btn{display:block;width:100%;border:1px solid var(--line);background:var(--surface);border-radius:12px;padding:13px;font:inherit;font-size:15px;font-weight:600;cursor:pointer;color:var(--ink);text-align:center}
.btn.primary{background:var(--accent);border-color:var(--accent);color:var(--accent-ink)}
.btn.good{background:var(--good);border-color:var(--good);color:#fff}
.btn:disabled{opacity:.45}.big2{display:grid;grid-template-columns:1fr 1.2fr;gap:8px}
.pill{display:inline-flex;align-items:center;font-size:11px;font-weight:600;padding:3px 8px;border-radius:999px;background:var(--surface);border:1px solid var(--line);color:var(--ink-2)}
.head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px}
.head b{font-size:16px;font-weight:650}
.meta{font-size:12.5px;color:var(--ink-2);margin:0 0 12px;line-height:1.4}
.card{background:var(--surface);border:1px solid var(--line);border-radius:14px;padding:12px 14px;margin-bottom:10px}
.card h4{margin:0 0 8px;font-size:11px;letter-spacing:.05em;text-transform:uppercase;color:var(--ink-2);font-weight:600}
.row{display:grid;grid-template-columns:76px 1fr;gap:8px;padding:7px 0;border-bottom:1px solid var(--line);font-size:13.5px;align-items:start}
.row:last-child{border-bottom:0}
.row span{color:var(--ink-2)}
.row b{font-weight:600;word-break:break-word}
.row a{color:var(--accent);font-weight:600;text-decoration:none}
.cmap{margin-top:10px;border-radius:12px;overflow:hidden;border:1px solid var(--line);background:#1a1f28;height:180px;position:relative}
.cmap iframe{position:absolute;inset:0;width:100%;height:100%;border:0}
.cmap-link{display:block;margin-top:8px;text-align:center;font-size:13px;font-weight:600;color:var(--accent);text-decoration:none}
.plist{margin:0;padding:0;list-style:none}
.plist li{display:flex;justify-content:space-between;gap:10px;padding:8px 0;border-bottom:1px solid var(--line);font-size:13.5px}
.plist li:last-child{border-bottom:0}
.plist .n{font-weight:600;min-width:0;line-height:1.35}
.plist .q{flex:none;color:var(--ink-2)}
.titem{background:var(--surface);border:1px solid var(--line);border-radius:12px;padding:12px;margin-bottom:8px;cursor:pointer}
.titem .r{display:flex;justify-content:space-between;gap:8px;align-items:center}
.titem small{display:block;color:var(--ink-2);font-size:12px;margin-top:4px}
.empty,.err{padding:32px 16px;text-align:center;color:var(--ink-2)}.err{color:#d64545}
.alert{border-radius:10px;padding:10px 12px;font-size:13px;margin:0 0 10px;background:var(--info-bg);color:var(--ink)}
.alert.warn{background:var(--warn-bg);color:var(--warn)}
.cd-prev{width:100%;max-height:120px;object-fit:cover;border-radius:10px;display:block;margin-bottom:8px}
</style>
</head>
<body>
<div class="app" id="app"><div class="body empty">Yuklanmoqda…</div></div>
<script>
const STATUS=@json($statuses);
const qs=new URLSearchParams(location.search);
function tgApp(){return (window.Telegram&&Telegram.WebApp)||null;}
function applyChrome(w){
  const r=document.documentElement;
  // Fullscreen yoʼq — Telegram header ostida oddiy expand
  const top=(w&&w.contentSafeAreaInset&&w.contentSafeAreaInset.top)||0;
  const bot=(w&&w.safeAreaInset&&w.safeAreaInset.bottom)||0;
  r.style.setProperty('--pad-top',top+'px');
  r.style.setProperty('--pad-bot',bot+'px');
  const h=(w&&(w.viewportStableHeight||w.viewportHeight))||window.innerHeight;
  r.style.setProperty('--vh',h+'px');
}
function bootShell(){
  const w=tgApp();if(!w) return null;
  try{
    w.ready();
    w.expand();
    // requestFullscreen oʼchirish — header ustiga chiqib ketardi
    if(w.disableVerticalSwipes) w.disableVerticalSwipes();
    if(w.setHeaderColor) w.setHeaderColor('secondary_bg_color');
    if(w.setBackgroundColor) w.setBackgroundColor(w.themeParams?.bg_color||'#f4f6fa');
    applyChrome(w);
    w.onEvent?.('viewportChanged',()=>applyChrome(w));
    w.onEvent?.('safeAreaChanged',()=>applyChrome(w));
    w.onEvent?.('contentSafeAreaChanged',()=>applyChrome(w));
    if(w.BackButton){
      w.BackButton.hide();
      w.BackButton.onClick(()=>{state.sel=null;state.boot&&(state.boot.claim=null);render();});
    }
  }catch(e){}
  return w;
}
const tg=bootShell();
const state={boot:null,err:null,sel:null,claimId:Number(qs.get('claim')||0)||null};
const $=s=>document.querySelector(s);
const pill=(st,l)=>`<span class="pill">${l||STATUS[st]||st}</span>`;
const fdt=s=>{if(!s)return '—';const d=new Date(s);return isNaN(d)?s:d.toLocaleString('uz-UZ',{day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'});};
const esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
function startParam(){
  const w=tgApp();
  const sp=(w&&w.initDataUnsafe&&w.initDataUnsafe.start_param)||qs.get('startapp')||'';
  const m=String(sp).match(/^c(\d+)$/i);
  return m?Number(m[1]):null;
}
async function api(path,body){
  const w=tgApp();
  const r=await fetch(path,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({initData:(w&&w.initData)||'',start_param:(w&&w.initDataUnsafe&&w.initDataUnsafe.start_param)||'',...(body||{})})});
  const d=await r.json().catch(()=>({}));
  if(!r.ok) throw new Error(d.message||'Xato');
  return d;
}
async function apiForm(path,fd){
  const w=tgApp();
  fd.append('initData',(w&&w.initData)||'');
  const r=await fetch(path,{method:'POST',headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:fd});
  const d=await r.json().catch(()=>({}));
  if(!r.ok) throw new Error(d.message||'Xato');
  return d;
}
function currentClaim(){
  const b=state.boot;if(!b) return null;
  if(state.sel) return (b.claims||[]).find(x=>x.id===state.sel)||b.claim;
  return b.claim||null;
}
function syncBack(show){
  const w=tgApp();
  if(!w||!w.BackButton) return;
  if(show) w.BackButton.show(); else w.BackButton.hide();
}
function render(){
  const root=$('#app');
  if(state.err){root.innerHTML=`<div class="body err"><b>Kirish mumkin emas</b><p>${esc(state.err)}</p></div>`;return;}
  const b=state.boot;if(!b){root.innerHTML=`<div class="body empty">Yuklanmoqda…</div>`;return;}
  const c=currentClaim();
  const first=(b.courier?.name||'Kuryer').split(/\s+/)[0];
  let body='',foot='',barRight='';
  if(c){
    syncBack(true);
    const tel=c.ph?`<a href="tel:${String(c.ph).replace(/[^\d+]/g,'')}">${esc(c.ph)}</a>`:'—';
    const addr=(c.address&&String(c.address).trim())||'';
    const hasGeo=c.address_lat!=null&&c.address_lng!=null&&!isNaN(Number(c.address_lat))&&!isNaN(Number(c.address_lng));
    const lat=hasGeo?Number(c.address_lat):null;
    const lng=hasGeo?Number(c.address_lng):null;
    const mapsUrl=hasGeo?`https://maps.google.com/?q=${lat},${lng}`:'';
    const mapEmbed=hasGeo?`https://maps.google.com/maps?q=${lat},${lng}&ll=${lat},${lng}&z=17&t=k&hl=uz&output=embed`:'';
    const mapBlock=hasGeo
      ?`<div class="cmap"><iframe title="Manzil" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="${mapEmbed}" allowfullscreen></iframe></div>
         <a class="cmap-link" href="${mapsUrl}" target="_blank" rel="noopener">📍 Xaritada ochish · ${lat.toFixed(5)}, ${lng.toFixed(5)}</a>`
      :'';
    body=`<div class="head"><b>${esc(c.number)}</b>${pill(c.status,STATUS[c.status])}</div>
      ${c.deadline?`<p class="meta">Muddat: ${fdt(c.deadline)}</p>`:''}
      <div class="card"><h4>Mijoz</h4>
        <div class="row"><span>Ism</span><b>${esc(c.cust)}</b></div>
        <div class="row"><span>Telefon</span><b>${tel}</b></div>
        <div class="row"><span>Manzil</span><b>${esc(addr||'—')}${hasGeo&&!addr?`📍 ${lat.toFixed(5)}, ${lng.toFixed(5)}`:''}</b></div>
        ${mapBlock}
      </div>
      <div class="card"><h4>Mahsulotlar</h4>
        <ul class="plist">${(c.products||[]).map(p=>`<li><span class="n">${esc(p.name)}</span><span class="q">×${p.qty||1}</span></li>`).join('')||'<li>—</li>'}</ul>
        <div class="row" style="margin-top:4px"><span>Olish</span><b>${esc(c.pickup)}</b></div>
        ${c.reason?`<div class="row"><span>Sabab</span><b>${esc(c.reason)}</b></div>`:''}
      </div>`;
    if(c.status==='assigned'){
      foot=`<button class="btn primary" type="button" data-act="en_route">Yoʼldaman</button>`;
    }else if(c.status==='en_route'){
      foot=`${c.delivery_photo?`<img class="cd-prev" src="${c.delivery_photo}" alt="">`:'<div class="alert warn" id="cPhotoNeed">Yetkazish fotosi kerak</div>'}
        <div id="cPhotoPrev"></div>
        <div class="big2"><label class="btn" style="cursor:pointer;margin:0">Foto<input type="file" accept="image/*" capture="environment" id="cPhoto" hidden></label>
        <button class="btn good" type="button" data-act="deliver" id="cDeliver" ${c.delivery_photo?'':'disabled'}>Yetkazdim</button></div>`;
    }else if(c.status==='delivered'){
      foot=`<div class="alert warn" style="margin:0">Mijoz tasdigʼi kutilmoqda</div>`;
    }
    barRight=`<button type="button" class="link" data-back="1">Roʼyxat</button>`;
  }else{
    syncBack(false);
    const list=b.claims||[];
    body=list.length?list.map(x=>`<div class="titem" data-sel="${x.id}"><div class="r"><b>${esc(x.number)}</b>${pill(x.status,STATUS[x.status])}</div><small>${esc(x.cust)} · ${esc((x.address||'').split(',')[0]||x.pickup||'')}</small></div>`).join(''):'<div class="empty">Ochiq topshiriq yoʼq</div>';
  }
  root.innerHTML=`<div class="bar"><div class="title">${c?esc(c.number):('Salom, '+esc(first))}</div>${barRight}</div>
    <div class="body">${body}</div>
    ${foot?`<div class="foot">${foot}</div>`:''}`;
  root.querySelector('[data-back]')?.addEventListener('click',()=>{state.sel=null;state.boot.claim=null;render();});
  root.querySelectorAll('[data-sel]').forEach(el=>el.onclick=()=>{state.sel=Number(el.dataset.sel);render();});
  const photoInp=root.querySelector('#cPhoto');
  const deliverBtn=root.querySelector('#cDeliver');
  const photoPrev=root.querySelector('#cPhotoPrev');
  const photoNeed=root.querySelector('#cPhotoNeed');
  if(photoInp){
    photoInp.onchange=()=>{
      const f=photoInp.files&&photoInp.files[0];
      if(deliverBtn) deliverBtn.disabled=!f&&!(c&&c.delivery_photo);
      if(photoNeed) photoNeed.hidden=!!(f||(c&&c.delivery_photo));
      if(photoPrev) photoPrev.innerHTML=f?`<img class="cd-prev" src="${URL.createObjectURL(f)}" alt="">`:'';
    };
  }
  root.querySelectorAll('[data-act]').forEach(btn=>btn.onclick=async()=>{
    const action=btn.dataset.act;
    const claim=currentClaim();if(!claim) return;
    const fd=new FormData();fd.append('action',action);
    if(action==='deliver'){
      const f=photoInp&&photoInp.files&&photoInp.files[0];
      if(!f&&!claim.delivery_photo){alert('Avval foto oling');return;}
      if(f) fd.append('photo',f);
    }
    btn.disabled=true;
    try{
      const r=await apiForm('/courier-app/api/claims/'+claim.id+'/action',fd);
      if(state.boot.claim&&state.boot.claim.id===r.claim.id) state.boot.claim=r.claim;
      const i=(state.boot.claims||[]).findIndex(x=>x.id===r.claim.id);
      if(i>=0) state.boot.claims[i]=r.claim;
      if(state.sel) state.sel=r.claim.id;
      if(tg&&tg.HapticFeedback) tg.HapticFeedback.notificationOccurred('success');
      render();
    }catch(e){alert(e.message);btn.disabled=false;}
  });
}
(async()=>{
  try{
    for(let i=0;i<10;i++){const w=tgApp();if(w&&w.initData)break;await new Promise(r=>setTimeout(r,80));}
    applyChrome(tgApp());
    if(!(tgApp()&&tgApp().initData)){state.err='Telegram orqali oching (guruhdagi tugma).';render();return;}
    const fromStart=startParam();
    const claimId=state.claimId||fromStart||0;
    state.boot=await api('/courier-app/api/boot',{claim_id:claimId,start_param:(tgApp().initDataUnsafe&&tgApp().initDataUnsafe.start_param)||''});
    if(claimId) state.claimId=claimId;
    if(state.boot.claim) state.sel=state.boot.claim.id;
    render();
  }catch(e){state.err=e.message;render();}
})();
</script>
</body>
</html>
