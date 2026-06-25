/**
 * Prépare le HTML d'une page CMS pour l'affichage « légal » (façon table des matières) :
 *  - supprime un éventuel <h1> en tête (le titre est déjà affiché dans le hero) ;
 *  - promeut les paragraphes 100 % gras courts (<p><strong>Titre</strong></p>) en <h2> ;
 *  - ajoute un id d'ancrage à chaque <h2> ;
 *  - renvoie le sommaire (liste des <h2>) et un sous-titre dérivé du 1er vrai paragraphe.
 * Tout est fait par regex côté serveur (pas de DOM) ; robuste aux langues non-latines.
 */
export type TocItem = { id: string; text: string };
export type PreparedCms = { html: string; toc: TocItem[]; subtitle: string };

function stripTags(s: string): string {
  return s.replace(/<[^>]+>/g, '').replace(/&nbsp;/g, ' ').replace(/\s+/g, ' ').trim();
}

function decodeEntities(s: string): string {
  return s
    .replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>')
    .replace(/&quot;/g, '"').replace(/&#0?39;|&apos;/g, "'").replace(/&rsquo;/g, '’');
}

function slugify(text: string, index: number): string {
  const base = stripTags(text)
    .toLowerCase()
    .normalize('NFD').replace(/[̀-ͯ]/g, '') // enlève les diacritiques
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
    .slice(0, 60);
  return base || `section-${index + 1}`;
}

export function prepareCms(rawHtml: string): PreparedCms {
  let html = rawHtml || '';

  // 1) Retire un <h1> de tête éventuel (doublon du titre affiché dans le hero).
  html = html.replace(/^\s*<h1[^>]*>[\s\S]*?<\/h1>\s*/i, '');

  // 2) Si la page n'a pas déjà de vraie structure de sections (< 2 <h2>),
  //    on promeut les <p><strong>Titre court</strong></p> (gras = tout le paragraphe) en <h2>.
  //    Sinon on n'y touche pas : les passages en gras restent de l'emphase, pas des titres.
  const existingH2 = (html.match(/<h2[\s>]/gi) || []).length;
  if (existingH2 < 2) {
    html = html.replace(
      /<p>\s*<strong>([^<]{1,90})<\/strong>\s*<\/p>/gi,
      (_m, t) => `<h2>${t.trim()}</h2>`
    );
  }

  // 3) Ajoute des ids uniques aux <h2> et collecte le sommaire.
  const toc: TocItem[] = [];
  const used = new Set<string>();
  let i = 0;
  html = html.replace(/<h2([^>]*)>([\s\S]*?)<\/h2>/gi, (_m, attrs: string, inner: string) => {
    const text = decodeEntities(stripTags(inner));
    if (!text) return `<h2${attrs}>${inner}</h2>`;
    let id = slugify(text, i);
    while (used.has(id)) id = `${id}-${i}`;
    used.add(id);
    toc.push({ id, text });
    i++;
    // évite de dupliquer un id déjà présent
    const cleanedAttrs = attrs.replace(/\sid="[^"]*"/i, '');
    return `<h2${cleanedAttrs} id="${id}">${inner}</h2>`;
  });

  // 4) Sous-titre = premier vrai paragraphe (> 40 caractères), tronqué.
  let subtitle = '';
  const pRe = /<p[^>]*>([\s\S]*?)<\/p>/gi;
  let m: RegExpExecArray | null;
  while ((m = pRe.exec(html))) {
    const t = decodeEntities(stripTags(m[1]));
    if (t.length > 40) { subtitle = t; break; }
  }
  if (subtitle.length > 180) subtitle = subtitle.slice(0, 177).replace(/\s+\S*$/, '') + '…';

  return { html, toc, subtitle };
}
