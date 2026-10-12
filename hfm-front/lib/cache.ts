// Couche cache côté front : Upstash Redis (REST, compatible serverless) + Data Cache Next.
// Dégradation gracieuse obligatoire : si Redis n'est pas configuré ou tombe en erreur,
// on retombe sur le fetcher (et le Data Cache Next reste, via revalidateTag pour la purge).
import { Redis } from '@upstash/redis';
import { revalidateTag } from 'next/cache';

const REDIS_URL = process.env.UPSTASH_REDIS_REST_URL;
const REDIS_TOKEN = process.env.UPSTASH_REDIS_REST_TOKEN;

// Redis n'est activé que si les deux variables sont présentes. Sinon : passe-plat.
// Le cache ne doit JAMAIS ralentir le site : par défaut le client Upstash réessaie 5 fois
// (~4,3 s cumulées) quand l'hôte ne répond pas -> chaque page coûtait 5 à 10 s pendant la
// panne Upstash d'octobre 2026. On coupe donc les tentatives, on borne chaque appel à
// REDIS_TIMEOUT_MS, et un disjoncteur ignore Redis pendant REDIS_COOLDOWN_MS après une erreur.
const REDIS_TIMEOUT_MS = 400;
const REDIS_COOLDOWN_MS = 60_000;
const redis = REDIS_URL && REDIS_TOKEN
  ? new Redis({
      url: REDIS_URL,
      token: REDIS_TOKEN,
      retry: { retries: 0 },
      signal: () => AbortSignal.timeout(REDIS_TIMEOUT_MS),
    })
  : null;

let redisDownUntil = 0;
const redisUsable = () => redis != null && Date.now() >= redisDownUntil;
function redisFailed(err: unknown) {
  // Un seul log par fenêtre de coupure : visible dans les logs Vercel, sans inonder.
  if (Date.now() >= redisDownUntil) {
    console.error(`[cache] Redis indisponible, ignoré ${REDIS_COOLDOWN_MS / 1000}s :`, err instanceof Error ? err.message : err);
  }
  redisDownUntil = Date.now() + REDIS_COOLDOWN_MS;
}

export const redisEnabled = redis != null;

// Préfixe d'index inverse pour la purge : 'tag:<tag>' -> set des clés de cache.
const tagIndexKey = (tag: string) => `tag:${tag}`;

/**
 * Clé de cache déterministe : 'hfm:<controller>:<params triés en query-string>'.
 * Les params sont triés par nom pour que l'ordre d'appel n'influe pas sur la clé.
 */
export function cacheKey(controller: string, params: Record<string, string | number> = {}): string {
  const qs = new URLSearchParams();
  for (const k of Object.keys(params).sort()) qs.set(k, String(params[k]));
  const suffix = qs.toString();
  return suffix ? `hfm:${controller}:${suffix}` : `hfm:${controller}:`;
}

/**
 * Lit/écrit une valeur JSON en cache Redis avec TTL + tags (index inverse pour la purge).
 * - Redis OFF -> exécute simplement le fetcher.
 * - Redis ON  -> GET (hit) sinon MISS : fetcher(), SET EX ttl, SADD 'tag:<tag>' key.
 * - Toute erreur Redis est absorbée : on retombe sur le fetcher (jamais de crash).
 */
export async function cachedJson<T>(
  key: string,
  ttlSeconds: number,
  tags: string[],
  fetcher: () => Promise<T>,
): Promise<T> {
  if (!redis || !redisUsable()) return fetcher();

  try {
    const hit = await redis.get<T>(key);
    if (hit != null) return hit;
  } catch (err) {
    // Lecture Redis en échec : on ignore et on va chercher la donnée fraîche.
    redisFailed(err);
    return fetcher();
  }

  const value = await fetcher();

  // On ne met JAMAIS en cache une réponse d'erreur du bridge (ex. { error: 'not_found' })
  // ni une valeur vide : sinon une panne transitoire serait figée pour tout le TTL et
  // servie à tous les visiteurs. On renvoie la valeur sans la persister.
  if (value == null || (typeof value === 'object' && 'error' in (value as object))) {
    return value;
  }

  try {
    await redis.set(key, value, { ex: ttlSeconds });
    if (tags.length) {
      await Promise.all(
        tags.map((tag) => redis.sadd(tagIndexKey(tag), key)),
      );
    }
  } catch (err) {
    // Écriture Redis en échec : on renvoie quand même la valeur fraîche.
    redisFailed(err);
  }

  return value;
}

/**
 * Purge par tags :
 * - Redis : SMEMBERS 'tag:<tag>' -> DEL toutes les clés + DEL le set.
 * - Next : revalidateTag(tag) pour purger aussi le Data Cache.
 * Tolérant aux erreurs (chaque étape est isolée).
 */
export async function purgeTags(tags: string[]): Promise<void> {
  for (const tag of tags) {
    if (redis) {
      try {
        const indexKey = tagIndexKey(tag);
        const keys = await redis.smembers(indexKey);
        if (keys.length) {
          await redis.del(...keys);
          // On retire de l'index UNIQUEMENT les clés qu'on vient de purger (srem), pas le set
          // entier (del) : une clé ajoutée entre le SMEMBERS et ici reste indexée donc
          // purgeable au prochain flush, au lieu de devenir un orphelin périmé jusqu'au TTL.
          await redis.srem(indexKey, ...keys);
        }
      } catch {
        // Purge Redis en échec pour ce tag : on continue (revalidateTag ci-dessous).
      }
    }
    try {
      // Next 16 : second argument = fenêtre stale-while-revalidate. 'max' = plus longue.
      revalidateTag(tag, 'max');
    } catch {
      // revalidateTag hors contexte de requête / erreur : ignoré.
    }
  }
}
