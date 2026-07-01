import { NextRequest, NextResponse } from 'next/server';
import { bridgeGet, bridgePost } from '@/lib/ps';
import { getSessionUser } from '@/lib/session';
import { NO_STORE } from '@/lib/cacheContract';

// Tunnel de commande : nécessite une session. L'id_customer est injecté côté serveur ;
// le bridge vérifie ensuite client+adresse+transporteur avant validateOrder().
// Données par-utilisateur + mutation : JAMAIS de cache.
export const dynamic = 'force-dynamic';

export async function GET(req: NextRequest) {
  const sp = req.nextUrl.searchParams;
  const params: Record<string, string | number> = {};
  for (const k of ['action', 'id_cart', 'id_lang', 'id_currency']) {
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
  return NextResponse.json(await bridgePost('checkout', { ...body, id_customer: user.id_customer }), { headers: NO_STORE });
}
