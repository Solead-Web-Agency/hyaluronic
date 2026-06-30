'use client';

import { useEffect, useMemo, useState } from 'react';
import { useTranslations, useLocale } from 'next-intl';
import { Link } from '@/i18n/navigation';
import { useRouter } from '@/i18n/navigation';
import { useSearchParams } from 'next/navigation';
import { toCard, type Card } from '@/lib/cardModel';
import type { ProductCard as ProductCardData } from '@/lib/ps';
import { idLangFor } from '@/lib/i18n-config';
import ProductCard from '../../components/ProductCard';

type Cat = { id_category: number; id_parent: number; name: string; link_rewrite: string; nb_products: number };
type Manu = { id_manufacturer: number; name: string; nb_products: number };

const SORT_DEFS: [string, 'sortPop' | 'sortPriceAsc' | 'sortPriceDesc'][] = [
  ['pop', 'sortPop'],
  ['price-asc', 'sortPriceAsc'],
  ['price-desc', 'sortPriceDesc'],
];

export default function CatalogueClient() {
  const t = useTranslations('catalogue');
  const tc = useTranslations('common');
  const locale = useLocale();
  const router = useRouter();
  const search = useSearchParams();
  const category = search.get('category');
  const brand = search.get('brand');
  const q = search.get('q');
  const filter = search.get('filter'); // onglet Promos & Top : new | best | promo | nolido

  const [cats, setCats] = useState<Cat[]>([]);
  const [manus, setManus] = useState<Manu[]>([]);
  const [products, setProducts] = useState<Card[]>([]);
  const [loading, setLoading] = useState(true);
  const [sort, setSort] = useState('pop');
  const [filtersOpen, setFiltersOpen] = useState(false);
  const [searchTerm, setSearchTerm] = useState(q ?? '');

  // Taxonomie (catégories + marques) pour la sidebar.
  useEffect(() => {
    fetch(`/api/taxonomy?action=categories&id_lang=${idLangFor(locale)}`).then((r) => r.json()).then((d) => setCats(d.categories ?? [])).catch(() => {});
    fetch(`/api/taxonomy?action=manufacturers&id_lang=${idLangFor(locale)}`).then((r) => r.json()).then((d) => setManus(d.manufacturers ?? [])).catch(() => {});
  }, [locale]);

  useEffect(() => { setSearchTerm(q ?? ''); }, [q]);

  // Produits selon le filtre actif dans l'URL.
  useEffect(() => {
    setLoading(true);
    const params = new URLSearchParams({ limit: '300' });
    params.set('id_lang', String(idLangFor(locale)));
    if (category) params.set('id_category', category);
    else if (brand) params.set('id_manufacturer', brand);
    else if (q) params.set('q', q);
    else if (filter) params.set('filter', filter);
    fetch(`/api/products?${params.toString()}`)
      .then((r) => r.json())
      .then((d) => setProducts((d.products ?? []).map((p: ProductCardData) => toCard(p))))
      .catch(() => setProducts([]))
      .finally(() => setLoading(false));
  }, [category, brand, q, filter, locale]);

  // Navigation vers un filtre (remplace l'URL).
  const go = (next: { category?: number; brand?: number; q?: string } | null) => {
    setFiltersOpen(false);
    if (!next) { router.push('/catalogue'); return; }
    const p = new URLSearchParams();
    if (next.category) p.set('category', String(next.category));
    else if (next.brand) p.set('brand', String(next.brand));
    else if (next.q) p.set('q', next.q);
    router.push(`/catalogue?${p.toString()}`);
  };

  const list = useMemo(() => {
    let l = products;
    if (sort === 'price-asc') l = [...l].sort((a, b) => a.ht - b.ht);
    else if (sort === 'price-desc') l = [...l].sort((a, b) => b.ht - a.ht);
    return l;
  }, [products, sort]);

  const count = list.length;
  const activeFilterCount = (category ? 1 : 0) + (brand ? 1 : 0) + (q ? 1 : 0) + (filter ? 1 : 0);
  const noFilter = !category && !brand && !q && !filter;

  const submitSearch = (e: React.FormEvent) => {
    e.preventDefault();
    const term = searchTerm.trim();
    if (term) go({ q: term });
    else go(null);
  };

  const filterRowStyle: React.CSSProperties = {
    display: 'flex', alignItems: 'center', gap: '10px', width: '100%', textAlign: 'left',
    background: 'transparent', border: 'none', padding: '8px 6px', cursor: 'pointer',
    borderRadius: '5px', fontFamily: "'Hanken Grotesk',sans-serif",
  };
  const radio = (active: boolean) => active
    ? <span style={{ width: '13px', height: '13px', borderRadius: '50%', border: '4px solid #434343', flex: 'none' }} />
    : <span style={{ width: '13px', height: '13px', borderRadius: '50%', border: '1.5px solid #C9C2AF', flex: 'none' }} />;

  const heading = q ? t('searchHeading', { query: q }) : t('heading');

  return (
    <main data-screen-label="Catalogue" className="hfm-wrap" style={{ maxWidth: '1340px', margin: '0 auto', padding: '34px 28px 70px' }}>
      <div style={{ fontSize: '12.5px', color: '#9A9A9A', marginBottom: '18px' }}><Link href="/" style={{ cursor: 'pointer' }}>{tc('home')}</Link>  /  {t('breadcrumb')}</div>
      <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: '38px', color: '#2B2B2B', margin: 0 }}>{heading}</h1>
      <div style={{ fontSize: '14px', color: '#6E7585', marginTop: '8px' }}>{loading ? tc('loading') : t('productsCount', { count })}</div>

      <button onClick={() => setFiltersOpen((o) => !o)} className="hfm-filterbar" style={{ display: 'none', alignItems: 'center', gap: '10px', width: '100%', marginTop: '18px', height: '50px', padding: '0 18px', background: '#fff', border: '1px solid #E7E3DA', borderRadius: '14px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14.5px', fontWeight: 600, color: '#34352F', cursor: 'pointer', boxShadow: '0 2px 10px -6px rgba(40,50,25,.25)' }}>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#5E8E1F" strokeWidth="1.9" strokeLinecap="round"><path d="M4 6h16M7 12h10M10 18h4" /></svg>
        <span style={{ flex: 1, textAlign: 'left' }}>{t('filterAndSort')}</span>
        {activeFilterCount ? <span style={{ minWidth: '22px', height: '22px', padding: '0 7px', display: 'inline-flex', alignItems: 'center', justifyContent: 'center', background: '#8CC63F', color: '#fff', borderRadius: '999px', fontSize: '12px', fontWeight: 700 }}>{activeFilterCount}</span> : null}
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#C9C2AF" strokeWidth="2" strokeLinecap="round"><path d="M6 9l6 6 6-6" /></svg>
      </button>
      {filtersOpen ? <div onClick={() => setFiltersOpen(false)} className="hfm-filterbackdrop" style={{ display: 'none', position: 'fixed', inset: 0, background: 'rgba(22,33,15,.45)', zIndex: 54 }} /> : null}

      <div className="hfm-catwrap" style={{ display: 'flex', flexWrap: 'wrap', gap: '34px', marginTop: '30px', alignItems: 'flex-start' }}>
        <aside className={`hfm-aside ${filtersOpen ? 'hfm-aside-open' : ''}`} style={{ position: 'sticky', top: '130px', flex: '0 0 240px', display: 'flex', flexDirection: 'column', gap: '26px' }}>
          <div className="hfm-filterhead" style={{ display: 'none', alignItems: 'center', justifyContent: 'space-between', paddingBottom: '6px' }}>
            <span style={{ fontFamily: "'Spectral',serif", fontSize: '20px', color: '#2B2B2B' }}>{t('filters')}</span>
            <button onClick={() => setFiltersOpen(false)} style={{ width: '38px', height: '38px', borderRadius: '50%', background: '#F2F0EA', border: 'none', fontSize: '19px', color: '#8A8170', cursor: 'pointer', lineHeight: 1 }}>×</button>
          </div>

          {/* Recherche */}
          <form onSubmit={submitSearch}>
            <div style={{ fontSize: '11px', fontWeight: 700, letterSpacing: '.1em', textTransform: 'uppercase', color: '#8A8170', marginBottom: '12px' }}>{t('searchLabel')}</div>
            <input value={searchTerm} onChange={(e) => setSearchTerm(e.target.value)} placeholder={t('searchPlaceholder')} style={{ width: '100%', boxSizing: 'border-box', height: '42px', padding: '0 14px', border: '1px solid #E2DECF', borderRadius: '8px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '13.5px', outline: 'none', background: '#fff', color: '#34352F' }} />
          </form>

          {/* Catégories */}
          <div>
            <div style={{ fontSize: '11px', fontWeight: 700, letterSpacing: '.1em', textTransform: 'uppercase', color: '#8A8170', marginBottom: '12px' }}>{t('categoryLabel')}</div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '3px' }}>
              <button onClick={() => go(null)} style={filterRowStyle}>
                {radio(noFilter)}<span style={{ fontSize: '13.5px', color: '#1B2433', flex: 1 }}>{t('all')}</span>
              </button>
              {cats.map((c) => {
                const active = category === String(c.id_category);
                return (
                  <button key={c.id_category} onClick={() => go({ category: c.id_category })} style={filterRowStyle}>
                    {radio(active)}<span style={{ fontSize: '13.5px', color: '#1B2433', flex: 1 }}>{c.name}</span><span style={{ fontSize: '11.5px', color: '#9A9A9A' }}>{c.nb_products}</span>
                  </button>
                );
              })}
            </div>
          </div>

          {/* Marques */}
          <div>
            <div style={{ fontSize: '11px', fontWeight: 700, letterSpacing: '.1em', textTransform: 'uppercase', color: '#8A8170', marginBottom: '12px' }}>{t('brandLabel')}</div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '3px', maxHeight: '320px', overflowY: 'auto' }}>
              {manus.map((m) => {
                const active = brand === String(m.id_manufacturer);
                return (
                  <button key={m.id_manufacturer} onClick={() => go({ brand: m.id_manufacturer })} style={filterRowStyle}>
                    {radio(active)}<span style={{ fontSize: '13.5px', color: '#1B2433', flex: 1 }}>{m.name.trim()}</span><span style={{ fontSize: '11.5px', color: '#9A9A9A' }}>{m.nb_products}</span>
                  </button>
                );
              })}
            </div>
          </div>

          <button onClick={() => setFiltersOpen(false)} className="hfm-filterapply" style={{ display: 'none', width: '100%', height: '50px', borderRadius: '999px', color: '#fff', background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))', border: '1px solid rgba(255,255,255,.42)', boxShadow: '0 10px 22px -12px rgba(140,198,63,.7)', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '15px', fontWeight: 600, cursor: 'pointer', marginTop: '4px' }}>{t('seeProducts', { count })}</button>
        </aside>

        <div style={{ flex: '1 1 480px', minWidth: 0 }}>
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '20px', gap: '14px', flexWrap: 'wrap' }}>
            <div style={{ fontSize: '13px', color: '#6E7585' }}>{loading ? '…' : t('resultsCount', { count })}</div>
            <div style={{ display: 'flex', gap: '6px', background: '#fff', border: '1px solid #E7E3DA', borderRadius: '7px', padding: '4px' }}>
              {SORT_DEFS.map(([k, labelKey]) => sort === k ? (
                <button key={k} style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '12.5px', fontWeight: 600, color: '#fff', background: '#434343', border: 'none', borderRadius: '4px', padding: '7px 14px', cursor: 'pointer' }}>{t(labelKey)}</button>
              ) : (
                <button key={k} onClick={() => setSort(k)} style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '12.5px', fontWeight: 600, color: '#55606F', background: 'transparent', border: 'none', borderRadius: '4px', padding: '7px 14px', cursor: 'pointer' }}>{t(labelKey)}</button>
              ))}
            </div>
          </div>
          {!loading && count === 0 ? (
            <div style={{ padding: '60px 0', textAlign: 'center', color: '#8A8170', fontSize: '15px' }}>{t('noResults')}</div>
          ) : (
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit,minmax(210px,1fr))', gap: '18px' }}>
              {list.map((item) => <ProductCard key={item.id} product={item} />)}
            </div>
          )}
        </div>
      </div>
    </main>
  );
}
