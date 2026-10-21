import { NextRequest, NextResponse } from 'next/server';
import { bridgePost } from '@/lib/ps';
import { getCharge } from '@/lib/amazonpay';
import { sendAlert } from '@/lib/alert';

// IPN Amazon Pay (notification SNS), déclarée chez Amazon sur l'URL de l'ancien module
// (/fr/module/amazonpay/ipn, réécrite ici par le middleware). Le corps ne sert qu'à connaître
// l'identifiant du débit : son état et son montant sont relus chez Amazon (corps non signé vérifié).
// Rôle : créer la commande si le client a été débité mais n'est jamais revenu sur le site
// (onglet fermé, erreur après le débit), et alerter si un débit est refusé ou annulé après coup.
export async function POST(req: NextRequest) {
  const envelope = await req.json().catch(() => null);
  if (envelope?.Type === 'SubscriptionConfirmation') {
    const url = String(envelope.SubscribeURL || '');
    if (/^https:\/\/sns\.[a-z0-9-]+\.amazonaws\.com\//.test(url)) await fetch(url).catch(() => null);
    return NextResponse.json({ ok: true, subscribed: true });
  }
  let msg: any = envelope?.Message ?? envelope;
  if (typeof msg === 'string') msg = (() => { try { return JSON.parse(msg); } catch { return null; } })();
  if (!msg || String(msg.ObjectType || '').toUpperCase() !== 'CHARGE' || !msg.ObjectId) {
    return NextResponse.json({ ok: true, ignored: true });
  }

  const chargeId = String(msg.ObjectId);
  const charge = await getCharge(chargeId);
  if (charge.status === 404) return NextResponse.json({ ok: true, ignored: 'unknown_charge' });
  if (!charge.ok) return NextResponse.json({ ok: false }, { status: 500 }); // Amazon renverra l'IPN

  if (charge.state === 'Declined' || charge.state === 'Canceled') {
    await sendAlert(`amazon-ipn-${chargeId}`, `Amazon Pay : débit ${charge.state}`, `Débit ${chargeId} (panier ${charge.idCart ?? '?'}) : vérifier la commande dans le BO.`);
    return NextResponse.json({ ok: true, state: charge.state });
  }
  if (charge.state !== 'Captured' || !charge.idCart) return NextResponse.json({ ok: true, ignored: charge.state });

  // Commande déjà créée au retour du client : le bridge renvoie la commande existante du panier.
  const order = await bridgePost('checkout', {
    action: 'order',
    id_cart: charge.idCart,
    paid: 1,
    payment_method: 'Amazon Pay',
    transaction_id: chargeId,
    amount_paid: charge.amount,
  }).catch(() => null);
  if (!order?.ok) {
    await sendAlert(`amazon-ipn-order-${chargeId}`, 'Amazon Pay : client débité sans commande', `Débit ${chargeId} capturé (${charge.amount} €) sur le panier ${charge.idCart} mais la commande n'a pas pu être créée.`);
  }
  return NextResponse.json({ ok: true, state: charge.state });
}
