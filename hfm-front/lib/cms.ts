import fs from 'fs';
import path from 'path';

export type CmsContent = { title: string; content: string; link_rewrite: string };

/**
 * Traduction locale d'une page CMS, stockée côté front : content-cms/{slug}/{locale}.json
 * (les pages juridiques sont traduites dans les 22 langues ; fr/ja restent servies par PrestaShop).
 * Lecture serveur uniquement (composant serveur).
 */
export function getLocalCms(slug: string, locale: string): CmsContent | null {
  try {
    const file = path.join(process.cwd(), 'content-cms', slug, `${locale}.json`);
    const d = JSON.parse(fs.readFileSync(file, 'utf8'));
    if (d && typeof d.content === 'string' && d.content.trim() !== '') {
      return { title: d.title || '', content: d.content, link_rewrite: slug };
    }
  } catch {
    /* pas de traduction locale -> on retombera sur PrestaShop */
  }
  return null;
}
