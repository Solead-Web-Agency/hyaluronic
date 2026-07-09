import type { ProductCard } from './ps';

// Forme attendue par le composant ProductCard de la maquette.
// On la dérive des produits live PrestaShop (pas de rating/badge inventés).
export type Card = {
  id: number;
  brand: string | null;
  name: string;
  img: string | null;
  ht: number;
  ttc: number;
  // 'backorder' = épuisé mais commandable (précommande) ; 'out' = non commandable.
  stock: 'in' | 'backorder' | 'out';
  link_rewrite: string;
  category: string | null;
  reference: string;
  rpps_required: boolean;
};

export function toCard(p: ProductCard): Card {
  return {
    id: p.id_product,
    brand: p.brand,
    name: p.name,
    img: p.image,
    ht: p.price_excl_tax,
    ttc: p.price_incl_tax,
    stock: p.availability === 'in_stock' ? 'in'
      : p.availability === 'backorder' ? 'backorder'
      : p.availability === 'unavailable' ? 'out'
      : p.available ? 'in' : 'out', // anciens payloads sans le champ availability
    link_rewrite: p.link_rewrite,
    category: p.category ?? null,
    reference: p.reference,
    rpps_required: p.rpps_required ?? false,
  };
}

// URL « parité prod » d'une fiche produit : /{categorie}/{slug}.
// Repli sur /produit/{id} (qui redirige 301) si la catégorie ou le slug manque.
export function productHref(c: { id: number; link_rewrite?: string | null; category?: string | null }): string {
  if (c.category && c.link_rewrite) {
    return `/${c.category}/${c.link_rewrite}`;
  }
  return `/produit/${c.id}`;
}

export function fmt(n: number): string {
  return (Number(n) || 0).toFixed(2).replace('.', ',');
}
