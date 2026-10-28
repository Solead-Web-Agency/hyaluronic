import type { StockStatus } from './cardModel';

// Promesse de livraison : UNE seule source, dérivée du statut de stock (lib/cardModel.ts).
// Les libellés vivent dans l'espace de traduction `offer`. Aucune autre promesse (J+1, « livré
// demain », « dispo immédiate »…) ne doit être codée ailleurs : tout passe par ici.
//   en stock      -> « Expédié aujourd'hui pour toute commande avant 15 h (jours ouvrés) · livraison 24-48 h en France »
//   sur commande  -> « Expédition sous 10 à 15 jours »
//   indisponible  -> rien
// Transporteurs réels : DHL Express et GLS.

export type DeliveryKey = 'shipInStock' | 'shipBackorder';

export function deliveryKey(status: StockStatus): DeliveryKey | null {
  if (status === 'in') return 'shipInStock';
  if (status === 'backorder') return 'shipBackorder';
  return null;
}

/** Libellé du statut de stock (clé `offer.*`). `short` : version badge (« Sur commande »). */
export function stockLabelKey(status: StockStatus, short = false): 'inStock' | 'backorder' | 'backorderShort' | 'unavailable' {
  if (status === 'in') return 'inStock';
  if (status === 'backorder') return short ? 'backorderShort' : 'backorder';
  return 'unavailable';
}
