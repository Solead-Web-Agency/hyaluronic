import { NextRequest, NextResponse } from 'next/server';
import { bridgeGet, bridgePost } from '@/lib/ps';
import { signSession, getSessionUser, SESSION_COOKIE, type SessionUser } from '@/lib/session';
import { NO_STORE } from '@/lib/cacheContract';

// Authentification / session : JAMAIS de cache.
export const dynamic = 'force-dynamic';

const COOKIE_OPTS = {
  httpOnly: true,
  sameSite: 'lax' as const,
  secure: process.env.NODE_ENV === 'production',
  path: '/',
  maxAge: 60 * 60 * 24 * 30, // 30 jours
};

function userFrom(c: { id_customer: number; email: string; firstname: string; lastname: string; id_lang?: number; is_guest?: number }): SessionUser {
  return {
    id_customer: Number(c.id_customer),
    email: c.email,
    firstname: c.firstname,
    lastname: c.lastname,
    id_lang: c.id_lang,
    is_guest: Number(c.is_guest || 0),
  };
}

// GET -> session courante (ou null), enrichie de is_guest (le cookie ne le contient pas).
export async function GET() {
  const user = await getSessionUser();
  if (!user) return NextResponse.json({ customer: null }, { headers: NO_STORE });
  let is_guest = 0;
  try {
    const d = await bridgeGet('customer', { action: 'me', id_customer: user.id_customer });
    is_guest = Number(d?.customer?.is_guest || 0);
  } catch {
    /* repli : on garde le user de session sans is_guest */
  }
  return NextResponse.json({ customer: { ...user, is_guest } }, { headers: NO_STORE });
}

// POST {action: login|register|logout, ...}
export async function POST(req: NextRequest) {
  const body = await req.json().catch(() => ({}));
  const action = String(body.action || '');

  if (action === 'logout') {
    const res = NextResponse.json({ ok: true }, { headers: NO_STORE });
    res.cookies.set(SESSION_COOKIE, '', { ...COOKIE_OPTS, maxAge: 0 });
    return res;
  }

  if (action === 'login' || action === 'register' || action === 'guest') {
    const data = await bridgePost('customer', body);
    const c = data?.customer;
    // login -> authenticated ; guest -> client présent (créé OU réutilisé en édition) ; register -> created.
    const ok = action === 'login' ? data?.authenticated && c : action === 'guest' ? !!c : data?.created && c;
    if (!ok) {
      return NextResponse.json({ error: data?.error || 'auth_failed', detail: data }, { status: 401, headers: NO_STORE });
    }
    const user = userFrom(c);
    const res = NextResponse.json({ customer: user, guest: !!data?.guest }, { headers: NO_STORE });
    res.cookies.set(SESSION_COOKIE, signSession(user), COOKIE_OPTS);
    return res;
  }

  return NextResponse.json({ error: 'unknown_action' }, { status: 400, headers: NO_STORE });
}
