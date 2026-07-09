import { NextRequest } from 'next/server';

// Proxy des 9 feeds Google Merchant (module gmerchantcenter). On sert les URLs
// historiques /gmerchantcenter{token}.{variante}.shop1.xml (réécrites ici par next.config)
// en PASSTHROUGH du feed généré par le module -> iso garanti (mêmes prix pays, mêmes g:id,
// mêmes champs). Les <link> pointent déjà sur hyaluronicfillermarket.com (voulu).
//
// Origine configurable (bascule de domaine le jour J) : MERCHANT_FEED_ORIGIN.
const ORIGIN = (process.env.MERCHANT_FEED_ORIGIN || 'https://hyaluronicfillermarket.com').replace(/\/$/, '');
const TOKEN = process.env.MERCHANT_FEED_TOKEN || '12345678901234567890123456789012';

// Les 9 feeds exacts (langue[.pays][.devise]) — liste blanche : le proxy n'est PAS ouvert.
const VARIANTS = ['fr', 'en.us.EUR', 'en.ca', 'en.jp', 'de', 'it', 'es', 'es.mx.EUR', 'ja.jp'];
const ALLOWED = new Set(VARIANTS.map((v) => `gmerchantcenter${TOKEN}.${v}.shop1.xml`));

const UA = 'Mozilla/5.0 (compatible; HFMFeedProxy/1.0; +https://hyaluronicfillermarket.com)';

export async function GET(req: NextRequest) {
  // Nom du feed lu depuis le pathname d'origine (préservé par le rewrite next.config).
  const feed = decodeURIComponent(req.nextUrl.pathname).replace(/^\/+/, '');
  if (!ALLOWED.has(feed)) {
    return new Response('Not found', { status: 404 });
  }

  try {
    const upstream = await fetch(`${ORIGIN}/${feed}`, {
      headers: { 'User-Agent': UA, Accept: 'application/xml,text/xml,*/*' },
      // Cache côté Data Cache Next : le module régénère au plus toutes les quelques heures.
      next: { revalidate: 3600 },
    });
    if (!upstream.ok) {
      return new Response(`Feed source unavailable (${upstream.status})`, { status: 502 });
    }
    const xml = await upstream.text();
    return new Response(xml, {
      status: 200,
      headers: {
        'Content-Type': 'application/xml; charset=utf-8',
        // Edge CDN court + SWR : le feed reste dispo même pendant une revalidation.
        'Cache-Control': 'public, s-maxage=3600, stale-while-revalidate=86400',
      },
    });
  } catch {
    return new Response('Feed source error', { status: 502 });
  }
}
