import { NextRequest, NextResponse } from 'next/server';
import { bridgePost } from '@/lib/ps';
import { NO_STORE } from '@/lib/cacheContract';
import { idLangFor } from '@/lib/i18n-config';

// Inscription newsletter -> table native ps_emailsubscription (même table que l'ancien site,
// pour que le back-office et les exports continuent de voir tous les inscrits).
// Écriture par-visiteur : jamais de cache.
export const dynamic = 'force-dynamic';

export async function POST(req: NextRequest) {
  const body = await req.json().catch(() => ({}));
  const email = typeof body?.email === 'string' ? body.email.trim() : '';
  const locale = typeof body?.locale === 'string' ? body.locale : 'fr';
  if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) {
    return NextResponse.json({ error: 'invalid_email' }, { status: 400, headers: NO_STORE });
  }
  const res = await bridgePost('newsletter', {
    email,
    id_lang: idLangFor(locale),
    referer: req.headers.get('referer') ?? '',
  }).catch(() => ({ error: 'subscribe_failed' }));
  const status = (res as { error?: string }).error ? 400 : 200;
  return NextResponse.json(res, { status, headers: NO_STORE });
}
