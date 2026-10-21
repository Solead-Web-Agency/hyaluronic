// Alertes de panne : notification sur un webhook (Slack, Discord ou Google Chat) défini par
// ALERT_WEBHOOK_URL. Sans cette variable : simple console.error (visible dans les logs Vercel).
// Anti-inondation : une même alerte (même clé) au plus une fois par ALERT_THROTTLE_SEC, partagé via
// Upstash Redis entre les fonctions Vercel (repli mémoire si Redis est absent ou en panne).
import { Redis } from '@upstash/redis';

const WEBHOOK = process.env.ALERT_WEBHOOK_URL;
const ALERT_THROTTLE_SEC = 600;
const SITE = process.env.PUBLIC_BASE_URL || 'hyaluronic';
const redis = process.env.UPSTASH_REDIS_REST_URL && process.env.UPSTASH_REDIS_REST_TOKEN
  ? new Redis({
      url: process.env.UPSTASH_REDIS_REST_URL,
      token: process.env.UPSTASH_REDIS_REST_TOKEN,
      retry: { retries: 0 },
      signal: () => AbortSignal.timeout(400),
    })
  : null;
const recent = new Map<string, number>();

async function firstTime(key: string): Promise<boolean> {
  if (redis) {
    try {
      return (await redis.set(`alert:${key}`, 1, { nx: true, ex: ALERT_THROTTLE_SEC })) === 'OK';
    } catch {
      /* repli mémoire ci-dessous */
    }
  }
  const now = Date.now();
  if ((recent.get(key) ?? 0) > now) return false;
  recent.set(key, now + ALERT_THROTTLE_SEC * 1000);
  return true;
}

/** Signale une panne. `key` regroupe les répétitions (ex. « bridge » ou la route en erreur). */
export async function sendAlert(key: string, title: string, details: string): Promise<void> {
  console.error(`[alerte] ${title} — ${details}`);
  if (!WEBHOOK || !(await firstTime(key))) return;
  const text = `🚨 ${SITE} — ${title}\n${details}`;
  try {
    await fetch(WEBHOOK, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      // `text` : Slack / Google Chat ; `content` : Discord.
      body: JSON.stringify({ text, content: text.slice(0, 1900) }),
      signal: AbortSignal.timeout(3000),
    });
  } catch (err) {
    console.error('[alerte] envoi du webhook impossible :', err instanceof Error ? err.message : err);
  }
}
