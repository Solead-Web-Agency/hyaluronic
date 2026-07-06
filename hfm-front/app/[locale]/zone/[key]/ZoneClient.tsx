'use client';

import { useTranslations } from 'next-intl';
import { useParams } from 'next/navigation';
import { Link } from '@/i18n/navigation';
import { type Card } from '@/lib/cardModel';
import { useAutoLoad } from '@/lib/useAutoLoad';
import ProductCard from '../../../components/ProductCard';

// Zones connues (libellés traduits) — les produits arrivent du serveur (SEO).
const KNOWN_ZONES = new Set(['levres', 'pommettes', 'cernes', 'rides', 'ovale', 'skinbooster']);

export default function ZoneClient({ products }: { products: Card[] }) {
  const t = useTranslations('zone');
  const tc = useTranslations('common');
  const params = useParams();
  const key = String(params.key ?? '');

  const known = KNOWN_ZONES.has(key);
  const label = known ? t(`${key}Label`) : t('fallbackLabel');
  const blurb = known ? t(`${key}Blurb`) : t('fallbackBlurb');

  const count = products.length;
  const { visible, sentinelRef } = useAutoLoad(count);

  return (
    <main data-screen-label="Zone" className="hfm-wrap" style={{ maxWidth: '1340px', margin: '0 auto', padding: '34px 28px 70px' }}>
      <div style={{ fontSize: '12.5px', color: '#9A9A9A', marginBottom: '18px' }}>
        <Link href="/" style={{ cursor: 'pointer' }}>{tc('home')}</Link>  /  <Link href="/catalogue" style={{ cursor: 'pointer' }}>{t('breadcrumbByZone')}</Link>  /  {label}
      </div>
      <div style={{ fontSize: '11.5px', fontWeight: 700, letterSpacing: '.13em', textTransform: 'uppercase', color: '#8CC63F' }}>{t('eyebrow')}</div>
      <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: '42px', color: '#2B2B2B', margin: '8px 0 0' }}>{label}</h1>
      <p style={{ fontSize: '15.5px', lineHeight: 1.6, color: '#55606F', maxWidth: '620px', margin: '14px 0 0' }}>{blurb}</p>
      <div style={{ fontSize: '14px', color: '#6E7585', marginTop: '12px' }}>{t('productsCount', { count })}</div>

      {count === 0 ? (
        <div style={{ padding: '60px 0', textAlign: 'center', color: '#8A8170', fontSize: '15px' }}>
          {t('noProducts')}
        </div>
      ) : (
        <>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill,minmax(220px,1fr))', gap: '18px', marginTop: '30px' }}>
            {products.slice(0, visible).map((item) => <ProductCard key={item.id} product={item} />)}
          </div>
          <div ref={sentinelRef} aria-hidden="true" style={{ height: '1px' }} />
        </>
      )}
    </main>
  );
}
