'use client';

import { useEffect, useState } from 'react';
import { useTranslations, useLocale } from 'next-intl';
import { toCard, type Card } from '@/lib/cardModel';
import type { ProductCard as ProductCardData } from '@/lib/ps';
import { idLangFor } from '@/lib/i18n-config';
import { readViewed } from '@/lib/viewed';
import ProductCard from './ProductCard';
import { useDragScroll } from './DragCarousel';

// Bloc « Déjà vus » (parité ancien site : ps_viewedproduct, 8 produits).
// Historique en localStorage -> rendu 100 % client, après hydratation : la fiche produit reste
// entièrement cacheable côté serveur et aucun historique de navigation ne part au bridge.
const SHOW = 8;

export default function ViewedProducts({ excludeId }: { excludeId?: number }) {
  const t = useTranslations('product');
  const locale = useLocale();
  const [cards, setCards] = useState<Card[]>([]);
  const { ref: track, handlers } = useDragScroll();

  useEffect(() => {
    // Le produit courant est exclu : il est déjà sous les yeux du visiteur.
    const ids = readViewed().filter((id) => id !== excludeId).slice(0, SHOW);
    if (!ids.length) return;
    let cancelled = false;
    fetch(`/api/products?ids=${ids.join(',')}&id_lang=${idLangFor(locale)}`)
      .then((r) => r.json())
      .then((d) => {
        if (cancelled) return;
        const rows: ProductCardData[] = Array.isArray(d?.products) ? d.products : [];
        setCards(rows.map(toCard));
      })
      .catch(() => {
        /* bloc décoratif : une panne ne doit jamais casser la fiche */
      });
    return () => { cancelled = true; };
  }, [excludeId, locale]);

  if (!cards.length) return null;

  return (
    <section style={{ marginTop: '44px' }}>
      <h2 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: '24px', color: '#2B2B2B', margin: 0 }}>
        {t('viewedTitle')}
      </h2>
      <div
        ref={track}
        {...handlers}
        className="hfm-carousel"
        style={{ display: 'flex', gap: '18px', marginTop: '20px', overflowX: 'auto', padding: '4px 2px 16px', cursor: 'grab' }}
      >
        {cards.map((item) => (
          <div key={item.id} style={{ flex: 'none', width: '252px' }}>
            <ProductCard product={item} />
          </div>
        ))}
      </div>
    </section>
  );
}
