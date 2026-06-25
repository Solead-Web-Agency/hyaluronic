import { NextRequest, NextResponse } from 'next/server';
import { bridgePost } from '@/lib/ps';
import { refundTransaction } from '@/lib/viva';

// Remboursement (action ADMIN, pas client) : protégé par un secret d'en-tête.
// Body: { transaction_id, amount (€), id_order? }. Rembourse chez Viva puis reflète l'état dans PS.
const ADMIN_SECRET = process.env.VIVA_ADMIN_SECRET || '';

export async function POST(req: NextRequest) {
  if (!ADMIN_SECRET || req.headers.get('x-admin-secret') !== ADMIN_SECRET) {
    return NextResponse.json({ error: 'forbidden' }, { status: 403 });
  }
  const body = await req.json().catch(() => ({}));
  const transactionId = String(body.transaction_id || '');
  const amount = Number(body.amount || 0);
  const idOrder = Number(body.id_order || 0);
  if (!transactionId || amount <= 0) {
    return NextResponse.json({ error: 'missing_params' }, { status: 400 });
  }

  const refund = await refundTransaction(transactionId, Math.round(amount * 100));
  if (!refund.ok) {
    return NextResponse.json({ error: 'viva_refund_failed', detail: refund.data }, { status: 502 });
  }
  // Reflète le remboursement dans PrestaShop (état "Remboursé").
  if (idOrder) {
    await bridgePost('checkout', { action: 'refund-order', id_order: idOrder }).catch(() => null);
  }
  return NextResponse.json({ ok: true, refund: refund.data });
}
