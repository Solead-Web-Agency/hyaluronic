import { NextRequest, NextResponse } from 'next/server';
import { bridgePost } from '@/lib/ps';
import { getSessionUser } from '@/lib/session';
import { captureOrder } from '@/lib/paypal';
import { locales, defaultLocale } from '@/lib/i18n-config';

// PayPal renvoie le navigateur ici après approbation : ?token=<orderId>&PayerID=...
// On CAPTURE la commande, on vérifie COMPLETED, on crée la commande PS, puis on redirige.
const BASE = process.env.PUBLIC_BASE_URL || 'http://localhost:3000';

function localeFrom(customId: string, fallback: string): string {
  const m = /lang:([a-z]{2})/.exec(customId || '');
  const l = m?.[1];
  return l && (locales as readonly string[]).includes(l) ? l : fallback;
}

export async function GET(req: NextRequest) {
  const sp = req.nextUrl.searchParams;
  const orderId = sp.get('token');
  const cookieLocale = req.cookies.get('NEXT_LOCALE')?.value;
  let lang = cookieLocale && (locales as readonly string[]).includes(cookieLocale) ? cookieLocale : defaultLocale;

  const redirect = (qs: string) => NextResponse.redirect(`${BASE}/${lang}/checkout?${qs}`, { status: 303 });

  if (!orderId) return redirect('paypal_failed=1');

  const cap = await captureOrder(String(orderId)).catch(() => null);
  if (cap?.customId) lang = localeFrom(cap.customId, lang);
  if (!cap || !cap.ok || cap.status !== 'COMPLETED') return redirect('paypal_failed=1');

  const m = /cart:(\d+)/.exec(cap.customId || '');
  const id_cart = m ? Number(m[1]) : 0;
  const user = await getSessionUser();
  const order = await bridgePost('checkout', {
    action: 'order',
    id_cart,
    ...(user ? { id_customer: user.id_customer } : {}),
    paid: 1,
    payment_method: 'PayPal',
    transaction_id: cap.captureId || String(orderId),
  }).catch(() => null);

  if (!order?.ok) return redirect('paypal_failed=1');
  const qs = new URLSearchParams({
    paypal_paid: '1',
    ref: String(order.reference || ''),
    order: String(order.id_order || ''),
    total: String(order.total_paid || ''),
  });
  return redirect(qs.toString());
}
