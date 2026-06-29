import { NextRequest, NextResponse } from 'next/server';
import { bridgePost } from '@/lib/ps';
import { getSessionUser } from '@/lib/session';
import { getCheckoutSession } from '@/lib/amazonpay';
import { locales, defaultLocale } from '@/lib/i18n-config';

const BASE = process.env.PUBLIC_BASE_URL || 'http://localhost:3000';

// Amazon renvoie ici après confirmation : ?amazonCheckoutSessionId=...&cart=..&lang=..
// On vérifie l'état (Completed) puis on crée la commande PrestaShop.
export async function GET(req: NextRequest) {
  const sp = req.nextUrl.searchParams;
  const sessionId = sp.get('amazonCheckoutSessionId');
  const id_cart = Number(sp.get('cart') || 0);
  const langParam = sp.get('lang') || '';
  const lang = (locales as readonly string[]).includes(langParam) ? langParam : defaultLocale;
  const redirect = (qs: string) => NextResponse.redirect(`${BASE}/${lang}/checkout?${qs}`, { status: 303 });

  if (!sessionId || !id_cart) return redirect('amazon_failed=1');

  const sess = await getCheckoutSession(sessionId).catch(() => null);
  if (!sess || !sess.ok || sess.state !== 'Completed') return redirect('amazon_failed=1');

  const user = await getSessionUser();
  const order = await bridgePost('checkout', {
    action: 'order',
    id_cart,
    ...(user ? { id_customer: user.id_customer } : {}),
    paid: 1,
    payment_method: 'Amazon Pay',
    transaction_id: sess.chargeId || sessionId,
  }).catch(() => null);

  if (!order?.ok) return redirect('amazon_failed=1');
  const qs = new URLSearchParams({
    amazon_paid: '1',
    ref: String(order.reference || ''),
    order: String(order.id_order || ''),
    total: String(order.total_paid || ''),
  });
  return redirect(qs.toString());
}
