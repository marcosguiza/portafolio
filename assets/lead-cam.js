// Lead Capture Camera Module
// Handles: QR scanning (BarcodeDetector API), photo capture,
//          offline lead queuing via IndexedDB, and background sync.
'use strict';
(function () {

  /* ── State ─────────────────────────────────────────── */
  var camStream       = null;
  var camMode         = 'qr';   // 'qr' | 'photo'
  var qrTimer         = null;
  var capturedDataUrl = null;   // base64 — for offline storage / preview
  var capturedBlob    = null;   // Blob  — for online upload

  /* DOM refs resolved on DOMContentLoaded */
  var overlay, videoEl, canvasEl, statusEl;

  /* ── IndexedDB helpers ──────────────────────────────── */
  var IDB_NAME = 'skywave_leads_v1';

  function openDB() {
    return new Promise(function (res, rej) {
      var req = indexedDB.open(IDB_NAME, 1);
      req.onupgradeneeded = function (e) {
        e.target.result.createObjectStore('pending', { keyPath: '__key', autoIncrement: true });
      };
      req.onsuccess = function (e) { res(e.target.result); };
      req.onerror   = function (e) { rej(e.target.error); };
    });
  }

  function dbGetAll() {
    return openDB().then(function (db) {
      return new Promise(function (res) {
        var req = db.transaction('pending', 'readonly').objectStore('pending').getAll();
        req.onsuccess = function (e) { res(e.target.result || []); };
        req.onerror   = function ()  { res([]); };
      });
    }).catch(function () { return []; });
  }

  function dbAdd(record) {
    return openDB().then(function (db) {
      return new Promise(function (res, rej) {
        var tx = db.transaction('pending', 'readwrite');
        tx.objectStore('pending').add(record);
        tx.oncomplete = res;
        tx.onerror    = rej;
      });
    });
  }

  function dbDelete(key) {
    return openDB().then(function (db) {
      return new Promise(function (res) {
        var tx = db.transaction('pending', 'readwrite');
        tx.objectStore('pending').delete(key);
        tx.oncomplete = res;
        tx.onerror    = res;
      });
    });
  }

  /* ── Sync banner ────────────────────────────────────── */
  function updateSyncBanner() {
    dbGetAll().then(function (records) {
      var banner  = document.getElementById('sync-banner');
      var countEl = document.getElementById('sync-count');
      if (!banner) return;
      if (records.length > 0) {
        banner.style.display = 'flex';
        if (countEl) countEl.textContent = records.length;
      } else {
        banner.style.display = 'none';
      }
    });
  }

  function syncNow() {
    var btn  = document.getElementById('btn-sync-now');
    var csrf = (document.querySelector('input[name=csrf]') || {}).value || '';
    if (btn) btn.disabled = true;

    dbGetAll().then(function (records) {
      if (!records.length) { if (btn) btn.disabled = false; return updateSyncBanner(); }

      var chain = Promise.resolve();
      records.forEach(function (rec) {
        chain = chain.then(function () {
          return syncOne(rec, csrf).then(function (ok) {
            if (ok) return dbDelete(rec.__key);
          });
        });
      });
      return chain;
    }).then(function () {
      updateSyncBanner();
      if (btn) btn.disabled = false;
    }).catch(function () {
      if (btn) btn.disabled = false;
    });
  }

  function syncOne(rec, csrf) {
    var imagePromise = Promise.resolve(rec.card_image_url || null);
    if (!rec.card_image_url && rec.card_image_data) {
      imagePromise = uploadDataUrl(rec.card_image_data, csrf).catch(function () { return null; });
    }
    return imagePromise.then(function (cardUrl) {
      var lead = Object.assign({}, rec.lead, { card_image: cardUrl || null });
      return fetch('lead_sync.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ csrf: csrf, leads: [lead] }),
      }).then(function (r) { return r.json(); })
        .then(function (j) { return !!j.ok; })
        .catch(function ()  { return false; });
    });
  }

  function uploadDataUrl(dataUrl, csrf) {
    var blob = dataUrlToBlob(dataUrl);
    var fd   = new FormData();
    fd.append('file', blob, 'card.jpg');
    fd.append('csrf', csrf);
    return fetch('card_upload.php', { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (j) { return j.ok ? j.url : null; });
  }

  function dataUrlToBlob(dataUrl) {
    var parts = dataUrl.split(',');
    var mime  = ((parts[0] || '').match(/:(.*?);/) || [, 'image/jpeg'])[1];
    var raw   = atob(parts[1] || '');
    var buf   = new Uint8Array(raw.length);
    for (var i = 0; i < raw.length; i++) buf[i] = raw.charCodeAt(i);
    return new Blob([buf], { type: mime });
  }

  /* ── Camera lifecycle ───────────────────────────────── */
  function openCamera(mode) {
    if (!overlay) return;
    camMode = mode || 'qr';
    refreshTabUI();
    overlay.style.display = 'flex';
    startStream();
  }

  function closeCamera() {
    stopStream();
    if (overlay) overlay.style.display = 'none';
  }

  function stopStream() {
    if (qrTimer) { clearInterval(qrTimer); qrTimer = null; }
    if (camStream) { camStream.getTracks().forEach(function (t) { t.stop(); }); camStream = null; }
    if (videoEl)   { videoEl.srcObject = null; }
  }

  function startStream() {
    stopStream();
    setStatus('…');
    navigator.mediaDevices.getUserMedia({
      video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 960 } },
    }).then(function (stream) {
      camStream = stream;
      videoEl.srcObject = stream;
      return videoEl.play();
    }).then(function () {
      if (camMode === 'qr') startQR(); else setStatus(uiStr('ph_status'));
    }).catch(function (err) {
      setStatus(err.name === 'NotAllowedError' ? uiStr('cam_denied') : 'Error: ' + err.message);
    });
  }

  /* ── QR Detection ───────────────────────────────────── */
  function startQR() {
    if (!('BarcodeDetector' in window)) {
      setStatus(uiStr('qr_unavail'));
      setTimeout(function () { switchCamMode('photo'); }, 2000);
      return;
    }
    var detector = new BarcodeDetector({ formats: ['qr_code'] });
    setStatus(uiStr('qr_status'));
    qrTimer = setInterval(function () {
      if (!camStream || videoEl.readyState < 2) return;
      detector.detect(videoEl).then(function (codes) {
        if (codes.length) handleQRResult(codes[0].rawValue);
      }).catch(function () {});
    }, 500);
  }

  function handleQRResult(raw) {
    var parsed = parseQRData(raw);
    if (parsed) {
      stopStream();
      if (overlay) overlay.style.display = 'none';
      fillForm(parsed);
      showFillIndicator();
    } else {
      setStatus(uiStr('qr_nocontact'));
      setTimeout(function () { switchCamMode('photo'); }, 1400);
    }
  }

  function parseQRData(raw) {
    if (!raw) return null;

    /* vCard 3.0 / 4.0 */
    if (/BEGIN:VCARD/i.test(raw)) {
      var getV = function (key) {
        var m = raw.match(new RegExp('^' + key + '[^:\\r\\n]*:\\s*([^\\r\\n]+)', 'mi'));
        return m ? m[1].trim() : '';
      };
      var fn  = getV('FN');
      var n   = getV('N').replace(/;/g, ' ').replace(/\s+/g, ' ').trim();
      var org = getV('ORG').replace(/;.*$/, '').trim();
      var em  = getV('EMAIL');
      var tel = getV('TEL');
      if (!fn && !em) return null;
      return { name: fn || n, company: org, email: em, phone: tel };
    }

    /* meCard */
    if (/^MECARD:/i.test(raw)) {
      var body = raw.slice(7).replace(/;;?\s*$/, '');
      var flds = {};
      body.split(';').forEach(function (f) {
        var i = f.indexOf(':');
        if (i > 0) flds[f.slice(0, i).trim().toUpperCase()] = f.slice(i + 1).trim();
      });
      var name  = (flds['N'] || '').replace(',', ' ').trim();
      var email = flds['EMAIL'] || '';
      if (!name && !email) return null;
      return { name: name, company: flds['ORG'] || '', email: email, phone: flds['TEL'] || flds['PHONE'] || '' };
    }

    return null;
  }

  function fillForm(data) {
    setFieldVal('f-name',    data.name);
    setFieldVal('f-company', data.company);
    setFieldVal('f-email',   data.email);
    if (data.phone) applyPhone(data.phone);
  }

  function setFieldVal(id, val) {
    if (!val) return;
    var el = document.getElementById(id);
    if (!el) return;
    el.value = val;
    el.style.transition = 'background .4s';
    el.style.background = '#ECFDF5';
    setTimeout(function () { el.style.background = ''; }, 3500);
  }

  function applyPhone(tel) {
    var cleaned = tel.replace(/[\s\-().]/g, '');
    var select  = document.getElementById('f-dial');
    var numEl   = document.getElementById('f-phone');
    if (!numEl) return;
    if (cleaned.charAt(0) === '+' && select) {
      var codes = Array.from(select.options)
        .map(function (o) { return o.value; })
        .filter(function (v) { return v.charAt(0) === '+'; });
      codes.sort(function (a, b) { return b.length - a.length; });
      for (var i = 0; i < codes.length; i++) {
        if (cleaned.startsWith(codes[i])) {
          for (var j = 0; j < select.options.length; j++) {
            if (select.options[j].value === codes[i]) { select.selectedIndex = j; break; }
          }
          numEl.value = cleaned.slice(codes[i].length);
          return;
        }
      }
    }
    numEl.value = tel.trim();
  }

  function showFillIndicator() {
    var el = document.getElementById('fill-indicator');
    if (!el) return;
    el.style.display = 'flex';
    setTimeout(function () { el.style.display = 'none'; }, 5000);
  }

  /* ── Photo capture ──────────────────────────────────── */
  function capturePhoto() {
    if (!camStream || videoEl.readyState < 2) return;
    canvasEl.width  = videoEl.videoWidth;
    canvasEl.height = videoEl.videoHeight;
    canvasEl.getContext('2d').drawImage(videoEl, 0, 0);
    capturedDataUrl = canvasEl.toDataURL('image/jpeg', 0.82);
    canvasEl.toBlob(function (blob) {
      capturedBlob = blob;
      showThumb(capturedDataUrl);
      closeCamera();
    }, 'image/jpeg', 0.82);
  }

  function showThumb(src) {
    var wrap = document.getElementById('card-thumb-wrap');
    var img  = document.getElementById('card-thumb-img');
    if (wrap && img) { img.src = src; wrap.style.display = 'flex'; }
  }

  function clearPhoto() {
    capturedDataUrl = null;
    capturedBlob    = null;
    var wrap = document.getElementById('card-thumb-wrap');
    if (wrap) wrap.style.display = 'none';
    var hid = document.getElementById('f-card-image');
    if (hid) hid.value = '';
  }

  /* ── Camera mode tabs ───────────────────────────────── */
  function switchCamMode(mode) {
    camMode = mode;
    if (qrTimer) { clearInterval(qrTimer); qrTimer = null; }
    refreshTabUI();
    if (mode === 'qr') startQR(); else setStatus(uiStr('ph_status'));
  }

  function refreshTabUI() {
    var tabQR    = document.getElementById('cam-tab-qr');
    var tabPh    = document.getElementById('cam-tab-photo');
    var capBtn   = document.getElementById('btn-cam-capture');
    var aimFrame = document.getElementById('qr-aim-frame');
    var qrHint   = document.getElementById('qr-hint');
    var isQR     = camMode === 'qr';
    if (tabQR)    tabQR.setAttribute('data-active',    isQR  ? '1' : '0');
    if (tabPh)    tabPh.setAttribute('data-active',    !isQR ? '1' : '0');
    if (capBtn)   capBtn.style.display   = isQR ? 'none' : 'block';
    if (aimFrame) aimFrame.style.display = isQR ? 'flex' : 'none';
    if (qrHint)   qrHint.style.display   = isQR ? 'block' : 'none';
  }

  function setStatus(msg) { if (statusEl) statusEl.textContent = msg; }

  /* ── i18n helper ────────────────────────────────────── */
  var _ui = null;
  function uiStr(key) {
    if (!_ui) _ui = window._CAM_STRINGS || {};
    return _ui[key] || key;
  }

  /* ── Form submit intercept ──────────────────────────── */
  function handleSubmit(e) {
    /* Only intercept when we have an image OR we are offline */
    if (!capturedBlob && navigator.onLine) return;
    e.preventDefault();

    var form      = e.target;
    var submitBtn = document.getElementById('btn-submit');
    if (submitBtn) submitBtn.disabled = true;

    var csrf = (document.querySelector('input[name=csrf]') || {}).value || '';

    if (navigator.onLine) {
      /* Upload image then let PHP handle the rest */
      var up = Promise.resolve(null);
      if (capturedBlob) {
        var fd = new FormData();
        fd.append('file', capturedBlob, 'card.jpg');
        fd.append('csrf', csrf);
        up = fetch('card_upload.php', { method: 'POST', body: fd })
          .then(function (r) { return r.json(); })
          .then(function (j) { return j.ok ? j.url : null; })
          .catch(function ()  { return null; });
      }
      up.then(function (url) {
        var hid = document.getElementById('f-card-image');
        if (hid && url) hid.value = url;
        form.submit(); /* bypasses listener — no recursion */
      });
    } else {
      /* Offline: store in IndexedDB */
      var lang     = document.documentElement.lang || 'es';
      var dialCode = (document.getElementById('f-dial')  || {}).value || '';
      var phoneNum = (document.getElementById('f-phone') || {}).value || '';
      var terminals = Array.from(form.querySelectorAll('input[name="terminals[]"]:checked'))
                       .map(function (x) { return x.value; });
      var record = {
        lead: {
          name:           ((document.getElementById('f-name')     || {}).value || '').trim(),
          jobtitle:       ((document.getElementById('f-jobtitle') || {}).value || '').trim(),
          company:        ((document.getElementById('f-company')  || {}).value || '').trim(),
          email:          ((document.getElementById('f-email')    || {}).value || '').trim(),
          dial_code:      dialCode,
          phone_num:      phoneNum,
          phone:          (dialCode + ' ' + phoneNum).trim(),
          terminals:      terminals,
          other_interest: ((document.getElementById('f-other') || {}).value || '').trim(),
          lang:           lang,
          created:        new Date().toISOString(),
        },
        card_image_data: capturedDataUrl || null,
        card_image_url:  null,
        queued:          new Date().toISOString(),
      };

      dbAdd(record).then(function () {
        showOfflineSuccess(lang);
      });
    }
  }

  function showOfflineSuccess(lang) {
    var msgs = {
      es: { h: '¡Guardado sin conexión!',
            m: 'Lead guardado en este dispositivo. Se sincronizará automáticamente al recuperar la conexión.',
            b: 'Capturar otro' },
      en: { h: 'Saved offline!',
            m: 'Lead saved on this device. It will sync automatically when connection is restored.',
            b: 'Capture another' },
      pt: { h: 'Salvo sem conexão!',
            m: 'Lead salvo neste dispositivo. Será sincronizado automaticamente ao recuperar a conexão.',
            b: 'Capturar outro' },
    };
    var m    = msgs[lang] || msgs.es;
    var card = document.querySelector('.card');
    if (!card) return;
    card.innerHTML =
      '<div class="success-wrap">' +
      '<div class="success-icon" style="color:#F59E0B;font-size:48px">📲</div>' +
      '<h1 class="success-heading">' + m.h + '</h1>' +
      '<p class="success-msg">' + m.m + '</p>' +
      '<button class="btn-again" onclick="location.reload()">' + m.b + '</button>' +
      '</div>';
  }

  /* ── Init ───────────────────────────────────────────── */
  document.addEventListener('DOMContentLoaded', function () {
    overlay  = document.getElementById('cam-overlay');
    videoEl  = document.getElementById('cam-video');
    canvasEl = document.getElementById('cam-canvas');
    statusEl = document.getElementById('cam-status');

    var form = document.querySelector('form[method=post]');
    if (form) form.addEventListener('submit', handleSubmit);

    window.addEventListener('online', updateSyncBanner);
    updateSyncBanner();
  });

  /* ── Public API ─────────────────────────────────────── */
  window.openCamera    = openCamera;
  window.closeCamera   = closeCamera;
  window.switchCamMode = switchCamMode;
  window.capturePhoto  = capturePhoto;
  window.clearPhoto    = clearPhoto;
  window.syncNow       = syncNow;

})();
