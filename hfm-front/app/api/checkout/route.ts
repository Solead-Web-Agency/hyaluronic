import { NextRequest, NextResponse } from 'next/server';
import { bridgeGet, bridgePost } from '@/lib/ps';
import { getSessionUser } from '@/lib/session';
import { NO_STORE } from '@/lib/cacheContract';

// Tunnel de commande : nécessite une session. L'id_customer est injecté côté serveur ;
// le bridge vérifie ensuite l'appartenance du panier + client+adresse+transporteur avant validateOrder().
// Données par-utilisateur + mutation : JAMAIS de cache.
export const dynamic = 'force-dynamic';

// Actions AUTORISÉES depuis le tunnel client. Les opérations privilégiées (marquer une commande
// payée, rembourser) ne transitent JAMAIS par ici : elles sont appelées en direct par les routes
// serveur vérifiées (retours PSP -> action 'order' + paid ; /api/payment/refund -> 'refund-order').
const CLIENT_ACTIONS = new Set(['set-address', 'set-carrier', 'voucher', 'order']);

export async function GET(req: NextRequest) {
  const sp = req.nextUrl.searchParams;
  const params: Record<string, string | number> = {};
  for (const k of ['action', 'id_cart', 'id_lang', 'id_currency', 'cart_token']) {
    const v = sp.get(k);
    if (v) params[k] = v;
  }
  const user = await getSessionUser();
  if (user) params.id_customer = user.id_customer;
  return NextResponse.json(await bridgeGet('checkout', params), { headers: NO_STORE });
}

export async function POST(req: NextRequest) {
  const user = await getSessionUser();
  if (!user) return NextResponse.json({ error: 'unauthenticated' }, { status: 401, headers: NO_STORE });
  const body = await req.json().catch(() => ({}));
  const action = String(body.action || '');
  // Allowlist : tout ce qui n'est pas une action client explicite est refusé (dont 'refund-order',
  // réservé à l'admin via /api/payment/refund).
  if (!CLIENT_ACTIONS.has(action)) {
    return NextResponse.json({ error: 'forbidden_action' }, { status: 403, headers: NO_STORE });
  }
  // Le statut de paiement et la référence PSP ne viennent JAMAIS du client : on les retire.
  // L'id_customer est imposé par la session (toute valeur cliente est écrasée).
  const forwarded: Record<string, unknown> = { ...body, id_customer: user.id_customer };
  delete forwarded.paid;
  delete forwarded.transaction_id;
  return NextResponse.json(await bridgePost('checkout', forwarded), { headers: NO_STORE });
}
