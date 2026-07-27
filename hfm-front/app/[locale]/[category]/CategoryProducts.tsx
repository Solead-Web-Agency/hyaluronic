'use client';

import { useTranslations } from 'next-intl';
import type { ProductCard as ProductCardData } from '@/lib/ps';
import { useProductPagination } from '@/lib/useProductPagination';
import ProductCard from '../../components/ProductCard';

// Grille produits de la page CATÉGORIE : page 1 rendue côté serveur (SEO), pages suivantes fetchées
// à la demande (infinite scroll) via /api/products. Tri fixe « position » (ordre catalogue PS).
export default function CategoryProducts({ initial, total, idCategory, idLang }: {
  initial: ProductCardData[];
  total: number;
  idCategory: number;
  idLang: number;
}) {
  const t = useTranslations('catalogue');
  const tc = useTranslations('common');
  const { items, loading, hasMore, sentinelRef } = useProductPagination({
    initial,
    total,
    idLang,
    filters: { id_category: idCategory },
    initialOrder: 'position',
  });

  return (
    <>
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill,minmax(228px,1fr))', gap: '18px' }}>
        {items.map((c) => (
          <ProductCard key={c.id} product={c} />
        ))}
      </div>
      <div ref={sentinelRef} aria-hidden="true" style={{ height: '1px' }} />
      {loading ? (
        <div style={{ padding: '26px 0', textAlign: 'center', color: '#8A8170', fontSize: '14px' }}>{tc('loading')}</div>
      ) : !hasMore && items.length > 0 ? (
        <div style={{ padding: '26px 0', textAlign: 'center', color: '#B4AE9E', fontSize: '13px' }}>{t('productsCount', { count: total })}</div>
      ) : null}
    </>
  );
}
