'use client';

import { useEffect, useState } from 'react';
import { useTranslations, useLocale } from 'next-intl';
import { idLangFor } from '@/lib/i18n-config';
import { useRouter } from 'next/navigation';
import { useStore } from '../store';
import { toCard, fmt, type Card } from '@/lib/cardModel';
import type { ProductCard } from '@/lib/ps';
import ProductCardComp from './ProductCard';

const QUICK_CHIPS = ['Juvéderm', 'Profhilo', 'Lèvres', 'Mésothérapie', 'Peelings'];
// Raccourcis vers les VRAIES catégories du méga-menu (typologies) — le catalogue redirige
// ?category={id} vers l'URL de la catégorie dans la langue courante.
const SEARCH_CATS = [
  { icon: '💧', labelKey: 'catFillers', id: 311 },
  { icon: '✦', labelKey: 'catSkinBoosters', id: 313 },
  { icon: '○', labelKey: 'catMeso', id: 314 },
  { icon: '◇', labelKey: 'catPeelings', id: 317 },
  { icon: '⋔', labelKey: 'catThreads', id: 316 },
];

export default function SearchOverlay() {
  const t = useTranslations('search');
  const tcat = useTranslations('catalogue');
  const tc = useTranslations('common');
  const { searchOpen, closeSearch } = useStore();
  const router = useRouter();
  const locale = useLocale();
  const idLang = idLangFor(locale);
  const [q, setQ] = useState('');
  const [pop, setPop] = useState<Card[]>([]);
  const [results, setResults] = useState<Card[]>([]);
  const [total, setTotal] = useState(0);

  // Suggestions « populaires » : 4 cartes, chargées une fois à l'ouverture.
  useEffect(() => {
    if (!searchOpen || pop.length) return;
    fetch(`/api/products?limit=4&id_lang=${idLang}`)
      .then((r) => r.json())
      .then((d) => setPop((d.products ?? []).map((p: ProductCard) => toCard(p))))
      .catch(() => {});
  }, [searchOpen, pop.length, idLang]);

  // Recherche CÔTÉ SERVEUR sur tout le catalogue actif (nom, marque, référence, EAN ; chaque mot,
  // accents ignorés), avec anti-rebond. Avant : filtre navigateur sur les 300 premiers produits
  // seulement -> ~280 produits introuvables (ex. Hyamira Forte).
  const query = q.trim();
  useEffect(() => {
    if (!searchOpen) return;
    if (!query) {
      setResults([]);
      setTotal(0);
      return;
    }
    const ctrl = new AbortController();
    const timer = setTimeout(() => {
      fetch(`/api/products?q=${encodeURIComponent(query)}&limit=8&id_lang=${idLang}`, { signal: ctrl.signal })
        .then((r) => r.json())
        .then((d) => {
          setResults((d.products ?? []).map((p: ProductCard) => toCard(p)));
          setTotal(Number(d.total ?? (d.products ?? []).length));
        })
        .catch(() => {});
    }, 220);
    return () => {
      clearTimeout(timer);
      ctrl.abort();
    };
  }, [query, searchOpen, idLang]);

  if (!searchOpen) return null;

  return (
    <div style={{ position: 'fixed', inset: 0, zIndex: 60, animation: 'fdOverlay .2s ease' }}>
      <div onClick={closeSearch} style={{ position: 'absolute', inset: 0, background: 'rgba(30,30,30,.5)' }} />
      <div style={{ position: 'absolute', top: 0, left: 0, right: 0, background: 'rgba(247,247,243,.86)', backdropFilter: 'blur(24px) saturate(150%)', WebkitBackdropFilter: 'blur(24px) saturate(150%)', maxHeight: '88vh', overflowY: 'auto', boxShadow: '0 30px 60px -20px rgba(30,40,15,.4)', borderBottom: '1px solid rgba(255,255,255,.6)' }}>
        <div className="hfm-wrap" style={{ maxWidth: '920px', margin: '0 auto', padding: '30px 28px 44px' }}>
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '16px' }}>
            <div style={{ fontSize: '11.5px', fontWeight: 700, letterSpacing: '.13em', textTransform: 'uppercase', color: '#5E8E1F' }}>{t('title')}</div>
            <button onClick={closeSearch} style={{ display: 'flex', alignItems: 'center', gap: '7px', background: '#fff', border: '1px solid #E7E3DA', borderRadius: '999px', padding: '7px 13px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '12px', fontWeight: 600, color: '#6E7585', cursor: 'pointer' }}>{t('close')}</button>
          </div>
          <div style={{ display: 'flex', alignItems: 'center', gap: '13px', background: '#fff', border: '1.5px solid #8CC63F', borderRadius: '999px', padding: '0 8px 0 22px', height: '62px', boxShadow: '0 14px 32px -18px rgba(140,198,63,.55)' }}>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#8CC63F" strokeWidth="2.2" strokeLinecap="round" style={{ flex: 'none' }}><circle cx="11" cy="11" r="7" /><path d="M21 21l-4.3-4.3" /></svg>
            <input autoFocus value={q} onChange={(e) => setQ(e.target.value)} placeholder={t('placeholder')} style={{ flex: 1, border: 'none', outline: 'none', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '16px', color: '#34352F', background: 'transparent' }} />
            <button onClick={closeSearch} style={{ flex: 'none', width: '42px', height: '42px', borderRadius: '50%', background: '#F2F0EA', border: 'none', fontSize: '18px', color: '#8A8170', cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>×</button>
          </div>
          {query ? (
            <>
              <div style={{ fontSize: '13px', color: '#6E7585', margin: '24px 4px 16px' }}>{t.rich('resultsCount', { count: total, query: q, b: (c) => <b style={{ color: '#34352F' }}>{c}</b> })}</div>
              <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit,minmax(200px,1fr))', gap: '18px' }}>
                {results.map((p) => <div key={p.id} onClick={closeSearch}><ProductCardComp product={p} /></div>)}
              </div>
              {total > results.length && (
                <div style={{ textAlign: 'center', marginTop: '22px' }}>
                  <button onClick={() => { closeSearch(); router.push(`/${locale}/catalogue?q=${encodeURIComponent(query)}`); }} style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14px', fontWeight: 600, color: '#5E8E1F', background: '#fff', border: '1px solid #CFE3B4', borderRadius: '999px', padding: '11px 22px', cursor: 'pointer' }}>{tcat('seeProducts', { count: total })} →</button>
                </div>
              )}
            </>
          ) : (
            <div className="hfm-searchgrid" style={{ display: 'grid', gridTemplateColumns: 'minmax(0,1fr) minmax(0,1fr)', gap: '30px', marginTop: '28px' }}>
              <div>
                <div style={{ fontSize: '11px', fontWeight: 700, letterSpacing: '.11em', textTransform: 'uppercase', color: '#8A8170', marginBottom: '13px' }}>{t('frequentSearches')}</div>
                <div style={{ display: 'flex', flexWrap: 'wrap', gap: '9px' }}>
                  {QUICK_CHIPS.map((c) => <span key={c} onClick={() => setQ(c)} style={{ cursor: 'pointer', display: 'inline-flex', alignItems: 'center', gap: '7px', fontSize: '13px', color: '#34352F', background: '#fff', border: '1px solid #E7E3DA', padding: '9px 15px', borderRadius: '999px', transition: 'border-color .2s ease, background .2s ease' }}><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#8CC63F" strokeWidth="2.4" strokeLinecap="round" style={{ flex: 'none' }}><circle cx="11" cy="11" r="7" /><path d="M21 21l-4.3-4.3" /></svg>{c}</span>)}
                </div>
                <div style={{ fontSize: '11px', fontWeight: 700, letterSpacing: '.11em', textTransform: 'uppercase', color: '#8A8170', margin: '26px 0 13px' }}>{t('categories')}</div>
                <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
                  {SEARCH_CATS.map((c) => <div key={c.labelKey} onClick={() => { closeSearch(); router.push(`/${locale}/catalogue?category=${c.id}`); }} style={{ cursor: 'pointer', display: 'flex', alignItems: 'center', gap: '12px', background: '#fff', border: '1px solid #ECEAE3', borderRadius: '11px', padding: '12px 15px', transition: 'border-color .2s ease, background .2s ease' }}><span style={{ width: '32px', height: '32px', flex: 'none', borderRadius: '8px', background: 'rgba(140,198,63,.14)', color: '#5E8E1F', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '15px' }}>{c.icon}</span><span style={{ fontFamily: "'Spectral',serif", fontSize: '15px', color: '#34352F', flex: 1 }}>{t(c.labelKey)}</span><span style={{ color: '#C9C2AF', fontSize: '16px' }}>›</span></div>)}
                </div>
              </div>
              <div>
                <div style={{ fontSize: '11px', fontWeight: 700, letterSpacing: '.11em', textTransform: 'uppercase', color: '#8A8170', marginBottom: '13px' }}>{t('popularProducts')}</div>
                <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
                  {pop.map((p) => <div key={p.id} onClick={() => { closeSearch(); router.push(`/produit/${p.id}`); }} style={{ cursor: 'pointer', display: 'flex', alignItems: 'center', gap: '13px', background: '#fff', border: '1px solid #ECEAE3', borderRadius: '11px', padding: '10px 13px', transition: 'border-color .2s ease' }}><div style={{ width: '46px', height: '46px', flex: 'none', borderRadius: '8px', overflow: 'hidden', background: '#F2F0EA' }}>{p.img ? (
                    // eslint-disable-next-line @next/next/no-img-element
                    <img src={p.img} alt="" loading="lazy" decoding="async" width={46} height={46} style={{ width: '46px', height: '46px', objectFit: 'cover', display: 'block' }} />
                  ) : null}</div><div style={{ flex: 1, minWidth: 0 }}>{p.brand ? <div style={{ fontSize: '10px', fontWeight: 600, letterSpacing: '.07em', textTransform: 'uppercase', color: '#8A8170' }}>{p.brand}</div> : null}<div style={{ fontFamily: "'Spectral',serif", fontSize: '14px', color: '#34352F', lineHeight: 1.25, marginTop: '2px', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{p.name}</div></div><span style={{ flex: 'none', textAlign: 'right', whiteSpace: 'nowrap' }}><span style={{ display: 'block', fontFamily: "'Hanken Grotesk',sans-serif", fontWeight: 700, fontSize: '13.5px', color: '#434343' }}>{fmt(p.ht)} € <span style={{ fontSize: '11px', fontWeight: 600, color: '#8A8170' }}>{tc('exclTax')}</span></span><span style={{ display: 'block', fontSize: '11px', color: '#9A9A9A', marginTop: '1px' }}>{tc('inclTaxShort', { amount: fmt(p.ttc) })}</span></span></div>)}
                </div>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
