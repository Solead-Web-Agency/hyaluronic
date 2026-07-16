import type { MetadataRoute } from 'next';
import { bridgeGetCached } from '@/lib/ps';
import { CACHE_TAGS, CACHE_TTL } from '@/lib/cacheContract';
import { locales, defaultLocale, idLangFor } from '@/lib/i18n-config';
import { urlFor } from '@/lib/seo';

type Cf = 'daily' | 'weekly' | 'monthly';
// Slug par langue tel que renvoyé par les endpoints slugmap : { c: slug catégorie, s: slug page }.
type Alt = Record<string, { c?: string | null; s?: string } | undefined>;

// Un bloc hreflang par URL. `altByLocale` (optionnel) = chemin par locale quand le slug varie
// d'une langue à l'autre (produit, catégorie, article) ; sinon même chemin pour les 22 langues.
function entry(path: string, changeFrequency: Cf, priority: number, altByLocale?: Record<string, string>): MetadataRoute.Sitemap[number] {
  const languages: Record<string, string> = {};
  for (const l of locales) {
    languages[l] = urlFor(l, altByLocale ? (altByLocale[l] ?? path) : path);
  }
  return {
    url: urlFor(defaultLocale, altByLocale ? (altByLocale[defaultLocale] ?? path) : path),
    changeFrequency,
    priority,
    alternates: { languages },
  };
}

// Convertit une map { id_lang: {c, s} } en { locale: chemin }, repli sur la langue par défaut
// (cohérent avec les pages : une locale sans slug dédié rend le contenu par défaut à la même URL).
function localePaths(alt: Alt | undefined, build: (c: string | null | undefined, s: string) => string, fallback: string): Record<string, string> {
  const out: Record<string, string> = {};
  const def = alt?.[idLangFor(defaultLocale)];
  const defPath = def?.s ? build(def.c, def.s) : fallback;
  for (const l of locales) {
    const a = alt?.[idLangFor(l)];
    out[l] = a?.s ? build(a.c, a.s) : defPath;
  }
  return out;
}

// Idem pour une map { id_lang: slug } (catégorie catalogue : un seul slug par langue).
function localePathsSimple(alt: Record<string, string> | undefined, build: (s: string) => string, fallback: string): Record<string, string> {
  const out: Record<string, string> = {};
  const defSlug = alt?.[idLangFor(defaultLocale)];
  const defPath = defSlug ? build(defSlug) : fallback;
  for (const l of locales) {
    const s = alt?.[idLangFor(l)];
    out[l] = s ? build(s) : defPath;
  }
  return out;
}

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const out: MetadataRoute.Sitemap = [
    // Pages à URL invariante par langue (même chemin pour les 22 locales).
    entry('', 'daily', 1),
    entry('/catalogue', 'daily', 0.9),
    entry('/blog', 'weekly', 0.7),
    ...['levres', 'pommettes', 'cernes', 'rides', 'ovale', 'skinbooster'].map((z) => entry(`/zone/${z}`, 'weekly', 0.7)),
  ];

  // Pages catégorie /{categorie} (parité prod), avec hreflang par langue (slug traduit).
  try {
    const taxo = await bridgeGetCached(
      'taxonomy',
      { action: 'all_active', id_lang: idLangFor(defaultLocale) },
      { ttl: CACHE_TTL.taxonomy, tags: [CACHE_TAGS.taxonomy] },
    );
    for (const c of taxo.categories ?? []) {
      if (c?.link_rewrite) {
        const fr = `/${c.link_rewrite}`;
        out.push(entry(fr, 'weekly', 0.7, localePathsSimple(c.alternates, (s) => `/${s}`, fr)));
      }
    }
  } catch {
    // Bridge indisponible : sitemap reste valable sans les catégories.
  }

  // Articles de blog + catégories blog, hreflang par langue (slug article ET catégorie traduits).
  try {
    const blog = await bridgeGetCached(
      'blog',
      { action: 'slugmap', id_lang: idLangFor(defaultLocale) },
      { ttl: CACHE_TTL.blog, tags: [CACHE_TAGS.blog] },
    );
    const catAlt = new Map<string, Record<string, string>>(); // slug FR catégorie -> { id_lang: slug }
    for (const it of blog.items ?? []) {
      const alt = it?.alt as Alt | undefined;
      const fr = alt?.[idLangFor(defaultLocale)];
      if (!fr?.s || !fr?.c) {
        continue;
      }
      const canonical = `/blog/${fr.c}/${fr.s}`;
      out.push(entry(canonical, 'monthly', 0.6, localePaths(alt, (c, s) => `/blog/${c || 'article'}/${s}`, canonical)));
      if (!catAlt.has(fr.c)) {
        const m: Record<string, string> = {};
        for (const [idLang, e] of Object.entries(alt ?? {})) {
          if (e?.c) m[idLang] = e.c;
        }
        catAlt.set(fr.c, m);
      }
    }
    for (const [frCat, alt] of catAlt) {
      const canonical = `/blog/${frCat}`;
      out.push(entry(canonical, 'weekly', 0.5, localePathsSimple(alt, (s) => `/blog/${s}`, canonical)));
    }
  } catch {
    // Bridge indisponible : le sitemap reste valable sans les articles.
  }

  // Marques (pages /marque/[id]) : URL par id, invariante par langue.
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

  // Produits actifs, hreflang par langue (slug produit ET slug catégorie traduits) via slugmap.
  try {
    const data = await bridgeGetCached(
      'products',
      { action: 'slugmap', id_lang: idLangFor(defaultLocale) },
      { ttl: CACHE_TTL.products, tags: [CACHE_TAGS.products] },
    );
    for (const it of data.items ?? []) {
      const alt = it?.alt as Alt | undefined;
      const fr = alt?.[idLangFor(defaultLocale)];
      if (!fr?.s) {
        continue;
      }
      // Repli /produit/{id} (redirection 308) si pas de catégorie -> pas d'hreflang traduit.
      if (!fr.c) {
        out.push(entry(`/produit/${it.id}`, 'weekly', 0.8));
        continue;
      }
      const canonical = `/${fr.c}/${fr.s}`;
      out.push(entry(canonical, 'weekly', 0.8, localePaths(alt, (c, s) => `/${c || 'produit'}/${s}`, canonical)));
    }
  } catch {
    // Idem : dégradation propre.
  }

  // Dédoublonnage défensif : la base porte des slugs DUPLIQUÉS sur des entités distinctes
  // (2 catégories « neauvia », 2 produits « silhouette-soft-8-cones », 2 articles…), qui
  // produisent la même URL canonique. Google dédoublonne, mais un sitemap propre évite le bruit.
  const seen = new Set<string>();
  return out.filter((e) => {
    const u = String(e.url);
    if (seen.has(u)) {
      return false;
    }
    seen.add(u);
    return true;
  });
}
