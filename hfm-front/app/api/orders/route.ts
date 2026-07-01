import { NextRequest, NextResponse } from 'next/server';
import { bridgeGet } from '@/lib/ps';
import { getSessionUser } from '@/lib/session';
import { NO_STORE } from '@/lib/cacheContract';

// Historique / détail commande. id_customer imposé par la session + le bridge vérifie l'appartenance.
// Données par-utilisateur : JAMAIS de cache.
export const dynamic = 'force-dynamic';

export async function GET(req: NextRequest) {
  const user = await getSessionUser();
  if (!user) return NextResponse.json({ error: 'unauthenticated' }, { status: 401, headers: NO_STORE });
  const sp = req.nextUrl.searchParams;
  const params: Record<string, string | number> = { id_customer: user.id_customer };
  for (const k of ['action', 'id_order']) {
    const v = sp.get(k);
    if (v) params[k] = v;
  }
  return NextResponse.json(await bridgeGet('orders', params), { headers: NO_STORE });
}
