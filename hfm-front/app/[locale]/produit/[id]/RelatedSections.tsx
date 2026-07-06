'use client';

import { useTranslations } from 'next-intl';
import { Link } from '@/i18n/navigation';
import type { Card } from '@/lib/cardModel';
import ProductCard from '../../../components/ProductCard';
import { useDragScroll } from '../../../components/DragCarousel';

export type RelatedView = {
  boughtTogether: Card[];
  youMayLike: Card[];
  brand: { id: number; name: string; products: Card[] };
  category: { id: number; name: string; products: Card[] };
  zone: { id: number; name: string; products: Card[] };
};

const arrowBtn: React.CSSProperties = {
  width: '36px',
  height: '36px',
  borderRadius: '50%',
  background: '#fff',
  border: '1px solid #E2DECF',
  color: '#434343',
  cursor: 'pointer',
  display: 'inline-flex',
  alignItems: 'center',
  justifyContent: 'center',
  flex: 'none',
};

function Section({ title, seeAllHref, seeAllLabel, products }: { title: string; seeAllHref?: string; seeAllLabel?: string; products: Card[] }) {
  const { ref: track, handlers } = useDragScroll();
  const scroll = (dir: 1 | -1) => {
    const el = track.current;
    if (el) el.scrollBy({ left: dir * Math.max(280, el.clientWidth * 0.8), behavior: 'smooth' });
  };

  if (!products.length) return null;
  return (
    <section style={{ marginTop: '44px' }}>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: '16px', flexWrap: 'wrap' }}>
        <h2 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: '24px', color: '#2B2B2B', margin: 0 }}>{title}</h2>
        <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
          {seeAllHref ? <Link href={seeAllHref} style={{ fontSize: '13.5px', fontWeight: 600, color: '#434343', textDecoration: 'none' }}>{seeAllLabel} →</Link> : null}
          <button type="button" onClick={() => scroll(-1)} aria-label="←" style={arrowBtn}>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round"><path d="M15 18l-6-6 6-6" /></svg>
          </button>
          <button type="button" onClick={() => scroll(1)} aria-label="→" style={arrowBtn}>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round"><path d="M9 6l6 6-6 6" /></svg>
          </button>
        </div>
      </div>
      {/* Pas de scroll-snap : il se bat avec le drag souris (mouvement saccadé). */}
      <div ref={track} {...handlers} className="hfm-carousel" style={{ display: 'flex', gap: '18px', marginTop: '20px', overflowX: 'auto', padding: '4px 2px 16px', cursor: 'grab' }}>
        {products.map((item) => (
          <div key={item.id} style={{ flex: 'none', width: '252px' }}>
            <ProductCard product={item} />
          </div>
        ))}
      </div>
    </section>
  );
}

export default function RelatedSections({ related }: { related: RelatedView }) {
  const t = useTranslations('product');

  return (
    <div>
      <Section title={t('relBoughtTogether')} products={related.boughtTogether} />
      <Section title={t('relYouMayLike')} products={related.youMayLike} />
      {related.brand.name ? (
        <Section
          title={t('relBrandTitle', { brand: related.brand.name })}
          seeAllHref={`/marque/${related.brand.id}`}
          seeAllLabel={t('relSeeAll')}
          products={related.brand.products}
        />
      ) : null}
      {related.category.name ? (
        <Section
          title={t('relCategoryTitle', { category: related.category.name })}
          seeAllHref={`/catalogue?category=${related.category.id}`}
          seeAllLabel={t('relSeeAll')}
          products={related.category.products}
        />
      ) : null}
      {related.zone.name ? (
        <Section
          title={t('relZoneTitle', { zone: related.zone.name })}
          seeAllHref={`/catalogue?category=${related.zone.id}`}
          seeAllLabel={t('relSeeAll')}
          products={related.zone.products}
        />
      ) : null}

      {/* Encadré réglementaire dispositif médical */}
      <div style={{ display: 'flex', gap: '14px', alignItems: 'flex-start', marginTop: '44px', background: '#fff', border: '1px solid #ECEAE3', borderRadius: '10px', padding: '22px 26px' }}>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6E7585" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round" style={{ flex: 'none', marginTop: '2px' }}><path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6z" /><path d="M12 8v5M12 16.5v.5" /></svg>
        <div>
          <div style={{ fontSize: '14.5px', fontWeight: 700, color: '#2B2B2B' }}>{t('medDeviceTitle')}</div>
          <p style={{ fontSize: '13px', lineHeight: 1.65, color: '#55606F', margin: '10px 0 0' }}>{t('medDeviceP1')}</p>
          <p style={{ fontSize: '13px', lineHeight: 1.65, color: '#55606F', margin: '10px 0 0' }}>{t('medDeviceP2')}</p>
        </div>
      </div>
    </div>
  );
}
