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
  // Prix HT avant remise, seulement s'il y a une vraie remise (prix barré des promotions).
  htBefore: number | null;
  // Statut unique (voir stockStatus) : repris tel quel par carte, aperçu, fiche et comparateur.
  stock: StockStatus;
  link_rewrite: string;
  category: string | null;
  rating: { rate: number; count: number } | null;
  reference: string;
  rpps_required: boolean;
};

// ---------------------------------------------------------------------------------------------
// Statut de stock : calculé ICI et nulle part ailleurs (carte, aperçu rapide, fiche, comparateur).
//   in        -> « En stock », achat possible
//   backorder -> « Sur commande — expédition sous 10 à 15 jours », achat possible (le bridge
//                accepte les produits sur commande au panier)
//   out       -> « Indisponible », pas d'achat
// Source : le champ `availability` du bridge. Repli sur `available` pour les anciens payloads.
// ---------------------------------------------------------------------------------------------
export type StockStatus = 'in' | 'backorder' | 'out';

export function stockStatus(availability?: string | null, available?: boolean | null): StockStatus {
  if (availability === 'in_stock') return 'in';
  if (availability === 'backorder') return 'backorder';
  if (availability === 'unavailable') return 'out';
  return available ? 'in' : 'out';
}

/** Achat possible (bouton « Ajouter au panier » actif) pour ce statut. */
export function canBuyStatus(s: StockStatus): boolean {
  return s !== 'out';
}

/**
 * Statut d'une déclinaison : non vendable -> indisponible ; quantité > 0 -> en stock ;
 * sinon vendable sans stock -> sur commande.
 */
export function comboStockStatus(c: { available: boolean; quantity: number }): StockStatus {
  if (!c.available) return 'out';
  return c.quantity > 0 ? 'in' : 'backorder';
}

/**
 * Prix sans TVA facturée (pays hors UE, exonération) : TTC = HT. On affiche alors « hors TVA »
 * au lieu d'un « soit X € TTC » identique au HT.
 */
export function isVatFree(ht: number, ttc: number): boolean {
  return Math.abs((Number(ttc) || 0) - (Number(ht) || 0)) < 0.005;
}

/**
 * Référence affichable : la référence produit PS, sauf si c'est un code-barres (EAN/GTIN :
 * 8 à 14 chiffres, ex. « 9999900002676 ») saisi par défaut dans le champ référence -> rien.
 */
export function displayReference(ref?: string | null): string {
  const r = (ref ?? '').trim();
  if (!r || /^\d{8,14}$/.test(r)) return '';
  return r;
}

export function toCard(p: ProductCard): Card {
  return {
    id: p.id_product,
    brand: p.brand,
    name: p.name,
    img: p.image,
    ht: p.price_excl_tax,
    ttc: p.price_incl_tax,
    htBefore: (p.price_without_reduction_excl_tax ?? 0) > p.price_excl_tax + 0.009 ? p.price_without_reduction_excl_tax! : null,
    stock: stockStatus(p.availability, p.available),
    link_rewrite: p.link_rewrite,
    category: p.category ?? null,
    rating: p.rating ?? null,
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
