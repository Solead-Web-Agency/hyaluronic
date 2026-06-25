import { NextRequest, NextResponse } from 'next/server';
import { bridgeGet, bridgePost } from '@/lib/ps';
import { getSessionUser } from '@/lib/session';

// Favoris persistés par client (table serveur). id_customer imposé par la session.
export async function GET() {
  const user = await getSessionUser();
  if (!user) return NextResponse.json({ ids: [], products: [] });
  return NextResponse.json(await bridgeGet('wishlist', { id_customer: user.id_customer }));
}

export async function POST(req: NextRequest) {
  const user = await getSessionUser();
  if (!user) return NextResponse.json({ error: 'unauthenticated' }, { status: 401 });
  const body = await req.json().catch(() => ({}));
  return NextResponse.json(await bridgePost('wishlist', { ...body, id_customer: user.id_customer }));
}
