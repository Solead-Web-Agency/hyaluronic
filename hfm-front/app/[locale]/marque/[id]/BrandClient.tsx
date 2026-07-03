'use client';

import { useEffect, useState } from 'react';
import { useTranslations, useLocale } from 'next-intl';
import { useParams } from 'next/navigation';
import { Link } from '@/i18n/navigation';
import { toCard, type Card } from '@/lib/cardModel';
import type { ProductCard as ProductCardData } from '@/lib/ps';
import { idLangFor } from '@/lib/i18n-config';
import ProductCard from '../../../components/ProductCard';

type Manu = { id_manufacturer: number; name: string; nb_products: number };

// Marques disposant d'une accroche dédiée ; à défaut, le texte générique.
const KNOWN_BLURBS = new Set(['vivacy', 'croma', 'teoxane', 'mccm']);

export default function BrandClient() {
  const t = useTranslations('brand');
  const tc = useTranslations('common');
  const locale = useLocale();
  const params = useParams();
  const id = String(params.id ?? '');

  const [manu, setManu] = useState<Manu | null>(null);
  const [products, setProducts] = useState<Card[]>([]);
  const [loading, setLoading] = useState(true);

  // Résolution du nom de marque depuis la taxonomie.
  useEffect(() => {
    fetch(`/api/taxonomy?action=manufacturers&id_lang=${idLangFor(locale)}`)
      .then((r) => r.json())
      .then((d: { manufacturers?: Manu[] }) => {
        const found = (d.manufacturers ?? []).find((m) => String(m.id_manufacturer) === id) ?? null;
        setManu(found);
      })
      .catch(() => {});
  }, [id, locale]);

  // Produits de la marque.
  useEffect(() => {
    if (!id) return;
    setLoading(true);
    fetch(`/api/products?id_manufacturer=${encodeURIComponent(id)}&limit=48&id_lang=${idLangFor(locale)}`)
      .then((r) => r.json())
      .then((d: { products?: ProductCardData[] }) => setProducts((d.products ?? []).map(toCard)))
      .catch(() => setProducts([]))
      .finally(() => setLoading(false));
  }, [id, locale]);

  const name = (manu?.name ?? '').trim() || t('fallbackName');
  const blurbKey = name.toLowerCase();
  const blurb = KNOWN_BLURBS.has(blurbKey) ? t(`blurbs.${blurbKey}`) : t('defaultBlurb');
  const count = products.length;

  return (
    <main data-screen-label="Marque" className="hfm-wrap" style={{ maxWidth: '1340px', margin: '0 auto', padding: '34px 28px 70px' }}>
      <div style={{ fontSize: '12.5px', color: '#9A9A9A', marginBottom: '18px' }}>
        <Link href="/" style={{ cursor: 'pointer' }}>{tc('home')}</Link>  /  <Link href="/catalogue" style={{ cursor: 'pointer' }}>{t('breadcrumbBrands')}</Link>  /  {name}
      </div>

      <div className="hfm-banner" style={{ background: '#434343', borderRadius: '12px', padding: '44px 48px', display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: '30px', flexWrap: 'wrap' }}>
        <div style={{ maxWidth: '620px' }}>
          <div style={{ fontSize: '11.5px', fontWeight: 700, letterSpacing: '.13em', textTransform: 'uppercase', color: '#8CC63F' }}>{t('partner')}</div>
          <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: '42px', color: '#fff', margin: '10px 0 0' }}>{name}</h1>
          <p style={{ fontSize: '14.5px', lineHeight: 1.6, color: '#D4D4D4', margin: '14px 0 0' }}>{blurb}</p>
          <div style={{ fontSize: '13px', color: '#B7E486', marginTop: '14px', fontWeight: 600 }}>{loading ? tc('loading') : t('productsCount', { count })}</div>
        </div>
        <div style={{ display: 'flex', gap: '10px', flexWrap: 'wrap' }}>
          <div style={{ background: 'rgba(255,255,255,.10)', backdropFilter: 'blur(10px)', WebkitBackdropFilter: 'blur(10px)', border: '1px solid rgba(255,255,255,.18)', borderRadius: '11px', padding: '14px 18px', color: '#fff', fontSize: '13px' }}>{t('directSourcing')}</div>
          <div style={{ background: 'rgba(255,255,255,.10)', backdropFilter: 'blur(10px)', WebkitBackdropFilter: 'blur(10px)', border: '1px solid rgba(255,255,255,.18)', borderRadius: '11px', padding: '14px 18px', color: '#fff', fontSize: '13px' }}>{t('tracedLots')}</div>
        </div>
      </div>

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
