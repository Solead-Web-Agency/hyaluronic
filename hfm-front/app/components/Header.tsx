'use client';

import { useEffect, useState } from 'react';
import { useTranslations } from 'next-intl';
import { Link } from '@/i18n/navigation';
import { useStore } from '../store';
import { useWishlist } from '../wishlist';
import LocaleSwitcher from './LocaleSwitcher';

const navLinkStyle: React.CSSProperties = {
  cursor: 'pointer',
  color: '#3A3A36',
  fontSize: '13.5px',
  fontWeight: 600,
  padding: '9px 13px',
  borderRadius: '8px',
  transition: 'background .2s ease, color .2s ease',
};

const acctItemStyle: React.CSSProperties = {
  display: 'flex',
  alignItems: 'center',
  gap: '10px',
  padding: '9px 12px',
  borderRadius: '9px',
  fontFamily: "'Hanken Grotesk',sans-serif",
  fontSize: '13.5px',
  color: '#3A3A36',
  cursor: 'pointer',
};

// Libellés de nav de la maquette -> mots-clés pour matcher les catégories réelles.
const NAV_DEFS: { labelKey: string; match: string[] }[] = [
  { labelKey: 'navDermalFillers', match: ['dermal', 'filler'] },
  { labelKey: 'navMeso', match: ['meso'] },
  { labelKey: 'navPeeling', match: ['peeling'] },
  { labelKey: 'navCosmetics', match: ['cosmetiq', 'cosmétiq'] },
  { labelKey: 'navAccessories', match: ['accessoir'] },
  { labelKey: 'navThreads', match: ['fil', 'tenseur', 'thread'] },
];

type Cat = { id_category: number; id_parent: number; name: string; link_rewrite: string; nb_products: number };
type Manu = { id_manufacturer: number; name: string; nb_products: number };

function norm(s: string) {
  return s.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
}

export default function Header() {
  const t = useTranslations('header');
  const tc = useTranslations('common');
  const { openSearch, openCart, toggleMenu, closeMenu, menuOpen, cartCount, customer, logout } = useStore();
  // Un invité (is_guest) n'a pas de vrai compte → traité comme NON connecté dans le header
  // (pas de menu Mes commandes / Déconnexion ; le lien « Mon compte » mène à la connexion).
  const loggedIn = !!customer && !customer.is_guest;
  const { ids: wishlistIds } = useWishlist();
  const [cats, setCats] = useState<Cat[]>([]);
  const [manus, setManus] = useState<Manu[]>([]);
  const [acctOpen, setAcctOpen] = useState(false);

  useEffect(() => {
    fetch('/api/taxonomy?action=categories')
      .then((r) => r.json())
      .then((d) => setCats(d.categories ?? []))
      .catch(() => {});
    fetch('/api/taxonomy?action=manufacturers')
      .then((r) => r.json())
      .then((d) => setManus(d.manufacturers ?? []))
      .catch(() => {});
  }, []);

  // Pour chaque entrée de nav, retrouve la catégorie réelle correspondante.
  const navLinks = NAV_DEFS.map((def) => {
    const cat = cats.find((c) => def.match.some((m) => norm(c.name).includes(norm(m))));
    return { label: t(def.labelKey), href: cat ? `/catalogue?category=${cat.id_category}` : '/catalogue' };
  });

  const brandList = manus.length
    ? manus.map((m) => ({ label: m.name, href: `/catalogue?brand=${m.id_manufacturer}` }))
    : [];

  return (
    <header style={{ position: 'sticky', top: 0, zIndex: 40 }}>
      <div style={{ background: 'linear-gradient(100deg,#33421E 0%,#4C672C 55%,#5E7E37 100%)', color: '#EDF4E2' }}>
        <div className="hfm-topbar" style={{ maxWidth: '1340px', margin: '0 auto', padding: '0 28px', height: '38px', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '26px', fontSize: '12px', letterSpacing: '.02em', overflow: 'hidden' }}>
          <span style={{ fontWeight: 600 }}>{t('topbarShipping')}</span>
          <span style={{ opacity: .4 }}>·</span>
          <span style={{ opacity: .85 }}>{t('topbarFreeShipping')}</span>
          <span style={{ opacity: .4 }}>·</span>
          <span style={{ opacity: .85 }}>{t('topbarCe')}</span>
        </div>
      </div>
      <div style={{ background: 'rgba(250,250,247,.70)', backdropFilter: 'blur(18px) saturate(150%)', WebkitBackdropFilter: 'blur(18px) saturate(150%)', borderBottom: '1px solid rgba(255,255,255,.6)', boxShadow: '0 1px 0 rgba(231,227,218,.7), 0 10px 30px -24px rgba(40,50,25,.5)' }}>
        <div className="hfm-head" style={{ maxWidth: '1340px', margin: '0 auto', padding: '8px 28px', minHeight: '58px', display: 'flex', alignItems: 'center', gap: '18px' }}>
          <button onClick={toggleMenu} className="hfm-burger" aria-label={t('menu')} style={{ display: 'none', alignItems: 'center', justifyContent: 'center', width: '44px', height: '44px', flex: 'none', borderRadius: '12px', background: '#fff', border: '1px solid #E7E3DA', cursor: 'pointer', color: '#4A4A4A', padding: 0 }}>
            {!menuOpen ? (
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><path d="M3 6h18M3 12h18M3 18h18" /></svg>
            ) : (
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><path d="M5 5l14 14M19 5L5 19" /></svg>
            )}
          </button>
          <Link href="/" onClick={closeMenu} className="hfm-logo" style={{ cursor: 'pointer', display: 'flex', flexDirection: 'column', lineHeight: 1, flex: 'none' }}>
            <span className="hfm-logo-name" style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '20px', fontWeight: 600, letterSpacing: '-.01em', color: '#4A4A4A' }}>Hyaluronic Filler <span style={{ color: '#8CC63F' }}>Market</span></span>
            <span className="hfm-logo-sub" style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '10.5px', fontWeight: 400, letterSpacing: '.01em', color: '#AEAEAE', marginTop: '2px' }}>{t('logoSub')}</span>
          </Link>
          <div onClick={openSearch} className="hfm-search" style={{ cursor: 'text', flex: '1 1 0', minWidth: 0, maxWidth: '460px', display: 'flex', alignItems: 'center', gap: '11px', height: '40px', padding: '0 16px', background: '#FFFFFF', border: '1px solid #E7E3DA', borderRadius: '999px', color: '#9A9A9A', boxShadow: '0 2px 10px -6px rgba(40,50,25,.25)', transition: 'border-color .2s ease, box-shadow .2s ease' }}>
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#8CC63F" strokeWidth="2.2" strokeLinecap="round" style={{ flex: 'none' }}><circle cx="11" cy="11" r="7" /><path d="M21 21l-4.3-4.3" /></svg>
            <span className="hfm-search-txt" style={{ fontSize: '13.5px', flex: '1 1 0', minWidth: 0, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{t('searchPlaceholder')}</span>
          </div>
          <div className="hfm-acct" style={{ display: 'flex', alignItems: 'center', gap: '16px', flex: 'none', marginLeft: 'auto', paddingLeft: '18px', borderLeft: '1px solid #E7E3DA' }}>
            <LocaleSwitcher />
            <Link href="/compte" className="hfm-icon-m" aria-label={t('account')} style={{ display: 'none', alignItems: 'center', justifyContent: 'center', width: '42px', height: '42px', flex: 'none', borderRadius: '50%', background: 'transparent', border: 'none', cursor: 'pointer', color: '#2B2B2B', padding: 0 }}><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><circle cx="12" cy="8" r="4" /><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7" /></svg></Link>
            <button onClick={openCart} className="hfm-icon-m hfm-cartm" aria-label={t('cart')} style={{ display: 'none', position: 'relative', alignItems: 'center', justifyContent: 'center', width: '42px', height: '42px', flex: 'none', borderRadius: '50%', background: 'transparent', border: 'none', cursor: 'pointer', color: '#2B2B2B', padding: 0 }}>
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><path d="M6 6h15l-1.5 9h-12z" /><path d="M6 6L5 2H2" /><circle cx="9" cy="20" r="1.4" /><circle cx="18" cy="20" r="1.4" /></svg>
              {cartCount ? <span style={{ position: 'absolute', top: '-2px', right: '-2px', minWidth: '18px', height: '18px', padding: '0 5px', display: 'inline-flex', alignItems: 'center', justifyContent: 'center', background: '#8CC63F', color: '#fff', borderRadius: '999px', fontSize: '10.5px', fontWeight: 700, border: '2px solid #FAFAF7' }}>{cartCount}</span> : null}
            </button>
            {/* Mon compte — menu déroulant au survol */}
            <div className="hfm-login" onMouseEnter={() => setAcctOpen(true)} onMouseLeave={() => setAcctOpen(false)} style={{ position: 'relative', display: 'flex', alignItems: 'center' }}>
              <Link href="/compte" style={{ cursor: 'pointer', display: 'flex', alignItems: 'center', gap: '7px', fontSize: '13.5px', color: '#6E7585' }}>
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" style={{ flex: 'none' }}><circle cx="12" cy="8" r="4" /><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7" /></svg>
                <span className="hfm-login-txt">{tc('myAccount')}</span>
                {loggedIn ? (
                  <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" style={{ flex: 'none', transition: 'transform .2s ease', transform: acctOpen ? 'rotate(180deg)' : 'none' }}><path d="M6 9l6 6 6-6" /></svg>
                ) : null}
              </Link>
              {loggedIn && acctOpen ? (
                <div style={{ position: 'absolute', top: '100%', right: 0, paddingTop: '12px', zIndex: 50 }}>
                  <div style={{ width: '230px', background: 'rgba(250,250,247,.98)', backdropFilter: 'blur(16px) saturate(150%)', WebkitBackdropFilter: 'blur(16px) saturate(150%)', border: '1px solid #E7E3DA', borderRadius: '14px', boxShadow: '0 24px 44px -22px rgba(40,50,25,.45)', padding: '6px' }}>
                    <div style={{ padding: '8px 12px', fontSize: '12px', color: '#9A9A9A', borderBottom: '1px solid #ECEAE3', marginBottom: '4px' }}>{customer.firstname} {customer.lastname}</div>
                    <Link href="/compte/commandes" onClick={() => setAcctOpen(false)} style={acctItemStyle}>
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" style={{ flex: 'none' }}><path d="M6 2h9l5 5v15H6z" /><path d="M9 12h7M9 16h7" /></svg>{tc('myOrders')}
                    </Link>
                    <Link href="/compte" onClick={() => setAcctOpen(false)} style={acctItemStyle}>
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" style={{ flex: 'none' }}><circle cx="12" cy="8" r="4" /><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7" /></svg>{tc('myInfo')}
                    </Link>
                    <button onClick={() => { setAcctOpen(false); logout(); }} style={{ ...acctItemStyle, width: '100%', textAlign: 'left', background: 'transparent', border: 'none', borderTop: '1px solid #ECEAE3', marginTop: '4px', color: '#A8503A' }}>
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" style={{ flex: 'none' }}><path d="M16 17l5-5-5-5M21 12H9M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" /></svg>{tc('logout')}
                    </button>
                  </div>
                </div>
              ) : null}
            </div>
            <Link href="/favoris" className="hfm-login" style={{ cursor: 'pointer', display: 'flex', alignItems: 'center', gap: '7px', fontSize: '13.5px', color: '#6E7585' }}><span style={{ position: 'relative', display: 'inline-flex' }}><svg width="17" height="17" viewBox="0 0 24 24" fill={wishlistIds.length ? '#A8503A' : 'none'} stroke={wishlistIds.length ? '#A8503A' : 'currentColor'} strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round" style={{ flex: 'none' }}><path d="M12 20s-7-4.5-7-10a4 4 0 0 1 7-2.5A4 4 0 0 1 19 10c0 5.5-7 10-7 10z" /></svg>{wishlistIds.length ? <span style={{ position: 'absolute', top: '-7px', right: '-9px', minWidth: '16px', height: '16px', padding: '0 4px', display: 'inline-flex', alignItems: 'center', justifyContent: 'center', background: '#A8503A', color: '#fff', borderRadius: '999px', fontSize: '10px', fontWeight: 700 }}>{wishlistIds.length}</span> : null}</span><span className="hfm-login-txt">{tc('favorites')}</span></Link>
            <button onClick={openCart} className="hfm-cart" style={{ position: 'relative', display: 'flex', alignItems: 'center', gap: '9px', height: '40px', padding: '0 18px', color: '#fff', background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))', border: '1px solid rgba(255,255,255,.42)', boxShadow: '0 10px 22px -12px rgba(140,198,63,.7)', backdropFilter: 'blur(8px) saturate(140%)', WebkitBackdropFilter: 'blur(8px) saturate(140%)', borderRadius: '999px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '13.5px', fontWeight: 600, cursor: 'pointer', transition: 'filter .2s ease, transform .2s ease' }}>
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinecap="round" strokeLinejoin="round" style={{ flex: 'none' }}><path d="M6 6h15l-1.5 9h-12z" /><path d="M6 6L5 2H2" /><circle cx="9" cy="20" r="1.4" /><circle cx="18" cy="20" r="1.4" /></svg>
              <span className="hfm-cart-txt">{t('cart')}</span>
              {cartCount ? <span style={{ minWidth: '21px', height: '21px', padding: '0 6px', display: 'inline-flex', alignItems: 'center', justifyContent: 'center', background: '#fff', color: '#5E8E1F', borderRadius: '999px', fontSize: '11.5px', fontWeight: 700 }}>{cartCount}</span> : null}
            </button>
          </div>
        </div>
        <div className="hfm-navrow" style={{ position: 'relative', borderTop: '1px solid #E7E3DA', background: 'rgba(255,255,255,.45)' }}>
          <div style={{ maxWidth: '1340px', margin: '0 auto', padding: '0 28px', height: '48px', display: 'flex', alignItems: 'center', gap: '2px' }}>
            {navLinks.map((l) => (
              <Link key={l.label} href={l.href} className="hfm-navlink" style={navLinkStyle}>{l.label}</Link>
            ))}
            <div className="hfm-megawrap" style={{ position: 'static', display: 'flex', alignItems: 'center' }}>
              <Link href="/catalogue" className="hfm-navlink hfm-megatrigger" style={{ cursor: 'pointer', display: 'inline-flex', alignItems: 'center', gap: '6px', color: '#3A3A36', fontSize: '13.5px', fontWeight: 600, padding: '9px 13px', borderRadius: '8px', transition: 'background .2s ease, color .2s ease' }}>{tc('brands')} <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" className="hfm-megachev" style={{ transition: 'transform .2s ease' }}><path d="M6 9l6 6 6-6" /></svg></Link>
              <div className="hfm-mega" style={{ position: 'absolute', top: '100%', left: 0, right: 0, background: '#fff', borderTop: '1px solid #ECEAE3', boxShadow: '0 30px 50px -28px rgba(40,50,25,.45)', zIndex: 38 }}>
                <div style={{ maxWidth: '1340px', margin: '0 auto', padding: '30px 28px 34px', display: 'grid', gridTemplateColumns: '1fr 300px', gap: '40px' }}>
                  <div>
                    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '18px' }}><div style={{ fontSize: '11.5px', fontWeight: 700, letterSpacing: '.13em', textTransform: 'uppercase', color: '#8A8170' }}>{t('allBrands')}{brandList.length ? ` · ${t('brandsCount', { count: brandList.length })}` : ''}</div><Link href="/catalogue" style={{ cursor: 'pointer', fontSize: '12.5px', fontWeight: 600, color: '#5E8E1F' }}>{t('seeAll')}</Link></div>
                    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4,1fr)', gap: '4px 24px' }}>
                      {brandList.map((b) => (
                        <Link key={b.href} href={b.href} style={{ cursor: 'pointer', display: 'block', fontSize: '13.5px', color: '#3A3A36', padding: '7px 0', borderBottom: '1px solid #F4F2EC', transition: 'color .15s ease' }}>{b.label}</Link>
                      ))}
                    </div>
                  </div>
                  <div style={{ borderLeft: '1px solid #ECEAE3', paddingLeft: '34px' }}>
                    <div style={{ fontSize: '11.5px', fontWeight: 700, letterSpacing: '.13em', textTransform: 'uppercase', color: '#8A8170', marginBottom: '14px' }}>{t('featured')}</div>
                    <Link href="/catalogue" style={{ cursor: 'pointer', display: 'block', border: '1px solid #ECEAE3', borderRadius: '12px', overflow: 'hidden', transition: 'box-shadow .25s ease, transform .25s ease' }}>
                      <div style={{ position: 'relative', aspectRatio: '4/3', background: '#F7F6F2' }}><span style={{ position: 'absolute', top: '12px', right: '12px', fontSize: '10px', fontWeight: 700, letterSpacing: '.08em', textTransform: 'uppercase', color: '#fff', background: '#C2705C', padding: '5px 9px', borderRadius: '999px' }}>{t('discovery')}</span></div>
                      <div style={{ padding: '14px 16px 16px' }}><div style={{ fontSize: '10.5px', fontWeight: 600, letterSpacing: '.08em', textTransform: 'uppercase', color: '#8A8170' }}>Galderma</div><div style={{ fontFamily: "'Spectral',serif", fontSize: '15px', color: '#2B2B2B', lineHeight: 1.3, marginTop: '4px' }}>Restylane Skinboosters Vital Light 1×1ml</div><div style={{ display: 'flex', alignItems: 'baseline', justifyContent: 'space-between', marginTop: '10px' }}><span style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontWeight: 700, fontSize: '17px', color: '#A8503A' }}>58,33 €</span><span style={{ fontSize: '12px', fontWeight: 600, color: '#5E8E1F' }}>{t('see')}</span></div></div>
                    </Link>
                  </div>
                </div>
              </div>
            </div>
            <Link href="/catalogue" style={{ cursor: 'pointer', display: 'inline-flex', alignItems: 'center', gap: '7px', color: '#A8503A', fontSize: '13.5px', fontWeight: 600, padding: '8px 15px', borderRadius: '999px', background: 'rgba(168,80,58,.09)', border: '1px solid rgba(168,80,58,.2)', transition: 'background .2s ease', marginLeft: '6px' }}><span style={{ width: '6px', height: '6px', borderRadius: '50%', background: '#A8503A' }} />{t('promosTop')}</Link>
            <span style={{ marginLeft: 'auto', display: 'inline-flex', alignItems: 'center', gap: '9px', fontSize: '12.5px', color: '#5E8E1F', fontWeight: 600 }}><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#8CC63F" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M20 6L9 17l-5-5" /></svg>{t('ceDelivery')}</span>
          </div>
        </div>
        <div onClick={openSearch} className="hfm-msearch" style={{ display: 'none', padding: '0 16px 13px' }}>
          <div style={{ cursor: 'text', display: 'flex', alignItems: 'center', gap: '11px', height: '48px', padding: '0 18px', background: '#fff', border: '1px solid #E7E3DA', borderRadius: '999px', boxShadow: '0 2px 10px -7px rgba(40,50,25,.3)' }}>
            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#9A9A9A" strokeWidth="1.8" strokeLinecap="round" style={{ flex: 'none' }}><circle cx="11" cy="11" r="7" /><path d="M21 21l-4.3-4.3" /></svg>
            <span style={{ fontSize: '14px', color: '#9A9A9A', flex: 1, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{t('searchPlaceholder')}</span>
          </div>
        </div>
        <div className={`hfm-mobilemenu ${menuOpen ? 'hfm-menu-open' : ''}`} style={{ display: 'none', borderTop: '1px solid #E7E3DA', background: 'rgba(250,250,247,.97)', backdropFilter: 'blur(16px) saturate(150%)', WebkitBackdropFilter: 'blur(16px) saturate(150%)', padding: '16px 16px 20px', boxShadow: '0 24px 40px -26px rgba(40,50,25,.5)' }}>
          <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
            <Link href="/catalogue" onClick={closeMenu} style={{ cursor: 'pointer', display: 'flex', alignItems: 'center', gap: '13px', padding: '12px 13px', borderRadius: '14px', background: '#fff', border: '1px solid #ECEAE3' }}>
              <span style={{ flex: 'none', width: '38px', height: '38px', borderRadius: '11px', background: 'rgba(140,198,63,.13)', color: '#5E8E1F', display: 'flex', alignItems: 'center', justifyContent: 'center' }}><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinecap="round" strokeLinejoin="round"><path d="M12 3s7 7.5 7 12a7 7 0 0 1-14 0c0-4.5 7-12 7-12z" /></svg></span>
              <span style={{ flex: 1 }}><span style={{ display: 'block', fontSize: '15.5px', fontWeight: 600, color: '#34352F' }}>{t('mFillersTitle')}</span><span style={{ display: 'block', fontSize: '12px', color: '#9A9A9A', marginTop: '1px' }}>{t('mFillersSub')}</span></span>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#C9C2AF" strokeWidth="2" strokeLinecap="round"><path d="M9 6l6 6-6 6" /></svg>
            </Link>
            <Link href="/catalogue" onClick={closeMenu} style={{ cursor: 'pointer', display: 'flex', alignItems: 'center', gap: '13px', padding: '12px 13px', borderRadius: '14px', background: '#fff', border: '1px solid #ECEAE3' }}>
              <span style={{ flex: 'none', width: '38px', height: '38px', borderRadius: '11px', background: 'rgba(140,198,63,.13)', color: '#5E8E1F', display: 'flex', alignItems: 'center', justifyContent: 'center' }}><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8"><circle cx="7" cy="7" r="2.3" /><circle cx="17" cy="7" r="2.3" /><circle cx="7" cy="17" r="2.3" /><circle cx="17" cy="17" r="2.3" /></svg></span>
              <span style={{ flex: 1 }}><span style={{ display: 'block', fontSize: '15.5px', fontWeight: 600, color: '#34352F' }}>{t('mMesoTitle')}</span><span style={{ display: 'block', fontSize: '12px', color: '#9A9A9A', marginTop: '1px' }}>{t('mMesoSub')}</span></span>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#C9C2AF" strokeWidth="2" strokeLinecap="round"><path d="M9 6l6 6-6 6" /></svg>
            </Link>
            <Link href="/catalogue" onClick={closeMenu} style={{ cursor: 'pointer', display: 'flex', alignItems: 'center', gap: '13px', padding: '12px 13px', borderRadius: '14px', background: '#fff', border: '1px solid #ECEAE3' }}>
              <span style={{ flex: 'none', width: '38px', height: '38px', borderRadius: '11px', background: 'rgba(140,198,63,.13)', color: '#5E8E1F', display: 'flex', alignItems: 'center', justifyContent: 'center' }}><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round"><path d="M20.6 13.4l-7.2 7.2a2 2 0 0 1-2.8 0l-7-7a2 2 0 0 1-.6-1.4V4a1 1 0 0 1 1-1h8.2a2 2 0 0 1 1.4.6l7 7a2 2 0 0 1 0 2.8z" /><circle cx="7.5" cy="7.5" r="1.3" /></svg></span>
              <span style={{ flex: 1 }}><span style={{ display: 'block', fontSize: '15.5px', fontWeight: 600, color: '#34352F' }}>{t('mByBrandTitle')}</span><span style={{ display: 'block', fontSize: '12px', color: '#9A9A9A', marginTop: '1px' }}>{t('mByBrandSub')}</span></span>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#C9C2AF" strokeWidth="2" strokeLinecap="round"><path d="M9 6l6 6-6 6" /></svg>
            </Link>
            <Link href="/catalogue" onClick={closeMenu} style={{ cursor: 'pointer', display: 'flex', alignItems: 'center', gap: '13px', padding: '12px 13px', borderRadius: '14px', background: 'rgba(168,80,58,.06)', border: '1px solid rgba(168,80,58,.22)' }}>
              <span style={{ flex: 'none', width: '38px', height: '38px', borderRadius: '11px', background: 'rgba(168,80,58,.12)', color: '#A8503A', display: 'flex', alignItems: 'center', justifyContent: 'center' }}><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinecap="round"><path d="M19 5L5 19" /><circle cx="7.5" cy="7.5" r="2" /><circle cx="16.5" cy="16.5" r="2" /></svg></span>
              <span style={{ flex: 1 }}><span style={{ display: 'block', fontSize: '15.5px', fontWeight: 700, color: '#A8503A' }}>{t('mPromosTitle')}</span><span style={{ display: 'block', fontSize: '12px', color: '#B57767', marginTop: '1px' }}>{t('mPromosSub')}</span></span>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#A8503A" strokeWidth="2" strokeLinecap="round"><path d="M9 6l6 6-6 6" /></svg>
            </Link>
          </div>
          <Link href="/compte" onClick={closeMenu} style={{ width: '100%', marginTop: '14px', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '9px', height: '50px', borderRadius: '999px', background: '#fff', border: '1.5px solid #8CC63F', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14.5px', fontWeight: 600, color: '#5E8E1F', cursor: 'pointer' }}><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round"><circle cx="12" cy="8" r="4" /><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7" /></svg>{loggedIn ? tc('myAccount') : t('loginPractitioner')}</Link>
          <div style={{ display: 'flex', justifyContent: 'center', flexWrap: 'wrap', gap: '7px', marginTop: '16px' }}>
            <span style={{ display: 'inline-flex', alignItems: 'center', gap: '6px', fontSize: '11.5px', color: '#7A8268', background: 'rgba(140,198,63,.09)', border: '1px solid rgba(140,198,63,.18)', padding: '6px 11px', borderRadius: '999px' }}>{t('tagCe')}</span>
            <span style={{ display: 'inline-flex', alignItems: 'center', gap: '6px', fontSize: '11.5px', color: '#7A8268', background: 'rgba(140,198,63,.09)', border: '1px solid rgba(140,198,63,.18)', padding: '6px 11px', borderRadius: '999px' }}>{t('tagDelay')}</span>
            <span style={{ display: 'inline-flex', alignItems: 'center', gap: '6px', fontSize: '11.5px', color: '#7A8268', background: 'rgba(140,198,63,.09)', border: '1px solid rgba(140,198,63,.18)', padding: '6px 11px', borderRadius: '999px' }}>{t('tagSecure')}</span>
          </div>
        </div>
      </div>
    </header>
  );
}
