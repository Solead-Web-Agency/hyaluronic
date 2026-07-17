// Comparateur de produits (parité ancien site : iqitcompare).
// Sélection gardée côté client (localStorage) : aucune page ne devient dépendante du visiteur,
// tout le catalogue reste cacheable.
export const COMPARE_MAX = 4;
export const COMPARE_EVENT = 'hfm:compare-changed';
const KEY = 'hfm_compare';

export function readCompare(): number[] {
  try {
    const raw = localStorage.getItem(KEY);
    const a: unknown = raw ? JSON.parse(raw) : [];
    return Array.isArray(a) ? a.filter((x): x is number => typeof x === 'number' && x > 0).slice(0, COMPARE_MAX) : [];
  } catch {
    return []; // stockage bloqué : pas de comparateur, pas d'erreur.
  }
}

function write(ids: number[]): void {
  try {
    localStorage.setItem(KEY, JSON.stringify(ids.slice(0, COMPARE_MAX)));
  } catch {
    /* ignore */
  }
  // Les composants (bouton de carte, barre flottante) vivent dans des arbres React différents :
  // un évènement global les garde synchronisés sans state partagé.
  try {
    window.dispatchEvent(new Event(COMPARE_EVENT));
  } catch {
    /* ignore */
  }
}

/** Ajoute/retire un produit. Renvoie false si la limite est atteinte (rien n'est ajouté). */
export function toggleCompare(id: number): boolean {
  const cur = readCompare();
  if (cur.includes(id)) {
    write(cur.filter((x) => x !== id));
    return true;
  }
  if (cur.length >= COMPARE_MAX) {
    return false;
  }
  write([...cur, id]);
  return true;
}

export function removeCompare(id: number): void {
  write(readCompare().filter((x) => x !== id));
}

export function clearCompare(): void {
  write([]);
}
