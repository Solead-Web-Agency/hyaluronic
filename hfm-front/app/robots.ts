import type { MetadataRoute } from 'next';
import { SITE_URL } from '@/lib/seo';

// Indexation gérée par variable d'env : le staging (vercel.app) reste NON indexé
// pour éviter le duplicate content avec le site live. Au cutover (domaine final),
// mettre SITE_INDEXABLE=true dans les variables Vercel pour autoriser l'index.
const INDEXABLE = process.env.SITE_INDEXABLE === 'true';

export default function robots(): MetadataRoute.Robots {
  if (!INDEXABLE) {
    // Staging : on bloque tout le crawl (pas d'index tant que la bascule n'est pas faite).
    return {
      rules: { userAgent: '*', disallow: '/' },
    };
  }
  return {
    rules: {
      userAgent: '*',
      allow: '/',
      // Espaces privés et API : inutiles à l'index (routes réelles : checkout / compte / favoris).
      disallow: ['/api/', '/*/checkout', '/*/compte', '/*/favoris'],
    },
    sitemap: `${SITE_URL}/sitemap.xml`,
  };
}
