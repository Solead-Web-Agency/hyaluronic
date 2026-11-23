'use client';

/**
 * Consentement cookies géré par CookieYes (18/11/2026) : le bandeau CookieYes est chargé par le
 * conteneur GTM de production et met lui-même à jour le Consent Mode v2 de Google. Le bandeau
 * maison a été retiré (il faisait doublon) ; ce module ne fait que LIRE le choix CookieYes.
 *
 * Cookie CookieYes : « cookieyes-consent=consentid:…,consent:yes,action:yes,necessary:yes,… »
 * (action:yes = le visiteur a cliqué) ; évènement « cookieyes_consent_update » émis sur document.
 */

const CKY_COOKIE = 'cookieyes-consent';
export const CKY_EVENT = 'cookieyes_consent_update';

/** Le visiteur a-t-il déjà fait son choix dans le bandeau CookieYes ? */
export function hasConsentChoice(): boolean {
  if (typeof document === 'undefined') return false;
  const raw = document.cookie.split('; ').find((c) => c.startsWith(`${CKY_COOKIE}=`));
  if (!raw) return false;
  let v = raw.slice(CKY_COOKIE.length + 1);
  try { v = decodeURIComponent(v); } catch { /* valeur brute */ }
  return /(^|,)action:yes(,|$)/.test(v);
}

/** Le bandeau CookieYes est-il affiché à l'écran ? */
export function consentBannerVisible(): boolean {
  if (typeof document === 'undefined') return false;
  const el = document.querySelector('.cky-consent-container') as HTMLElement | null;
  return !!el && !el.classList.contains('cky-hide') && el.offsetParent !== null;
}

/**
 * Appelle `cb` une fois le choix cookies fait, ou dès qu'il est clair qu'aucun bandeau ne
 * s'affichera (CookieYes bloqué par un bloqueur de publicité, GTM absent…) : on attend au plus
 * quelques secondes l'apparition du bandeau. Renvoie la fonction d'annulation.
 */
export function afterConsentChoice(cb: () => void): () => void {
  let done = false;
  const fire = () => {
    if (done) return;
    done = true;
    cleanup();
    cb();
  };
  const onUpdate = () => fire();
  const started = Date.now();
  const timer = window.setInterval(() => {
    if (hasConsentChoice()) return fire();
    // Pas de bandeau 4 s après l'arrivée : il ne viendra pas -> on n'attend pas indéfiniment.
    if (!consentBannerVisible() && Date.now() - started > 4000) fire();
  }, 500);
  function cleanup() {
    window.clearInterval(timer);
    document.removeEventListener(CKY_EVENT, onUpdate);
  }
  if (hasConsentChoice()) {
    window.setTimeout(fire, 0);
  } else {
    document.addEventListener(CKY_EVENT, onUpdate);
  }
  return () => {
    done = true;
    cleanup();
  };
}

/** Rouvre les préférences CookieYes (lien « Gérer les cookies » du pied de page). */
export function openConsentPreferences(): void {
  const w = window as unknown as { revisitCkyConsent?: () => void };
  if (typeof w.revisitCkyConsent === 'function') w.revisitCkyConsent();
}
