import type { MetadataRoute } from 'next';
import { bridgeGetCached } from '@/lib/ps';
import { CACHE_TAGS, CACHE_TTL } from '@/lib/cacheContract';
import { locales, defaultLocale, idLangFor } from '@/lib/i18n-config';
import { urlFor } from '@/lib/seo';

// Un bloc hreflang par URL : chaque entrée pointe ses 22 variantes de langue.
function entry(path: string, changeFrequency: 'daily' | 'weekly', priority: number): MetadataRoute.Sitemap[number] {
  const languages: Record<string, string> = {};
  for (const l of locales) {
    languages[l] = urlFor(l, path);
  }
  return {
    url: urlFor(defaultLocale, path),
    changeFrequency,
    priority,
    alternates: { languages },
  };
}

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const out: MetadataRoute.Sitemap = [
    entry('', 'daily', 1),
    entry('/catalogue', 'daily', 0.9),
    ...['levres', 'pommettes', 'cernes', 'rides', 'ovale', 'skinbooster'].map((z) => entry(`/zone/${z}`, 'weekly', 0.7)),
  ];

  // Marques (pages /marque/[id]).
  try {
    const taxo = await bridgeGetCached(
      'taxonomy',
      { action: 'manufacturers', id_lang: idLangFor(defaultLocale) },
      { ttl: CACHE_TTL.products, tags: [CACHE_TAGS.products] },
    );
    for (const m of taxo.manufacturers ?? []) {
      if (m?.id_manufacturer) {
        out.push(entry(`/marque/${m.id_manufacturer}`, 'weekly', 0.6));
      }
    }
  } catch {
    // Bridge indisponible : le sitemap reste valable avec les pages statiques.
  }

  // Produits actifs (le bridge pagine à 300 max par appel).
  try {
    for (let page = 1; page <= 10; page++) {
      const data = await bridgeGetCached(
        'products',
        { limit: 300, page, id_lang: idLangFor(defaultLocale) },
        { ttl: CACHE_TTL.products, tags: [CACHE_TAGS.products] },
      );
      const products = data.products ?? [];
      for (const p of products) {
        if (p?.id_product) {
          out.push(entry(`/produit/${p.id_product}`, 'weekly', 0.8));
        }
      }
      if (products.length < 300) {
        break;
      }
    }
  } catch {
    // Idem : dégradation propre.
  }

  return out;
}
