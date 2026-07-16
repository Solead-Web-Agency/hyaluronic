// Helpers dataLayer / GTM (client). GTM + Consent Mode v2 sont injectés dans le layout.
// Les events e-commerce suivent le schéma GA4 standard (view_item, add_to_cart, purchase),
// ceux qu'attendent GA4 + la plupart des configs GTM (conversions Ads, remarketing).
type DL = Record<string, unknown>;

function push(obj: DL): void {
  if (typeof window === 'undefined') return;
  const w = window as unknown as { dataLayer?: DL[] };
  w.dataLayer = w.dataLayer || [];
  w.dataLayer.push(obj);
}

export type EcItem = { id: number; name?: string; price?: number; brand?: string | null; category?: string | null; quantity?: number };

function toItem(i: EcItem) {
  const it: DL = { item_id: String(i.id) };
  if (i.name) it.item_name = i.name;
  if (typeof i.price === 'number') it.price = i.price;
  if (i.brand) it.item_brand = i.brand;
  if (i.category) it.item_category = i.category;
  it.quantity = i.quantity ?? 1;
  return it;
}

export function trackViewItem(p: EcItem): void {
  push({ ecommerce: null });
  push({ event: 'view_item', ecommerce: { currency: 'EUR', value: p.price ?? 0, items: [toItem({ ...p, quantity: 1 })] } });
}

export function trackAddToCart(p: EcItem): void {
  const qty = p.quantity ?? 1;
  push({ ecommerce: null });
  push({ event: 'add_to_cart', ecommerce: { currency: 'EUR', value: (p.price ?? 0) * qty, items: [toItem(p)] } });
}

export function trackPurchase(o: { reference: string; value: number; items?: EcItem[] }): void {
  push({ ecommerce: null });
  push({
    event: 'purchase',
    ecommerce: {
      transaction_id: o.reference,
      currency: 'EUR',
      value: o.value,
      items: (o.items ?? []).map(toItem),
    },
  });
}

// --- Google Ads (parité ancien site) ---
// L'ancien site chargeait gtag EN DIRECT pour Google Ads (modules `gadwordstracking` conversion +
// `gremarketing`), EN PLUS du conteneur GTM. On reproduit ce câblage à l'identique pour ne pas
// dépendre du contenu du conteneur GTM (conversions Ads + audiences de remarketing).
// Valeurs reprises telles quelles de la config PrestaShop migrée (GACT_CONVERSION_ID /
// GACT_CONVERSION_LABEL / GR_REMARKETING_ID). Ce sont des identifiants PUBLICS (visibles dans le
// source de n'importe quelle page) — surchargeables par env si la config Ads change.
export const ADS_ID = process.env.NEXT_PUBLIC_ADS_ID ?? 'AW-527878129';
export const ADS_CONVERSION_LABEL = process.env.NEXT_PUBLIC_ADS_LABEL ?? '1HwQCIPdwokCEPGP2_sB';

/**
 * Conversion Google Ads à la confirmation de commande (équivalent du module gadwordstracking).
 * Le remarketing de base est assuré par le gtag('config', ADS_ID) posé dans le layout
 * (l'ancien site avait GR_REMARKETING_DYNAMIC=0 -> pas de remarketing dynamique à reproduire).
 * Respecte le Consent Mode v2 : gtag n'envoie rien tant qu'ad_storage est refusé.
 *
 * PARITÉ STRICTE avec l'ancien module (vérifiée sur son source, modules/gadwordstracking) :
 *  - `transaction_id` = id_order NUMÉRIQUE (et non la référence) — hook-display_class.php:122 ;
 *  - pas de conversion si la valeur est vide/nulle — garde `!empty($fTotalPaid)` du header.tpl ;
 *  - l'appelant ne doit tirer que sur une commande ENCAISSÉE (garde `$oOrder->valid`).
 */
export function trackAdsConversion(o: { transactionId: string | number; value: number }): void {
  if (typeof window === 'undefined') return;
  const w = window as unknown as { gtag?: (...a: unknown[]) => void };
  if (typeof w.gtag !== 'function') return;
  if (!(o.value > 0)) return;
  w.gtag('event', 'conversion', {
    send_to: `${ADS_ID}/${ADS_CONVERSION_LABEL}`,
    value: o.value,
    currency: 'EUR',
    transaction_id: String(o.transactionId),
  });
}

// Consent Mode v2 : met à jour le consentement (bandeau CMP) + notifie GTM.
export function updateConsent(granted: boolean): void {
  if (typeof window === 'undefined') return;
  const w = window as unknown as { gtag?: (...a: unknown[]) => void };
  const v = granted ? 'granted' : 'denied';
  if (typeof w.gtag === 'function') {
    w.gtag('consent', 'update', {
      ad_storage: v,
      ad_user_data: v,
      ad_personalization: v,
      analytics_storage: v,
    });
  }
  push({ event: granted ? 'hfm_consent_granted' : 'hfm_consent_denied' });
}
