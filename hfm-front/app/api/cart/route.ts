import { NextRequest, NextResponse } from 'next/server';
import { bridgeGet, bridgePost } from '@/lib/ps';
import { getSessionUser } from '@/lib/session';
import { NO_STORE } from '@/lib/cacheContract';

// Données par-utilisateur : JAMAIS de cache. Toujours dynamique + no-store.
export const dynamic = 'force-dynamic';

export async function GET(req: NextRequest) {
  const sp = req.nextUrl.searchParams;
  const params: Record<string, string | number> = {};
  for (const k of ['id_cart', 'id_lang', 'id_currency']) {
    const v = sp.get(k);
    if (v) params[k] = v;
  }
  // Si connecté, on lie le panier au client (calcul prix/exonération correct).
  const user = await getSessionUser();
  if (user) params.id_customer = user.id_customer;
  return NextResponse.json(await bridgeGet('cart', params), { headers: NO_STORE });
}

export async function POST(req: NextRequest) {
  const body = await req.json().catch(() => ({}));
  const user = await getSessionUser();
  // L'id_customer vient de la session (rattachement panier) ; jamais du client.
  const merged = user ? { ...body, id_customer: user.id_customer } : body;
  return NextResponse.json(await bridgePost('cart', merged), { headers: NO_STORE });
}
