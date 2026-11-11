<?php
// Inlined inside dashboard.php's <script> block — no <script> tags here.
$sky_cat = file_exists(__DIR__ . '/skywave_catalog.json')
    ? json_decode(file_get_contents(__DIR__ . '/skywave_catalog.json'), true) : [];
$via_cat = file_exists(__DIR__ . '/viachain_catalog.json')
    ? json_decode(file_get_contents(__DIR__ . '/viachain_catalog.json'), true) : [];
$sky_cat = is_array($sky_cat) ? $sky_cat : [];
$via_cat = is_array($via_cat) ? $via_cat : [];
$catalog_init = json_encode(['skywave' => $sky_cat, 'viachain' => $via_cat],
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
/* ─── CATALOG MODULE ─────────────────────────────────────────────────────── */
let C = <?= $catalog_init ?>;
let catState = { brand: 'skywave', subTab: 'products', lang: 'es', editId: null, editType: null };

/* ── helpers ── */
function catTL(val, l) {
  if (!val) return '';
  if (typeof val === 'string') return val;
  return val[l] || val.es || val.en || val.pt || '';
}
function catSlug(s) { return s.toLowerCase().replace(/[^a-z0-9]/g, '_').replace(/_+/g,'_').replace(/^_|_$/g,''); }
function catV(id) { const e = document.getElementById(id); return e ? e.value.trim() : ''; }
function catVN(id) { const e = document.getElementById(id); return e ? Number(e.value) : 0; }
function catChk(id) { const e = document.getElementById(id); return e ? e.checked : false; }
function catArr(id) { return catV(id).split('\n').map(s=>s.trim()).filter(Boolean); }
function catTriLingual(prefix) {
  const es = catV(prefix+'-es'), en = catV(prefix+'-en'), pt = catV(prefix+'-pt');
  if (!es && !en && !pt) return '';
  if (es === en && en === pt) return es;
  return {es, en, pt};
}

/* ── render tab ── */
function renderCatalog() {
  const brandLabel = catState.brand === 'skywave' ? 'Skywave' : 'VIACHAIN';
  const prods    = (C[catState.brand].products || []).length;
  const allAccs  = C[catState.brand].accessories || [];
  const ownAccs  = allAccs.filter(a => !a.thirdParty).length;
  const thirdAccs = allAccs.filter(a => a.thirdParty).length;
  const accsLabel = thirdAccs > 0 ? `${ownAccs}+${thirdAccs}3P` : `${ownAccs}`;
  return `
<style>
.cat-brand-btn{padding:7px 20px;border-radius:20px;border:1px solid #D1D5DB;background:#fff;
  font-size:12px;font-weight:700;cursor:pointer;letter-spacing:.5px;transition:all .15s}
.cat-brand-btn.active{background:#0D0A07;color:#fff;border-color:#0D0A07}
.cat-stab{padding:8px 18px;border:none;background:none;cursor:pointer;font-size:12px;font-weight:600;
  color:#666;border-bottom:2px solid transparent;margin-bottom:-2px;transition:all .15s}
.cat-stab.active{color:#BA0C2F;border-bottom-color:#BA0C2F}
.cat-stabs{display:flex;gap:2px;border-bottom:2px solid #E5E7EB;margin-bottom:20px;padding-bottom:0}
.cat-pane{display:none}.cat-pane.active{display:block}
.order-btn{padding:2px 7px;border:1px solid #D1D5DB;border-radius:4px;background:#fff;
  font-size:11px;cursor:pointer;color:#555}
.order-btn:hover{background:#F3F4F6}
.cp-lang-tabs{display:flex;gap:4px;margin-bottom:10px}
.cp-lang-tab{padding:4px 12px;border-radius:14px;border:1px solid #D1D5DB;background:#fff;
  font-size:11px;font-weight:700;cursor:pointer;transition:all .15s}
.cp-lang-tab.active{background:#BA0C2F;color:#fff;border-color:#BA0C2F}
.hs-box{background:#F9FAFB;border-radius:8px;padding:14px;margin-bottom:10px}
.hs-box-title{font-size:10px;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px}
.cat-subtab-bar{display:flex;gap:4px;margin-bottom:16px}
.cat-subtab{padding:6px 14px;border-radius:6px;border:1px solid #D1D5DB;background:#fff;
  font-size:12px;font-weight:600;cursor:pointer;color:#555;transition:all .15s}
.cat-subtab.active{background:#BA0C2F;color:#fff;border-color:#BA0C2F}
.cat-fp{display:none}.cat-fp.active{display:block}
.docs-table td{vertical-align:top;font-size:12px;padding:10px 14px}
.docs-table td:first-child{font-weight:600;white-space:nowrap}
.doc-path{color:#6B7280;font-size:11px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
</style>
<div class="sec-hdr" style="flex-wrap:wrap;gap:10px">
  <h2>Catálogo de Productos</h2>
  <div style="display:flex;gap:8px">
    <button class="cat-brand-btn ${catState.brand==='skywave'?'active':''}" onclick="catSetBrand('skywave')">Skywave</button>
    <button class="cat-brand-btn ${catState.brand==='viachain'?'active':''}" onclick="catSetBrand('viachain')">VIACHAIN</button>
  </div>
</div>
<div class="cat-stabs">
  <button class="cat-stab ${catState.subTab==='products'?'active':''}" onclick="catSubTab('products')">Productos (${prods})</button>
  <button class="cat-stab ${catState.subTab==='accessories'?'active':''}" onclick="catSubTab('accessories')">Accesorios (${accsLabel})</button>
  <button class="cat-stab ${catState.subTab==='docs'?'active':''}" onclick="catSubTab('docs')">Documentos</button>
  <button class="cat-stab ${catState.subTab==='content'?'active':''}" onclick="catSubTab('content')">Hero / Cierre</button>
</div>
<div id="catPane-products" class="cat-pane ${catState.subTab==='products'?'active':''}">${catRenderProducts()}</div>
<div id="catPane-accessories" class="cat-pane ${catState.subTab==='accessories'?'active':''}">${catRenderAccessories()}</div>
<div id="catPane-docs" class="cat-pane ${catState.subTab==='docs'?'active':''}">${catRenderDocs()}</div>
<div id="catPane-content" class="cat-pane ${catState.subTab==='content'?'active':''}">${catRenderContent()}</div>`;
}

function catSetBrand(b) {
  catState.brand = b;
  renderTabContent();
}
function catSubTab(t) {
  catState.subTab = t;
  document.querySelectorAll('.cat-stab').forEach((b) => b.classList.toggle('active', b.getAttribute('onclick') === `catSubTab('${t}')`));
  document.querySelectorAll('.cat-pane').forEach(p => p.classList.toggle('active', p.id === 'catPane-' + t));
}

function catRenderContent() {
  const c = C[catState.brand] || {};
  const hero = c.hero || {};
  const closing = c.closing || {};
  const brandLabel = catState.brand === 'skywave' ? 'Skywave' : 'VIACHAIN';
  const input = (id, label, val) => `<div class="form-row"><label>${label}</label><input type="text" id="${id}" value="${esc(val || '')}"></div>`;
  const text = (id, label, val) => `<div class="form-row"><label>${label}</label><textarea id="${id}" style="min-height:72px">${esc(val || '')}</textarea></div>`;
  const langPanels = ['es','en','pt'].map(l => `
    <div class="cat-fp ${l==='es'?'active':''}" data-cpf="content" data-cpl="${l}">
      <div class="form-2col">
        ${input(`cc-hero-eyebrow-${l}`, `Hero eyebrow (${l.toUpperCase()})`, catTL(hero.eyebrow, l))}
        ${input(`cc-hero-scroll-${l}`, `CTA hero (${l.toUpperCase()})`, catTL(hero.scroll, l))}
      </div>
      ${text(`cc-hero-title-${l}`, `Título hero (${l.toUpperCase()}) · una línea por renglón`, Array.isArray(hero.title && hero.title[l]) ? hero.title[l].join('\n') : catTL(hero.title, l))}
      ${text(`cc-hero-sub-${l}`, `Subtítulo hero (${l.toUpperCase()})`, catTL(hero.sub, l))}
      <div style="height:1px;background:#E5E7EB;margin:18px 0"></div>
      ${input(`cc-closing-title-${l}`, `Título cierre (${l.toUpperCase()})`, catTL(closing.title, l))}
      ${text(`cc-closing-sub-${l}`, `Texto cierre (${l.toUpperCase()})`, catTL(closing.sub, l))}
      <div class="form-2col">
        ${input(`cc-closing-cta-${l}`, `CTA cierre (${l.toUpperCase()})`, catTL(closing.cta, l))}
        ${input(`cc-closing-contact-${l}`, `Contacto cierre (${l.toUpperCase()})`, catTL(closing.contact, l))}
      </div>
    </div>
  `).join('');

  return `<div class="tbl-wrap" style="padding:22px">
    <div class="sec-hdr" style="margin-bottom:8px">
      <div>
        <h2 style="font-size:14px">Hero y cierre · ${brandLabel}</h2>
        <p style="font-size:12px;color:#666;margin-top:4px">Edita solo los textos de primera impresión y cierre comercial del portafolio.</p>
      </div>
      <button class="btn-save" onclick="catSaveContent()">Guardar contenido</button>
    </div>
    <div class="cp-lang-tabs" style="margin-top:16px">
      <button class="cp-lang-tab active" onclick="catFormLang('es')">ES</button>
      <button class="cp-lang-tab" onclick="catFormLang('en')">EN</button>
      <button class="cp-lang-tab" onclick="catFormLang('pt')">PT</button>
    </div>
    <div id="form-error" class="error-msg" style="display:none"></div>
    ${langPanels}
  </div>`;
}

function catContentObj(prefix) {
  return {
    es: catV(prefix + '-es'),
    en: catV(prefix + '-en'),
    pt: catV(prefix + '-pt')
  };
}

function catContentTitle(prefix) {
  const obj = {};
  ['es','en','pt'].forEach(l => {
    obj[l] = catV(prefix + '-' + l).split('\n').map(s => s.trim()).filter(Boolean);
  });
  return obj;
}

async function catSaveContent() {
  const hero = {
    eyebrow: catContentObj('cc-hero-eyebrow'),
    title: catContentTitle('cc-hero-title'),
    sub: catContentObj('cc-hero-sub'),
    scroll: catContentObj('cc-hero-scroll')
  };
  const closing = {
    title: catContentObj('cc-closing-title'),
    sub: catContentObj('cc-closing-sub'),
    cta: catContentObj('cc-closing-cta'),
    contact: catContentObj('cc-closing-contact')
  };

  ['en','pt'].forEach((l) => {
    if (!hero.eyebrow[l]) hero.eyebrow[l] = hero.eyebrow.es;
    if (!hero.title[l].length) hero.title[l] = hero.title.es;
    if (!hero.sub[l]) hero.sub[l] = hero.sub.es;
    if (!hero.scroll[l]) hero.scroll[l] = hero.scroll.es;
    if (!closing.title[l]) closing.title[l] = closing.title.es;
    if (!closing.sub[l]) closing.sub[l] = closing.sub.es;
    if (!closing.cta[l]) closing.cta[l] = closing.cta.es;
    if (!closing.contact[l]) closing.contact[l] = closing.contact.es;
  });

  if (!hero.title.es.length || !hero.sub.es || !closing.title.es) {
    const el = document.getElementById('form-error');
    el.textContent = 'Completa al menos título hero, subtítulo hero y título de cierre en español.';
    el.style.display = 'block';
    catFormLang('es');
    return;
  }

  const errEl = document.getElementById('form-error');
  errEl.style.display = 'none';

  try {
    const r = await fetch('catalog_api.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ action: 'save_content', brand: catState.brand, hero, closing, csrf: CSRF })
    });
    const raw = await r.text();
    let j;
    try {
      j = JSON.parse(raw);
    } catch (parseErr) {
      throw new Error(raw.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 260) || parseErr.message);
    }
    if (j.ok) {
      C[j.brand] = j.catalog;
      renderTabContent();
    } else {
      errEl.textContent = j.error || 'No se pudo guardar';
      errEl.style.display = 'block';
    }
  } catch (e) {
    errEl.textContent = 'Error de red: ' + e.message;
    errEl.style.display = 'block';
  }
}

/* ── products list ── */
function catRenderProducts() {
  const items = C[catState.brand].products || [];
  const rows = items.length ? items.map((p, i) => `
    <tr>
      <td style="width:56px">${p.img ? `<img class="thumb" src="${esc(p.img)}" onerror="this.style.display='none'">` : '<div class="thumb-ph">—</div>'}</td>
      <td><strong>${esc(p.name)}</strong><br><span style="color:#888;font-size:11px">${esc(p.id)}</span></td>
      <td style="font-size:12px">${esc(catTL(p.category,'es'))}</td>
      <td style="font-size:12px;color:#888">${esc(catTL(p.role,'es')).slice(0,60)}${catTL(p.role,'es').length>60?'…':''}</td>
      <td><div class="row-actions">
        ${i>0?`<button class="order-btn" title="Subir" onclick="catMoveProduct('${esc(p.id)}','up')">↑</button>`:'<span style="width:28px;display:inline-block"></span>'}
        ${i<items.length-1?`<button class="order-btn" title="Bajar" onclick="catMoveProduct('${esc(p.id)}','down')">↓</button>`:'<span style="width:28px;display:inline-block"></span>'}
        <button class="btn-edit" onclick="catOpenProduct('${esc(p.id)}')">Editar</button>
        <button class="btn-del" onclick="catConfirmDelete('product','${esc(p.id)}','${esc(p.name)}')">Eliminar</button>
      </div></td>
    </tr>`).join('') : '<tr><td colspan="5"><div class="empty">Sin productos. Agrega el primero.</div></td></tr>';
  return `<div class="sec-hdr"><h2 style="font-size:14px">Productos ${catState.brand==='skywave'?'Skywave':'VIACHAIN'}</h2>
    <button class="btn-add" onclick="catOpenProduct(null)">+ Agregar producto</button></div>
    <div class="tbl-wrap"><table>
      <thead><tr><th>Imagen</th><th>Nombre / ID</th><th>Categoría</th><th>Rol</th><th>Acciones</th></tr></thead>
      <tbody>${rows}</tbody></table></div>`;
}

/* ── accessories list ── */
function catRenderAccessories() {
  const items = C[catState.brand].accessories || [];
  const rows = items.length ? items.map((a, i) => `
    <tr>
      <td style="width:56px">${a.img ? `<img class="thumb" src="${esc(a.img)}" onerror="this.style.display='none'">` : '<div class="thumb-ph">—</div>'}</td>
      <td><strong>${esc(a.name)}</strong>${a.thirdParty ? ' <span style="font-size:9px;padding:2px 6px;background:#FEF3C7;color:#92400E;border-radius:4px;font-family:monospace;letter-spacing:.4px;vertical-align:middle">3P</span>' : ''}<br><span style="color:#888;font-size:11px">${esc(a.id)}</span></td>
      <td style="font-size:12px">${esc(catTL(a.category,'es'))}</td>
      <td style="font-size:12px;color:#888">${(a.compatible||[]).join(', ')}</td>
      <td><div class="row-actions">
        ${i>0?`<button class="order-btn" onclick="catMoveAccessory('${esc(a.id)}','up')">↑</button>`:'<span style="width:28px;display:inline-block"></span>'}
        ${i<items.length-1?`<button class="order-btn" onclick="catMoveAccessory('${esc(a.id)}','down')">↓</button>`:'<span style="width:28px;display:inline-block"></span>'}
        <button class="btn-edit" onclick="catOpenAccessory('${esc(a.id)}')">Editar</button>
        <button class="btn-del" onclick="catConfirmDelete('accessory','${esc(a.id)}','${esc(a.name)}')">Eliminar</button>
      </div></td>
    </tr>`).join('') : '<tr><td colspan="5"><div class="empty">Sin accesorios. Agrega el primero.</div></td></tr>';
  return `<div class="sec-hdr"><h2 style="font-size:14px">Accesorios</h2>
    <button class="btn-add" onclick="catOpenAccessory(null)">+ Agregar accesorio</button></div>
    <div class="tbl-wrap"><table>
      <thead><tr><th>Imagen</th><th>Nombre / ID</th><th>Categoría</th><th>Compatible con</th><th>Acciones</th></tr></thead>
      <tbody>${rows}</tbody></table></div>`;
}

/* ── docs table ── */
function catRenderDocs() {
  const prods = C[catState.brand].products || [];
  const accessories = C[catState.brand].accessories || [];
  const ds    = C[catState.brand].datasheets    || {};
  const hw    = C[catState.brand].hardwareGuides || {};
  const ig    = C[catState.brand].installGuides  || {};
  const rows = prods.map(p => {
    const dsVal  = ds[p.id];
    const dsStr  = !dsVal ? '—' : Array.isArray(dsVal) ? dsVal.map(d=>d.label||d.en).join(', ') : (dsVal.en||dsVal.es||'—');
    const hwStr  = hw[p.id] ? (hw[p.id].en||'—') : '—';
    const igStr  = ig[p.id] ? (ig[p.id].en||'—') : '—';
    return `<tr>
      <td><strong>${esc(p.name)}</strong></td>
      <td><div class="doc-path" title="${esc(dsStr)}">${esc(dsStr.slice(0,50))}${dsStr.length>50?'…':''}</div></td>
      <td><div class="doc-path" title="${esc(hwStr)}">${esc(hwStr.replace('assets/manuals/',''))}</div></td>
      <td><div class="doc-path" title="${esc(igStr)}">${esc(igStr.replace('assets/manuals/',''))}</div></td>
      <td><button class="btn-edit" onclick="catOpenDocs('${esc(p.id)}','${esc(p.name)}')">Editar</button></td>
    </tr>`;
  }).join('');
  const accRows = accessories.map(a => {
    const doc = a.datasheet || '—';
    return `<tr>
      <td><strong>${esc(a.name)}</strong><br><span style="color:#888;font-size:11px">${esc(a.id)}</span></td>
      <td><div class="doc-path" title="${esc(doc)}">${esc(String(doc).replace('assets/accessories/',''))}</div></td>
      <td style="font-size:12px;color:#888">${esc((a.compatible || []).join(', ') || '—')}</td>
      <td><button class="btn-edit" onclick="catOpenAccessory('${esc(a.id)}')">Editar</button></td>
    </tr>`;
  }).join('');

  return `<div class="sec-hdr" style="margin-bottom:10px"><h2 style="font-size:14px">Documentos de terminales</h2></div>
    <div class="tbl-wrap"><table class="docs-table">
      <thead><tr><th>Producto</th><th>Datasheet(s)</th><th>Hardware Guide</th><th>Install Guide</th><th></th></tr></thead>
      <tbody>${rows || '<tr><td colspan="5"><div class="empty">Sin productos</div></td></tr>'}</tbody></table></div>

    <div class="sec-hdr" style="margin:22px 0 10px"><h2 style="font-size:14px">Documentos de accesorios</h2></div>
    <div class="tbl-wrap"><table class="docs-table">
      <thead><tr><th>Accesorio</th><th>Datasheet / PDF</th><th>Compatible con</th><th></th></tr></thead>
      <tbody>${accRows || '<tr><td colspan="4"><div class="empty">Sin accesorios</div></td></tr>'}</tbody></table></div>`;
}

/* ─── PRODUCT FORM ───────────────────────────────────────────────────────── */
function catOpenProduct(id) {
  catState.editId   = id;
  catState.editType = 'product';
  catState.lang     = 'es';
  const p = id ? (C[catState.brand].products||[]).find(x=>x.id===id) : null;

  const tl = (v,l) => catTL(v,l);
  const pLang = (field, label, type='input') => ['es','en','pt'].map(l => `
    <div class="cat-fp ${l==='es'?'active':''}" data-cpf="${field}" data-cpl="${l}">
      <div class="form-row" style="margin-bottom:10px">
        <label>${label} (${l.toUpperCase()})</label>
        ${type==='textarea'
          ? `<textarea id="cpf-${field}-${l}" style="min-height:70px">${esc(tl(p?p[field]:'',l))}</textarea>`
          : `<input type="text" id="cpf-${field}-${l}" value="${esc(tl(p?p[field]:'',l))}">`
        }
      </div>
    </div>`).join('');

  const hotspotSlot = (i) => {
    const h = p && p.hotspots && p.hotspots[i];
    return `<div class="hs-box">
      <div class="hs-box-title">Punto ${i+1} (opcional)</div>
      <div class="form-2col" style="gap:8px">
        <div class="form-row"><label>X %</label><input type="number" id="cpf-hx${i}" min="0" max="100" value="${h?h.x:''}"></div>
        <div class="form-row"><label>Y %</label><input type="number" id="cpf-hy${i}" min="0" max="100" value="${h?h.y:''}"></div>
      </div>
      ${['es','en','pt'].map(l=>`
        <div class="cat-fp ${l==='es'?'active':''}" data-cpf="ht${i}" data-cpl="${l}">
          <div class="form-row">
            <label>Texto punto ${i+1} (${l.toUpperCase()})</label>
            <input type="text" id="cpf-ht${i}-${l}" value="${esc(h&&h.t?tl(h.t,l):'')}">
          </div>
        </div>`).join('')}
    </div>`;
  };

  const progVal = p ? (typeof p.prog==='string' ? p.prog : '') : '';

  document.getElementById('modal-box').innerHTML = `
    <div class="modal-hdr">
      <h3>${id?'Editar':'Nuevo'} Producto — ${catState.brand==='skywave'?'Skywave':'VIACHAIN'}</h3>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-body" style="max-height:70vh;overflow-y:auto">
      <div id="form-error" class="error-msg" style="display:none"></div>
      <div class="cat-subtab-bar">
        <button class="cat-subtab active" onclick="catFormPane('general')">General</button>
        <button class="cat-subtab" onclick="catFormPane('specs')">Specs</button>
        <button class="cat-subtab" onclick="catFormPane('hotspots')">Hotspots</button>
      </div>

      <!-- Language selector (shared across all panes) -->
      <div class="cp-lang-tabs" id="cpLangTabs">
        <button class="cp-lang-tab active" onclick="catFormLang('es')">ES</button>
        <button class="cp-lang-tab" onclick="catFormLang('en')">EN</button>
        <button class="cp-lang-tab" onclick="catFormLang('pt')">PT</button>
      </div>

      <!-- GENERAL pane -->
      <div id="cpPane-general" style="display:block">
        <div class="form-2col">
          <div class="form-row"><label>ID (slug) *</label>
            <input type="text" id="cpf-id" value="${esc(p?p.id:'')}" placeholder="ej. st6100" oninput="if(!${!!id})document.getElementById('cpf-id').value=catSlug(this.value)">
          </div>
          <div class="form-row"><label>Nombre *</label>
            <input type="text" id="cpf-name" value="${esc(p?p.name:'')}">
          </div>
        </div>
        <div class="form-row">
          <label>Imagen (ruta relativa o subir)</label>
          <div class="upload-row">
            <input type="text" id="cpf-img" value="${esc(p?p.img:'')}" placeholder="assets/products/NOMBRE.png">
            <button type="button" class="btn-upload" onclick="document.getElementById('cpfuImg').click()">Subir</button>
            <input type="file" id="cpfuImg" accept="image/*" style="display:none" onchange="catUploadImg(this,'cpf-img','cpfPrevImg','product')">
          </div>
          <div class="upload-status" id="cpfImgStatus"></div>
          ${p&&p.img?`<img id="cpfPrevImg" class="img-preview" src="${esc(p.img)}" onerror="this.style.display='none'">`:`<img id="cpfPrevImg" class="img-preview" style="display:none">`}
        </div>
        ${pLang('category','Categoría')}
        ${pLang('role','Rol')}
        ${pLang('hero','Hero / Tagline')}
        ${pLang('description','Descripción','textarea')}
      </div>

      <!-- SPECS pane -->
      <div id="cpPane-specs" style="display:none">
        <div class="form-2col">
          <div class="form-row"><label>Celular</label><input type="text" id="cpf-cellular" value="${esc(p?p.cellular:'—')}"></div>
          <div class="form-row"><label>Ambiente / IP</label><input type="text" id="cpf-env" value="${esc(p?p.env:'')}"></div>
        </div>
        <div class="form-2col">
          <div class="form-row"><label>Dimensiones</label><input type="text" id="cpf-dims" value="${esc(p?p.dims||'':'')}"></div>
          <div class="form-row"><label>Memoria</label><input type="text" id="cpf-memory" value="${esc(p?p.memory||'':'')}"></div>
        </div>
        <!-- Trilingual specs (shared lang selector above) -->
        ${['es','en','pt'].map(l=>`
          <div class="cat-fp ${l==='es'?'active':''}" data-cpf="specs-tri" data-cpl="${l}">
            <div class="form-2col">
              <div class="form-row"><label>Satélite (${l.toUpperCase()})</label><input type="text" id="cpf-sat-${l}" value="${esc(tl(p?p.satellite:'',l))}"></div>
              <div class="form-row"><label>GNSS (${l.toUpperCase()})</label><input type="text" id="cpf-gnss-${l}" value="${esc(tl(p?p.gnss:'',l))}"></div>
            </div>
            <div class="form-2col">
              <div class="form-row"><label>Energía (${l.toUpperCase()})</label><input type="text" id="cpf-power-${l}" value="${esc(tl(p?p.power:'',l))}"></div>
              <div class="form-row"><label>Programación (${l.toUpperCase()})</label><input type="text" id="cpf-prog-${l}" value="${esc(typeof (p?p.prog:'') === 'string' ? (l==='es'?(p?p.prog:''):(p?p.prog:'')) : tl(p?p.prog:'',l))}"></div>
            </div>
          </div>`).join('')}
        <div class="form-2col">
          <div class="form-row"><label>I/O (uno por línea)</label>
            <textarea id="cpf-io" style="min-height:80px">${esc((p?p.io||[]:[]).join('\n'))}</textarea>
          </div>
          <div class="form-row"><label>Puertos (uno por línea)</label>
            <textarea id="cpf-ports" style="min-height:80px">${esc((p?p.ports||[]:[]).join('\n'))}</textarea>
          </div>
        </div>
        <div class="form-row">
          <label class="form-check"><input type="checkbox" id="cpf-backup" ${p&&p.backup?'checked':''}>Tiene batería de respaldo</label>
        </div>
      </div>

      <!-- HOTSPOTS pane -->
      <div id="cpPane-hotspots" style="display:none">
        <p style="font-size:12px;color:#888;margin-bottom:14px">Coordenadas en % relativo a la imagen del producto (0–100). Máximo 3 puntos.</p>
        ${[0,1,2].map(hotspotSlot).join('')}
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-cancel" onclick="closeModal()">Cancelar</button>
      <button class="btn-save" id="btn-save" onclick="catSaveProduct()">Guardar</button>
    </div>`;

  document.getElementById('modal-backdrop').classList.remove('hidden');
}

function catFormPane(pane) {
  document.querySelectorAll('#modal-box .cat-subtab').forEach(b => b.classList.toggle('active', b.textContent.toLowerCase().startsWith(pane.slice(0,3))));
  ['general','specs','hotspots'].forEach(p => {
    const el = document.getElementById('cpPane-'+p);
    if (el) el.style.display = p===pane?'block':'none';
  });
}

function catFormLang(l) {
  catState.lang = l;
  document.querySelectorAll('.cp-lang-tab').forEach(b => b.classList.toggle('active', b.textContent.toLowerCase()===l));
  document.querySelectorAll('.cat-fp').forEach(p => {
    p.style.display = p.dataset.cpl === l ? 'block' : 'none';
  });
}

async function catUploadImg(input, fieldId, previewId, type) {
  if (!input.files[0]) return;
  const status = document.getElementById(fieldId.replace('cpf-','cpfImgStatus') + 'Status') || document.getElementById('cpfImgStatus');
  if (status) { status.textContent = 'Subiendo…'; status.className='upload-status'; }
  const fd = new FormData();
  fd.append('file', input.files[0]);
  fd.append('type', type);
  fd.append('csrf', CSRF);
  try {
    const r = await fetch('img_upload.php', {method:'POST', body:fd});
    const j = await r.json();
    if (j.ok) {
      document.getElementById(fieldId).value = j.url;
      const prev = document.getElementById(previewId);
      if (prev) { prev.src = j.url; prev.style.display = 'block'; }
      if (status) { status.textContent = '✓ '+j.name; status.className='upload-status ok'; }
    } else {
      if (status) { status.textContent = '✗ '+j.error; status.className='upload-status err'; }
    }
  } catch(e) {
    if (status) { status.textContent = '✗ Error de red'; status.className='upload-status err'; }
  }
}

async function catSaveProduct() {
  const btn = document.getElementById('btn-save');
  const errEl = document.getElementById('form-error');
  errEl.style.display = 'none';
  const id   = catV('cpf-id');
  const name = catV('cpf-name');
  if (!id)   { errEl.textContent='El ID es obligatorio'; errEl.style.display='block'; return; }
  if (!name) { errEl.textContent='El Nombre es obligatorio'; errEl.style.display='block'; return; }

  const product = {
    id, name,
    category: catTriLingual('cpf-category'),
    role:     catTriLingual('cpf-role'),
    img:      catV('cpf-img'),
    hero:     catTriLingual('cpf-hero'),
    description: catTriLingual('cpf-description'),
    satellite:   catTriLingual('cpf-sat'),
    cellular:    catV('cpf-cellular'),
    gnss:        catTriLingual('cpf-gnss'),
    power:       catTriLingual('cpf-power'),
    env:         catV('cpf-env'),
    dims:        catV('cpf-dims'),
    memory:      catV('cpf-memory'),
    io:          catArr('cpf-io'),
    ports:       catArr('cpf-ports'),
    prog:        catTriLingual('cpf-prog'),
    backup:      catChk('cpf-backup'),
    hotspots: [0,1,2].map(i => {
      const x = catVN('cpf-hx'+i), y = catVN('cpf-hy'+i);
      const t = catTriLingual('cpf-ht'+i);
      if (!x && !y && !t) return null;
      return {x, y, t};
    }).filter(Boolean),
  };

  btn.disabled = true; btn.textContent = 'Guardando…';
  try {
    const r = await fetch('catalog_api.php', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({action:'save_product', brand:catState.brand, product, csrf:CSRF})
    });
    const j = await r.json();
    if (j.ok) {
      C[j.brand] = j.catalog;
      closeModal();
      renderTabContent();
    } else {
      errEl.textContent = j.error || 'Error desconocido';
      errEl.style.display = 'block';
    }
  } catch(e) {
    errEl.textContent = 'Error de red: '+e.message;
    errEl.style.display = 'block';
  } finally {
    btn.disabled = false; btn.textContent = 'Guardar';
  }
}

/* ─── ACCESSORY FORM ─────────────────────────────────────────────────────── */
function catOpenAccessory(id) {
  catState.editId   = id;
  catState.editType = 'accessory';
  catState.lang     = 'es';
  const a = id ? (C[catState.brand].accessories||[]).find(x=>x.id===id) : null;
  const tl = (v,l) => catTL(v,l);

  const aLang = (field, label, type='input') => ['es','en','pt'].map(l => `
    <div class="cat-fp ${l==='es'?'active':''}" data-cpf="${field}" data-cpl="${l}">
      <div class="form-row">
        <label>${label} (${l.toUpperCase()})</label>
        ${type==='textarea'
          ? `<textarea id="caf-${field}-${l}" style="min-height:70px">${esc(tl(a?a[field]:'',l))}</textarea>`
          : `<input type="text" id="caf-${field}-${l}" value="${esc(tl(a?a[field]:'',l))}">`
        }
      </div>
    </div>`).join('');

  document.getElementById('modal-box').innerHTML = `
    <div class="modal-hdr">
      <h3>${id?'Editar':'Nuevo'} Accesorio — ${catState.brand==='skywave'?'Skywave':'VIACHAIN'}</h3>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-body" style="max-height:70vh;overflow-y:auto">
      <div id="form-error" class="error-msg" style="display:none"></div>
      <div class="cp-lang-tabs">
        <button class="cp-lang-tab active" onclick="catFormLang('es')">ES</button>
        <button class="cp-lang-tab" onclick="catFormLang('en')">EN</button>
        <button class="cp-lang-tab" onclick="catFormLang('pt')">PT</button>
      </div>
      <div class="form-2col">
        <div class="form-row"><label>ID (slug) *</label>
          <input type="text" id="caf-id" value="${esc(a?a.id:'')}" placeholder="ej. db300">
        </div>
        <div class="form-row"><label>Nombre *</label>
          <input type="text" id="caf-name" value="${esc(a?a.name:'')}">
        </div>
      </div>
      <div class="form-row">
        <label>Imagen</label>
        <div class="upload-row">
          <input type="text" id="caf-img" value="${esc(a?a.img:'')}" placeholder="assets/accessories/NOMBRE.png">
          <button type="button" class="btn-upload" onclick="document.getElementById('cafuImg').click()">Subir</button>
          <input type="file" id="cafuImg" accept="image/*" style="display:none" onchange="catUploadImg(this,'caf-img','cafPrevImg','accessory')">
        </div>
        <div class="upload-status" id="cpfImgStatus"></div>
        ${a&&a.img?`<img id="cafPrevImg" class="img-preview" src="${esc(a.img)}" onerror="this.style.display='none'">`:`<img id="cafPrevImg" class="img-preview" style="display:none">`}
      </div>
      ${aLang('category','Categoría')}
      ${aLang('hero','Hero / Tagline')}
      ${aLang('description','Descripción','textarea')}
      <div class="form-2col">
        <div class="form-row"><label>Ambiente / IP</label><input type="text" id="caf-env" value="${esc(a?a.env||'':'')}"></div>
        <div class="form-row"><label>Dimensiones</label><input type="text" id="caf-dims" value="${esc(a?a.dims||'':'')}"></div>
      </div>
      <div class="form-row"><label>Compatible con (separado por comas)</label>
        <input type="text" id="caf-compatible" value="${esc(a?(a.compatible||[]).join(', '):'')}" placeholder="ST 6100, ST 9100, SC 1000">
      </div>
      <div class="form-row"><label>Puertos (uno por línea)</label>
        <textarea id="caf-ports" style="min-height:60px">${esc(a?(a.ports||[]).join('\n'):'')}</textarea>
      </div>
      <div class="form-row"><label>Datasheet (ruta relativa)</label>
        <input type="text" id="caf-datasheet" value="${esc(a?a.datasheet||'':'')}" placeholder="assets/accessories/NOMBRE.pdf">
      </div>
      <div class="form-row" style="margin-top:14px;padding-top:14px;border-top:1px solid #E5E7EB">
        <label class="form-check"><input type="checkbox" id="caf-thirdparty" ${a&&a.thirdParty?'checked':''}>Accesorio de tercero — compatible pero no vendido directamente por nosotros</label>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-cancel" onclick="closeModal()">Cancelar</button>
      <button class="btn-save" id="btn-save" onclick="catSaveAccessory()">Guardar</button>
    </div>`;
  document.getElementById('modal-backdrop').classList.remove('hidden');
}

async function catSaveAccessory() {
  const btn = document.getElementById('btn-save');
  const errEl = document.getElementById('form-error');
  errEl.style.display = 'none';
  const id   = catV('caf-id');
  const name = catV('caf-name');
  if (!id)   { errEl.textContent='El ID es obligatorio'; errEl.style.display='block'; return; }
  if (!name) { errEl.textContent='El Nombre es obligatorio'; errEl.style.display='block'; return; }

  const compatible = catV('caf-compatible').split(',').map(s=>s.trim()).filter(Boolean);
  const ds = catV('caf-datasheet');
  const accessory = {
    id, name,
    category:    catTriLingual('caf-category'),
    img:         catV('caf-img'),
    hero:        catTriLingual('caf-hero'),
    description: catTriLingual('caf-description'),
    compatible,
    env:         catV('caf-env'),
    dims:        catV('caf-dims'),
    ports:       catArr('caf-ports'),
    datasheet:   ds || null,
    thirdParty:  catChk('caf-thirdparty') || false,
  };

  btn.disabled = true; btn.textContent = 'Guardando…';
  try {
    const r = await fetch('catalog_api.php', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({action:'save_accessory', brand:catState.brand, accessory, csrf:CSRF})
    });
    const j = await r.json();
    if (j.ok) { C[j.brand] = j.catalog; closeModal(); renderTabContent(); }
    else { errEl.textContent = j.error||'Error'; errEl.style.display='block'; }
  } catch(e) { errEl.textContent='Error de red: '+e.message; errEl.style.display='block'; }
  finally { btn.disabled=false; btn.textContent='Guardar'; }
}

/* ─── DOCS FORM ──────────────────────────────────────────────────────────── */
function catOpenDocs(productId, productName) {
  const ds = (C[catState.brand].datasheets    || {})[productId];
  const hw = (C[catState.brand].hardwareGuides || {})[productId];
  const ig = (C[catState.brand].installGuides  || {})[productId];

  // Datasheet rows: array format or single {es/en/pt} or null
  const isArr = Array.isArray(ds);
  const dsRows = isArr ? ds : (ds ? [ds] : [{}]);
  const dsHtml = dsRows.map((d, i) => `
    <div class="form-2col" style="margin-bottom:8px">
      ${isArr ? `<div class="form-row" style="min-width:80px"><label>Label</label><input type="text" id="ds-label-${i}" value="${esc(d.label||'')}"></div>` : ''}
      <div class="form-row"><label>${isArr ? 'Ruta EN' : 'Ruta ES'}</label><input type="text" id="ds-es-${i}" value="${esc(isArr?d.en||'':d.es||'')}"></div>
      ${!isArr ? `<div class="form-row"><label>Ruta EN</label><input type="text" id="ds-en-${i}" value="${esc(d.en||'')}"></div>` : ''}
    </div>
    ${!isArr ? `<div class="form-row"><label>Ruta PT</label><input type="text" id="ds-pt-${i}" value="${esc(d.pt||'')}"></div>` : ''}
  `).join('');

  const dsType = isArr ? 'array' : 'object';

  document.getElementById('modal-box').innerHTML = `
    <div class="modal-hdr">
      <h3>Documentos — ${esc(productName)}</h3>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-body">
      <div id="form-error" class="error-msg" style="display:none"></div>
      <div style="font-size:11px;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px">Datasheets</div>
      <div id="dsRows">${dsHtml}</div>
      ${isArr ? `<button type="button" onclick="catAddDsRow()" style="font-size:12px;color:#BA0C2F;background:#FFF5F6;border:1px dashed #FECACA;border-radius:6px;padding:5px 14px;cursor:pointer;margin-bottom:16px">+ Agregar datasheet</button>` : ''}
      <input type="hidden" id="dsType" value="${dsType}">
      <input type="hidden" id="dsCount" value="${dsRows.length}">

      <div style="font-size:11px;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.5px;margin:16px 0 10px">Hardware Guide (EN)</div>
      <div class="form-row"><input type="text" id="hwPath" value="${esc(hw?hw.en||'':'')}" placeholder="assets/manuals/…"></div>

      <div style="font-size:11px;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.5px;margin:16px 0 10px">Install Guide (EN)</div>
      <div class="form-row"><input type="text" id="igPath" value="${esc(ig?ig.en||'':'')}" placeholder="assets/manuals/…"></div>
    </div>
    <div class="modal-footer">
      <button class="btn-cancel" onclick="closeModal()">Cancelar</button>
      <button class="btn-save" id="btn-save" onclick="catSaveDocs('${esc(productId)}')">Guardar</button>
    </div>`;
  document.getElementById('modal-backdrop').classList.remove('hidden');
}

function catAddDsRow() {
  const cnt = parseInt(document.getElementById('dsCount').value);
  const row = document.createElement('div');
  row.className = 'form-2col';
  row.style.marginBottom = '8px';
  row.innerHTML = `<div class="form-row" style="min-width:80px"><label>Label</label><input type="text" id="ds-label-${cnt}" value=""></div>
    <div class="form-row"><label>Ruta EN</label><input type="text" id="ds-es-${cnt}" value=""></div>`;
  document.getElementById('dsRows').appendChild(row);
  document.getElementById('dsCount').value = cnt + 1;
}

async function catSaveDocs(productId) {
  const btn   = document.getElementById('btn-save');
  const errEl = document.getElementById('form-error');
  errEl.style.display = 'none';
  const dsType  = document.getElementById('dsType').value;
  const cnt     = parseInt(document.getElementById('dsCount').value);
  const hwPath  = (document.getElementById('hwPath') ||{}).value?.trim() || '';
  const igPath  = (document.getElementById('igPath') ||{}).value?.trim() || '';

  let datasheets = null;
  if (dsType === 'array') {
    const items = [];
    for (let i = 0; i < cnt; i++) {
      const lbl = (document.getElementById('ds-label-'+i)||{}).value?.trim()||'';
      const path = (document.getElementById('ds-es-'+i)||{}).value?.trim()||'';
      if (path) items.push({en: path, label: lbl});
    }
    if (items.length) datasheets = items;
  } else {
    const es = (document.getElementById('ds-es-0')||{}).value?.trim()||'';
    const en = (document.getElementById('ds-en-0')||{}).value?.trim()||'';
    const pt = (document.getElementById('ds-pt-0')||{}).value?.trim()||'';
    if (es||en||pt) datasheets = {es, en, pt};
  }

  btn.disabled = true; btn.textContent = 'Guardando…';
  try {
    const r = await fetch('catalog_api.php', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({
        action: 'save_docs', brand: catState.brand, product_id: productId, csrf: CSRF,
        datasheets,
        hardwareGuide: hwPath ? {en: hwPath} : null,
        installGuide:  igPath ? {en: igPath} : null,
      })
    });
    const j = await r.json();
    if (j.ok) { C[j.brand] = j.catalog; closeModal(); renderTabContent(); }
    else { errEl.textContent = j.error||'Error'; errEl.style.display='block'; }
  } catch(e) { errEl.textContent='Error de red: '+e.message; errEl.style.display='block'; }
  finally { btn.disabled=false; btn.textContent='Guardar'; }
}

/* ─── DELETE & MOVE ──────────────────────────────────────────────────────── */
function catConfirmDelete(type, id, label) {
  document.getElementById('modal-box').innerHTML = `
    <div class="confirm-box" style="margin:0;border-radius:0;box-shadow:none">
      <h3>¿Confirmar eliminación?</h3>
      <p>Se eliminará permanentemente: <strong>${esc(label)}</strong></p>
      <div class="confirm-btns">
        <button class="btn-cancel" onclick="closeModal()">Cancelar</button>
        <button class="btn-confirm-del" onclick="catDoDelete('${type}','${esc(id)}')">Eliminar</button>
      </div>
    </div>`;
  document.getElementById('modal-backdrop').classList.remove('hidden');
}

async function catDoDelete(type, id) {
  const action = type === 'product' ? 'delete_product' : 'delete_accessory';
  try {
    const r = await fetch('catalog_api.php', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({action, brand: catState.brand, id, csrf: CSRF})
    });
    const j = await r.json();
    if (j.ok) { C[j.brand] = j.catalog; closeModal(); renderTabContent(); }
    else alert('Error: '+j.error);
  } catch(e) { alert('Error de red: '+e.message); }
}

async function catMoveProduct(id, dir) {
  const r = await fetch('catalog_api.php', {method:'POST',headers:{'Content-Type':'application/json'},
    body:JSON.stringify({action:'move_product',brand:catState.brand,id,dir,csrf:CSRF})});
  const j = await r.json();
  if (j.ok) { C[j.brand] = j.catalog; renderTabContent(); }
}

async function catMoveAccessory(id, dir) {
  const r = await fetch('catalog_api.php', {method:'POST',headers:{'Content-Type':'application/json'},
    body:JSON.stringify({action:'move_accessory',brand:catState.brand,id,dir,csrf:CSRF})});
  const j = await r.json();
  if (j.ok) { C[j.brand] = j.catalog; renderTabContent(); }
}
