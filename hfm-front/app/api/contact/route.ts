import { NextRequest, NextResponse } from 'next/server';
import { bridgeGet, bridgePost } from '@/lib/ps';
import { getSessionUser } from '@/lib/session';
import { NO_STORE } from '@/lib/cacheContract';
import { idLangFor } from '@/lib/i18n-config';

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
  const form = await req.formData().catch(() => null);
  if (!form) {
    return NextResponse.json({ error: 'invalid_request' }, { status: 400, headers: NO_STORE });
  }
  const email = String(form.get('email') ?? '').trim();
  const message = String(form.get('message') ?? '').trim();
  const idContact = Number(form.get('id_contact') ?? 0);
  const locale = String(form.get('locale') ?? 'fr');

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

  const res = await bridgePost('contact', {
    email,
    message,
    id_contact: idContact,
    id_lang: idLangFor(locale),
    user_agent: req.headers.get('user-agent') ?? '',
    ...(user ? { id_customer: user.id_customer } : {}),
    ...(fileBase64 ? { file_base64: fileBase64, file_name: fileName } : {}),
  }).catch(() => ({ error: 'send_failed' }));

  const status = (res as { error?: string }).error ? 400 : 200;
  return NextResponse.json(res, { status, headers: NO_STORE });
}
