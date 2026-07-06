import { locales, defaultLocale } from '@/lib/i18n-config';

// Base publique du site (canonique, OG, sitemap). PUBLIC_BASE_URL en prod.
export const SITE_URL = (process.env.PUBLIC_BASE_URL ?? 'http://localhost:3000').replace(/\/$/, '');

export function urlFor(locale: string, path = ''): string {
  return `${SITE_URL}/${locale}${path}`;
}

// Bloc alternates de Next Metadata : canonique de la locale courante +
// hreflang vers les 22 locales + x-default sur la locale par défaut.
export function alternatesFor(locale: string, path = '') {
  const languages: Record<string, string> = {};
  for (const l of locales) {
    languages[l] = urlFor(l, path);
  }
  languages['x-default'] = urlFor(defaultLocale, path);

  return {
    canonical: urlFor(locale, path),
    languages,
  };
}

// Description propre à partir d'un HTML (meta absente : on retombe sur le texte).
export function textFromHtml(html: string, max = 300): string {
  return html
    .replace(/<[^>]+>/g, ' ')
    .replace(/&nbsp;/gi, ' ')
    .replace(/&amp;/gi, '&')
    .replace(/&#0?39;|&apos;/gi, '’')
    .replace(/&quot;/gi, '"')
    .replace(/\s+/g, ' ')
    .trim()
    .slice(0, max);
}
