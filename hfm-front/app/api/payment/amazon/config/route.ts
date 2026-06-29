import { NextRequest, NextResponse } from 'next/server';
import { getSessionUser } from '@/lib/session';
import { buttonConfig, amazonConfigured, amazonEnv } from '@/lib/amazonpay';

const BASE = process.env.PUBLIC_BASE_URL || 'http://localhost:3000';

// Langues supportées par le bouton Amazon Pay (région EU) ; repli en_GB.
const AMZ_LANG: Record<string, string> = { fr: 'fr_FR', en: 'en_GB', de: 'de_DE', it: 'it_IT', es: 'es_ES' };

// POST {id_cart, locale} -> config signée du bouton Amazon Pay pour ce panier.
export async function POST(req: NextRequest) {
  const user = await getSessionUser();
  if (!user) return NextResponse.json({ error: 'unauthenticated' }, { status: 401 });
  if (!amazonConfigured) return NextResponse.json({ error: 'amazon_not_configured' }, { status: 501 });

  const body = await req.json().catch(() => ({}));
  const id_cart = Number(body.id_cart);
  if (!id_cart) return NextResponse.json({ error: 'missing_cart' }, { status: 400 });
  const locale = /^[a-z]{2}$/.test(String(body.locale || '')) ? String(body.locale) : 'fr';

  // Amazon revient sur cette URL après login ; on y embarque panier + langue.
  const reviewUrl = `${BASE}/api/payment/amazon/review?cart=${id_cart}&lang=${locale}`;
  const cfg = buttonConfig(reviewUrl);
  return NextResponse.json({
    ...cfg,
    checkoutLanguage: AMZ_LANG[locale] || 'en_GB',
    productType: 'PayOnly',
    placement: 'Checkout',
    mode: amazonEnv,
  });
}
