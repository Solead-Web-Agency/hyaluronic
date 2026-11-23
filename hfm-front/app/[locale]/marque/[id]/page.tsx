import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { setRequestLocale, getTranslations } from 'next-intl/server';
import { bridgeGetCached, type ProductCard } from '@/lib/ps';
import { CACHE_TAGS, CACHE_TTL } from '@/lib/cacheContract';
import { idLangFor } from '@/lib/i18n-config';
import { alternatesFor } from '@/lib/seo';
import { fetchAllProducts, fetchMenu, listingFacets, type ListingFacets } from '@/lib/facets';
import Chrome from '../../../components/Chrome';
import Footer from '../../../components/Footer';
import BrandClient, { type Manu } from './BrandClient';

// Marque + produits, récupérés CÔTÉ SERVEUR (SEO).
// `hidden` : tous les produits de la marque sont bloqués dans le pays du visiteur -> page introuvable.
async function fetchBrand(id: string, locale: string): Promise<{ manu: Manu | null; products: ProductCard[]; hidden: boolean }> {
  const idLang = idLangFor(locale);
  let manu: Manu | null = null;
  let products: ProductCard[] = [];
  let hidden = false;
  try {
    const taxo = await bridgeGetCached(
      'taxonomy',
      { action: 'manufacturers', id_lang: idLang },
      { ttl: CACHE_TTL.taxonomy, tags: [CACHE_TAGS.taxonomy] },
    );
    manu = ((taxo.manufacturers ?? []) as Manu[]).find((m) => String(m.id_manufacturer) === id) ?? null;
    hidden = ((taxo.hidden_manufacturers ?? []) as number[]).some((h) => String(h) === id);
  } catch {
    manu = null;
  }
  try {
    // Toute la marque (avant : 48 premiers seulement -> « mccm » tronqué à 48 sur 109).
    products = (await fetchAllProducts({ id_manufacturer: id }, idLang)).products;
  } catch {
    products = [];
  }
  return { manu, products, hidden };
}

export async function generateMetadata({ params }: { params: Promise<{ locale: string; id: string }> }): Promise<Metadata> {
  const { locale, id } = await params;
  const { manu, hidden } = await fetchBrand(id, locale);
  const t = await getTranslations({ locale, namespace: 'brand' });
  if (!manu || hidden) {
    return { robots: { index: false } };
  }
  const name = manu.name.trim();

  return {
    title: name,
    description: t('defaultBlurb'),
    alternates: alternatesFor(locale, `/marque/${id}`),
  };
}

export default async function MarquePage({ params }: { params: Promise<{ locale: string; id: string }> }) {
  const { locale, id } = await params;
  setRequestLocale(locale);
  const { manu, products, hidden } = await fetchBrand(id, locale);
  if (hidden) {
    notFound();
  }
  const menuRaw = await fetchMenu(idLangFor(locale)).catch(() => []);
  const facets: ListingFacets = await listingFacets(products, menuRaw);

  return (
    <div style={{ fontFamily: "'Hanken Grotesk',sans-serif", color: '#34352F', background: 'radial-gradient(1100px 560px at 82% -6%, rgba(140,198,63,0.13), transparent 58%), radial-gradient(820px 520px at -8% 14%, rgba(95,184,154,0.10), transparent 55%), radial-gradient(700px 600px at 50% 118%, rgba(140,198,63,0.08), transparent 60%), #F3F4EF', minHeight: '100vh' }}>
      <Chrome />
      <BrandClient manu={manu} products={products} facets={facets} />
      <Footer />
    </div>
  );
}
