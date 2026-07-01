// Contrat de cache partagé (aligné avec le module PS hfmstorefront).
// Vocabulaire de TAGS fermé + TTL par défaut. Ne pas ajouter de tag hors de cette liste.
export const CACHE_TAGS = {
  taxonomy: 'taxonomy', // menu, catégories, marques (manufacturers), pays
  products: 'products', // listes produits + fiche produit
  content: 'content', // pages CMS
} as const;

export const CACHE_TTL = {
  taxonomy: 3600,
  products: 300,
  content: 3600,
} as const;

export type CacheTag = keyof typeof CACHE_TAGS;

// Fenêtre max de cache CDN edge (Vercel). Le CDN basé sur `s-maxage` n'est PAS purgeable
// par purgeTags (qui ne touche que Redis + le Data Cache Next). On borne donc l'edge à une
// fenêtre courte : après une modif BO, l'edge se rafraîchit en ≤ CDN_MAX_SMAXAGE, tandis que
// Redis — lui purgé instantanément — assure la mise en cache sur toute la durée du TTL.
const CDN_MAX_SMAXAGE = 60;

// En-tête CDN edge (Vercel) : cache public borné + stale-while-revalidate = TTL applicatif.
// s-maxage court -> l'edge revalide vite (donc voit la purge Redis) ; SWR laisse servir un
// contenu périmé pendant la revalidation d'arrière-plan (jamais de latence perçue).
export function cdnCacheControl(ttl: number): string {
  const sMaxAge = Math.min(ttl, CDN_MAX_SMAXAGE);
  return `public, s-maxage=${sMaxAge}, stale-while-revalidate=${ttl}`;
}

// En-tête pour les routes NON cacheables (données par-user / mutations).
export const NO_STORE = { 'Cache-Control': 'no-store' } as const;
