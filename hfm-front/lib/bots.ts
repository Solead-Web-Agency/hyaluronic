// Robots de Google (Googlebot, AdsBot, Merchant Center…) : comportement de l'ANCIEN site repris à la
// demande du client (18/11/2026, modules pbc et groupinc, fonction botDetected()). Pour eux, le prix
// n'est pas géolocalisé : pays = celui du lien (?pays= des flux) sinon celui de la langue de l'URL
// (/fr/ -> FR, /en/ -> GB, /ja/ -> JP…), et la fiche annonce le prix HORS TAXES, comme les flux
// Google Shopping (prix HT). Sans ça, Googlebot (qui explore depuis les États-Unis) voyait le prix
// majoré « US » (+30 %) sur toutes les fiches. Choix assumé par le client malgré le risque « cloaking ».
import { localeMeta, localeToIdLang, type Locale } from './i18n-config';

const GOOGLE_BOT = /googlebot|adsbot-google|apis-google|mediapartners-google|feedfetcher-google|google web preview|google-read-aloud|duplexweb-google|google favicon|google/i;

export function isGoogleBot(userAgent: string | null | undefined): boolean {
  return GOOGLE_BOT.test(userAgent || '');
}

/** Pays « de l'URL » pour un robot Google : celui de la langue (drapeau du sélecteur de langue). */
export function botCountryForIdLang(idLang: unknown): string | null {
  const id = Number(idLang);
  const locale = (Object.keys(localeToIdLang) as Locale[]).find((l) => localeToIdLang[l] === id);
  const c = locale ? localeMeta[locale]?.country : '';
  return c && /^[a-z]{2}$/i.test(c) ? c.toUpperCase() : null;
}
