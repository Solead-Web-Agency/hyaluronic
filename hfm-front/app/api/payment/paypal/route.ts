import { NextRequest, NextResponse } from 'next/server';
import { bridgeGet } from '@/lib/ps';
import { getSessionUser } from '@/lib/session';
import { createOrder, paypalConfigured } from '@/lib/paypal';

const BASE = process.env.PUBLIC_BASE_URL || 'http://localhost:3000';

// POST {id_cart, locale} -> crée une commande PayPal et renvoie l'URL d'approbation.
export async function POST(req: NextRequest) {
  const user = await getSessionUser();
  if (!user) return NextResponse.json({ error: 'unauthenticated' }, { status: 401 });
  if (!paypalConfigured) return NextResponse.json({ error: 'paypal_not_configured' }, { status: 501 });

  const body = await req.json().catch(() => ({}));
  const id_cart = Number(body.id_cart);
  if (!id_cart) return NextResponse.json({ error: 'missing_cart' }, { status: 400 });
  const locale = /^[a-z]{2}$/.test(String(body.locale || '')) ? String(body.locale) : 'fr';

  // Montant TTC réel calculé côté serveur (transport inclus si transporteur choisi).
  const cart = await bridgeGet('cart', { id_cart, id_customer: user.id_customer });
  const total = Number(cart?.totals?.total_incl_tax ?? cart?.totals?.products_incl_tax ?? 0);
  if (total <= 0) return NextResponse.json({ error: 'empty_cart' }, { status: 400 });

  try {
    const { orderId, approveUrl } = await createOrder({
      amountEUR: total,
      // custom_id : relu à la capture pour retrouver panier + langue (max 127 car).
      customId: `cart:${id_cart};cust:${user.id_customer};lang:${locale}`,
      returnUrl: `${BASE}/api/payment/paypal/return`,
      cancelUrl: `${BASE}/${locale}/checkout?paypal_cancel=1`,
      description: 'Commande Hyaluronic Filler Market',
    });
    return NextResponse.json({ order_id: orderId, checkout_url: approveUrl, amount: total });
  } catch (e) {
    return NextResponse.json({ error: 'paypal_error', detail: (e as Error).message }, { status: 502 });
  }
}
