import { NextRequest, NextResponse } from 'next/server';
import { bridgePost } from '@/lib/ps';
import { getSessionUser } from '@/lib/session';
import { getTransaction, VIVA_SUCCESS_STATUS } from '@/lib/viva';
import { locales, defaultLocale } from '@/lib/i18n-config';

// URL de retour UNIQUE (locale-agnostique) configurée sur la Source Viva, valable pour
// toutes les langues. Viva renvoie le navigateur ici avec ?t=<transactionId>&s=<orderCode>.
// On vérifie le paiement, on crée la commande, puis on REDIRIGE vers /{lang}/checkout.
const BASE = process.env.PUBLIC_BASE_URL || 'http://localhost:3000';

function localeFrom(merchantTrns: string, fallback: string): string {
  const m = /lang:([a-z]{2})/.exec(merchantTrns || '');
  const l = m?.[1];
  return l && (locales as readonly string[]).includes(l) ? l : fallback;
}

export async function GET(req: NextRequest) {
  const sp = req.nextUrl.searchParams;
  const t = sp.get('t') || sp.get('transaction_id');
  // Locale de repli : cookie next-intl, sinon défaut.
  const cookieLocale = req.cookies.get('NEXT_LOCALE')?.value;
  let lang = cookieLocale && (locales as readonly string[]).includes(cookieLocale) ? cookieLocale : defaultLocale;

  const redirect = (qs: string) => NextResponse.redirect(`${BASE}/${lang}/checkout?${qs}`, { status: 303 });

  if (!t) return redirect('viva_failed=1');

  const tx = await getTransaction(String(t)).catch(() => null);
  if (tx?.merchantTrns) lang = localeFrom(tx.merchantTrns, lang);

  if (!tx || !tx.ok || !tx.statusId || !VIVA_SUCCESS_STATUS.includes(tx.statusId)) {
    return redirect('viva_failed=1');
  }

  const m = /cart:(\d+)/.exec(tx.merchantTrns || '');
  const id_cart = m ? Number(m[1]) : 0;
  const user = await getSessionUser();
  const order = await bridgePost('checkout', {
    action: 'order',
    id_cart,
    ...(user ? { id_customer: user.id_customer } : {}),
    paid: 1,
    payment_method: 'Carte bancaire (Viva Wallet)',
    transaction_id: String(t),
    // Montant réellement encaissé (EUR) -> le bridge refuse de marquer "payé" s'il ne couvre
    // pas le total du panier (garde-fou anti « payer 50 recevoir 500 »). API classique Viva :
    // /api/transactions renvoie Amount en euros. Absent -> garde-fou inactif (fail-open) côté bridge.
    amount_paid: tx.amount,
  }).catch(() => null);

  if (!order?.ok) return redirect('viva_failed=1');
  const qs = new URLSearchParams({
    viva_paid: '1',
    ref: String(order.reference || ''),
    order: String(order.id_order || ''),
    total: String(order.total_paid || ''),
  });
  return redirect(qs.toString());
}
