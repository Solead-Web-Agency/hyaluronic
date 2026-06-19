{* Custom 2026-06-06 : popup promo -5EUR — design ORIGINAL Carts Guru rapatrie en local (contrat resilie).
   - HTML d'origine par langue dans /themes/warehouse/assets/promo/popup-XX.html (charge a la demande, zero poids initial)
   - Trigger : 16s (comme l'original) ou intention de sortie ; 1x/visiteur/30j ; jamais sur le tunnel de commande
   - Formulaire branche sur ps_emailsubscription (newsletter PrestaShop) ; ecran MERCI affiche le code BIENVENUE5
   - Le code BIENVENUE5 = regle panier #33524 (-5EUR TTC des 100EUR, 1x/client) *}
{assign var="hfmPromoExcluded" value=['cart','order','checkout','orderconfirmation','order-confirmation']}
{if !in_array($page.page_name, $hfmPromoExcluded)}
<script>
var hfmPromoCfg = {ldelim}
  lang: '{$language.iso_code|escape:'javascript'}',
  base: '{$urls.base_url|escape:'javascript'}',
  asset: '{$urls.theme_assets|escape:'javascript'}promo/popup-',
  txtThanks: {ldelim}fr:'MERCI POUR VOTRE INSCRIPTION !', en:'THANK YOU FOR REGISTERING!', de:'DANKE FÜR IHRE ANMELDUNG!', it:'GRAZIE PER LA REGISTRAZIONE!', es:'¡GRACIAS POR INSCRIBIRSE!'{rdelim},
  txtCode: {ldelim}fr:'Voici votre code promo :', en:'Here is your promo code:', de:'Hier ist Ihr Gutscheincode:', it:'Ecco il tuo codice promozionale:', es:'Aquí está su código promocional:'{rdelim},
  code: 'BIENVENUE5'
{rdelim};
</script>
{literal}
<script>
(function() {
  var KEY = 'hfmPromo5Seen', TTL = 30 * 86400000, shown = false;
  try {
    var seen = parseInt(localStorage.getItem(KEY) || '0', 10);
    if (seen && (Date.now() - seen) < TTL) { return; }
  } catch (e) { return; }
  var iso = ['fr','en','de','it','es'].indexOf(hfmPromoCfg.lang) !== -1 ? hfmPromoCfg.lang : 'en';

  function show() {
    if (shown) { return; }
    shown = true;
    try { localStorage.setItem(KEY, String(Date.now())); } catch (e) {}
    fetch(hfmPromoCfg.asset + iso + '.html').then(function(r) { return r.ok ? r.text() : null; }).then(function(html) {
      if (!html) { return; }
      /* le HTML Unlayer etait concu pour un iframe : on neutralise les hauteurs plein ecran */
      html = html.replace(/min-height:\s*100vh/gi, 'min-height:0');
      var ov = document.createElement('div');
      ov.id = 'hfm-cg-overlay';
      ov.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:99990;display:flex;align-items:center;justify-content:center;padding:15px;overflow:auto';
      var box = document.createElement('div');
      box.style.cssText = 'position:relative;width:400px;max-width:100%;max-height:95vh;overflow:auto;border-radius:6px;box-shadow:0 10px 50px rgba(0,0,0,.35)';
      box.innerHTML = html;
      var x = document.createElement('div');
      x.setAttribute('aria-label', 'Close');
      x.style.cssText = 'position:absolute;top:6px;right:6px;width:22px;height:22px;background:rgba(0,0,0,.55);color:#fff;border-radius:50%;text-align:center;line-height:22px;font-size:14px;cursor:pointer;z-index:2';
      x.textContent = '×';
      box.appendChild(x);
      ov.appendChild(box);
      document.body.appendChild(ov);
      function close() { ov.remove(); }
      x.addEventListener('click', close);
      ov.addEventListener('click', function(e) { if (e.target === ov) { close(); } });
      var form = box.querySelector('form') || box;
      box.addEventListener('submit', function(e) {
        e.preventDefault();
        var emailEl = box.querySelector('input[name="email"]');
        var email = emailEl ? emailEl.value.trim() : '';
        if (!email || email.indexOf('@') < 1) { if (emailEl) { emailEl.style.borderColor = 'red'; } return; }
        var fnEl = box.querySelector('input[name="first_name"]');
        var fd = new FormData();
        fd.append('email', email);
        fd.append('first_name', fnEl ? fnEl.value.trim() : '');
        fd.append('lang', iso);
        // inscrit à la newsletter + envoie l'email de bienvenue avec le code dans la bonne langue
        fetch('/promo-signup.php', { method: 'POST', body: fd, credentials: 'same-origin' }).catch(function() {});
        box.innerHTML = '<div style="background:#fff url(/img/cms/promo/popup-bg.png) center/cover;padding:48px 28px;text-align:center;border-radius:6px">' +
          '<p style="font-size:20px;font-weight:700;color:#383838;margin:0 0 14px">' + hfmPromoCfg.txtThanks[iso] + '</p>' +
          '<p style="font-size:14px;color:#555;margin:0 0 10px">' + hfmPromoCfg.txtCode[iso] + '</p>' +
          '<p style="border:2px dashed #94c55d;background:#fff;display:inline-block;padding:10px 22px;font-weight:700;letter-spacing:2px;font-size:18px;color:#383838;user-select:all">' + hfmPromoCfg.code + '</p></div>';
        box.appendChild(x);
        setTimeout(close, 8000);
      });
    }).catch(function() {});
  }
  window.addEventListener('load', function() { setTimeout(show, 16000); });
  document.addEventListener('mouseout', function(e) { if (!e.relatedTarget && e.clientY <= 0) { show(); } });
})();
</script>
{/literal}
{/if}
