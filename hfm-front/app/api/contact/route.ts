import { NextRequest, NextResponse } from 'next/server';
import { bridgeGet, bridgePost } from '@/lib/ps';
import { getSessionUser } from '@/lib/session';
import { NO_STORE } from '@/lib/cacheContract';
import { idLangFor } from '@/lib/i18n-config';
import { clientIp, hit, LIMITS } from '@/lib/rateLimit';

// Formulaire de contact -> fil Service Client natif (ps_customer_thread) + notification email.
// Écriture par-visiteur : jamais de cache.
export const dynamic = 'force-dynamic';

// Garde-fou de charge : la pièce jointe transite en base64 (~+33 %), on borne la requête.
const MAX_FILE_BYTES = 2_000_000;

export async function GET(req: NextRequest) {
  const locale = req.nextUrl.searchParams.get('locale') ?? 'fr';
  const d = await bridgeGet('contact', { id_lang: idLangFor(locale) }).catch(() => ({ contacts: [] }));
  return NextResponse.json(d, { headers: NO_STORE });
}

export async function POST(req: NextRequest) {
  // Anti-spam : 5 messages par heure et par IP.
  const wait = await hit('contact', clientIp(req), LIMITS.contact);
  if (wait > 0) {
    return NextResponse.json({ error: 'rate_limited', retry_after: wait }, { status: 429, headers: { ...NO_STORE, 'Retry-After': String(wait) } });
  }
  const form = await req.formData().catch(() => null);
  if (!form) {
    return NextResponse.json({ error: 'invalid_request' }, { status: 400, headers: NO_STORE });
  }
  const name = String(form.get('name') ?? '').replace(/\s+/g, ' ').trim().slice(0, 120);
  const email = String(form.get('email') ?? '').trim();
  const phone = String(form.get('phone') ?? '').trim().slice(0, 32);
  const orderReference = String(form.get('order_reference') ?? '').trim().toUpperCase().slice(0, 32);
  const message = String(form.get('message') ?? '').trim();
  const idContact = Number(form.get('id_contact') ?? 0);
  const locale = String(form.get('locale') ?? 'fr');

  if (!name || /[<>{}]/.test(name)) {
    return NextResponse.json({ error: 'invalid_name' }, { status: 400, headers: NO_STORE });
  }
  if (phone && !/^\+?[\d\s().\-]{6,}$/.test(phone)) {
    return NextResponse.json({ error: 'invalid_phone' }, { status: 400, headers: NO_STORE });
  }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) {
    return NextResponse.json({ error: 'invalid_email' }, { status: 400, headers: NO_STORE });
  }
  if (!message) {
    return NextResponse.json({ error: 'invalid_message' }, { status: 400, headers: NO_STORE });
  }

  // Pièce jointe optionnelle -> base64 pour le bridge (qui valide extension/taille à son tour,
  // avec les règles natives PrestaShop : il ne fait jamais confiance au front).
  let fileName: string | undefined;
  let fileBase64: string | undefined;
  const file = form.get('file');
  if (file && typeof file === 'object' && 'arrayBuffer' in file) {
    const f = file as File;
    if (f.size > 0) {
      if (f.size > MAX_FILE_BYTES) {
        return NextResponse.json({ error: 'file_too_big' }, { status: 400, headers: NO_STORE });
      }
      fileName = f.name;
      fileBase64 = Buffer.from(await f.arrayBuffer()).toString('base64');
    }
  }

  // id_customer imposé par la SESSION (jamais par le navigateur) : rattache le fil au compte.
  const user = await getSessionUser();

  // Le fil Service Client natif n'a ni nom, ni téléphone, ni référence de commande : on les place
  // en tête du message (lisibles par l'équipe dans le BO et dans l'e-mail de notification).
  // La référence n'est PAS convertie en id_order : elle n'est pas vérifiée contre l'e-mail.
  const header = [
    `Nom : ${name}`,
    phone ? `Téléphone : ${phone}` : '',
    orderReference ? `N° de commande : ${orderReference.replace(/[^A-Z0-9#\-]/g, '')}` : '',
  ].filter(Boolean).join('\n');

  const res = await bridgePost('contact', {
    email,
    message: `${header}\n\n${message}`,
    name,
    ...(phone ? { phone } : {}),
    ...(orderReference ? { order_reference: orderReference } : {}),
    id_contact: idContact,
    id_lang: idLangFor(locale),
    user_agent: req.headers.get('user-agent') ?? '',
    ...(user ? { id_customer: user.id_customer } : {}),
    ...(fileBase64 ? { file_base64: fileBase64, file_name: fileName } : {}),
  }).catch(() => ({ error: 'send_failed' }));

  const status = (res as { error?: string }).error ? 400 : 200;
  return NextResponse.json(res, { status, headers: NO_STORE });
}
