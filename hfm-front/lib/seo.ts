import { locales, defaultLocale, idLangFor, localeMeta, type Locale } from '@/lib/i18n-config';

// Base publique du site (canonique, OG, sitemap). PUBLIC_BASE_URL en prod.
const RAW_BASE = (process.env.PUBLIC_BASE_URL ?? '').replace(/\/$/, '');

// Garde-fou go-live : en prod indexable, un PUBLIC_BASE_URL absent ou pointant sur localhost
// ferait émettre des canoniques / hreflang / sitemap vers localhost -> désastre SEO silencieux.
// On échoue FORT au démarrage plutôt que de laisser passer. En staging (non indexable), le repli
// localhost reste toléré pour le dev.
if (process.env.SITE_INDEXABLE === 'true' && (!RAW_BASE || /localhost|127\.0\.0\.1/.test(RAW_BASE))) {
  throw new Error(
    '[SEO] PUBLIC_BASE_URL manquant ou localhost alors que SITE_INDEXABLE=true. '
    + 'Définir PUBLIC_BASE_URL sur l’URL publique (https://…) avant la mise en ligne.',
  );
}

export const SITE_URL = RAW_BASE || 'http://localhost:3000';

export function urlFor(locale: string, path = ''): string {
  return `${SITE_URL}/${locale}${path}`;
}

// Bloc alternates de Next Metadata : canonique de la locale courante +
// hreflang vers les 22 locales + x-default sur la locale par défaut.
// NB : ne PAS utiliser pour produit/catégorie/blog dont le slug varie par langue
// -> passer par alternatesFromSlugs (sinon les hreflang pointent vers des 404).
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

// og:locale attend `language_TERRITORY` (fr_FR) : `fr` seul est ignoré par Facebook, qui retombe
// alors sur en_US. On reconstruit depuis le pays déjà porté par localeMeta (fr->FR, en->GB, ja->JP…).
export function ogLocale(locale: string): string {
  const country = localeMeta[locale as Locale]?.country;
  return country ? `${locale}_${country.toUpperCase()}` : locale;
}

// Bloc Open Graph + Twitter Card partagé (parité avec l'ancien site qui avait l'OG partout).
// À étaler dans generateMetadata : `...socialMeta(locale, path, title, description)`.
// NB1 : og:type=product (fiche) se gère à part — Next n'émet pas les types hors de son enum
// (et son champ `other` sortirait un <meta name=…>, invalide pour l'OG).
// NB2 : sans `images`, on retombe sur l'image générée 1200×630 (route /api/og). Aucun visuel du
// projet n'était exploitable (le plus grand : 710×553 ; la hero 299×480 en portrait passait sous
// le minimum Twitter de 300×157 -> carte dégradée sur toutes les pages sans visuel propre).
export const OG_DEFAULT_IMAGE = `${SITE_URL}/api/og`;

export function socialMeta(
  locale: string,
  path: string,
  title: string,
  description?: string,
  images?: string[],
) {
  const imgs = images && images.length ? images : [OG_DEFAULT_IMAGE];
  return {
    openGraph: {
      title,
      description,
      url: urlFor(locale, path),
      siteName: 'Hyaluronic Filler Market',
      locale: ogLocale(locale),
      type: 'website' as const,
      images: imgs,
    },
    twitter: {
      card: 'summary_large_image' as const,
      title,
      description,
      images: imgs,
    },
  };
}

// hreflang quand le SLUG varie d'une langue à l'autre (produit, catégorie, article de blog).
// `pathFor(idLang)` renvoie le chemin relatif (sans /{locale}) pour cette langue PrestaShop,
// ou null si la langue n'a pas d'entrée dédiée -> on retombe alors sur la locale par défaut
// (cohérent avec une page qui rend le contenu par défaut, ex. blog non traduit).
export function alternatesFromSlugs(
  locale: string,
  currentPath: string,
  pathFor: (idLang: number) => string | null,
) {
  const languages: Record<string, string> = {};
  const defPath = pathFor(idLangFor(defaultLocale));
  for (const l of locales) {
    const p = pathFor(idLangFor(l)) ?? defPath;
    if (p) {
      languages[l] = urlFor(l, p);
    }
  }
  if (defPath) {
    languages['x-default'] = urlFor(defaultLocale, defPath);
  }
  return {
    canonical: urlFor(locale, currentPath),
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

// Sérialise un objet JSON-LD pour injection dans <script type="application/ld+json">.
// Échappe < > & (et séparateurs de ligne JS) pour qu'aucune donnée produit ne puisse
// fermer la balise (</script>) ni casser le parsing -> à utiliser partout au lieu de JSON.stringify.
export function jsonLdString(data: unknown): string {
  return JSON.stringify(data)
    .replace(/</g, '\\u003c')
    .replace(/>/g, '\\u003e')
    .replace(/&/g, '\\u0026')
    .replace(/\u2028/g, '\\u2028')
    .replace(/\u2029/g, '\\u2029');
}

// Liste de produits structurée (page catégorie) -> parité ancien site (ItemList).
export function itemListJsonLd(items: { name: string; url: string }[]) {
  return {
    '@context': 'https://schema.org',
    '@type': 'ItemList',
    itemListElement: items.map((it, i) => ({
      '@type': 'ListItem',
      position: i + 1,
      url: it.url,
      name: it.name,
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
