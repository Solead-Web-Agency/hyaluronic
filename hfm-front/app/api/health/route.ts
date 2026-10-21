import { NextResponse } from 'next/server';
import { bridgeGet } from '@/lib/ps';
import { sendAlert } from '@/lib/alert';
import { NO_STORE } from '@/lib/cacheContract';

// Point de contrôle pour une surveillance externe (UptimeRobot, Better Stack…) interrogée toutes les
// quelques minutes : 200 si le back PrestaShop répond, 503 sinon (le service prévient alors par
// e-mail/SMS). Une panne déclenche aussi l'alerte webhook (lib/alert.ts). Jamais mis en cache.
export const dynamic = 'force-dynamic';

export async function GET() {
  const started = Date.now();
  try {
    // Appel léger, sans cache : liste des pays à règles de prix.
    await bridgeGet('taxonomy', { action: 'rule-countries' });
    const ms = Date.now() - started;
    return NextResponse.json({ ok: true, bridge_ms: ms }, { headers: NO_STORE });
  } catch (err) {
    const message = err instanceof Error ? `${err.name} : ${err.message}` : String(err);
    await sendAlert('health:bridge', 'Le back PrestaShop ne répond pas', `${message} (après ${Date.now() - started} ms)`);
    return NextResponse.json({ ok: false, error: 'bridge_unavailable' }, { status: 503, headers: NO_STORE });
  }
}
