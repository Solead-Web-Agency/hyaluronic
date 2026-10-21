// Limite de tentatives (anti force brute, anti envoi massif d'e-mails, anti spam) sur les routes
// publiques sensibles : connexion, inscription, invité, mot de passe oublié/réinitialisation, contact,
// newsletter. Compteur à fenêtre fixe dans Upstash Redis (partagé entre les fonctions Vercel).
// Redis absent ou en panne : repli sur un compteur en mémoire de l'instance (protection partielle
// plutôt que de bloquer les clients légitimes).
import { Redis } from '@upstash/redis';
import type { NextRequest } from 'next/server';

const REDIS_URL = process.env.UPSTASH_REDIS_REST_URL;
const REDIS_TOKEN = process.env.UPSTASH_REDIS_REST_TOKEN;
const redis = REDIS_URL && REDIS_TOKEN
  ? new Redis({ url: REDIS_URL, token: REDIS_TOKEN, retry: { retries: 0 }, signal: () => AbortSignal.timeout(400) })
  : null;

export type Limit = { max: number; windowSec: number };

// Règles par action (nombre d'essais autorisés par fenêtre).
export const LIMITS = {
  loginIp: { max: 20, windowSec: 15 * 60 },
  loginEmail: { max: 8, windowSec: 15 * 60 },
  register: { max: 10, windowSec: 60 * 60 },
  guest: { max: 30, windowSec: 15 * 60 },
  forgotIp: { max: 5, windowSec: 60 * 60 },
  forgotEmail: { max: 3, windowSec: 60 * 60 },
  reset: { max: 10, windowSec: 60 * 60 },
  contact: { max: 5, windowSec: 60 * 60 },
  newsletter: { max: 10, windowSec: 60 * 60 },
} satisfies Record<string, Limit>;

const memory = new Map<string, { count: number; resetAt: number }>();

function memoryHit(key: string, limit: Limit): number {
  const now = Date.now();
  const cur = memory.get(key);
  if (!cur || cur.resetAt <= now) {
    memory.set(key, { count: 1, resetAt: now + limit.windowSec * 1000 });
    if (memory.size > 5000) for (const [k, v] of memory) if (v.resetAt <= now) memory.delete(k);
    return 1;
  }
  cur.count += 1;
  return cur.count;
}

export function clientIp(req: NextRequest): string {
  return (req.headers.get('x-forwarded-for') || '').split(',')[0].trim() || req.headers.get('x-real-ip') || 'inconnu';
}

/**
 * Compte une tentative. Renvoie le nombre de secondes à attendre si la limite est dépassée, sinon 0.
 * `id` = IP ou e-mail (normalisé en minuscules).
 */
export async function hit(bucket: string, id: string, limit: Limit): Promise<number> {
  const window = Math.floor(Date.now() / 1000 / limit.windowSec);
  const key = `rl:${bucket}:${id.toLowerCase()}:${window}`;
  const retryAfter = limit.windowSec - (Math.floor(Date.now() / 1000) % limit.windowSec);
  let count: number;
  if (redis) {
    try {
      const [n] = await redis.pipeline().incr(key).expire(key, limit.windowSec).exec<[number, number]>();
      count = n;
    } catch {
      count = memoryHit(key, limit);
    }
  } else {
    count = memoryHit(key, limit);
  }
  return count > limit.max ? retryAfter : 0;
}

/** Plusieurs contrôles d'un coup (ex. par IP ET par e-mail) : renvoie la plus longue attente, 0 sinon. */
export async function hitAll(checks: [string, string, Limit][]): Promise<number> {
  const waits = await Promise.all(checks.filter(([, id]) => id).map(([b, id, l]) => hit(b, id, l)));
  return Math.max(0, ...waits);
}
