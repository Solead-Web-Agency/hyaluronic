import type { Metadata } from 'next';
import { Suspense } from 'react';
import { setRequestLocale, getTranslations } from 'next-intl/server';
import { bridgeGetCached } from '@/lib/ps';
import { CACHE_TAGS, CACHE_TTL } from '@/lib/cacheContract';
import { idLangFor } from '@/lib/i18n-config';
import { alternatesFor } from '@/lib/seo';
import Chrome from '../../components/Chrome';
import Footer from '../../components/Footer';
import CatalogueClient, { type CatalogueInitial } from './CatalogueClient';
import { PRODUCT_PAGE_SIZE } from '@/lib/useProductPagination';

type Search = { category?: string; brand?: string; q?: string; filter?: string };

// Premier état du catalogue rendu CÔTÉ SERVEUR (SEO) : SEULEMENT la page 1 (48 produits) du filtre
// courant + son TOTAL + la taxonomie de la sidebar. Le client reprend la main : il va chercher les
// pages suivantes à la demande (infinite scroll) et refait la page 1 quand on change le tri.
async function fetchInitial(search: Search, locale: string): Promise<CatalogueInitial> {
  const idLang = idLangFor(locale);
  const category = search.category ?? null;
  const brand = search.brand ?? null;
  const q = search.q ?? null;
  const filter = search.filter ?? null;

  // Ordre initial = « position » (défaut), aligné sur le tri « Populaires » du client : le rendu
  // serveur correspond donc au tri par défaut, aucun refetch au montage.
  const productParams: Record<string, string | number> = { limit: PRODUCT_PAGE_SIZE, page: 1, order: 'position', id_lang: idLang };
  if (category) productParams.id_category = category;
  else if (brand) productParams.id_manufacturer = brand;
  else if (q) productParams.q = q;
  else if (filter) productParams.filter = filter;

  const opts = { ttl: CACHE_TTL.products, tags: [CACHE_TAGS.products] };
  const taxoOpts = { ttl: CACHE_TTL.taxonomy, tags: [CACHE_TAGS.taxonomy] };
  const [page1, cats, manus] = await Promise.all([
    bridgeGetCached('products', productParams, opts)
      .then((d) => ({ products: d.products ?? [], total: Number(d.total ?? (d.products?.length ?? 0)) }))
      .catch(() => ({ products: [], total: 0 })),
    bridgeGetCached('taxonomy', { action: 'categories', id_lang: idLang }, taxoOpts).then((d) => d.categories ?? []).catch(() => []),
    bridgeGetCached('taxonomy', { action: 'manufacturers', id_lang: idLang }, taxoOpts).then((d) => d.manufacturers ?? []).catch(() => []),
  ]);

  return {
    search: { category, brand, q, filter },
    products: page1.products,
    total: page1.total,
    order: 'position',
    idLang,
    cats,
    manus,
  };
}

export async function generateMetadata({ params }: { params: Promise<{ locale: string }> }): Promise<Metadata> {
  const { locale } = await params;
  const tc = await getTranslations({ locale, namespace: 'common' });

  return {
    title: tc('catalogue'),
    // Canonique sans paramètres : les filtres ne créent pas de contenu dupliqué.
    alternates: alternatesFor(locale, '/catalogue'),
  };
}

export default async function CataloguePage({ params, searchParams }: { params: Promise<{ locale: string }>; searchParams: Promise<Search> }) {
  const { locale } = await params;
  setRequestLocale(locale);
  const search = await searchParams;
  const t = await getTranslations('catalogue');
  const initial = await fetchInitial(search, locale);

  return (
    <div style={{ fontFamily: "'Hanken Grotesk',sans-serif", color: '#34352F', background: 'radial-gradient(1100px 560px at 82% -6%, rgba(140,198,63,0.13), transparent 58%), radial-gradient(820px 520px at -8% 14%, rgba(95,184,154,0.10), transparent 55%), radial-gradient(700px 600px at 50% 118%, rgba(140,198,63,0.08), transparent 60%), #F3F4EF', minHeight: '100vh' }}>
      <Chrome />
      <Suspense fallback={<main className="hfm-wrap" style={{ maxWidth: '1340px', margin: '0 auto', padding: '34px 28px 70px', color: '#8A8170' }}>{t('loadingCatalogue')}</main>}>
        {/* key = filtre courant : changer de filtre RÉINITIALISE la pagination (remonte le client
            avec la page 1 fraîche, remet le tri par défaut et le total du nouveau filtre). */}
        <CatalogueClient key={`${search.category ?? ''}|${search.brand ?? ''}|${search.q ?? ''}|${search.filter ?? ''}`} initial={initial} />
      </Suspense>
      <Footer />
    </div>
  );
}
