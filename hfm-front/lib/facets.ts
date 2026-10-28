// Facettes des listings (pages catégorie, catalogue, marque) — SERVEUR uniquement.
//
// Le bridge ne renvoie, pour une carte produit, ni sa marque éditoriale ni ses zones/effets :
// `brand` = fabricant PS, absent sur 260 des 575 produits actifs (Dives, Innoaesthetics, Me Line…).
// On reconstruit donc l'appartenance « produit -> catégories éditoriales » à partir des listes
// produits des sous-catégories du méga-menu (typologies, zones, effets, marques), une fois, puis
// on la met en cache (index compact, indépendant de la langue). Si le calcul n'est pas prêt dans
// le temps imparti, le listing s'affiche quand même (filtres réduits) et l'index se termine en
// arrière-plan pour les requêtes suivantes.
// TODO bridge : exposer ces ids directement sur les cartes produit rendrait ce module inutile.
import { bridgeGet, bridgeGetCached, type ProductCard } from './ps';
import { cachedJson } from './cache';
import { CACHE_TAGS, CACHE_TTL } from './cacheContract';
import {
  ROOT_BRANDS, ROOT_EFFECTS, ROOT_TYPOLOGIES, ROOT_ZONES,
  categoryHref, nameKey, visibleMenu, type MenuNode,
} from './menu';

export type FacetGroup = 'category' | 'zone' | 'effect' | 'brand';
export type FacetOption = { key: string; name: string; group: FacetGroup };
export type ListingFacets = {
  options: FacetOption[];
  // id_product -> clés d'options (« c311 », « c331 », « m:needleconcept »…)
  membership: Record<number, string[]>;
};

const GROUP_OF_ROOT: Record<number, FacetGroup> = {
  [ROOT_TYPOLOGIES]: 'category',
  [ROOT_ZONES]: 'zone',
  [ROOT_EFFECTS]: 'effect',
  [ROOT_BRANDS]: 'brand',
};

const MEMBERSHIP_TTL_S = 3 * 3600;
const MEMORY_TTL_MS = 10 * 60 * 1000;
const WAIT_MS = 2500;
const CONCURRENCY = 6;

type Membership = Record<string, number[]>; // id_category -> ids produits

let memo: { at: number; value: Membership } | null = null;
let inflight: Promise<Membership> | null = null;

export async function fetchMenu(idLang: number): Promise<MenuNode[]> {
  const d = await bridgeGetCached(
    'taxonomy',
    { action: 'menu', id_lang: idLang },
    { ttl: CACHE_TTL.taxonomy, tags: [CACHE_TAGS.taxonomy] },
  );
  return ((d as { menu?: MenuNode[] })?.menu ?? []) as MenuNode[];
}

async function computeMembership(catIds: number[]): Promise<Membership> {
  const out: Membership = {};
  let i = 0;
  const worker = async () => {
    while (i < catIds.length) {
      const id = catIds[i++];
      // Appel NEUTRE (ni pays ni client) : l'appartenance ne dépend pas du visiteur, et on évite
      // de lire les en-têtes de requête (le calcul peut se poursuivre après la réponse).
      const d = await bridgeGet('products', { id_category: id, limit: 1000, page: 1, order: 'position', country: '', id_customer: 0 });
      out[String(id)] = (((d as { products?: ProductCard[] }).products) ?? []).map((p) => p.id_product);
    }
  };
  await Promise.all(Array.from({ length: Math.min(CONCURRENCY, catIds.length) }, worker));
  return out;
}

function loadMembership(catIds: number[]): Promise<Membership> {
  if (memo && Date.now() - memo.at < MEMORY_TTL_MS) return Promise.resolve(memo.value);
  if (!inflight) {
    const key = `hfm:facets:membership:${catIds.join(',')}`;
    inflight = cachedJson(key, MEMBERSHIP_TTL_S, [CACHE_TAGS.taxonomy], () => computeMembership(catIds))
      .then((value) => {
        memo = { at: Date.now(), value };
        return value;
      })
      .finally(() => {
        inflight = null;
      });
  }
  return inflight;
}

async function withDeadline<T>(p: Promise<T>, ms: number): Promise<T | null> {
  let timer: ReturnType<typeof setTimeout> | undefined;
  const timeout = new Promise<null>((resolve) => {
    timer = setTimeout(() => resolve(null), ms);
  });
  try {
    return await Promise.race([p.catch(() => null), timeout]);
  } finally {
    if (timer) clearTimeout(timer);
  }
}

/**
 * Facettes d'un listing : options (noms du menu, dans la langue) + appartenance des produits
 * listés. Marque = marque éditoriale du menu ; à défaut, le fabricant de la carte (rapproché d'une
 * marque du menu par son nom quand c'est la même maison).
 */
export async function listingFacets(products: ProductCard[], menuRaw: MenuNode[]): Promise<ListingFacets> {
  const menu = visibleMenu(menuRaw);
  const options: FacetOption[] = [];
  const groupOf = new Map<number, FacetGroup>();
  const brandByKey: { key: string; id: number }[] = [];
  for (const root of menu) {
    const group = GROUP_OF_ROOT[root.id_category];
    if (!group) continue;
    for (const c of root.children ?? []) {
      groupOf.set(c.id_category, group);
      options.push({ key: `c${c.id_category}`, name: c.name.trim(), group });
      if (group === 'brand') brandByKey.push({ key: nameKey(c.name), id: c.id_category });
    }
  }

  const membershipByCat = (await withDeadline(loadMembership([...groupOf.keys()].sort((a, b) => a - b)), WAIT_MS)) ?? {};
  const byProduct = new Map<number, Set<string>>();
  for (const [cat, ids] of Object.entries(membershipByCat)) {
    if (!groupOf.has(Number(cat))) continue;
    for (const id of ids) {
      if (!byProduct.has(id)) byProduct.set(id, new Set());
      byProduct.get(id)!.add(`c${cat}`);
    }
  }

  const manuOptions = new Map<string, string>();
  const membership: Record<number, string[]> = {};
  for (const p of products) {
    const keys = new Set(byProduct.get(p.id_product) ?? []);
    const hasBrand = [...keys].some((k) => groupOf.get(Number(k.slice(1))) === 'brand');
    const manu = (p.brand ?? '').trim();
    if (!hasBrand && manu) {
      const mk = nameKey(manu);
      const match = mk ? brandByKey.find((b) => b.key.startsWith(mk) || mk.startsWith(b.key)) : undefined;
      if (match) keys.add(`c${match.id}`);
      else if (mk) {
        keys.add(`m:${mk}`);
        if (!manuOptions.has(mk)) manuOptions.set(mk, manu);
      }
    }
    if (keys.size) membership[p.id_product] = [...keys];
  }
  for (const [mk, name] of manuOptions) options.push({ key: `m:${mk}`, name, group: 'brand' });

  return { options, membership };
}

// Toutes les cartes d'un listing (le bridge plafonne à 1000 ; le catalogue complet en compte ~575).
export async function fetchAllProducts(params: Record<string, string | number>, idLang: number): Promise<{ products: ProductCard[]; total: number }> {
  const d = await bridgeGetCached(
    'products',
    { ...params, limit: 1000, page: 1, order: 'position', id_lang: idLang },
    { ttl: CACHE_TTL.products, tags: [CACHE_TAGS.products] },
  ); // pas de .catch : une panne remonte en 5xx plutôt qu'une page vide indexée
  const products = ((d as { products?: ProductCard[] }).products) ?? [];
  return { products, total: Number((d as { total?: number }).total ?? products.length) };
}

export { categoryHref };
