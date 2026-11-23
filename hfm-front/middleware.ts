import createMiddleware from 'next-intl/middleware';
import { NextRequest, NextResponse } from 'next/server';
import { routing } from './i18n/routing';
import { LEGACY_PAGES } from './lib/legacyRedirects';

const intl = createMiddleware(routing);

// Redirections legacy PrestaShop par PARAMÈTRE d'id (formats potentiellement encore indexés par Google
// avant la bascule vers les URLs propres). On renvoie vers la route par id qui redirige ensuite (308)
// vers l'URL canonique. Les formats à extension (.html) sont gérés par next.config (le matcher
// ci-dessous exclut les chemins contenant un point).
function legacyRedirect(req: NextRequest): NextResponse | null {
  const { searchParams, pathname } = req.nextUrl;
  const seg = pathname.split('/')[1];
  const locale = (routing.locales as readonly string[]).includes(seg) ? seg : routing.defaultLocale;

  // Reprise d'une redirection 301 CUSTOM de l'ancien site (.htaccess) :
  //   RewriteRule "^(.*)dmae(.*)$" "/$1dma-e$2" [R=301,L,NC]
  // Les slugs « dmae » ont été renommés en « dma-e » et cette règle faisait le pont. Elle est
  // toujours active en prod (vérifié) et 30 URLs « dmae » figurent dans les anciens sitemaps
  // (7 produits actifs concernés) -> sans elle, ces URLs indexées tomberaient en 404.
  // Pas de boucle possible : « dma-e » ne contient plus « dmae ».
  if (/dmae/i.test(pathname)) {
    const url = req.nextUrl.clone();
    url.pathname = pathname.replace(/dmae/gi, 'dma-e');
    return NextResponse.redirect(url, 308);
  }

  // Pages techniques de l'ancien site (suivi de livraison, paniers enregistrés, slugs japonais).
  let decoded = pathname;
  try { decoded = decodeURIComponent(pathname); } catch { /* chemin déjà décodé */ }
  const page = LEGACY_PAGES[decoded.replace(/\/+$/, '')];
  if (page) {
    const [path, query] = page.split('?');
    const url = req.nextUrl.clone();
    url.pathname = path;
    url.search = query ? `?${query}` : '';
    return NextResponse.redirect(url, 308);
  }

  const idProduct = searchParams.get('id_product');
  if (idProduct && /^\d+$/.test(idProduct)) {
    const url = req.nextUrl.clone();
    url.pathname = `/${locale}/produit/${idProduct}`;
    url.search = '';
    return NextResponse.redirect(url, 308);
  }

  const idCategory = searchParams.get('id_category');
  if (idCategory && /^\d+$/.test(idCategory)) {
    const url = req.nextUrl.clone();
    url.pathname = `/${locale}/catalogue`;
    url.search = '';
    url.searchParams.set('category', idCategory);
    return NextResponse.redirect(url, 308);
  }

  return null;
}

// Images de l'ancien site (adresses « jolies » de PrestaShop, indexées par Google Images et Shopping) :
// /1082-home_default/stylage.jpg -> image réelle sur le back (/img/p/1/0/8/2/1082-home_default.jpg).
// Le back n'a pas la réécriture d'URL PrestaShop : on renvoie directement vers le fichier.
const IMG_ORIGIN = 'https://back.hyaluronicfillermarket.com';
function legacyImage(req: NextRequest): NextResponse | null {
  const p = req.nextUrl.pathname;
  let m = /^\/(\d+)-([a-z0-9_]+)\/[^/]+\.(?:jpe?g|png|webp|gif)$/i.exec(p);
  if (m) {
    return NextResponse.redirect(`${IMG_ORIGIN}/img/p/${m[1].split('').join('/')}/${m[1]}-${m[2]}.jpg`, 301);
  }
  m = /^\/c\/(\d+)-([a-z0-9_]+)\/[^/]+\.(?:jpe?g|png|webp|gif)$/i.exec(p);
  if (m) {
    return NextResponse.redirect(`${IMG_ORIGIN}/img/c/${m[1]}-${m[2]}.jpg`, 301);
  }
  if (p.startsWith('/img/')) {
    return NextResponse.redirect(`${IMG_ORIGIN}${p}`, 301);
  }
  return null;
}

export default function middleware(req: NextRequest) {
  const img = legacyImage(req);
  if (img) return img;
  // Webhook Viva déclaré chez Viva sur l'URL de l'ancien module PrestaShop
  // (/module/vivawalletsmartcheckout/webhook?environment=live) : réécrit (pas redirigé, Viva poste
  // en POST) vers la route webhook, sinon le préfixe de langue le casserait après la bascule DNS.
  if (req.nextUrl.pathname === '/module/vivawalletsmartcheckout/webhook') {
    const url = req.nextUrl.clone();
    url.pathname = '/api/payment/webhook';
    return NextResponse.rewrite(url);
  }
  // Retour du CLIENT après paiement Viva : la « source » du compte Viva peut encore pointer sur les
  // pages de l'ancien module (/module/vivawalletsmartcheckout/success ou /failure, avec ou sans
  // préfixe de langue, suivies de ?t=<transaction>&s=<ordre>). Sans cette réécriture : client débité
  // puis page 404. La route de retour revérifie la transaction, un échec y est traité comme tel.
  if (/^\/(?:[a-z]{2}(?:-[a-z]{2})?\/)?module\/vivawalletsmartcheckout\/(?:success|failure)\/?$/i.test(req.nextUrl.pathname)) {
    const url = req.nextUrl.clone();
    url.pathname = '/api/payment/return';
    return NextResponse.rewrite(url);
  }
  // Idem pour l'IPN Amazon Pay, déclarée chez Amazon sur /fr/module/amazonpay/ipn.
  if (/^\/(?:[a-z]{2}(?:-[a-z]{2})?\/)?module\/amazonpay\/ipn\/?$/i.test(req.nextUrl.pathname)) {
    const url = req.nextUrl.clone();
    url.pathname = '/api/payment/amazon/ipn';
    return NextResponse.rewrite(url);
  }
  return legacyRedirect(req) ?? intl(req);
}

export const config = {
  // On exclut /api, /_next, /_vercel et tous les fichiers statiques (avec extension).
  // Les routes API restent donc non préfixées, accessibles sur /api/*.
  matcher: [
    '/((?!api|_next|_vercel|.*\\..*).*)',
    // Images de l'ancien site (voir legacyImage) : seuls chemins « avec extension » traités ici.
    '/img/:path*',
    '/c/:path*',
    '/(\\d+-[a-zA-Z0-9_]+)/:file*',
  ],
};
