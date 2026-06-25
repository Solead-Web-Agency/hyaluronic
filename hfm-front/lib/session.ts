import crypto from 'crypto';
import { cookies } from 'next/headers';

// Session client signée (HMAC) stockée dans un cookie httpOnly.
// Le navigateur ne peut ni lire ni forger l'id_customer : toute opération sensible
// (adresses, commande, rattachement panier) lit l'identité ICI, côté serveur.

const SECRET = process.env.HFM_SESSION_SECRET || process.env.HFM_BRIDGE_SECRET || 'dev-only-secret';
export const SESSION_COOKIE = 'hfm_session';

export type SessionUser = {
  id_customer: number;
  email: string;
  firstname: string;
  lastname: string;
  id_lang?: number;
};

function b64url(buf: Buffer | string): string {
  return Buffer.from(buf).toString('base64url');
}

export function signSession(user: SessionUser): string {
  const payload = b64url(JSON.stringify({ ...user, iat: Date.now() }));
  const sig = crypto.createHmac('sha256', SECRET).update(payload).digest('base64url');
  return `${payload}.${sig}`;
}

export function verifySession(token: string | undefined | null): SessionUser | null {
  if (!token || !token.includes('.')) return null;
  const [payload, sig] = token.split('.');
  const expected = crypto.createHmac('sha256', SECRET).update(payload).digest('base64url');
  if (sig.length !== expected.length || !crypto.timingSafeEqual(Buffer.from(sig), Buffer.from(expected))) {
    return null;
  }
  try {
    const data = JSON.parse(Buffer.from(payload, 'base64url').toString('utf8'));
    if (!data?.id_customer) return null;
    return {
      id_customer: Number(data.id_customer),
      email: String(data.email || ''),
      firstname: String(data.firstname || ''),
      lastname: String(data.lastname || ''),
      id_lang: data.id_lang ? Number(data.id_lang) : undefined,
    };
  } catch {
    return null;
  }
}

/** Lit la session courante depuis le cookie (dans un route handler / server component). */
export async function getSessionUser(): Promise<SessionUser | null> {
  const store = await cookies();
  return verifySession(store.get(SESSION_COOKIE)?.value);
}
