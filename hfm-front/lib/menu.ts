// Méga-menu éditorial (taxonomy?action=menu) : règles d'affichage PARTAGÉES par l'en-tête, le pied
// de page, les pages rubrique et les filtres des listings (module sans dépendance serveur).
//
// Racines figées côté bridge : #30 Marques, #301 Catalogue (typologies), #302 Par zone, #303 Par effet.

export const ROOT_BRANDS = 30;
export const ROOT_TYPOLOGIES = 301;
export const ROOT_ZONES = 302;
export const ROOT_EFFECTS = 303;

export type MenuItem = { id_category: number; id_parent?: number; name: string; link_rewrite: string; nb_products: number };
export type MenuNode = MenuItem & { children?: MenuItem[] };

// Clé de comparaison d'un nom (accents, casse, ponctuation ignorés) : « MAILI by Sinclair Pharma »
// présent deux fois, « L'Esthétic Paris » vs fabricant « LESTHETIC PARIS ».
export function nameKey(s: string): string {
  return (s || '')
    .normalize('NFD')
    .replace(/[̀-ͯ]/g, '')
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '');
}

/**
 * Menu « montrable » : une entrée sans aucun produit actif est MASQUÉE (jamais de lien vers une
 * page vide ou une recherche à 0 résultat) et les doublons de nom sont fusionnés (on garde celle
 * qui a le plus de produits). Une racine sans enfant visible ni produit propre disparaît aussi.
 */
export function visibleMenu<T extends MenuNode>(menu: T[]): T[] {
  const out: T[] = [];
  for (const root of menu ?? []) {
    const seen = new Map<string, MenuItem>();
    for (const c of root.children ?? []) {
      if (!(c.nb_products > 0)) continue;
      const k = nameKey(c.name);
      const prev = seen.get(k);
      if (!prev || c.nb_products > prev.nb_products) seen.set(k, c);
    }
    const kept = (root.children ?? []).filter((c) => seen.get(nameKey(c.name)) === c);
    if (!kept.length && !(root.nb_products > 0)) continue;
    out.push({ ...root, children: kept });
  }
  return out;
}

// Lien d'une catégorie du menu : sa vraie page catégorie (URL « parité prod », filtres + tri).
// Repli sur l'id (le catalogue redirige alors vers le slug de la langue).
export function categoryHref(c: { id_category: number; link_rewrite?: string | null }): string {
  return c.link_rewrite ? `/${c.link_rewrite}` : `/catalogue?category=${c.id_category}`;
}

// Lien d'une racine : Marques -> index des marques ; Catalogue -> catalogue complet ;
// Par zone / Par effet -> page rubrique (liste de ses sous-catégories avec leurs comptes).
export function rootHref(c: { id_category: number; link_rewrite?: string | null }): string {
  if (c.id_category === ROOT_BRANDS) return '/marque';
  if (c.id_category === ROOT_TYPOLOGIES) return '/catalogue';
  return categoryHref(c);
}
