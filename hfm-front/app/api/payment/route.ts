import { NextRequest, NextResponse } from 'next/server';
import { bridgeGet } from '@/lib/ps';
import { getSessionUser } from '@/lib/session';
import { createOrder, vivaConfigured, vivaEnv } from '@/lib/viva';
import { paypalConfigured, paypalEnv } from '@/lib/paypal';
import { amazonConfigured, amazonEnv } from '@/lib/amazonpay';
import { NO_STORE } from '@/lib/cacheContract';

// Paiement (état PSP + création d'ordre) : JAMAIS de cache.
export const dynamic = 'force-dynamic';

// GET -> état des PSP pour le front (affiche chaque option si configurée).
// `configured`/`mode` restent l'état Viva (carte bancaire) pour compat ascendante.
export async function GET() {
  return NextResponse.json({
    configured: vivaConfigured,
    provider: 'viva',
    mode: vivaEnv,
    paypal: { configured: paypalConfigured, mode: paypalEnv },
    amazon: { configured: amazonConfigured, mode: amazonEnv },
  }, { headers: NO_STORE });
}

// POST {id_cart} -> crée une "payment order" Viva et renvoie l'URL Smart Checkout.
export async function POST(req: NextRequest) {
  const user = await getSessionUser();
  if (!user) return NextResponse.json({ error: 'unauthenticated' }, { status: 401, headers: NO_STORE });
  if (!vivaConfigured) return NextResponse.json({ error: 'viva_not_configured' }, { status: 501, headers: NO_STORE });

  const body = await req.json().catch(() => ({}));
  const id_cart = Number(body.id_cart);
  if (!id_cart) return NextResponse.json({ error: 'missing_cart' }, { status: 400, headers: NO_STORE });
  // Locale du client (pour rediriger dans la bonne langue au retour). Sécurisée : [a-z]{2}.
  const locale = /^[a-z]{2}$/.test(String(body.locale || '')) ? String(body.locale) : 'fr';

  // Montant TTC réel calculé côté serveur (transport inclus si transporteur choisi).
  const cart = await bridgeGet('cart', { id_cart, id_customer: user.id_customer });
  const total = Number(cart?.totals?.total_incl_tax ?? cart?.totals?.products_incl_tax ?? 0);
  if (total <= 0) return NextResponse.json({ error: 'empty_cart' }, { status: 400, headers: NO_STORE });

  try {
    const { orderCode, checkoutUrl } = await createOrder({
      amountCents: Math.round(total * 100),
      email: user.email,
      fullName: `${user.firstname} ${user.lastname}`.trim() || 'Client',
      // merchantTrns : relu au retour/webhook pour retrouver panier + langue.
      merchantTrns: `cart:${id_cart};cust:${user.id_customer};lang:${locale}`,
      customerTrns: `Commande Hyaluronic Filler Market`,
    });
    return NextResponse.json({ order_code: orderCode, checkout_url: checkoutUrl, amount: total }, { headers: NO_STORE });
  } catch (e) {
    return NextResponse.json({ error: 'viva_error', detail: (e as Error).message }, { status: 502, headers: NO_STORE });
  }
}
