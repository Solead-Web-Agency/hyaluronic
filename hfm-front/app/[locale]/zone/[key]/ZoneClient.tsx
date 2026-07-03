'use client';

import { useEffect, useState } from 'react';
import { useTranslations, useLocale } from 'next-intl';
import { useParams } from 'next/navigation';
import { Link } from '@/i18n/navigation';
import { toCard, type Card } from '@/lib/cardModel';
import type { ProductCard as ProductCardData } from '@/lib/ps';
import { idLangFor } from '@/lib/i18n-config';
import ProductCard from '../../../components/ProductCard';

// Terme de recherche par zone — TOUJOURS en français (pilote l'API), inchangé.
const ZONE_QUERIES: Record<string, string> = {
  levres: 'lèvres',
  pommettes: 'pommettes',
  cernes: 'cernes',
  rides: 'rides',
  ovale: 'ovale',
  skinbooster: 'skinbooster',
};

const KNOWN_ZONES = new Set(Object.keys(ZONE_QUERIES));

export default function ZoneClient() {
  const t = useTranslations('zone');
  const tc = useTranslations('common');
  const locale = useLocale();
  const params = useParams();
  const key = String(params.key ?? '');

  const known = KNOWN_ZONES.has(key);
  const label = known ? t(`${key}Label`) : t('fallbackLabel');
  const blurb = known ? t(`${key}Blurb`) : t('fallbackBlurb');
  const query = known ? ZONE_QUERIES[key] : '';

  const [products, setProducts] = useState<Card[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    setLoading(true);
    // On pilote la grille par recherche sur le terme de la zone ;
    // à défaut de résultats, on retombe sur une sélection générale pour
    // que la page ne paraisse jamais vide.
    const fetchGeneral = () =>
      fetch(`/api/products?limit=24&id_lang=${idLangFor(locale)}`)
        .then((r) => r.json())
        .then((d: { products?: ProductCardData[] }) => (d.products ?? []).map(toCard));

    const run = async () => {
      try {
        let cards: Card[] = [];
        if (query) {
          const r = await fetch(`/api/products?q=${encodeURIComponent(query)}&limit=48&id_lang=${idLangFor(locale)}`);
          const d: { products?: ProductCardData[] } = await r.json();
          cards = (d.products ?? []).map(toCard);
        }
        if (cards.length === 0) cards = await fetchGeneral();
        setProducts(cards);
      } catch {
        try {
          setProducts(await fetchGeneral());
        } catch {
          setProducts([]);
        }
      } finally {
        setLoading(false);
      }
    };
    run();
  }, [query, locale]);

  const count = products.length;

  return (
    <main data-screen-label="Zone" className="hfm-wrap" style={{ maxWidth: '1340px', margin: '0 auto', padding: '34px 28px 70px' }}>
      <div style={{ fontSize: '12.5px', color: '#9A9A9A', marginBottom: '18px' }}>
        <Link href="/" style={{ cursor: 'pointer' }}>{tc('home')}</Link>  /  <Link href="/catalogue" style={{ cursor: 'pointer' }}>{t('breadcrumbByZone')}</Link>  /  {label}
      </div>
      <div style={{ fontSize: '11.5px', fontWeight: 700, letterSpacing: '.13em', textTransform: 'uppercase', color: '#8CC63F' }}>{t('eyebrow')}</div>
      <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: '42px', color: '#2B2B2B', margin: '8px 0 0' }}>{label}</h1>
      <p style={{ fontSize: '15.5px', lineHeight: 1.6, color: '#55606F', maxWidth: '620px', margin: '14px 0 0' }}>{blurb}</p>
      <div style={{ fontSize: '14px', color: '#6E7585', marginTop: '12px' }}>{loading ? tc('loading') : t('productsCount', { count })}</div>

      {!loading && count === 0 ? (
        <div style={{ padding: '60px 0', textAlign: 'center', color: '#8A8170', fontSize: '15px' }}>
          {t('noProducts')}
        </div>
      ) : (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill,minmax(220px,1fr))', gap: '18px', marginTop: '30px' }}>
          {products.map((item) => <ProductCard key={item.id} product={item} />)}
        </div>
      )}
    </main>
  );
}
