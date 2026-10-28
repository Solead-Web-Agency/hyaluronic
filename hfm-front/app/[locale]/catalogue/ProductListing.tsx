'use client';

import { useEffect, useMemo, useRef, useState } from 'react';
import { useTranslations } from 'next-intl';
import { useRouter } from '@/i18n/navigation';
import type { ProductCard as ProductCardData } from '@/lib/ps';
import { toCard, type Card } from '@/lib/cardModel';
import type { FacetGroup, ListingFacets } from '@/lib/facets';
import ProductCard from '../../components/ProductCard';

// Listing produits FILTRABLE + TRIABLE, partagé par le catalogue, les pages catégorie et les pages
// marque (ordinateur : colonne de filtres ; mobile : bouton « Filtrer & trier » + tiroir).
// Le serveur fournit TOUT le listing (≤ 1000, le catalogue complet en compte ~575) + l'appartenance
// des produits aux catégories éditoriales : filtres combinables (ET entre groupes, OU dans un groupe)
// et tri se font ici, instantanément. Les 48 premiers produits sont rendus côté serveur (SEO), la
// suite est révélée au défilement.

type Group = FacetGroup | 'availability' | 'price';
type Sort = 'pop' | 'price-asc' | 'price-desc';

const PAGE = 48;
const SORTS: [Sort, 'sortPop' | 'sortPriceAsc' | 'sortPriceDesc'][] = [
  ['pop', 'sortPop'],
  ['price-asc', 'sortPriceAsc'],
  ['price-desc', 'sortPriceDesc'],
];
const GROUP_ORDER: Group[] = ['category', 'zone', 'effect', 'brand', 'availability', 'price'];
const GROUP_LABEL: Record<Group, 'categoryLabel' | 'zoneLabel' | 'effectLabel' | 'brandLabel' | 'availabilityLabel' | 'priceLabel'> = {
  category: 'categoryLabel',
  zone: 'zoneLabel',
  effect: 'effectLabel',
  brand: 'brandLabel',
  availability: 'availabilityLabel',
  price: 'priceLabel',
};
// Tranches de prix HT (bornes basses incluses).
const PRICE_BUCKETS: [string, number, number][] = [
  ['p0', 0, 50],
  ['p1', 50, 100],
  ['p2', 100, 200],
  ['p3', 200, 500],
  ['p4', 500, Infinity],
];
const priceBucket = (ht: number) => (PRICE_BUCKETS.find(([, lo, hi]) => ht >= lo && ht < hi) ?? PRICE_BUCKETS[0])[0];
// Achetable d'abord (en stock ou sur commande), indisponible en dernier — quel que soit le tri.
const buyRank = (c: Card) => (c.stock === 'out' ? 1 : 0);

export default function ProductListing({ products, facets, hideKeys = [], search }: {
  products: ProductCardData[];
  facets: ListingFacets;
  hideKeys?: string[];
  search?: { q: string | null };
}) {
  const t = useTranslations('catalogue');
  const tc = useTranslations('common');
  const router = useRouter();

  const cards = useMemo(() => products.map(toCard), [products]);
  const [sort, setSort] = useState<Sort>('pop');
  const [sel, setSel] = useState<Partial<Record<Group, string[]>>>({});
  const [filtersOpen, setFiltersOpen] = useState(false);
  const [searchTerm, setSearchTerm] = useState(search?.q ?? '');
  const [visible, setVisible] = useState(PAGE);
  const sentinelRef = useRef<HTMLDivElement | null>(null);

  useEffect(() => { setSearchTerm(search?.q ?? ''); }, [search?.q]);

  // Libellés et groupe de chaque option de facette éditoriale.
  const optionByKey = useMemo(() => new Map(facets.options.map((o) => [o.key, o])), [facets.options]);
  const hidden = useMemo(() => new Set(hideKeys), [hideKeys]);

  const keysOf = useMemo(() => {
    const cache = new Map<number, Record<Group, string[]>>();
    for (const c of cards) {
      const r: Record<Group, string[]> = { category: [], zone: [], effect: [], brand: [], availability: [c.stock], price: [priceBucket(c.ht)] };
      for (const k of facets.membership[c.id] ?? []) {
        const o = optionByKey.get(k);
        if (o) r[o.group].push(k);
      }
      cache.set(c.id, r);
    }
    return cache;
  }, [cards, facets.membership, optionByKey]);

  const matches = (c: Card, except?: Group) => {
    const ks = keysOf.get(c.id)!;
    for (const g of GROUP_ORDER) {
      if (g === except) continue;
      const s = sel[g];
      if (s?.length && !s.some((k) => ks[g].includes(k))) return false;
    }
    return true;
  };

  const filtered = useMemo(() => {
    const list = cards.map((c, i) => ({ c, i })).filter(({ c }) => matches(c));
    list.sort((a, b) => {
      const r = buyRank(a.c) - buyRank(b.c);
      if (r) return r;
      if (sort === 'price-asc' && a.c.ht !== b.c.ht) return a.c.ht - b.c.ht;
      if (sort === 'price-desc' && a.c.ht !== b.c.ht) return b.c.ht - a.c.ht;
      return a.i - b.i; // ordre du bridge (position catalogue)
    });
    return list.map(({ c }) => c);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [cards, sel, sort, keysOf]);

  // Options affichées par groupe, avec le nombre de produits qu'elles donneraient (les AUTRES
  // groupes restant appliqués). Une option à 0 disparaît ; un groupe sans choix utile aussi.
  const groups = useMemo(() => {
    const out: { group: Group; options: { key: string; label: string; count: number }[] }[] = [];
    for (const g of GROUP_ORDER) {
      const counts = new Map<string, number>();
      for (const c of cards) {
        if (!matches(c, g)) continue;
        for (const k of keysOf.get(c.id)![g]) counts.set(k, (counts.get(k) ?? 0) + 1);
      }
      let opts: { key: string; label: string; count: number }[];
      if (g === 'availability') {
        opts = (['in', 'backorder', 'out'] as const).map((k) => ({
          key: k,
          label: k === 'in' ? tc('inStock') : k === 'backorder' ? tc('onOrder') : t('unavailable'),
          count: counts.get(k) ?? 0,
        }));
      } else if (g === 'price') {
        opts = PRICE_BUCKETS.map(([k, lo, hi]) => ({
          key: k,
          label: lo === 0 ? t('priceUnder', { max: hi }) : hi === Infinity ? t('priceOver', { min: lo }) : t('priceBetween', { min: lo, max: hi }),
          count: counts.get(k) ?? 0,
        }));
      } else {
        opts = facets.options
          .filter((o) => o.group === g && !hidden.has(o.key))
          .map((o) => ({ key: o.key, label: o.name, count: counts.get(o.key) ?? 0 }));
        if (g === 'brand') opts.sort((a, b) => a.label.localeCompare(b.label));
      }
      const selected = sel[g] ?? [];
      opts = opts.filter((o) => o.count > 0 || selected.includes(o.key));
      // Inutile de proposer un filtre qui ne distingue rien (chaque valeur couvre tout le listing).
      const useful = selected.length > 0 || opts.some((o) => o.count < cards.length);
      if (opts.length && useful) out.push({ group: g, options: opts });
    }
    return out;
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [cards, sel, keysOf, facets.options, hidden]);

  const activeCount = Object.values(sel).reduce((n, s) => n + (s?.length ?? 0), 0);
  const count = filtered.length;

  // Retour aux 48 premiers à chaque changement de filtre / tri.
  useEffect(() => { setVisible(PAGE); }, [sel, sort]);
  useEffect(() => {
    const el = sentinelRef.current;
    if (!el || visible >= count) return;
    const io = new IntersectionObserver(
      (entries) => { if (entries.some((e) => e.isIntersecting)) setVisible((v) => Math.min(v + PAGE, count)); },
      { rootMargin: '600px 0px' },
    );
    io.observe(el);
    return () => io.disconnect();
  }, [visible, count]);

  const toggle = (g: Group, key: string) => {
    setSel((prev) => {
      const cur = prev[g] ?? [];
      const next = cur.includes(key) ? cur.filter((k) => k !== key) : [...cur, key];
      return { ...prev, [g]: next };
    });
  };

  const submitSearch = (e: React.FormEvent) => {
    e.preventDefault();
    const term = searchTerm.trim();
    setFiltersOpen(false);
    router.push(term ? `/catalogue?q=${encodeURIComponent(term)}` : '/catalogue');
  };

  const labelStyle: React.CSSProperties = { fontSize: '11px', fontWeight: 700, letterSpacing: '.1em', textTransform: 'uppercase', color: '#8A8170', marginBottom: '10px' };
  const rowStyle: React.CSSProperties = {
    display: 'flex', alignItems: 'center', gap: '10px', width: '100%', textAlign: 'left',
    background: 'transparent', border: 'none', padding: '7px 6px', cursor: 'pointer',
    borderRadius: '5px', fontFamily: "'Hanken Grotesk',sans-serif",
  };
  const box = (on: boolean) => (
    <span aria-hidden="true" style={{ width: '15px', height: '15px', flex: 'none', borderRadius: '4px', border: on ? 'none' : '1.5px solid #C9C2AF', background: on ? '#5E8E1F' : '#fff', display: 'inline-flex', alignItems: 'center', justifyContent: 'center' }}>
      {on ? <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="3.4" strokeLinecap="round" strokeLinejoin="round"><path d="M20 6L9 17l-5-5" /></svg> : null}
    </span>
  );
  const sortButtons = (
    <div role="group" aria-label={t('sortLabel')} style={{ display: 'flex', gap: '6px', background: '#fff', border: '1px solid #E7E3DA', borderRadius: '7px', padding: '4px' }}>
      {SORTS.map(([k, labelKey]) => (
        <button key={k} type="button" aria-pressed={sort === k} onClick={() => setSort(k)} style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '12.5px', fontWeight: 600, color: sort === k ? '#fff' : '#55606F', background: sort === k ? '#434343' : 'transparent', border: 'none', borderRadius: '4px', padding: '7px 14px', cursor: 'pointer' }}>{t(labelKey)}</button>
      ))}
    </div>
  );

  return (
    <>
      <button type="button" onClick={() => setFiltersOpen((o) => !o)} className="hfm-filterbar" style={{ display: 'none', alignItems: 'center', gap: '10px', width: '100%', marginTop: '18px', height: '50px', padding: '0 18px', background: '#fff', border: '1px solid #E7E3DA', borderRadius: '14px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14.5px', fontWeight: 600, color: '#34352F', cursor: 'pointer', boxShadow: '0 2px 10px -6px rgba(40,50,25,.25)' }}>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#5E8E1F" strokeWidth="1.9" strokeLinecap="round"><path d="M4 6h16M7 12h10M10 18h4" /></svg>
        <span style={{ flex: 1, textAlign: 'left' }}>{t('filterAndSort')}</span>
        {activeCount ? <span style={{ minWidth: '22px', height: '22px', padding: '0 7px', display: 'inline-flex', alignItems: 'center', justifyContent: 'center', background: '#8CC63F', color: '#fff', borderRadius: '999px', fontSize: '12px', fontWeight: 700 }}>{activeCount}</span> : null}
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#C9C2AF" strokeWidth="2" strokeLinecap="round"><path d="M6 9l6 6 6-6" /></svg>
      </button>
      {filtersOpen ? <div onClick={() => setFiltersOpen(false)} className="hfm-filterbackdrop" style={{ display: 'none', position: 'fixed', inset: 0, background: 'rgba(22,33,15,.45)', zIndex: 54 }} /> : null}

      <div className="hfm-catwrap" style={{ display: 'flex', flexWrap: 'wrap', gap: '34px', marginTop: '26px', alignItems: 'flex-start' }}>
        <aside className={`hfm-aside ${filtersOpen ? 'hfm-aside-open' : ''}`} style={{ position: 'sticky', top: '130px', flex: '0 0 240px', display: 'flex', flexDirection: 'column', gap: '24px', maxHeight: 'calc(100vh - 150px)', overflowY: 'auto' }}>
          <div className="hfm-filterhead" style={{ display: 'none', alignItems: 'center', justifyContent: 'space-between', paddingBottom: '6px' }}>
            <span style={{ fontFamily: "'Spectral',serif", fontSize: '20px', color: '#2B2B2B' }}>{t('filters')}</span>
            <button type="button" onClick={() => setFiltersOpen(false)} aria-label={tc('cancel')} style={{ width: '38px', height: '38px', borderRadius: '50%', background: '#F2F0EA', border: 'none', fontSize: '19px', color: '#8A8170', cursor: 'pointer', lineHeight: 1 }}>×</button>
          </div>

          {search ? (
            <form onSubmit={submitSearch}>
              <div style={labelStyle}>{t('searchLabel')}</div>
              <input value={searchTerm} onChange={(e) => setSearchTerm(e.target.value)} placeholder={t('searchPlaceholder')} aria-label={t('searchLabel')} style={{ width: '100%', boxSizing: 'border-box', height: '42px', padding: '0 14px', border: '1px solid #E2DECF', borderRadius: '8px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '13.5px', outline: 'none', background: '#fff', color: '#34352F' }} />
            </form>
          ) : null}

          {activeCount ? (
            <button type="button" onClick={() => setSel({})} style={{ alignSelf: 'flex-start', background: 'transparent', border: 'none', padding: 0, cursor: 'pointer', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '13px', fontWeight: 600, color: '#A8503A', textDecoration: 'underline' }}>{t('clearFilters')}</button>
          ) : null}

          {groups.map(({ group, options }) => (
            <div key={group}>
              <div style={labelStyle}>{t(GROUP_LABEL[group])}</div>
              <div style={{ display: 'flex', flexDirection: 'column', gap: '1px', maxHeight: options.length > 9 ? '300px' : undefined, overflowY: options.length > 9 ? 'auto' : undefined }}>
                {options.map((o) => {
                  const on = (sel[group] ?? []).includes(o.key);
                  return (
                    <button key={o.key} type="button" role="checkbox" aria-checked={on} onClick={() => toggle(group, o.key)} style={rowStyle}>
                      {box(on)}<span style={{ fontSize: '13.5px', color: '#1B2433', flex: 1 }}>{o.label}</span><span style={{ fontSize: '11.5px', color: '#9A9A9A' }}>{o.count}</span>
                    </button>
                  );
                })}
              </div>
            </div>
          ))}

          <button type="button" onClick={() => setFiltersOpen(false)} className="hfm-filterapply" style={{ display: 'none', width: '100%', minHeight: '50px', borderRadius: '999px', color: '#fff', background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))', border: '1px solid rgba(255,255,255,.42)', boxShadow: '0 10px 22px -12px rgba(140,198,63,.7)', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '15px', fontWeight: 600, cursor: 'pointer', marginTop: '4px', flex: 'none' }}>{t('seeProducts', { count })}</button>
        </aside>

        <div style={{ flex: '1 1 480px', minWidth: 0 }}>
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '20px', gap: '14px', flexWrap: 'wrap' }}>
            <div style={{ fontSize: '13px', color: '#6E7585' }} aria-live="polite">{t('resultsCount', { count })}</div>
            {sortButtons}
          </div>
          {count === 0 ? (
            <div style={{ padding: '60px 0', textAlign: 'center', color: '#8A8170', fontSize: '15px' }}>
              {t('noResults')}
              {activeCount ? (
                <div style={{ marginTop: '14px' }}>
                  <button type="button" onClick={() => setSel({})} style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14px', fontWeight: 600, color: '#5E8E1F', background: '#fff', border: '1px solid #CFE3B4', borderRadius: '999px', padding: '10px 20px', cursor: 'pointer' }}>{t('clearFilters')}</button>
                </div>
              ) : null}
            </div>
          ) : (
            <>
              <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill,minmax(210px,1fr))', gap: '18px' }}>
                {filtered.slice(0, visible).map((item) => <ProductCard key={item.id} product={item} />)}
              </div>
              <div ref={sentinelRef} aria-hidden="true" style={{ height: '1px' }} />
            </>
          )}
        </div>
      </div>
    </>
  );
}
