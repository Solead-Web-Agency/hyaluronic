import { NextRequest } from 'next/server';
import { idLangFor } from '@/lib/i18n-config';

// Les 9 flux Google Merchant, servis aux URLs historiques /gmerchantcenter{token}.{variante}.shop1.xml
// (réécrites ici par next.config) -> rien à changer dans Merchant Center.
//
// Source par défaut : générés À LA VOLÉE par le module gmerchantcenter du back PrestaShop
// (point d'entrée gmerchantcenter.xml.php), adapté au headless (patch [HFM]) : liens vers ce front,
// images servies par le back, frais de port du transporteur le moins cher au tarif réel.
// Repli possible : MERCHANT_FEED_ORIGIN = URL d'un site qui publie déjà les fichiers (ancien site).
const ORIGIN = (process.env.MERCHANT_FEED_ORIGIN || '').replace(/\/$/, '');
const PS_URL = (process.env.PS_URL || 'http://localhost:8080').replace(/\/$/, '');
const TOKEN = process.env.MERCHANT_FEED_TOKEN || '12345678901234567890123456789012';

// Variante -> langue du flux, pays cible, devise (configuration GMC_CHECK_EXPORT du module).
const VARIANTS: Record<string, { lang: string; country: string; currency: string }> = {
  fr: { lang: 'fr', country: 'FR', currency: 'EUR' },
  'en.us.EUR': { lang: 'en', country: 'US', currency: 'EUR' },
  'en.ca': { lang: 'en', country: 'CA', currency: 'EUR' },
  'en.jp': { lang: 'en', country: 'JP', currency: 'EUR' },
  de: { lang: 'de', country: 'DE', currency: 'EUR' },
  it: { lang: 'it', country: 'IT', currency: 'EUR' },
  es: { lang: 'es', country: 'ES', currency: 'EUR' },
  'es.mx.EUR': { lang: 'es', country: 'MX', currency: 'EUR' },
  'ja.jp': { lang: 'ja', country: 'JP', currency: 'EUR' },
};

const UA = 'Mozilla/5.0 (compatible; HFMFeedProxy/1.0; +https://hyaluronicfillermarket.com)';

// La génération d'un flux prend 10 à 30 s côté PrestaShop.
export const maxDuration = 60;

export async function GET(req: NextRequest) {
  // Nom du feed lu depuis le pathname d'origine (préservé par le rewrite next.config).
  const feed = decodeURIComponent(req.nextUrl.pathname).replace(/^\/+/, '');
  const m = /^gmerchantcenter([^.]+)\.(.+)\.shop1\.xml$/.exec(feed);
  const variant = m && m[1] === TOKEN ? VARIANTS[m[2]] : undefined;
  if (!variant) {
    return new Response('Not found', { status: 404 });
  }

  const source = ORIGIN
    ? `${ORIGIN}/${feed}`
    : `${PS_URL}/gmerchantcenter.xml.php?${new URLSearchParams({
        id_shop: '1',
        id_lang: String(idLangFor(variant.lang)),
        country: variant.country,
        currency_iso: variant.currency,
        token: TOKEN,
      })}`;

  try {
    const upstream = await fetch(source, {
      headers: { 'User-Agent': UA, Accept: 'application/xml,text/xml,*/*' },
      // Data Cache Next : régénéré au plus une fois par heure et par flux.
      next: { revalidate: 3600 },
      signal: AbortSignal.timeout(55_000),
    });
    const xml = upstream.ok ? await upstream.text() : '';
    // Un flux valide commence par la déclaration XML / <rss> ; sinon (erreur PHP, jeton refusé)
    // on ne le publie pas : Merchant Center garderait sinon un flux vide.
    if (!upstream.ok || !/<rss[\s>]/.test(xml.slice(0, 2000))) {
      return new Response(`Feed source unavailable (${upstream.status})`, { status: 502 });
    }
    return new Response(xml, {
      status: 200,
      headers: {
        'Content-Type': 'application/xml; charset=utf-8',
        // Edge CDN + SWR : le flux reste servi pendant une régénération.
        'Cache-Control': 'public, s-maxage=3600, stale-while-revalidate=86400',
      },
    });
  } catch {
    return new Response('Feed source error', { status: 502 });
  }
}
