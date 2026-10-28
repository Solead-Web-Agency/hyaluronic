import type { Metadata } from 'next';
import { redirect } from 'next/navigation';
import { setRequestLocale, getTranslations } from 'next-intl/server';
import { bridgeGetCached } from '@/lib/ps';
import { CACHE_TAGS, CACHE_TTL } from '@/lib/cacheContract';
import { idLangFor } from '@/lib/i18n-config';
import { alternatesFor } from '@/lib/seo';
import { Link } from '@/i18n/navigation';
import { fetchAllProducts, fetchMenu, listingFacets } from '@/lib/facets';
import { ROOT_BRANDS } from '@/lib/menu';
import Chrome from '../../components/Chrome';
import Footer from '../../components/Footer';
import ProductListing from './ProductListing';

type Search = { category?: string; brand?: string; q?: string; filter?: string };

// Filtres dynamiques « Promos & Top » (pas des catégories) -> titre de la page.
const FILTER_TITLES: Record<string, 'promoBest' | 'promoSales' | 'promoNew' | 'promoNoLido'> = {
  best: 'promoBest',
  promo: 'promoSales',
  new: 'promoNew',
  nolido: 'promoNoLido',
};

// ?category={id} (anciens liens, fiche produit) -> la VRAIE page catégorie, dans la langue : elle
// porte l'URL indexable, le fil d'Ariane et les filtres. Catégorie inconnue/vide -> catalogue.
async function categorySlug(id: string, idLang: number): Promise<string | null> {
  const d = await bridgeGetCached(
    'taxonomy',
    { action: 'all_active', id_lang: idLang },
    { ttl: CACHE_TTL.taxonomy, tags: [CACHE_TAGS.taxonomy] },
  ).catch(() => null);
  const cats = (d as { categories?: { id_category: number; link_rewrite: string }[] } | null)?.categories ?? [];
  return cats.find((c) => String(c.id_category) === id)?.link_rewrite ?? null;
}

export async function generateMetadata({ params, searchParams }: { params: Promise<{ locale: string }>; searchParams: Promise<Search> }): Promise<Metadata> {
  const { locale } = await params;
  const search = await searchParams;
  const tc = await getTranslations({ locale, namespace: 'common' });
  const th = await getTranslations({ locale, namespace: 'header' });
  const filterTitle = search.filter && FILTER_TITLES[search.filter] ? th(FILTER_TITLES[search.filter]) : null;

  return {
    title: filterTitle ?? tc('catalogue'),
    // Canonique sans paramètres : les filtres ne créent pas de contenu dupliqué.
    alternates: alternatesFor(locale, '/catalogue'),
  };
}

export default async function CataloguePage({ params, searchParams }: { params: Promise<{ locale: string }>; searchParams: Promise<Search> }) {
  const { locale } = await params;
  setRequestLocale(locale);
  const search = await searchParams;
  const idLang = idLangFor(locale);

  if (search.category) {
    if (search.category === String(ROOT_BRANDS)) redirect(`/${locale}/marque`);
    const slug = await categorySlug(search.category, idLang);
    if (slug) redirect(`/${locale}/${slug}`);
  }
  if (search.brand && /^\d+$/.test(search.brand)) {
    redirect(`/${locale}/marque/${search.brand}`);
  }

  const t = await getTranslations('catalogue');
  const tc = await getTranslations('common');
  const th = await getTranslations('header');
  const q = search.q?.trim() || null;
  const filter = !q && search.filter && FILTER_TITLES[search.filter] ? search.filter : null;

  const productParams: Record<string, string> = {};
  if (q) productParams.q = q;
  else if (filter) productParams.filter = filter;
  const [{ products }, menuRaw] = await Promise.all([
    fetchAllProducts(productParams, idLang),
    fetchMenu(idLang).catch(() => []),
  ]);
  const facets = await listingFacets(products, menuRaw);

  const heading = q ? t('searchHeading', { query: q }) : filter ? th(FILTER_TITLES[filter]) : t('heading');

  return (
    <div style={{ fontFamily: "'Hanken Grotesk',sans-serif", color: '#34352F', background: 'radial-gradient(1100px 560px at 82% -6%, rgba(140,198,63,0.13), transparent 58%), radial-gradient(820px 520px at -8% 14%, rgba(95,184,154,0.10), transparent 55%), radial-gradient(700px 600px at 50% 118%, rgba(140,198,63,0.08), transparent 60%), #F3F4EF', minHeight: '100vh' }}>
      <Chrome />
      <main data-screen-label="Catalogue" className="hfm-wrap" style={{ maxWidth: '1340px', margin: '0 auto', padding: '34px 28px 70px' }}>
        <div style={{ fontSize: '12.5px', color: '#9A9A9A', marginBottom: '18px' }}>
          <Link href="/">{tc('home')}</Link>{'  /  '}
          {q || filter ? <><Link href="/catalogue">{t('breadcrumb')}</Link>{'  /  '}{heading}</> : t('breadcrumb')}
        </div>
        <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: 'clamp(28px,4vw,38px)', color: '#2B2B2B', margin: 0 }}>{heading}</h1>
        <div style={{ fontSize: '14px', color: '#6E7585', marginTop: '8px' }}>{t('productsCount', { count: products.length })}</div>
        {/* key = recherche/filtre courant : en changer remet filtres, tri et défilement à zéro. */}
        <ProductListing key={`${q ?? ''}|${filter ?? ''}`} products={products} facets={facets} search={{ q }} />
      </main>
      <Footer />
    </div>
  );
}
