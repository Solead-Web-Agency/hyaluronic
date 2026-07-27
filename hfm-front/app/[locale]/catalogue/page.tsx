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

type Search = { category?: string; brand?: string; q?: string; filter?: string };

// Premier état du catalogue rendu CÔTÉ SERVEUR (SEO) : produits du filtre courant
// + taxonomie de la sidebar. Le client reprend la main pour les filtres suivants.
async function fetchInitial(search: Search, locale: string): Promise<CatalogueInitial> {
  const idLang = idLangFor(locale);
  const category = search.category ?? null;
  const brand = search.brand ?? null;
  const q = search.q ?? null;
  const filter = search.filter ?? null;

  // Charge TOUT le catalogue actif (~578) : le filtrage se fait côté client, donc une limite basse
  // rendait invisibles ET infiltrables les produits au-delà. 700 = marge au-dessus du catalogue actuel.
  const productParams: Record<string, string | number> = { limit: 700, id_lang: idLang };
  if (category) productParams.id_category = category;
  else if (brand) productParams.id_manufacturer = brand;
  else if (q) productParams.q = q;
  else if (filter) productParams.filter = filter;

  const opts = { ttl: CACHE_TTL.products, tags: [CACHE_TAGS.products] };
  const taxoOpts = { ttl: CACHE_TTL.taxonomy, tags: [CACHE_TAGS.taxonomy] };
  const [products, cats, manus] = await Promise.all([
    bridgeGetCached('products', productParams, opts).then((d) => d.products ?? []).catch(() => []),
    bridgeGetCached('taxonomy', { action: 'categories', id_lang: idLang }, taxoOpts).then((d) => d.categories ?? []).catch(() => []),
    bridgeGetCached('taxonomy', { action: 'manufacturers', id_lang: idLang }, taxoOpts).then((d) => d.manufacturers ?? []).catch(() => []),
  ]);

  return { search: { category, brand, q, filter }, products, cats, manus };
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
        <CatalogueClient initial={initial} />
      </Suspense>
      <Footer />
    </div>
  );
}
