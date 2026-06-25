import { NextRequest, NextResponse } from 'next/server';
import { bridgeGet, bridgePost } from '@/lib/ps';
import { getSessionUser } from '@/lib/session';

// Profil & adresses : l'id_customer provient TOUJOURS de la session (le client ne le fournit pas).
export async function GET(req: NextRequest) {
  const user = await getSessionUser();
  if (!user) return NextResponse.json({ error: 'unauthenticated' }, { status: 401 });
  const sp = req.nextUrl.searchParams;
  const params: Record<string, string | number> = { id_customer: user.id_customer };
  const action = sp.get('action');
  if (action) params.action = action;
  return NextResponse.json(await bridgeGet('customer', params));
}

export async function POST(req: NextRequest) {
  const user = await getSessionUser();
  if (!user) return NextResponse.json({ error: 'unauthenticated' }, { status: 401 });
  const body = await req.json().catch(() => ({}));
  // Validation RPPS (Luhn + existence au registre local) faite côté bridge — instantané.
  // add-address et autres opérations profil : on force l'id_customer de la session.
  return NextResponse.json(await bridgePost('customer', { ...body, id_customer: user.id_customer }));
}
