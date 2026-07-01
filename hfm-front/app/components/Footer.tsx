'use client';

import { useEffect, useState } from 'react';
import { useTranslations, useLocale } from 'next-intl';
import { Link } from '@/i18n/navigation';
import { idLangFor } from '@/lib/i18n-config';

type Cat = { id_category: number; name: string; nb_products: number };
type MenuCat = Cat & { children: Cat[] };

const colTitle: React.CSSProperties = { fontSize: '12px', fontWeight: 700, letterSpacing: '.08em', textTransform: 'uppercase', color: '#fff', marginBottom: '14px' };
const colWrap: React.CSSProperties = { display: 'flex', flexDirection: 'column', gap: '9px', fontSize: '13px' };
const linkStyle: React.CSSProperties = { cursor: 'pointer', color: '#C7D2BA', textDecoration: 'none', transition: 'color .15s ease' };

// Pages CMS / légales (barre du bas). Éditables en BO.
// external:true -> rendu en <a> (mailto). Contact = mailto tant qu'il n'y a pas de page CMS dédiée.
const LEGAL_LINKS: { key: string; href: string; external?: boolean }[] = [
  { key: 'serviceDelivery', href: '/content/livraison-retours' },
  { key: 'serviceTerms', href: '/content/conditions-generales-de-ventes' },
  { key: 'servicePrivacy', href: '/content/politique-de-confidentialite' },
  { key: 'serviceContact', href: 'mailto:sales@hyaluronicfillermarket.com', external: true },
];

// Lien d'une catégorie : sa page si elle a des produits, sinon recherche sur son nom (jamais de page vide).
const catHref = (c: Cat) => (c.nb_products > 0 ? `/catalogue?category=${c.id_category}` : `/catalogue?q=${encodeURIComponent(c.name)}`);

export default function Footer() {
  const t = useTranslations('footer');
  const locale = useLocale();
  const [menu, setMenu] = useState<MenuCat[]>([]);

  useEffect(() => {
    fetch(`/api/taxonomy?action=menu&id_lang=${idLangFor(locale)}`)
      .then((r) => r.json())
      .then((d) => setMenu(d.menu ?? []))
      .catch(() => {});
  }, [locale]);

  const byId = (id: number) => menu.find((c) => c.id_category === id);
  const brands = (byId(30)?.children ?? []).slice(0, 14);
  const typologies = byId(301)?.children ?? [];
  const zones = byId(302)?.children ?? [];
  const effects = byId(303)?.children ?? [];

  // Rend une colonne de liens catégories (avec repli sur libellés statiques si l'arbre n'est pas encore chargé).
  const linkCol = (title: string, items: Cat[]) => (
    <div>
      <div style={colTitle}>{title}</div>
      <div style={colWrap}>
        {items.map((c) => (
          <Link key={c.id_category} href={catHref(c)} className="hfm-footlink" style={linkStyle}>{c.name}</Link>
        ))}
      </div>
    </div>
  );

  return (
    <footer style={{ position: 'relative', background: 'radial-gradient(900px 420px at 88% -30%, rgba(140,198,63,0.16), transparent 60%), linear-gradient(160deg,#202B16 0%,#26331A 55%,#2C3A1D 100%)', color: '#C7D2BA', marginTop: 0, borderTop: '3px solid #8CC63F' }}>
      <div className="hfm-wrap hfm-foot" style={{ maxWidth: '1340px', margin: '0 auto', padding: '58px 28px 30px', display: 'grid', gridTemplateColumns: '1.7fr 1fr 1fr 1fr 1fr', gap: '40px', alignItems: 'start' }}>
        <div>
          <div style={{ display: 'flex', flexDirection: 'column', gap: '4px' }}>
            <Link href="/" style={{ ...linkStyle, fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '22px', fontWeight: 600, color: '#fff' }}>Hyaluronic Filler <span style={{ color: '#8CC63F' }}>Market</span></Link>
            <div style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '11.5px', color: '#9A9A9A' }}>{t('logoSub')}</div>
          </div>
          <p style={{ fontSize: '13px', lineHeight: 1.6, margin: '16px 0 0', maxWidth: '300px', color: '#A8B79A' }}>{t('about')}</p>
          <div style={{ fontSize: '12.5px', color: '#94A580', marginTop: '18px', lineHeight: 1.7 }}>{t('address')}<br />{t('phone')}</div>
        </div>

        {linkCol(t('catalogueTitle'), typologies)}
        {linkCol(t('zonesTitle'), zones)}
        {linkCol(t('effectsTitle'), effects)}

        <div>
          <div style={colTitle}>{t('brandsTitle')}</div>
          <div style={colWrap}>
            {brands.map((b) => (
              <Link key={b.id_category} href={catHref(b)} className="hfm-footlink" style={linkStyle}>{b.name}</Link>
            ))}
            <Link href="/catalogue?category=30" className="hfm-footlink" style={{ ...linkStyle, color: '#8CC63F', fontWeight: 600, marginTop: '2px' }}>{t('allBrands')} →</Link>
          </div>
        </div>
      </div>

      <div className="hfm-wrap" style={{ borderTop: '1px solid rgba(255,255,255,.1)', maxWidth: '1340px', margin: '0 auto', padding: '20px 28px', display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: '14px 22px', flexWrap: 'wrap', fontSize: '12px', color: '#94A580' }}>
        <span>{t('copyright')}</span>
        <div style={{ display: 'flex', gap: '18px', flexWrap: 'wrap' }}>
          {LEGAL_LINKS.map((s) => s.external ? (
            <a key={s.key} href={s.href} className="hfm-footlink" style={{ ...linkStyle, color: '#94A580', fontSize: '12px' }}>{t(s.key)}</a>
          ) : (
            <Link key={s.key} href={s.href} className="hfm-footlink" style={{ ...linkStyle, color: '#94A580', fontSize: '12px' }}>{t(s.key)}</Link>
          ))}
        </div>
        <span>{t('payments')}</span>
      </div>
    </footer>
  );
}
