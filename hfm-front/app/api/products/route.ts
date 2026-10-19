import { NextRequest, NextResponse } from 'next/server';
import { bridgeGetCached } from '@/lib/ps';
import { CACHE_TAGS, CACHE_TTL, NO_STORE } from '@/lib/cacheContract';

// Proxy catalogue : transmet limit/page/order/id_category/id_manufacturer/q/filter/id_product au bridge PS.
// La réponse liste porte { products, total, page, limit } -> le front pagine (infinite scroll).
// CACHEABLE (public) : tag "products" (listes + fiche), TTL 300s.
export async function GET(req: NextRequest) {
  const sp = req.nextUrl.searchParams;
  const params: Record<string, string> = {};
  for (const k of ['limit', 'page', 'order', 'id_category', 'id_manufacturer', 'q', 'filter', 'id_product', 'related', 'ids', 'action', 'id_lang', 'id_currency']) {
    const v = sp.get(k);
    if (v) params[k] = v;
  }
  const data = await bridgeGetCached('products', params, {
    ttl: CACHE_TTL.products,
    tags: [CACHE_TAGS.products],
  });
  // PAS de cache CDN public : les prix et la liste dépendent du pays du visiteur et du groupe du
  // client (règles par pays / par groupe). Le CDN Vercel met en cache par URL seulement : il
  // servirait des prix d'un pays à un autre. Le cache Redis (clé incluant pays/groupe) protège le back.
  return NextResponse.json(data, { headers: NO_STORE });
}
