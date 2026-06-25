import { NextRequest, NextResponse } from 'next/server';
import { bridgePost } from '@/lib/ps';
import { getTransaction, getWebhookKey, VIVA_SUCCESS_STATUS } from '@/lib/viva';

// EventTypeId Viva (cf. module officiel) : 1796 = paiement créé (succès), 1798 = échec.
const EVENT_SUCCESS = 1796;
const EVENT_FAILURE = 1798;

// Vérification de l'URL par Viva : GET attendu renvoyant { "Key": "<clé>" }.
export async function GET() {
  const Key = await getWebhookKey().catch(() => '');
  return NextResponse.json({ Key });
}

export async function POST(req: NextRequest) {
  const payload = await req.json().catch(() => null);
  const eventTypeId = Number(payload?.EventTypeId ?? payload?.eventTypeId ?? 0);
  const data = payload?.EventData || payload?.eventData || payload || {};
  const transactionId = data?.TransactionId || data?.transactionId;
  if (!transactionId) return NextResponse.json({ ok: true, ignored: true });

  // On ne traite que les évènements de succès (l'échec ne crée pas de commande).
  if (eventTypeId && eventTypeId === EVENT_FAILURE) {
    return NextResponse.json({ ok: true, ignored: 'failure' });
  }

  // Ne jamais faire confiance au corps : on revérifie la transaction côté Viva.
  const tx = await getTransaction(String(transactionId));
  if (!tx.ok || !tx.statusId || !VIVA_SUCCESS_STATUS.includes(tx.statusId)) {
    return NextResponse.json({ ok: true, ignored: 'not_paid' });
  }
  const m = /cart:(\d+)/.exec(tx.merchantTrns || '');
  const id_cart = m ? Number(m[1]) : 0;
  if (!id_cart) return NextResponse.json({ ok: true, ignored: 'no_cart' });

  await bridgePost('checkout', {
    action: 'order',
    id_cart,
    paid: 1,
    payment_method: 'Carte bancaire (Viva Wallet)',
    transaction_id: String(transactionId),
  });
  return NextResponse.json({ ok: true, event: eventTypeId || EVENT_SUCCESS });
}
