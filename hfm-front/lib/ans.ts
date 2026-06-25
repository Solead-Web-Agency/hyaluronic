// Vérification d'un n° RPPS au registre officiel ANS (API FHIR « Annuaire Santé »).
// Actif uniquement si ANS_API_KEY est défini (clé gratuite via https://portal.api.esante.gouv.fr).
// Sans clé : on ne bloque pas (la validation Luhn côté bridge fait foi).
const KEY = process.env.ANS_API_KEY || '';
const BASE = process.env.ANS_BASE_URL || 'https://gateway.api.esante.gouv.fr/fhir/v2';

export const ansConfigured = !!KEY;

export type AnsResult = { found: boolean; name?: string; profession?: string; error?: string };

// L'API ANS est lente (~15-30 s). On laisse un timeout large, et on est "fail-open" :
// on ne bloque QUE si l'ANS répond clairement "0 résultat". Toute erreur/lenteur -> on n'empêche
// pas la commande (le contrôle Luhn côté bridge reste la garantie de cohérence).
const TIMEOUT_MS = Number(process.env.ANS_TIMEOUT_MS || 30000);

/** Cherche un Practitioner par identifiant RPPS. found=false UNIQUEMENT si l'ANS confirme l'absence. */
export async function verifyRpps(rpps: string): Promise<AnsResult> {
  if (!KEY) return { found: true }; // non configuré -> ne bloque pas
  const ctrl = new AbortController();
  const to = setTimeout(() => ctrl.abort(), TIMEOUT_MS);
  try {
    const r = await fetch(`${BASE}/Practitioner?identifier=${encodeURIComponent(rpps)}`, {
      headers: { 'ESANTE-API-KEY': KEY, Accept: 'application/fhir+json' },
      cache: 'no-store',
      signal: ctrl.signal,
    });
    if (!r.ok) return { found: true, error: 'ans_http_' + r.status }; // fail-open
    const bundle = await r.json();
    const entries = Array.isArray(bundle?.entry) ? bundle.entry : [];
    const total = typeof bundle?.total === 'number' ? bundle.total : entries.length;
    if (total > 0 && entries.length > 0) {
      const nm = entries[0]?.resource?.name?.[0];
      const name = nm ? [(nm.given || []).join(' '), nm.family].filter(Boolean).join(' ').trim() : undefined;
      return { found: true, name: name || undefined };
    }
    return { found: false }; // l'ANS a répondu : numéro absent du registre
  } catch {
    return { found: true, error: 'ans_unreachable' }; // fail-open (timeout/réseau)
  } finally {
    clearTimeout(to);
  }
}
