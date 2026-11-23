// Client serveur du module bridge PrestaShop "hfmstorefront".
// Les secrets restent ici (serveur), jamais envoyés au navigateur.
import { cachedJson, cacheKey } from './cache';
import { headers, cookies } from 'next/headers';
import { unstable_rethrow } from 'next/navigation';
import { getSessionUser } from './session';
import { visitorCountryFrom } from './geoip';
import { botCountryForIdLang, isGoogleBot } from './bots';

const BASE = process.env.PS_URL || 'http://localhost:8080';
const SECRET = process.env.HFM_BRIDGE_SECRET || '';

const DEFAULTS = { id_lang: 1, id_currency: 1 };

// Délais d'attente vers le bridge PHP (mutualisé) : sans eux, un back LENT (pas mort — le cas le
// plus fréquent d'un mutualisé saturé) empile les fonctions Vercel jusqu'à la limite plateforme.
// Lectures = rapides -> timeout court, une panne remonte proprement en 5xx (error.tsx). Mutations
// (surtout la création de commande + génération de facture) = légitimement plus lentes -> délai
// généreux pour NE JAMAIS couper une commande en cours (un retour PSP tronqué = « client débité,
// aucune commande »). L'idempotence du bridge (Order::getIdByCartId) couvre un éventuel rejeu.
const READ_TIMEOUT_MS = 8000;
const WRITE_TIMEOUT_MS = 25000;

// ---------- Pays du visiteur (règles de prix / produits interdits par pays) ----------
// Le bridge applique les règles du pays de LIVRAISON quand le panier en a une ; sinon celles du
// pays du VISITEUR (géolocalisation Vercel). On ne transmet ce pays QUE s'il a des règles
// (liste servie par le bridge) : tous les autres visiteurs partagent le même cache.
const COUNTRY_CONTROLLERS = new Set(['products', 'cart', 'checkout', 'wishlist']);
const RULE_COUNTRIES_TTL_MS = 10 * 60 * 1000;
let ruleCountries: { list: string[]; groups: number[]; blocks: string[]; at: number } | null = null;

// Lectures de taxonomie dont les comptes de produits dépendent du pays (23/11/2026) : une marque ou
// une catégorie dont TOUS les produits sont bloqués dans le pays du visiteur disparaît du menu, de
// l'index des marques et des filtres, et sa page répond 404. Le pays n'est transmis que s'il a des
// blocages (liste servie par le bridge) : tous les autres visiteurs partagent le même cache.
const COUNTRY_TAXONOMY_ACTIONS = new Set(['menu', 'category', 'categories', 'manufacturers']);

async function ruleCountryList(): Promise<string[]> {
  return (await ruleInfo()).list;
}

async function ruleInfo(): Promise<{ list: string[]; groups: number[]; blocks: string[] }> {
  if (ruleCountries && Date.now() - ruleCountries.at < RULE_COUNTRIES_TTL_MS) return ruleCountries;
  let list: string[] = ruleCountries?.list ?? [];
  let groups: number[] = ruleCountries?.groups ?? [];
  let blocks: string[] = ruleCountries?.blocks ?? [];
  try {
    const r = await fetch(bridgeUrl('taxonomy', { action: 'rule-countries' }), {
      headers: { 'X-Storefront-Token': SECRET },
      cache: 'no-store',
      signal: AbortSignal.timeout(READ_TIMEOUT_MS),
    });
    const d = r.ok ? await r.json() : null;
    if (Array.isArray(d?.countries)) list = d.countries.map((c: unknown) => String(c).toUpperCase());
    if (Array.isArray(d?.groups)) groups = d.groups.map((g: unknown) => Number(g)).filter((g: number) => g > 0);
    if (Array.isArray(d?.block_countries)) blocks = d.block_countries.map((c: unknown) => String(c).toUpperCase());
  } catch (e) {
    unstable_rethrow(e);
  }
  ruleCountries = { list, groups, blocks, at: Date.now() };
  return ruleCountries;
}

// Prix par GROUPE client dans le catalogue : on transmet le client connecté au bridge seulement si
// son groupe a un tarif particulier (B2B exonéré de TVA, réductions…). Le cache Redis est alors
// propre à ce client ; tous les autres visiteurs partagent le cache public.
const GROUP_CONTROLLERS = new Set(['products', 'wishlist']);
async function withGroup<T extends Record<string, unknown>>(controller: string, params: T): Promise<T> {
  if (!GROUP_CONTROLLERS.has(controller) || 'id_customer' in params) return params;
  let user = null;
  try {
    user = await getSessionUser();
  } catch (e) {
    unstable_rethrow(e);
    return params;
  }
  if (!user?.id_group) return params;
  const { groups } = await ruleInfo();
  if (!groups.includes(user.id_group)) return params;
  return { ...params, id_customer: user.id_customer };
}

async function visitorCountry(): Promise<string | null> {
  try {
    const h = await headers();
    // En-tête de l'hébergeur (Vercel) s'il existe, sinon recherche par adresse IP (lib/geoip.ts).
    let c: string | null | undefined = visitorCountryFrom(h).country;
    // En local (ni en-tête ni adresse publique) : cookie `hfm_geo=US` pour tester les règles.
    if (!c && process.env.NODE_ENV !== 'production') c = (await cookies()).get('hfm_geo')?.value ?? null;
    return c && /^[A-Za-z]{2}$/.test(c) ? c.toUpperCase() : null;
  } catch (e) {
    // Hors requête (build) : pas de pays. Les erreurs de contrôle de flux Next remontent intactes.
    unstable_rethrow(e);
    return null;
  }
}

/** La requête en cours vient-elle d'un robot Google ? (faux hors requête, ex. pendant le build) */
export async function isGoogleBotRequest(): Promise<boolean> {
  try {
    return isGoogleBot((await headers()).get('user-agent'));
  } catch (e) {
    unstable_rethrow(e);
    return false;
  }
}

async function withCountry<T extends Record<string, unknown>>(controller: string, params: T): Promise<T> {
  params = await withGroup(controller, params);
  if (controller === 'taxonomy') return withBlockCountry(params);
  if (!COUNTRY_CONTROLLERS.has(controller) || 'country' in params) return params;
  // Pays du visiteur transmis dans TOUS les cas (et plus seulement pour les pays à règles de prix) :
  // le bridge calcule alors le TTC avec la TVA de son pays (DE 19 %, IT 22 %…), comme le panier une
  // fois l'adresse saisie et comme les flux Google Shopping. Même traitement pour tout le monde.
  // Robot Google : pays de la langue de la page, jamais sa géolocalisation (lib/bots.ts). Un ?pays= de
  // lien de flux, transmis par la page, est déjà dans `params` et a priorité (retour plus haut).
  const c = (await isGoogleBotRequest()) ? botCountryForIdLang(params.id_lang ?? DEFAULTS.id_lang) : await visitorCountry();
  if (!c) return params;
  return { ...params, country: c };
}

// Taxonomie : pays du visiteur (robot Google : pays de la langue de la page) transmis seulement
// s'il a des produits bloqués. `country` déjà présent (même vide = lecture neutre, ex. sitemap) : inchangé.
async function withBlockCountry<T extends Record<string, unknown>>(params: T): Promise<T> {
  if ('country' in params || !COUNTRY_TAXONOMY_ACTIONS.has(String(params.action ?? 'categories'))) return params;
  const { blocks } = await ruleInfo();
  if (!blocks.length) return params;
  const c = (await isGoogleBotRequest()) ? botCountryForIdLang(params.id_lang ?? DEFAULTS.id_lang) : await visitorCountry();
  if (!c || !blocks.includes(c)) return params;
  return { ...params, country: c };
}

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
  params = await withCountry(controller, params);
  const r = await fetch(bridgeUrl(controller, params), {
    headers: { 'X-Storefront-Token': SECRET },
    cache: 'no-store',
    // Timeout lecture : un back lent -> AbortError qui se propage -> 5xx (error.tsx), au lieu de
    // bloquer la fonction Vercel. N'interfère PAS avec les erreurs de contrôle-de-flux Next
    // (jetées par le rendu, pas par le fetch).
    signal: AbortSignal.timeout(READ_TIMEOUT_MS),
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
  body = await withCountry(controller, body);
  const r = await fetch(bridgeUrl(controller), {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-Storefront-Token': SECRET },
    body: JSON.stringify({ ...DEFAULTS, ...body }),
    cache: 'no-store',
    // Timeout écriture généreux : borne un vrai blocage sans jamais couper une création de
    // commande légitime (idempotence côté bridge en filet si rejeu).
    signal: AbortSignal.timeout(WRITE_TIMEOUT_MS),
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
  // Le pays (s'il a des règles) fait partie de la clé de cache : prix/produits différents.
  const p = await withCountry(controller, params);
  const merged = { ...DEFAULTS, ...p };
  const key = cacheKey(controller, merged);
  return cachedJson(key, opts.ttl, opts.tags, () => bridgeGet(controller, p));
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
  price_without_reduction_excl_tax?: number;
  image: string | null;
  quantity: number;
  available: boolean;
  availability?: 'in_stock' | 'backorder' | 'unavailable';
  rpps_required: boolean;
};
