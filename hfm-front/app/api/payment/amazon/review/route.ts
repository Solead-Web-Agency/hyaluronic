import { NextRequest, NextResponse } from 'next/server';
import { bridgeGet } from '@/lib/ps';
import { getSessionUser } from '@/lib/session';
import { updateCheckoutSession } from '@/lib/amazonpay';
import { locales, defaultLocale } from '@/lib/i18n-config';

const BASE = process.env.PUBLIC_BASE_URL || 'http://localhost:3000';

// Amazon renvoie ici après le login acheteur : ?amazonCheckoutSessionId=...&cart=..&lang=..
// On fixe le montant (AuthorizeWithCapture) + l'URL de résultat, puis on redirige vers Amazon pour confirmation.
export async function GET(req: NextRequest) {
  const sp = req.nextUrl.searchParams;
  const sessionId = sp.get('amazonCheckoutSessionId');
  const id_cart = Number(sp.get('cart') || 0);
  const langParam = sp.get('lang') || '';
  const lang = (locales as readonly string[]).includes(langParam) ? langParam : defaultLocale;
  const fail = () => NextResponse.redirect(`${BASE}/${lang}/checkout?amazon_failed=1`, { status: 303 });

  if (!sessionId || !id_cart) return fail();
  const user = await getSessionUser();
  if (!user) return fail();

  const cart = await bridgeGet('cart', { id_cart, id_customer: user.id_customer }).catch(() => null);
  const total = Number(cart?.totals?.total_incl_tax ?? cart?.totals?.products_incl_tax ?? 0);
  if (total <= 0) return fail();

  const resultUrl = `${BASE}/api/payment/amazon/return?cart=${id_cart}&lang=${lang}`;
  const upd = await updateCheckoutSession(sessionId, total, resultUrl).catch(() => null);
  if (!upd?.ok || !upd.redirectUrl) return fail();
  return NextResponse.redirect(upd.redirectUrl, { status: 303 });
}
