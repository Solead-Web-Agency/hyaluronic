'use client';

import { useTranslations } from 'next-intl';
import { Link } from '@/i18n/navigation';

const colLink: React.CSSProperties = { cursor: 'pointer' };

// Colonne "Service" : 3 items pointent vers les pages CMS (éditables en BO).
const SERVICE_LINKS: { key: string; href?: string }[] = [
  { key: 'serviceDelivery', href: '/content/livraison-retours' },
  { key: 'serviceTracking' },
  { key: 'serviceTerms', href: '/content/conditions-generales-de-ventes' },
  { key: 'servicePrivacy', href: '/content/politique-de-confidentialite' },
  { key: 'serviceContact' },
];

export default function Footer() {
  const t = useTranslations('footer');
  return (
    <footer style={{ position: 'relative', background: 'radial-gradient(900px 420px at 88% -30%, rgba(140,198,63,0.16), transparent 60%), linear-gradient(160deg,#202B16 0%,#26331A 55%,#2C3A1D 100%)', color: '#C7D2BA', marginTop: 0, borderTop: '3px solid #8CC63F' }}>
      <div className="hfm-wrap hfm-foot" style={{ maxWidth: '1340px', margin: '0 auto', padding: '58px 28px 30px', display: 'grid', gridTemplateColumns: '1.8fr 1fr 1fr 1fr', gap: '48px', alignItems: 'start' }}>
        <div>
          <div style={{ display: 'flex', flexDirection: 'column', gap: '4px' }}>
            <div style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '22px', fontWeight: 600, color: '#fff' }}>Hyaluronic Filler <span style={{ color: '#8CC63F' }}>Market</span></div>
            <div style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '11.5px', color: '#9A9A9A' }}>{t('logoSub')}</div>
          </div>
          <p style={{ fontSize: '13px', lineHeight: 1.6, margin: '16px 0 0', maxWidth: '300px', color: '#A8B79A' }}>{t('about')}</p>
          <div style={{ fontSize: '12.5px', color: '#94A580', marginTop: '18px', lineHeight: 1.7 }}>{t('address')}<br />{t('phone')}</div>
        </div>
        <div><div style={{ fontSize: '12px', fontWeight: 700, letterSpacing: '.08em', textTransform: 'uppercase', color: '#fff', marginBottom: '14px' }}>{t('catalogueTitle')}</div><div style={{ display: 'flex', flexDirection: 'column', gap: '9px', fontSize: '13px' }}>{['catFillers', 'catMeso', 'catPeelings', 'catThreads', 'catAccessories'].map((k) => <span key={k} style={colLink}>{t(k)}</span>)}</div></div>
        <div><div style={{ fontSize: '12px', fontWeight: 700, letterSpacing: '.08em', textTransform: 'uppercase', color: '#fff', marginBottom: '14px' }}>{t('brandsTitle')}</div><div style={{ display: 'flex', flexDirection: 'column', gap: '9px', fontSize: '13px' }}>{['Juvéderm', 'Restylane', 'Teoxane', 'Revolax', 'Profhilo'].map((x) => <span key={x} style={colLink}>{x}</span>)}</div></div>
        <div><div style={{ fontSize: '12px', fontWeight: 700, letterSpacing: '.08em', textTransform: 'uppercase', color: '#fff', marginBottom: '14px' }}>{t('serviceTitle')}</div><div style={{ display: 'flex', flexDirection: 'column', gap: '9px', fontSize: '13px' }}>{SERVICE_LINKS.map((s) => s.href ? <Link key={s.key} href={s.href} style={colLink}>{t(s.key)}</Link> : <span key={s.key} style={colLink}>{t(s.key)}</span>)}</div></div>
      </div>
      <div className="hfm-wrap" style={{ borderTop: '1px solid rgba(255,255,255,.1)', maxWidth: '1340px', margin: '0 auto', padding: '20px 28px', display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: '14px', flexWrap: 'wrap', fontSize: '12px', color: '#94A580' }}><span>{t('copyright')}</span><span>{t('payments')}</span></div>
    </footer>
  );
}
