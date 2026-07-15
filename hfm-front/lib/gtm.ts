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
