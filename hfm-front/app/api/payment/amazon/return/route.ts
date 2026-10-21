import { NextRequest, NextResponse } from 'next/server';
import { bridgeGet, bridgePost } from '@/lib/ps';
import { getSessionUser } from '@/lib/session';
import { completeCheckoutSession, getCheckoutSession } from '@/lib/amazonpay';
import { locales, defaultLocale } from '@/lib/i18n-config';

const BASE = process.env.PUBLIC_BASE_URL || 'http://localhost:3000';

// Amazon renvoie ici après confirmation : ?amazonCheckoutSessionId=...&cart=..&lang=..
// On finalise la session (CompleteCheckoutSession = débit), puis on crée la commande PrestaShop.
export async function GET(req: NextRequest) {
  const sp = req.nextUrl.searchParams;
  const sessionId = sp.get('amazonCheckoutSessionId');
  const id_cart = Number(sp.get('cart') || 0);
  const langParam = sp.get('lang') || '';
  const lang = (locales as readonly string[]).includes(langParam) ? langParam : defaultLocale;
  const redirect = (qs: string) => NextResponse.redirect(`${BASE}/${lang}/checkout?${qs}`, { status: 303 });

  if (!sessionId || !id_cart) return redirect('amazon_failed=1');

  const sess = await getCheckoutSession(sessionId).catch(() => null);
  if (!sess || !sess.ok || (sess.state !== 'Open' && sess.state !== 'Completed')) return redirect('amazon_failed=1');
  // Le panier payé est celui scellé dans la session Amazon, pas celui de l'URL : une URL de retour
  // rejouée avec un autre `cart` est refusée (le bridge refuse aussi un paiement déjà utilisé).
  if (!sess.idCart || sess.idCart !== id_cart) {
    console.error(`[amazon] panier de l'URL (${id_cart}) différent du panier scellé dans la session (${sess.idCart ?? 'aucun'})`);
    return redirect('amazon_failed=1');
  }

  const user = await getSessionUser();
  let chargeId = sess.chargeId;
  if (sess.state === 'Open') {
    // Le panier a pu changer (autre onglet) depuis l'étape review : on ne débite que si le montant
    // posé chez Amazon est toujours le total du panier, sinon la commande serait refusée après débit.
    const cart = user ? await bridgeGet('cart', { id_cart, id_customer: user.id_customer }).catch(() => null) : null;
    const total = Number(cart?.totals?.total_incl_tax ?? cart?.totals?.products_incl_tax ?? 0);
    if (!sess.amount || Math.abs(total - sess.amount) > 0.01) {
      console.error(`[amazon] total du panier ${id_cart} (${total}) différent du montant de la session (${sess.amount})`);
      return redirect('amazon_failed=1');
    }
    const done = await completeCheckoutSession(sessionId, sess.amount).catch(() => null);
    if (!done?.ok || done.state !== 'Completed') {
      console.error(`[amazon] complete refusé pour la session ${sessionId}`, done?.data);
      return redirect('amazon_failed=1');
    }
    chargeId = done.chargeId;
  }

  const order = await bridgePost('checkout', {
    action: 'order',
    id_cart,
    ...(user ? { id_customer: user.id_customer } : {}),
    paid: 1,
    payment_method: 'Amazon Pay',
    transaction_id: chargeId || sessionId,
    // Montant réellement facturé (EUR) -> garde-fou montant côté bridge (anti sous-paiement).
    amount_paid: sess.amount,
  }).catch(() => null);

  if (!order?.ok) return redirect('amazon_failed=1');
  const qs = new URLSearchParams({
    amazon_paid: '1',
    ref: String(order.reference || ''),
    order: String(order.id_order || ''),
    total: String(order.total_paid || ''),
  });
  return redirect(qs.toString());
}
