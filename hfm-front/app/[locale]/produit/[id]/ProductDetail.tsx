'use client';

import { useState } from 'react';
import { useTranslations } from 'next-intl';
import { Link } from '@/i18n/navigation';
import { useStore } from '../../../store';
import { fmt } from '@/lib/cardModel';

export type ProductView = {
  id: number;
  name: string;
  reference: string;
  brand: string | null;
  ht: number;
  ttc: number;
  inStock: boolean;
  description: string;
  images: string[];
  features: { name: string; value: string }[];
  rpps_required: boolean;
};

export default function ProductDetail({ product }: { product: ProductView }) {
  const t = useTranslations('product');
  const tc = useTranslations('common');
  const { addToCart } = useStore();
  const [qty, setQty] = useState(1);

  const REVIEWS = [
    { text: t('review1Text'), name: t('review1Name'), role: t('review1Role') },
    { text: t('review2Text'), name: t('review2Name'), role: t('review2Role') },
  ];

  const specs: { k: string; v: string }[] = [
    { k: t('specBrand'), v: product.brand || '—' },
    { k: t('specReference'), v: product.reference || '—' },
    { k: t('specAvailability'), v: product.inStock ? t('shippedToday') : tc('onOrder') },
    ...product.features.map((f) => ({ k: f.name, v: f.value })),
    { k: t('specCompliance'), v: t('complianceValue') },
  ];

  const [imgIdx, setImgIdx] = useState(0);
  const img = product.images[imgIdx] ?? product.images[0] ?? null;

  return (
    <main data-screen-label="Fiche produit" className="hfm-wrap" style={{ maxWidth: '1340px', margin: '0 auto', padding: '34px 28px 70px' }}>
      <div style={{ fontSize: '12.5px', color: '#9A9A9A', marginBottom: '24px' }}><Link href="/" style={{ cursor: 'pointer' }}>{tc('home')}</Link>  /  <Link href="/catalogue" style={{ cursor: 'pointer' }}>{tc('catalogue')}</Link>  /  {product.name}</div>
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit,minmax(320px,1fr))', gap: '54px', alignItems: 'start' }}>
        <div className="hfm-sticky" style={{ position: 'sticky', top: '130px' }}>
          <div style={{ display: 'block', width: '100%', aspectRatio: '1/1', borderRadius: '10px', overflow: 'hidden', background: 'repeating-linear-gradient(135deg,#F7F6F2,#F7F6F2 9px,#F1EFE8 9px,#F1EFE8 18px)', border: '1px solid #ECEAE3' }}>
            {img ? (
              // eslint-disable-next-line @next/next/no-img-element
              <img src={img} alt={product.name} style={{ display: 'block', width: '100%', aspectRatio: '1/1', objectFit: 'cover' }} />
            ) : null}
          </div>
          {product.images.length ? (
            <div style={{ display: 'flex', gap: '12px', marginTop: '12px', flexWrap: 'wrap' }}>
              {product.images.map((src, i) => (
                <button key={i} type="button" onClick={() => setImgIdx(i)} aria-label={`${product.name} — ${i + 1}`} style={{ padding: 0, width: '74px', height: '74px', borderRadius: '7px', overflow: 'hidden', cursor: 'pointer', background: '#F7F6F2', border: i === imgIdx ? '1.5px solid #8CC63F' : '1px solid #ECEAE3' }}>
                  {/* eslint-disable-next-line @next/next/no-img-element */}
                  <img src={src} alt="" style={{ display: 'block', width: '100%', height: '100%', objectFit: 'cover' }} />
                </button>
              ))}
            </div>
          ) : null}
        </div>
        <div>
          {product.brand ? <div style={{ fontSize: '11.5px', fontWeight: 600, letterSpacing: '.1em', textTransform: 'uppercase', color: '#8A8170' }}>{product.brand}</div> : null}
          <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: '34px', lineHeight: 1.2, color: '#2B2B2B', margin: '8px 0 0' }}>{product.name}</h1>
          <div style={{ display: 'flex', alignItems: 'baseline', gap: '12px', marginTop: '22px' }}><span style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontWeight: 700, fontSize: '32px', color: '#434343' }}>{fmt(product.ht)} €</span><span style={{ fontSize: '13px', fontWeight: 700, color: '#8A8170' }}>{tc('exclTax')}</span><span style={{ fontSize: '14px', color: '#9A9A9A' }}>{tc('inclTaxShort', { amount: fmt(product.ttc) })}</span></div>
          <div style={{ fontSize: '13px', color: product.inStock ? '#3F7256' : '#6E7585', marginTop: '10px', fontWeight: 600 }}>{product.inStock ? tc('inStock') : tc('onOrder')}</div>
          {product.rpps_required ? (
            <div style={{ display: 'flex', gap: '10px', alignItems: 'flex-start', marginTop: '16px', padding: '12px 14px', background: 'rgba(168,80,58,.08)', border: '1px solid rgba(168,80,58,.22)', borderRadius: '8px' }}>
              <span style={{ color: '#A8503A', fontSize: '15px', lineHeight: 1.3, flex: 'none' }} aria-hidden="true">⚕</span>
              <span style={{ fontSize: '13px', lineHeight: 1.5, color: '#A8503A', fontWeight: 500 }}>{t('rppsNotice')}</span>
            </div>
          ) : null}
          {product.description ? (
            <div style={{ fontSize: '15px', lineHeight: 1.65, color: '#55606F', margin: '22px 0 0' }} dangerouslySetInnerHTML={{ __html: product.description }} />
          ) : null}
          <div style={{ display: 'flex', alignItems: 'center', gap: '14px', marginTop: '28px' }}>
            <div style={{ display: 'flex', alignItems: 'center', border: '1.5px solid #E2DECF', borderRadius: '7px', overflow: 'hidden' }}><button onClick={() => setQty((q) => Math.max(1, q - 1))} style={{ width: '46px', height: '52px', background: '#fff', border: 'none', fontSize: '18px', color: '#434343', cursor: 'pointer' }}>−</button><span style={{ width: '46px', textAlign: 'center', fontSize: '15px', fontWeight: 600 }}>{qty}</span><button onClick={() => setQty((q) => q + 1)} style={{ width: '46px', height: '52px', background: '#fff', border: 'none', fontSize: '18px', color: '#434343', cursor: 'pointer' }}>+</button></div>
            {product.inStock ? (
              <button onClick={() => addToCart({ id: product.id, quantity: qty })} style={{ flex: 1, height: '52px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '15px', fontWeight: 600, color: '#fff', background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))', border: '1px solid rgba(255,255,255,.42)', boxShadow: '0 12px 26px -10px rgba(116,176,51,.55)', backdropFilter: 'blur(8px) saturate(140%)', WebkitBackdropFilter: 'blur(8px) saturate(140%)', borderRadius: '999px', cursor: 'pointer', transition: 'background .2s ease' }}>{tc('addToCart')}</button>
            ) : (
              <button disabled style={{ flex: 1, height: '52px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '15px', fontWeight: 600, color: '#6E7585', background: 'rgba(242,240,234,.7)', border: '1px solid rgba(226,222,207,.9)', borderRadius: '999px', cursor: 'default' }}>{t('notifyOnReturn')}</button>
            )}
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2,1fr)', gap: '12px', marginTop: '24px' }}>
            <div style={{ display: 'flex', gap: '10px', alignItems: 'center', fontSize: '12.5px', color: '#55606F' }}><span style={{ color: '#434343', fontSize: '15px' }}>✓</span> {t('trustCe')}</div>
            <div style={{ display: 'flex', gap: '10px', alignItems: 'center', fontSize: '12.5px', color: '#55606F' }}><span style={{ color: '#434343', fontSize: '15px' }}>✓</span> {t('trustShip')}</div>
            <div style={{ display: 'flex', gap: '10px', alignItems: 'center', fontSize: '12.5px', color: '#55606F' }}><span style={{ color: '#434343', fontSize: '15px' }}>✓</span> {t('trustDelivery')}</div>
            <div style={{ display: 'flex', gap: '10px', alignItems: 'center', fontSize: '12.5px', color: '#55606F' }}><span style={{ color: '#434343', fontSize: '15px' }}>✓</span> {t('trustSecure')}</div>
          </div>
        </div>
      </div>
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit,minmax(300px,1fr))', gap: '54px', marginTop: '64px', alignItems: 'start' }}>
        <div>
          <h2 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: '24px', color: '#2B2B2B', margin: '0 0 18px' }}>{t('specsTitle')}</h2>
          <div style={{ background: '#fff', border: '1px solid #ECEAE3', borderRadius: '8px', overflow: 'hidden' }}>
            {specs.map((sp, i) => (
              <div key={i} style={{ display: 'flex', justifyContent: 'space-between', gap: '16px', padding: '14px 18px', borderBottom: '1px solid #F1EFE8', fontSize: '13.5px' }}><span style={{ color: '#8A8170' }}>{sp.k}</span><span style={{ color: '#1B2433', fontWeight: 500, textAlign: 'right' }}>{sp.v}</span></div>
            ))}
          </div>
        </div>
        <div>
          <h2 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: '24px', color: '#2B2B2B', margin: '0 0 18px' }}>{t('verifiedReviewsTitle')}</h2>
          <div style={{ display: 'flex', alignItems: 'center', gap: '18px', background: '#fff', border: '1px solid #ECEAE3', borderRadius: '8px', padding: '18px 22px' }}>
            <div style={{ textAlign: 'center' }}><div style={{ fontFamily: "'Spectral',serif", fontSize: '38px', color: '#434343', lineHeight: 1 }}>{t('reviewsScore')}</div><div style={{ color: '#8CC63F', fontSize: '13px', letterSpacing: '1px', marginTop: '6px' }}>★★★★★</div></div>
            <div style={{ flex: 1, fontSize: '12.5px', color: '#6E7585', lineHeight: 1.7 }}>{t('reviewsBlurb')}</div>
          </div>
          <div style={{ display: 'flex', flexDirection: 'column', gap: '12px', marginTop: '14px' }}>
            {REVIEWS.map((r, i) => (
              <div key={i} style={{ background: '#fff', border: '1px solid #ECEAE3', borderRadius: '8px', padding: '16px 18px' }}><span style={{ color: '#8CC63F', fontSize: '12px', letterSpacing: '1px' }}>★★★★★</span><p style={{ fontFamily: "'Spectral',serif", fontSize: '14.5px', lineHeight: 1.5, color: '#2A3447', margin: '8px 0 10px' }}>« {r.text} »</p><div style={{ fontSize: '12px', color: '#6E7585' }}><b style={{ color: '#1B2433' }}>{r.name}</b> · {r.role}</div></div>
            ))}
          </div>
        </div>
      </div>
    </main>
  );
}
