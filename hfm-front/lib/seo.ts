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

// --- Données structurées schema.org (parité avec l'ancien site) ---

// Organization : identité de l'entreprise (site-wide). NB : pas de logo tant que le vrai
// logo HFM n'est pas configuré côté PS (PS_LOGO pointe sur une marque tierce).
export function organizationJsonLd() {
  return {
    '@context': 'https://schema.org',
    '@type': 'Organization',
    name: 'Hyaluronic Filler Market',
    url: SITE_URL,
    description: 'Spécialiste des produits injectables esthétiques (acide hyaluronique, mésothérapie, skinbooster) pour les professionnels.',
  };
}

// WebSite + SearchAction : active la sitelinks searchbox (recherche -> /catalogue?q=).
export function websiteJsonLd(locale: string) {
  return {
    '@context': 'https://schema.org',
    '@type': 'WebSite',
    name: 'Hyaluronic Filler Market',
    url: SITE_URL,
    potentialAction: {
      '@type': 'SearchAction',
      target: {
        '@type': 'EntryPoint',
        urlTemplate: `${SITE_URL}/${locale}/catalogue?q={search_term_string}`,
      },
      'query-input': 'required name=search_term_string',
    },
  };
}

// Fil d'Ariane structuré (produit, catégorie, article).
export function breadcrumbJsonLd(items: { name: string; url: string }[]) {
  return {
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: items.map((it, i) => ({
      '@type': 'ListItem',
      position: i + 1,
      name: it.name,
      item: it.url,
    })),
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
