import { NextRequest, NextResponse } from 'next/server';
import { bridgeGet, bridgePost } from '@/lib/ps';
import { signSession, getSessionUser, SESSION_COOKIE, type SessionUser } from '@/lib/session';
import { NO_STORE } from '@/lib/cacheContract';
import { idLangFor } from '@/lib/i18n-config';
import { SITE_URL } from '@/lib/seo';
import { clientIp, hitAll, LIMITS } from '@/lib/rateLimit';

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
// Professions acceptées à l'inscription (contrat bridge `customer` action=register).
const PROFESSIONS = new Set(['medecin', 'chirurgien-dentiste', 'pharmacien', 'infirmier', 'autre']);

// RPPS = 11 chiffres avec clé de Luhn (même contrôle que le bridge) ; ADELI = 9 chiffres.
function validRpps(v: string): boolean {
  if (/^\d{9}$/.test(v)) return true;
  if (!/^\d{11}$/.test(v)) return false;
  let sum = 0;
  for (let i = 0; i < 11; i++) {
    let d = Number(v[10 - i]);
    if (i % 2 === 1) { d *= 2; if (d > 9) d -= 9; }
    sum += d;
  }
  return sum % 10 === 0;
}

// Authentification / session : JAMAIS de cache.
export const dynamic = 'force-dynamic';

const COOKIE_OPTS = {
  httpOnly: true,
  sameSite: 'lax' as const,
  secure: process.env.NODE_ENV === 'production',
  path: '/',
  maxAge: 60 * 60 * 24 * 30, // 30 jours
};

function userFrom(c: { id_customer: number; email: string; firstname: string; lastname: string; id_lang?: number; is_guest?: number; id_default_group?: number }): SessionUser {
  return {
    id_customer: Number(c.id_customer),
    email: c.email,
    firstname: c.firstname,
    lastname: c.lastname,
    id_lang: c.id_lang,
    is_guest: Number(c.is_guest || 0),
    ...(c.id_default_group ? { id_group: Number(c.id_default_group) } : {}),
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


// Réponse uniforme quand la limite de tentatives est atteinte (lib/rateLimit.ts).
const rateLimited = (retryAfter: number) =>
  NextResponse.json({ error: 'rate_limited', retry_after: retryAfter }, { status: 429, headers: { ...NO_STORE, 'Retry-After': String(retryAfter) } });

// POST {action: login|register|logout, ...}
export async function POST(req: NextRequest) {
  const body = await req.json().catch(() => ({}));
  const action = String(body.action || '');

  // Limite de tentatives : par IP, et par e-mail pour la connexion et le mot de passe oublié.
  const ip = clientIp(req);
  const email = String(body.email || '').trim().toLowerCase();
  const checks: Record<string, [string, string, (typeof LIMITS)[keyof typeof LIMITS]][]> = {
    login: [['login-ip', ip, LIMITS.loginIp], ['login-email', email, LIMITS.loginEmail]],
    register: [['register', ip, LIMITS.register]],
    guest: [['guest', ip, LIMITS.guest]],
    'forgot-password': [['forgot-ip', ip, LIMITS.forgotIp], ['forgot-email', email, LIMITS.forgotEmail]],
    'reset-password': [['reset', ip, LIMITS.reset]],
  };
  if (checks[action]) {
    const wait = await hitAll(checks[action]);
    if (wait > 0) return rateLimited(wait);
  }

  if (action === 'logout') {
    const res = NextResponse.json({ ok: true }, { headers: NO_STORE });
    res.cookies.set(SESSION_COOKIE, '', { ...COOKIE_OPTS, maxAge: 0 });
    return res;
  }

  // « Mot de passe oublié » — DEMANDE. Aucune session requise.
  // Réponse NON-ORACLE : on renvoie toujours { ok:true } quel que soit le résultat côté bridge
  // (compte existant ou non) pour ne pas révéler l'existence d'un e-mail. Seul un format d'e-mail
  // invalide est signalé (n'expose rien sur l'existence d'un compte).
  if (action === 'forgot-password') {
    const email = String(body.email || '').trim();
    const locale = String(body.locale || 'fr');
    if (!EMAIL_RE.test(email)) {
      return NextResponse.json({ error: 'invalid_email' }, { status: 400, headers: NO_STORE });
    }
    // Le bridge PHP ne connaît pas l'URL du front headless : on lui passe la page de réinitialisation
    // localisée. Il y ajoutera id_customer + reset_token pour composer le lien de l'e-mail natif.
    const resetUrlBase = `${SITE_URL}/${locale}/reinitialiser-mot-de-passe`;
    await bridgePost('customer', {
      action: 'forgot-password',
      email,
      id_lang: idLangFor(locale),
      reset_url_base: resetUrlBase,
    }).catch(() => ({}));
    return NextResponse.json({ ok: true }, { headers: NO_STORE });
  }

  // « Mot de passe oublié » — RÉINITIALISATION. Vérifie le jeton natif côté bridge.
  // En cas de succès, on ouvre directement la session (auto-connexion), comme login/register.
  if (action === 'reset-password') {
    const id_customer = Number(body.id_customer || 0);
    const email = String(body.email || '').trim();
    const reset_token = String(body.reset_token || '');
    const password = String(body.password || '');
    if ((!id_customer && !email) || !reset_token || !password) {
      return NextResponse.json({ error: 'missing_fields' }, { status: 400, headers: NO_STORE });
    }
    const data = await bridgePost('customer', {
      action: 'reset-password',
      id_customer,
      email,
      reset_token,
      password,
    }).catch(() => ({ error: 'reset_failed' }));
    const c = data?.customer;
    if (!data?.reset || !c) {
      return NextResponse.json({ error: data?.error || 'reset_failed' }, { status: 400, headers: NO_STORE });
    }
    const user = userFrom(c);
    const res = NextResponse.json({ ok: true, customer: user }, { headers: NO_STORE });
    res.cookies.set(SESSION_COOKIE, signSession(user), COOKIE_OPTS);
    return res;
  }

  // Inscription professionnelle : champs en liste blanche, validés ici avant le bridge (qui revalide).
  let payload: Record<string, unknown> = body;
  if (action === 'register') {
    const profession = String(body.profession || '');
    if (!PROFESSIONS.has(profession)) {
      return NextResponse.json({ error: 'invalid_profession' }, { status: 400, headers: NO_STORE });
    }
    const rpps = String(body.rpps || '').replace(/\s+/g, '');
    // Facultatif à l'inscription ; s'il est donné, il doit être valide.
    if (rpps && !validRpps(rpps)) {
      return NextResponse.json({ error: 'invalid_rpps' }, { status: 400, headers: NO_STORE });
    }
    const company = String(body.company || '').trim().slice(0, 255);
    const newsletter = body.newsletter === true || body.newsletter === 1 || body.newsletter === '1';
    payload = {
      action: 'register',
      email: String(body.email || '').trim(),
      password: String(body.password || ''),
      firstname: String(body.firstname || '').trim(),
      lastname: String(body.lastname || '').trim(),
      id_lang: Number(body.id_lang) || idLangFor(String(body.locale || 'fr')),
      profession,
      ...(company ? { company } : {}),
      ...(rpps ? { rpps } : {}),
      // Consentement newsletter EXPLICITE (case non cochée par défaut) ; IP conservée comme preuve.
      newsletter: newsletter ? 1 : 0,
      ...(newsletter ? { client_ip: ip } : {}),
    };
  }

  if (action === 'login' || action === 'register' || action === 'guest') {
    const data = await bridgePost('customer', payload);
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
