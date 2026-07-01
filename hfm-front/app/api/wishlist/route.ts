import { NextRequest, NextResponse } from 'next/server';
import { bridgeGet, bridgePost } from '@/lib/ps';
import { getSessionUser } from '@/lib/session';
import { NO_STORE } from '@/lib/cacheContract';

// Favoris persistés par client (table serveur). id_customer imposé par la session.
// Données par-utilisateur : JAMAIS de cache.
export const dynamic = 'force-dynamic';

export async function GET() {
  const user = await getSessionUser();
  if (!user) return NextResponse.json({ ids: [], products: [] }, { headers: NO_STORE });
  return NextResponse.json(await bridgeGet('wishlist', { id_customer: user.id_customer }), { headers: NO_STORE });
}

export async function POST(req: NextRequest) {
  const user = await getSessionUser();
  if (!user) return NextResponse.json({ error: 'unauthenticated' }, { status: 401, headers: NO_STORE });
  const body = await req.json().catch(() => ({}));
  return NextResponse.json(await bridgePost('wishlist', { ...body, id_customer: user.id_customer }), { headers: NO_STORE });
}
