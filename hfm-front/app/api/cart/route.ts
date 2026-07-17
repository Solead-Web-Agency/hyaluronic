import { NextRequest, NextResponse } from 'next/server';
import { bridgeGet, bridgePost } from '@/lib/ps';
import { getSessionUser } from '@/lib/session';
import { NO_STORE } from '@/lib/cacheContract';

// Données par-utilisateur : JAMAIS de cache. Toujours dynamique + no-store.
export const dynamic = 'force-dynamic';

export async function GET(req: NextRequest) {
  const sp = req.nextUrl.searchParams;
  const params: Record<string, string | number> = {};
  // cart_token = preuve d'appartenance d'un panier INVITÉ (émis par le bridge à la création,
  // stocké côté client à côté de l'id_cart). On le relaie tel quel au bridge.
  for (const k of ['id_cart', 'id_lang', 'id_currency', 'cart_token']) {
    const v = sp.get(k);
    if (v) params[k] = v;
  }
  // Si connecté, on lie le panier au client (calcul prix/exonération correct + appartenance).
  // L'id_customer vient EXCLUSIVEMENT de la session HMAC ; jamais d'un paramètre client.
  const user = await getSessionUser();
  if (user) params.id_customer = user.id_customer;
  return NextResponse.json(await bridgeGet('cart', params), { headers: NO_STORE });
}

export async function POST(req: NextRequest) {
  const body = await req.json().catch(() => ({}));
  const user = await getSessionUser();
  // L'id_customer vient de la session (rattachement panier) ; JAMAIS du client : on écrase
  // toute valeur fournie dans le corps, et sans session on la supprime (un panier invité
  // n'est alors accessible que via son cart_token signé).
  const forwarded: Record<string, unknown> = { ...body };
  if (user) forwarded.id_customer = user.id_customer;
  else delete forwarded.id_customer;
  return NextResponse.json(await bridgePost('cart', forwarded), { headers: NO_STORE });
}
