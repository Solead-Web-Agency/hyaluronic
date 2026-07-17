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

// Panne du bridge (réseau/HTTP/parse) — distincte d'une « ressource absente ». On l'expose pour
// que les rares appelants qui avalent les erreurs (`.catch(() => null)`) puissent, s'ils le
// souhaitent, ne re-neutraliser QUE ce cas et laisser une vraie panne remonter en 5xx.
export class BridgeError extends Error {
  status?: number;
  constructor(message: string, options?: { status?: number; cause?: unknown }) {
    super(message, options?.cause !== undefined ? { cause: options.cause } : undefined);
    this.name = 'BridgeError';
    this.status = options?.status;
  }
}

// IMPORTANT (robustesse SEO) : le bridge ne signale JAMAIS une ressource absente par un statut HTTP.
// Une ressource introuvable = HTTP 200 + corps `{ error: 'not_found' }` (l'appelant fait alors
// `data?.page ?? null` -> `notFound()` -> 404 légitime). Tout statut non-2xx (400 `server_error`
// renvoyé sur exception interne du bridge, 401, 405, 5xx…), toute erreur réseau, ou un corps 200
// non-JSON, sont donc des PANNES : on JETTE une erreur pour qu'elles remontent vers `error.tsx`
// (5xx) au lieu d'être dégradées silencieusement en `null` -> 404 (ce qui ferait désindexer une
// page saine par Google sur un simple hoquet transitoire).
export async function bridgeGet(controller: string, params: Record<string, string | number> = {}) {
  // On n'enveloppe PAS le fetch dans un try/catch : Next.js s'appuie sur des erreurs de
  // contrôle-de-flux jetées pendant le rendu (bail-out « dynamic server usage » d'un fetch
  // `no-store` en génération statique, `notFound()`, `redirect()`…) qui doivent remonter INTACTES
  // — les capturer casserait la détection statique/dynamique au build. Une vraie panne réseau
  // (connexion refusée, DNS, timeout) se propage déjà d'elle-même en erreur -> 5xx.
  const r = await fetch(bridgeUrl(controller, params), {
    headers: { 'X-Storefront-Token': SECRET },
    cache: 'no-store',
  });

  if (!r.ok) {
    // Non-2xx = panne (jamais une ressource absente, qui reste servie en 200 + { error: 'not_found' }).
    throw new BridgeError(`bridge ${controller} responded ${r.status}`, { status: r.status });
  }

  // 200 : corps JSON attendu. Un corps illisible (page d'erreur HTML d'un proxy, réponse tronquée)
  // fera jeter r.json() -> panne -> 5xx, comportement voulu. Une ressource absente reste un 200
  // JSON `{ error: 'not_found' }` que l'appelant convertit en `notFound()` (404 légitime).
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
  category: string | null;
  rating: { rate: number; count: number } | null;
  brand: string | null;
  price_incl_tax: number;
  price_excl_tax: number;
  image: string | null;
  quantity: number;
  available: boolean;
  availability?: 'in_stock' | 'backorder' | 'unavailable';
  rpps_required: boolean;
};
