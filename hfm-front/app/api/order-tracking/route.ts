import { NextRequest, NextResponse } from 'next/server';
import { bridgeGet } from '@/lib/ps';
import { NO_STORE } from '@/lib/cacheContract';
import { idLangFor } from '@/lib/i18n-config';
import { clientIp, hit, LIMITS } from '@/lib/rateLimit';

// Suivi de commande SANS compte (commande invité) : référence de commande + e-mail de la commande.
// Le bridge renvoie la même réponse `not_found` si la référence OU l'e-mail est faux (rien n'est
// révélé) ; la limite par IP borne l'énumération des références. Données personnelles : jamais de cache.
export const dynamic = 'force-dynamic';

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
// Référence PrestaShop : 9 lettres (ex. « XKBKNABJK »).
const REF_RE = /^[A-Z]{9}$/;

type TrackedOrder = {
  reference: string;
  date_add: string;
  status: string;
  shipped: boolean;
  delivered: boolean;
  total_paid_incl_tax: number;
  carrier: string;
  tracking_number: string;
  tracking_url: string;
  products: { name: string; quantity: number }[];
};

const notFound = () => NextResponse.json({ error: 'not_found' }, { status: 404, headers: NO_STORE });

export async function POST(req: NextRequest) {
  const wait = await hit('order-tracking', clientIp(req), LIMITS.orderTracking);
  if (wait > 0) {
    return NextResponse.json({ error: 'rate_limited', retry_after: wait }, { status: 429, headers: { ...NO_STORE, 'Retry-After': String(wait) } });
  }
  const body = await req.json().catch(() => ({}));
  const reference = String(body?.reference ?? '').trim().toUpperCase().replace(/^#/, '');
  const email = String(body?.email ?? '').trim().toLowerCase();
  const locale = typeof body?.locale === 'string' ? body.locale : 'fr';
  if (!reference || !EMAIL_RE.test(email)) {
    return NextResponse.json({ error: 'invalid_request' }, { status: 400, headers: NO_STORE });
  }
  // Format impossible : même réponse qu'une commande introuvable, sans appeler le back.
  if (!REF_RE.test(reference)) return notFound();

  let data: { ok?: boolean; order?: TrackedOrder; error?: string };
  try {
    data = await bridgeGet('customer', { action: 'track-order', reference, email, id_lang: idLangFor(locale) });
  } catch {
    return NextResponse.json({ error: 'unavailable' }, { status: 503, headers: NO_STORE });
  }
  if (!data?.ok || !data.order) return notFound();

  // Liste blanche des champs renvoyés au navigateur (aucune donnée personnelle).
  const o = data.order;
  const trackingUrl = /^https?:\/\//i.test(String(o.tracking_url || '')) ? String(o.tracking_url) : '';
  return NextResponse.json(
    {
      ok: true,
      order: {
        reference: String(o.reference || reference),
        date_add: String(o.date_add || ''),
        status: String(o.status || ''),
        shipped: !!o.shipped,
        delivered: !!o.delivered,
        total_paid_incl_tax: Number(o.total_paid_incl_tax || 0),
        carrier: String(o.carrier || ''),
        tracking_number: String(o.tracking_number || ''),
        tracking_url: trackingUrl,
        products: Array.isArray(o.products)
          ? o.products.map((p) => ({ name: String(p?.name || ''), quantity: Number(p?.quantity || 0) }))
          : [],
      },
    },
    { headers: NO_STORE },
  );
}
