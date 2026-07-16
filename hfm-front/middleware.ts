import createMiddleware from 'next-intl/middleware';
import { NextRequest, NextResponse } from 'next/server';
import { routing } from './i18n/routing';

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

export default function middleware(req: NextRequest) {
  return legacyRedirect(req) ?? intl(req);
}

export const config = {
  // On exclut /api, /_next, /_vercel et tous les fichiers statiques (avec extension).
  // Les routes API restent donc non préfixées, accessibles sur /api/*.
  matcher: ['/((?!api|_next|_vercel|.*\\..*).*)'],
};
