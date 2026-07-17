// Historique « Déjà vus » (parité ancien site : bloc ps_viewedproduct, 8 produits).
// L'ancien site le stockait côté serveur (cookie PS) ; en headless on le garde côté client :
// aucune donnée personnelle ne part au serveur, et la fiche produit reste cacheable.
const KEY = 'hfm_viewed';
const MAX = 12; // on en garde un peu plus que les 8 affichés (le produit courant est exclu).

export function readViewed(): number[] {
  try {
    const raw = localStorage.getItem(KEY);
    const a: unknown = raw ? JSON.parse(raw) : [];
    return Array.isArray(a) ? a.filter((x): x is number => typeof x === 'number' && x > 0) : [];
  } catch {
    return []; // stockage bloqué (Safari, politique d'entreprise) : pas d'historique, pas d'erreur.
  }
}

export function pushViewed(id: number): void {
  if (!id) return;
  try {
    const next = [id, ...readViewed().filter((x) => x !== id)].slice(0, MAX);
    localStorage.setItem(KEY, JSON.stringify(next));
  } catch {
    /* ignore */
  }
}
