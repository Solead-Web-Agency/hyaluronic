'use client';

import { useTranslations } from 'next-intl';
import { useRouter } from '@/i18n/navigation';
import { useStore } from '../store';
import { fmt } from '@/lib/cardModel';

export default function CartDrawer() {
  const t = useTranslations('cart');
  const tc = useTranslations('common');
  const { cartOpen, closeCart, cart, updateLine, removeLine } = useStore();
  const router = useRouter();
  if (!cartOpen) return null;

  const subtotal = cart.total_excl_tax;
  const vat = subtotal * 0.2;
  const ttc = cart.total_incl_tax;
  const remain = Math.max(0, 400 - subtotal);
  const pct = Math.min(100, (subtotal / 400) * 100);
  const hasItems = cart.products.length > 0;

  const goCheckout = () => {
    if (!hasItems) return;
    closeCart();
    router.push('/checkout');
  };

  return (
    <div style={{ position: 'fixed', inset: 0, zIndex: 60, animation: 'fdOverlay .25s ease' }}>
      <div onClick={closeCart} style={{ position: 'absolute', inset: 0, background: 'rgba(30,30,30,.45)' }} />
      <div style={{ position: 'absolute', top: 0, right: 0, height: '100%', width: '420px', maxWidth: '92vw', background: '#fff', borderLeft: '1px solid #E7E3DA', boxShadow: '-30px 0 60px -30px rgba(30,40,15,.45)', display: 'flex', flexDirection: 'column', animation: 'fdDrawer .32s cubic-bezier(.22,1,.36,1)' }}>
        <div style={{ padding: '22px 24px', borderBottom: '1px solid #E7E3DA', display: 'flex', alignItems: 'center', justifyContent: 'space-between', background: '#fff' }}><span style={{ fontFamily: "'Spectral',serif", fontSize: '21px', color: '#2B2B2B' }}>{t('title')}</span><button onClick={closeCart} style={{ background: 'none', border: 'none', fontSize: '22px', color: '#8A8170', cursor: 'pointer', lineHeight: 1 }}>×</button></div>
        {!hasItems ? (
          <div style={{ flex: 1, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', gap: '16px', padding: '40px' }}><div style={{ fontSize: '38px', opacity: .3 }}>🛍</div><div style={{ fontSize: '14px', color: '#8A8170', textAlign: 'center' }}>{t('empty')}</div><button onClick={() => { closeCart(); router.push('/catalogue'); }} style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '13.5px', fontWeight: 600, color: '#fff', background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))', border: '1px solid rgba(255,255,255,.42)', boxShadow: '0 12px 26px -10px rgba(116,176,51,.55)', backdropFilter: 'blur(8px) saturate(140%)', WebkitBackdropFilter: 'blur(8px) saturate(140%)', borderRadius: '999px', padding: '13px 24px', cursor: 'pointer' }}>{tc('browseCatalogue')}</button></div>
        ) : (
          <>
            <div style={{ padding: '16px 24px', background: '#fff', borderBottom: '1px solid #E7E3DA' }}>
              {subtotal >= 400 ? (
                <div style={{ fontSize: '12.5px', color: '#3F7256', fontWeight: 600 }}>{t('freeShippingUnlocked')}</div>
              ) : (
                <div style={{ fontSize: '12.5px', color: '#55606F' }}>{t.rich('freeShippingRemaining', { amount: fmt(remain), b: (c) => <b style={{ color: '#434343' }}>{c}</b> })}</div>
              )}
              <div style={{ height: '7px', background: '#EFEDE5', borderRadius: '999px', marginTop: '10px', overflow: 'hidden' }}><div style={{ height: '100%', width: `${pct}%`, background: '#8CC63F', borderRadius: '999px', transition: 'width .4s ease' }} /></div>
            </div>
            <div style={{ flex: 1, overflowY: 'auto', padding: '8px 24px' }}>
              {cart.products.map((ci) => (
                <div key={ci.id_product} style={{ display: 'flex', gap: '14px', padding: '18px 0', borderBottom: '1px solid #E7E3DA' }}>
                  <div style={{ width: '64px', height: '64px', flex: 'none', borderRadius: '6px', overflow: 'hidden', background: '#F7F6F2', border: '1px solid #ECEAE3' }}>
                    {ci.image ? <img src={ci.image} alt={ci.name} style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block' }} /> : null}
                  </div>
                  <div style={{ flex: 1, minWidth: 0 }}>
                    <div style={{ fontFamily: "'Spectral',serif", fontSize: '14px', color: '#1B2433', lineHeight: 1.3, marginTop: '2px' }}>{ci.name}</div>
                    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginTop: '10px' }}>
                      <div style={{ display: 'flex', alignItems: 'center', border: '1px solid #E2DECF', borderRadius: '6px', overflow: 'hidden' }}><button onClick={() => updateLine(ci.id_product, ci.quantity - 1)} style={{ width: '28px', height: '28px', background: '#fff', border: 'none', color: '#434343', cursor: 'pointer' }}>−</button><span style={{ width: '28px', textAlign: 'center', fontSize: '13px', fontWeight: 600 }}>{ci.quantity}</span><button onClick={() => updateLine(ci.id_product, ci.quantity + 1)} style={{ width: '28px', height: '28px', background: '#fff', border: 'none', color: '#434343', cursor: 'pointer' }}>+</button></div>
                      <span style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontWeight: 700, fontSize: '14px', color: '#434343' }}>{fmt(ci.total_excl_tax)} €</span>
                    </div>
                  </div>
                  <button onClick={() => removeLine(ci.id_product)} style={{ background: 'none', border: 'none', color: '#B0A99A', fontSize: '16px', cursor: 'pointer', alignSelf: 'flex-start' }}>×</button>
                </div>
              ))}
            </div>
            <div style={{ padding: '20px 24px', background: '#fff', borderTop: '1px solid #E7E3DA' }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '13px', color: '#55606F' }}><span>{t('subtotalExcl')}</span><span style={{ fontWeight: 600, color: '#1B2433' }}>{fmt(subtotal)} €</span></div>
              <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '13px', color: '#55606F', marginTop: '6px' }}><span>{t('vat')}</span><span>{fmt(vat)} €</span></div>
              <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '16px', marginTop: '12px', paddingTop: '12px', borderTop: '1px solid #E7E3DA' }}><span style={{ fontWeight: 600, color: '#2B2B2B' }}>{t('totalIncl')}</span><span style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontWeight: 700, color: '#434343' }}>{fmt(ttc)} €</span></div>
              <button onClick={goCheckout} style={{ width: '100%', marginTop: '16px', height: '52px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '15px', fontWeight: 600, color: '#fff', background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))', border: '1px solid rgba(255,255,255,.42)', boxShadow: '0 12px 26px -10px rgba(116,176,51,.55)', backdropFilter: 'blur(8px) saturate(140%)', WebkitBackdropFilter: 'blur(8px) saturate(140%)', borderRadius: '999px', cursor: 'pointer', transition: 'background .2s ease' }}>{t('checkout', { amount: fmt(ttc) })}</button>
              <div style={{ textAlign: 'center', fontSize: '11.5px', color: '#9A9A9A', marginTop: '12px' }}>{t('securePayment')}</div>
            </div>
          </>
        )}
      </div>
    </div>
  );
}
