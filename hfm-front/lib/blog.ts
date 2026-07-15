// Fetchers du blog (module PrestaShop ph_simpleblog via le bridge hfmstorefront).
// Lecture serveur uniquement. Le bridge convertit le corps Elementor -> HTML,
// et sert des URLs de cover absolues. Cache event-driven (tag « blog », TTL 24 h).
import { bridgeGetCached } from './ps';
import { CACHE_TAGS, CACHE_TTL } from './cacheContract';
import { idLangFor } from './i18n-config';

export type BlogCover = { src: string; wide: string; thumb: string } | null;

export type BlogCategory = { id: number; name: string; slug: string; alternates?: Record<string, string> };

export type BlogCard = {
  id: number;
  slug: string;
  title: string;
  excerpt: string;
  cover: BlogCover;
  category: BlogCategory;
  author: string;
  date: string;
  views: number;
  isFeatured: boolean;
};

export type BlogPost = BlogCard & {
  metaTitle: string;
  metaDescription: string;
  content: string;
  dateUpd: string | null;
  videoCode: string;
  externalUrl: string;
  tags: string[];
  relatedProductIds: number[];
  // hreflang : slug article + slug catégorie par langue (id_lang -> {category, slug}).
  alternates?: Record<string, { category?: string; slug?: string }>;
};

export type BlogList = {
  posts: BlogCard[];
  total: number;
  page: number;
  pages: number;
  category: BlogCategory | null;
};

export type BlogCategoryCount = BlogCategory & { count: number };

const opts = { ttl: CACHE_TTL.blog, tags: [CACHE_TAGS.blog] };

export async function fetchBlogList(
  locale: string,
  { page = 1, limit = 9, category }: { page?: number; limit?: number; category?: string | null } = {},
): Promise<BlogList> {
  const params: Record<string, string | number> = {
    action: 'list',
    id_lang: idLangFor(locale),
    page,
    limit,
  };
  if (category) params.category = category;
  const d = await bridgeGetCached('blog', params, opts).catch(() => null);
  return {
    posts: d?.posts ?? [],
    total: d?.total ?? 0,
    page: d?.page ?? 1,
    pages: d?.pages ?? 0,
    category: d?.category ?? null,
  };
}

export async function fetchBlogPost(locale: string, slug: string): Promise<BlogPost | null> {
  const d = await bridgeGetCached(
    'blog',
    { action: 'post', id_lang: idLangFor(locale), slug },
    opts,
  ).catch(() => null);
  return d?.post ?? null;
}

export async function fetchBlogCategories(locale: string): Promise<BlogCategoryCount[]> {
  const d = await bridgeGetCached(
    'blog',
    { action: 'categories', id_lang: idLangFor(locale) },
    opts,
  ).catch(() => null);
  return d?.categories ?? [];
}

export async function fetchBlogLatest(locale: string, limit = 3): Promise<BlogCard[]> {
  const d = await bridgeGetCached(
    'blog',
    { action: 'latest', id_lang: idLangFor(locale), limit },
    opts,
  ).catch(() => null);
  return d?.posts ?? [];
}
