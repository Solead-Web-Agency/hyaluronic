import { notFound, permanentRedirect } from 'next/navigation';
import { bridgeGetCached } from '@/lib/ps';
import { CACHE_TAGS, CACHE_TTL } from '@/lib/cacheContract';
import { idLangFor } from '@/lib/i18n-config';

// Ancienne URL par id -> redirection 301 vers l'URL canonique /{categorie}/{slug}
// (parité prod). Conserve les liens internes historiques et le SEO des anciennes URLs.
export default async function ProductByIdRedirect({ params }: { params: Promise<{ locale: string; id: string }> }) {
  const { locale, id } = await params;
  const data = await bridgeGetCached(
    'products',
    { id_product: id, id_lang: idLangFor(locale) },
    { ttl: CACHE_TTL.products, tags: [CACHE_TAGS.products] },
  ).catch(() => ({} as Record<string, any>));
  const p = (data as { product?: Record<string, any> }).product;
  if (!p?.link_rewrite) notFound();
  const cat = p.category || 'produit';
  permanentRedirect(`/${locale}/${cat}/${p.link_rewrite}`);
}
