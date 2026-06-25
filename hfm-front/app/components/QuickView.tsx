'use client';

import { useTranslations } from 'next-intl';
import { useRouter } from 'next/navigation';
import { useStore } from '../store';
import { fmt } from '@/lib/cardModel';

export default function QuickView() {
  const t = useTranslations('quickView');
  const tc = useTranslations('common');
  const tp = useTranslations('product');
  const { quickProduct, closeQuick, addToCart } = useStore();
  const router = useRouter();
  if (!quickProduct) return null;
  const p = quickProduct;

  return (
    <div style={{ position: 'fixed', inset: 0, zIndex: 65, animation: 'fdOverlay .2s ease' }}>
      <div onClick={closeQuick} style={{ position: 'absolute', inset: 0, background: 'rgba(30,30,30,.5)' }} />
      <div style={{ position: 'absolute', top: '50%', left: '50%', transform: 'translate(-50%,-50%)', width: '780px', maxWidth: '94vw', maxHeight: '90vh', overflowY: 'auto', background: 'rgba(255,255,255,.80)', backdropFilter: 'blur(26px) saturate(150%)', WebkitBackdropFilter: 'blur(26px) saturate(150%)', border: '1px solid rgba(255,255,255,.6)', borderRadius: '12px', boxShadow: '0 40px 80px -30px rgba(0,0,0,.5)', display: 'grid', gridTemplateColumns: 'repeat(auto-fit,minmax(280px,1fr))' }}>
        <div style={{ display: 'block', width: '100%', minHeight: '320px', overflow: 'hidden', background: 'repeating-linear-gradient(135deg,#F7F6F2,#F7F6F2 9px,#F1EFE8 9px,#F1EFE8 18px)' }}>
          {p.img ? (
            // eslint-disable-next-line @next/next/no-img-element
            <img src={p.img} alt={p.name} style={{ display: 'block', width: '100%', height: '100%', minHeight: '320px', objectFit: 'cover' }} />
          ) : null}
        </div>
        <div style={{ padding: '34px', position: 'relative' }}>
          <button onClick={closeQuick} style={{ position: 'absolute', top: '18px', right: '18px', background: 'none', border: 'none', fontSize: '22px', color: '#8A8170', cursor: 'pointer', lineHeight: 1 }}>×</button>
          {p.brand ? <div style={{ fontSize: '11px', fontWeight: 600, letterSpacing: '.1em', textTransform: 'uppercase', color: '#8A8170' }}>{p.brand}</div> : null}
          <h2 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: '25px', lineHeight: 1.2, color: '#2B2B2B', margin: '8px 0 0' }}>{p.name}</h2>
          <div style={{ display: 'flex', alignItems: 'baseline', gap: '10px', marginTop: '18px' }}><span style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontWeight: 700, fontSize: '27px', color: '#434343' }}>{fmt(p.ht)} €</span><span style={{ fontSize: '12px', fontWeight: 700, color: '#8A8170' }}>{tc('exclTax')}</span><span style={{ fontSize: '13px', color: '#9A9A9A' }}>{tc('inclTaxShort', { amount: fmt(p.ttc) })}</span></div>
          <p style={{ fontSize: '13.5px', lineHeight: 1.6, color: '#55606F', margin: '16px 0 0' }}>{t('refStock', { reference: p.reference, status: p.stock === 'in' ? tc('inStock') : tc('onOrder') })}</p>
          {p.stock === 'out' ? (
            <button disabled style={{ width: '100%', marginTop: '22px', height: '50px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14.5px', fontWeight: 600, color: '#6E7585', background: 'rgba(242,240,234,.7)', border: '1px solid rgba(226,222,207,.9)', borderRadius: '999px', cursor: 'default' }}>{tp('notifyOnReturn')}</button>
          ) : (
            <button onClick={() => { addToCart({ id: p.id }); closeQuick(); }} style={{ width: '100%', marginTop: '22px', height: '50px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14.5px', fontWeight: 600, color: '#fff', background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))', border: '1px solid rgba(255,255,255,.42)', boxShadow: '0 12px 26px -10px rgba(116,176,51,.55)', backdropFilter: 'blur(8px) saturate(140%)', WebkitBackdropFilter: 'blur(8px) saturate(140%)', borderRadius: '999px', cursor: 'pointer', transition: 'background .2s ease' }}>{tc('addToCart')}</button>
          )}
          <button onClick={() => { closeQuick(); router.push(`/produit/${p.id}`); }} style={{ width: '100%', marginTop: '10px', height: '46px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '13.5px', fontWeight: 600, color: '#5E8E1F', background: 'rgba(140,198,63,0.08)', backdropFilter: 'blur(6px)', WebkitBackdropFilter: 'blur(6px)', border: '1px solid rgba(155,209,89,.7)', borderRadius: '999px', cursor: 'pointer' }}>{t('viewFullPage')}</button>
          <div style={{ display: 'flex', gap: '16px', marginTop: '18px', fontSize: '11.5px', color: '#7A8290', flexWrap: 'wrap' }}><span>{t('trustCe')}</span><span>{t('trustShip')}</span><span>{t('trustDelivery')}</span></div>
        </div>
      </div>
    </div>
  );
}
