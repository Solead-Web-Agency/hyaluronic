import type { MetadataRoute } from 'next';
import { bridgeGetCached } from '@/lib/ps';
import { CACHE_TAGS, CACHE_TTL } from '@/lib/cacheContract';
import { locales, defaultLocale, idLangFor } from '@/lib/i18n-config';
import { urlFor } from '@/lib/seo';

// Un bloc hreflang par URL : chaque entrée pointe ses 22 variantes de langue.
function entry(path: string, changeFrequency: 'daily' | 'weekly' | 'monthly', priority: number): MetadataRoute.Sitemap[number] {
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
    entry('/blog', 'weekly', 0.7),
    ...['levres', 'pommettes', 'cernes', 'rides', 'ovale', 'skinbooster'].map((z) => entry(`/zone/${z}`, 'weekly', 0.7)),
  ];

  // Pages catégorie /{categorie} (parité prod : catégories actives ayant des produits).
  try {
    const taxo = await bridgeGetCached(
      'taxonomy',
      { action: 'all_active', id_lang: idLangFor(defaultLocale) },
      { ttl: CACHE_TTL.taxonomy, tags: [CACHE_TAGS.taxonomy] },
    );
    for (const c of taxo.categories ?? []) {
      if (c?.link_rewrite) {
        out.push(entry(`/${c.link_rewrite}`, 'weekly', 0.7));
      }
    }
  } catch {
    // Bridge indisponible : sitemap reste valable sans les catégories.
  }

  // Articles de blog (module ph_simpleblog).
  try {
    const blog = await bridgeGetCached(
      'blog',
      { action: 'list', limit: 100, id_lang: idLangFor(defaultLocale) },
      { ttl: CACHE_TTL.blog, tags: [CACHE_TAGS.blog] },
    );
    const seenCats = new Set<string>();
    for (const post of blog.posts ?? []) {
      const cat = post?.category?.slug;
      if (post?.slug && cat) {
        out.push(entry(`/blog/${cat}/${post.slug}`, 'monthly', 0.6));
        if (!seenCats.has(cat)) {
          seenCats.add(cat);
          out.push(entry(`/blog/${cat}`, 'weekly', 0.5));
        }
      }
    }
  } catch {
    // Bridge indisponible : le sitemap reste valable sans les articles.
  }

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
        // URL canonique « parité prod » : /{categorie}/{slug} (repli /produit/{id}).
        if (p?.category && p?.link_rewrite) {
          out.push(entry(`/${p.category}/${p.link_rewrite}`, 'weekly', 0.8));
        } else if (p?.id_product) {
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
