import sanitizeHtml from 'sanitize-html';

// Sanitisation des HTML venant de la base PrestaShop (descriptions produit, pages
// CMS) avant rendu via dangerouslySetInnerHTML. Liste BLANCHE : tout ce qui n'y
// figure pas saute — scripts, handlers on*, iframes, styles inline, classes…
// La mise en forme est prise en charge par la charte du site (.hfm-richtext).
// À n'utiliser que CÔTÉ SERVEUR (la lib n'est pas embarquée dans le bundle client).
const OPTIONS: sanitizeHtml.IOptions = {
  allowedTags: [
    'h1', 'h2', 'h3', 'h4', 'p', 'br', 'hr',
    'ul', 'ol', 'li',
    'strong', 'b', 'em', 'i', 'u', 'sub', 'sup', 'blockquote',
    'a', 'img', 'span', 'div', 'figure', 'figcaption',
    'table', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th',
  ],
  allowedAttributes: {
    a: ['href', 'title'],
    img: ['src', 'alt', 'title'],
    td: ['colspan', 'rowspan'],
    th: ['colspan', 'rowspan'],
  },
  allowedSchemes: ['http', 'https', 'mailto'],
  // Liens externes : pas de fuite d'opener.
  transformTags: {
    a: sanitizeHtml.simpleTransform('a', { rel: 'noopener noreferrer' }),
  },
};

export function sanitizeCatalogHtml(html: string): string {
  return sanitizeHtml(html ?? '', OPTIONS);
}

// Reformate les descriptions « <p><strong>Label :</strong> texte</p> » (anciens
// contenus générés) en vraies sections titrées « <h3>Label</h3><p>texte</p> ».
// Le deux-points est exigé : un simple nom en gras en début de phrase reste intact.
// S'applique APRÈS sanitisation (n'opère que sur des balises déjà propres).
export function structureDescription(html: string): string {
  return sanitizeCatalogHtml(html)
    .replace(/<p[^>]*>\s*<strong>([^<]{2,80}?)\s*:\s*<\/strong>\s*:?\s*/gi, '<h3>$1</h3><p>')
    .replace(/<p[^>]*>\s*<strong>([^<]{2,80}?)\s*<\/strong>\s*:\s*/gi, '<h3>$1</h3><p>')
    .replace(/<p[^>]*>\s*<\/p>/gi, '');
}
