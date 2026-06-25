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
  stock: 'in' | 'out';
  link_rewrite: string;
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
    stock: p.available ? 'in' : 'out',
    link_rewrite: p.link_rewrite,
    reference: p.reference,
    rpps_required: p.rpps_required ?? false,
  };
}

export function fmt(n: number): string {
  return (Number(n) || 0).toFixed(2).replace('.', ',');
}
