import type { Metadata } from 'next';
import { setRequestLocale, getTranslations } from 'next-intl/server';
import { bridgeGetCached, type ProductCard } from '@/lib/ps';
import { CACHE_TAGS, CACHE_TTL } from '@/lib/cacheContract';
import { idLangFor } from '@/lib/i18n-config';
import { alternatesFor } from '@/lib/seo';
import { toCard, type Card } from '@/lib/cardModel';
import Chrome from '../../../components/Chrome';
import Footer from '../../../components/Footer';
import ZoneClient from './ZoneClient';

// Terme de recherche par zone — TOUJOURS en français (pilote l'API), inchangé.
const ZONE_QUERIES: Record<string, string> = {
  levres: 'lèvres',
  pommettes: 'pommettes',
  cernes: 'cernes',
  rides: 'rides',
  ovale: 'ovale',
  skinbooster: 'skinbooster',
};

// Produits de la zone, rendus CÔTÉ SERVEUR (SEO) : recherche sur le terme de la
// zone, repli sur une sélection générale pour que la page ne paraisse jamais vide.
async function fetchZoneProducts(key: string, locale: string): Promise<Card[]> {
  const idLang = idLangFor(locale);
  const query = ZONE_QUERIES[key] ?? '';
  const opts = { ttl: CACHE_TTL.products, tags: [CACHE_TAGS.products] };

  let products: ProductCard[] = [];
  if (query) {
    try {
      const d = await bridgeGetCached('products', { q: query, limit: 48, id_lang: idLang }, opts);
      products = d.products ?? [];
    } catch {
      products = [];
    }
  }
  if (!products.length) {
    try {
      const d = await bridgeGetCached('products', { limit: 24, id_lang: idLang }, opts);
      products = d.products ?? [];
    } catch {
      products = [];
    }
  }
  return products.map(toCard);
}

export async function generateMetadata({ params }: { params: Promise<{ locale: string; key: string }> }): Promise<Metadata> {
  const { locale, key } = await params;
  const t = await getTranslations({ locale, namespace: 'zone' });
  const known = key in ZONE_QUERIES;
  const label = known ? t(`${key}Label`) : t('fallbackLabel');
  const blurb = known ? t(`${key}Blurb`) : t('fallbackBlurb');

  return {
    title: label,
    description: blurb,
    alternates: alternatesFor(locale, `/zone/${key}`),
    ...(known ? {} : { robots: { index: false } }),
  };
}

export default async function ZonePage({ params }: { params: Promise<{ locale: string; key: string }> }) {
  const { locale, key } = await params;
  setRequestLocale(locale);
  const products = await fetchZoneProducts(key, locale);

  return (
    <div style={{ fontFamily: "'Hanken Grotesk',sans-serif", color: '#34352F', background: 'radial-gradient(1100px 560px at 82% -6%, rgba(140,198,63,0.13), transparent 58%), radial-gradient(820px 520px at -8% 14%, rgba(95,184,154,0.10), transparent 55%), radial-gradient(700px 600px at 50% 118%, rgba(140,198,63,0.08), transparent 60%), #F3F4EF', minHeight: '100vh' }}>
      <Chrome />
      <ZoneClient products={products} />
      <Footer />
    </div>
  );
}
