/**
 * [HFM 19/11/2026] Douchette dans la grille produit.
 *
 * Chaque ligne a un champ « Scan » (list_content.tpl). La douchette (mode clavier HID) tape le code puis
 * Entrée : le code est découpé par le parseur GS1 de fdlots (window.fdlotsParseScan : correction clavier
 * US/AZERTY, identifiants AIM, forme (01)(17)(10), repli EAN / lot seul), puis envoyé à
 * AdminProductGrid::ajaxProcessHfmScan qui écrit l'EAN-13 du produit et/ou le lot dans fdlots.
 * Entrée dans ce champ ne soumet JAMAIS le formulaire de la grille (filtres).
 */
(function () {
  'use strict';

  var STYLE = '.hfm-scan{display:inline-block;width:110px;height:28px;margin-right:4px;vertical-align:middle;font-family:monospace}'
    + '.hfm-scan.is-busy{background:#f5f5f5}'
    + 'tr.hfm-scan-ok>td{background:#e8f6ec!important;transition:background .2s}'
    + 'tr.hfm-scan-ko>td{background:#fdecea!important;transition:background .2s}'
    + '#hfm-scan-status{position:fixed;left:16px;bottom:16px;z-index:10000;max-width:min(640px,calc(100vw - 32px));padding:10px 14px;'
    + 'border-radius:4px;box-shadow:0 2px 10px rgba(0,0,0,.2);font-size:13px;line-height:1.4;white-space:pre-line;color:#fff;background:#2e7d32}'
    + '#hfm-scan-status.is-error{background:#c62828}#hfm-scan-status.is-warn{background:#ef6c00}#hfm-scan-status[hidden]{display:none}';

  var bar = null;
  var hideTimer = null;

  function ensureUi() {
    if (bar) return;
    var st = document.createElement('style');
    st.textContent = STYLE;
    document.head.appendChild(st);
    bar = document.createElement('div');
    bar.id = 'hfm-scan-status';
    bar.setAttribute('role', 'status');
    bar.setAttribute('aria-live', 'polite');
    bar.hidden = true;
    document.body.appendChild(bar);
  }

  function say(text, kind) {
    ensureUi();
    bar.className = kind === 'error' ? 'is-error' : (kind === 'warn' ? 'is-warn' : '');
    bar.textContent = text;
    bar.hidden = false;
    window.clearTimeout(hideTimer);
    hideTimer = window.setTimeout(function () { bar.hidden = true; }, kind === 'error' ? 12000 : 6000);
  }

  function flash(tr, ok) {
    if (!tr) return;
    tr.classList.remove('hfm-scan-ok', 'hfm-scan-ko');
    tr.classList.add(ok ? 'hfm-scan-ok' : 'hfm-scan-ko');
    window.setTimeout(function () { tr.classList.remove('hfm-scan-ok', 'hfm-scan-ko'); }, 2500);
  }

  function frDate(iso) {
    var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(iso || '');
    return m ? m[3] + '/' + m[2] + '/' + m[1] : '';
  }

  function productName(tr) {
    var field = tr && tr.querySelector('[data-field-name="name"]');
    var td = field && field.closest('td');
    var cell = td && td.querySelector('.edit_field');
    var t = cell ? cell.textContent.replace(/\s+/g, ' ').trim() : '';
    return t.length > 60 ? t.slice(0, 57) + '…' : t;
  }

  /** Met à jour la cellule EAN-13 de la ligne si la colonne est affichée. */
  function showEan(tr, ean) {
    if (!tr) return;
    var ta = tr.querySelector('textarea[data-field-name="ean13"]');
    if (!ta) return;
    ta.value = ean;
    var td = ta.closest('td');
    var cell = td && td.querySelector('.edit_field');
    if (cell) cell.textContent = ean;
  }

  function send(input, parsed, confirmed) {
    var tr = input.closest('tr');
    var idProduct = input.getAttribute('data-id-product');
    input.classList.add('is-busy');
    $.ajax({
      url: ajax_url,
      type: 'POST',
      dataType: 'json',
      data: {
        ajax: 1,
        action: 'hfm_scan',
        id_product: idProduct,
        gtin: parsed.gtin || '',
        lot: parsed.lot || '',
        expiry: parsed.expiry || '',
        confirm: confirmed ? 1 : 0
      }
    }).done(function (r) {
      input.classList.remove('is-busy');
      if (r && r.confirm && !confirmed) {
        var name = productName(tr);
        if (window.confirm((name ? name + '\n\n' : '') + r.confirm)) {
          send(input, parsed, true);
        } else {
          say('Scan annulé : rien n\'a été enregistré.', 'warn');
          flash(tr, false);
          input.focus();
        }
        return;
      }
      if (!r || !r.ok) {
        say((r && r.error) || 'Erreur inattendue : rien n\'a été enregistré.', 'error');
        flash(tr, false);
        input.focus();
        return;
      }
      var parts = [];
      if (r.ean) {
        if (r.ean.status === 'set') parts.push('EAN ' + r.ean.value + ' enregistré');
        else if (r.ean.status === 'replaced') parts.push('EAN ' + r.ean.previous + ' remplacé par ' + r.ean.value);
        else if (r.ean.status === 'same') parts.push('EAN ' + r.ean.value + ' déjà correct');
        else if (r.ean.status === 'combinations') parts.push('EAN non écrit (produit à déclinaisons)');
        if (r.ean.status === 'set' || r.ean.status === 'replaced') showEan(tr, r.ean.value);
      }
      if (r.lot) {
        var exp = frDate(r.lot.expiry);
        parts.push('lot ' + r.lot.value + (exp ? ' (exp. ' + exp + ')' : '') + (r.lot.revealed ? ' remis dans la liste' : ' ajouté')
          + ' · ' + r.lot.count + ' lot(s) connu(s)');
      }
      var name = productName(tr);
      say((name ? name + ' : ' : '') + parts.join(' · '), '');
      flash(tr, true);
      input.focus(); // prêt pour le scan suivant (ex. code 1D puis QR de la même boîte)
    }).fail(function (xhr) {
      input.classList.remove('is-busy');
      say('Connexion impossible (' + (xhr && xhr.status) + ') : rien n\'a été enregistré, réessayez.', 'error');
      flash(tr, false);
    });
  }

  function isScanInput(el) {
    return !!(el && el.classList && el.classList.contains('hfm-scan'));
  }

  // Phase de capture : avant les gestionnaires de la grille (soumission des filtres sur Entrée).
  document.addEventListener('keydown', function (e) {
    var input = e.target;
    if (!isScanInput(input)) return;
    var enter = e.key === 'Enter' || e.keyCode === 13;
    var tab = e.key === 'Tab' || e.keyCode === 9;
    if (!enter && !(tab && input.value.trim() !== '')) return;
    e.preventDefault();
    e.stopPropagation();
    var raw = input.value;
    input.value = '';
    if (!raw.trim()) return;
    if (typeof window.fdlotsParseScan !== 'function') {
      say('Lecture des codes indisponible : le module de traçabilité des lots (fdlots) n\'est pas chargé.', 'error');
      return;
    }
    var parsed = window.fdlotsParseScan(raw);
    if (!parsed || (!parsed.gtin && !parsed.lot)) {
      say('Code non reconnu : « ' + raw.trim().slice(0, 80) + ' ». Rien n\'a été enregistré.', 'error');
      flash(input.closest('tr'), false);
      return;
    }
    send(input, parsed, false);
  }, true);

  ['keypress', 'keyup'].forEach(function (type) {
    document.addEventListener(type, function (e) {
      if (isScanInput(e.target) && (e.key === 'Enter' || e.keyCode === 13)) {
        e.preventDefault();
        e.stopPropagation();
      }
    }, true);
  });
})();
