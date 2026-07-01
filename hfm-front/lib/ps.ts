// Client serveur du module bridge PrestaShop "hfmstorefront".
// Les secrets restent ici (serveur), jamais envoyés au navigateur.
import { cachedJson, cacheKey } from './cache';

const BASE = process.env.PS_URL || 'http://localhost:8080';
const SECRET = process.env.HFM_BRIDGE_SECRET || '';

const DEFAULTS = { id_lang: 1, id_currency: 1 };

function bridgeUrl(controller: string, params: Record<string, string | number> = {}) {
  const qs = new URLSearchParams({ fc: 'module', module: 'hfmstorefront', controller });
  for (const [k, v] of Object.entries({ ...DEFAULTS, ...params })) qs.set(k, String(v));
  return `${BASE}/index.php?${qs.toString()}`;
}

export async function bridgeGet(controller: string, params: Record<string, string | number> = {}) {
  const r = await fetch(bridgeUrl(controller, params), {
    headers: { 'X-Storefront-Token': SECRET },
    cache: 'no-store',
  });
  return r.json();
}

export async function bridgePost(controller: string, body: Record<string, unknown>) {
  const r = await fetch(bridgeUrl(controller), {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-Storefront-Token': SECRET },
    body: JSON.stringify({ ...DEFAULTS, ...body }),
    cache: 'no-store',
  });
  return r.json();
}

// Lecture CACHEABLE : enveloppe bridgeGet dans le cache Redis/Data Cache.
// Le fetch interne reste no-store ; c'est cachedJson (Redis + revalidateTag) qui gère le cache.
export async function bridgeGetCached(
  controller: string,
  params: Record<string, string | number> = {},
  opts: { ttl: number; tags: string[] },
) {
  const merged = { ...DEFAULTS, ...params };
  const key = cacheKey(controller, merged);
  return cachedJson(key, opts.ttl, opts.tags, () => bridgeGet(controller, params));
}

export type ProductCard = {
  id_product: number;
  name: string;
  reference: string;
  link_rewrite: string;
  brand: string | null;
  price_incl_tax: number;
  price_excl_tax: number;
  image: string | null;
  quantity: number;
  available: boolean;
  rpps_required: boolean;
};
