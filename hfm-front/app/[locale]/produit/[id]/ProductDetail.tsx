'use client';

import { useState } from 'react';
import { useTranslations, useLocale } from 'next-intl';
import { Link } from '@/i18n/navigation';
import { useStore } from '../../../store';
import { fmt } from '@/lib/cardModel';
import RelatedSections, { type RelatedView } from './RelatedSections';

export type ProductView = {
  id: number;
  name: string;
  reference: string;
  ean13: string;
  brand: string | null;
  ht: number;
  ttc: number;
  // 'backorder' = épuisé mais commandable (précommande) ; 'out' = non commandable.
  availabilityState: 'in' | 'backorder' | 'out';
  descriptionShort: string;
  description: string;
  images: string[];
  features: { name: string; value: string }[];
  keyPoints: string[];
  faq: { q: string; a: string }[];
  composition: { k: string; v: string }[];
  rpps_required: boolean;
  reviews: ProductReviews | null;
};

export type ProductReviews = {
  rate: number; // /5
  rate10: number; // /10
  count: number;
  distribution: number[]; // [nb1, nb2, nb3, nb4, nb5]
  certificateUrl: string | null;
  items: {
    name: string;
    rate: number;
    review: string;
    date: string;
    orderDate: string | null;
    translated: boolean;
    sourceLang: string;
    answer: string | null;
    answerDate: string | null;
  }[];
};

type TabKey = 'description' | 'tech' | 'composition' | 'faq' | 'reviews';

// Les descriptions arrivent DÉJÀ sanitisées et structurées par le serveur
// (lib/sanitize.ts, appliqué dans page.tsx) : le client ne fait que rendre.

// Icônes filaires (cohérentes avec le reste du site).
const icons = {
  truck: <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><path d="M1 9h13v8H1zM14 12h4l3 3v2h-7z" /><circle cx="6" cy="19" r="1.6" /><circle cx="17.5" cy="19" r="1.6" /></svg>,
  box: <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><path d="M21 8l-9-5-9 5v8l9 5 9-5z" /><path d="M3 8l9 5 9-5M12 13v9" /></svg>,
  shield: <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6z" /><path d="M9 12l2 2 4-4" /></svg>,
};

export default function ProductDetail({ product, related }: { product: ProductView; related: RelatedView }) {
  const t = useTranslations('product');
  const tc = useTranslations('common');
  const { addToCart } = useStore();
  const [qty, setQty] = useState(1);
  const [tab, setTab] = useState<TabKey>('description');
  const [openFaq, setOpenFaq] = useState<number | null>(null);
  const [imgIdx, setImgIdx] = useState(0);
  const img = product.images[imgIdx] ?? product.images[0] ?? null;

  // Cartes « Caractéristiques principales » : 4 caractéristiques max, priorité aux
  // familles attendues (composition, indication, zones, conditionnement).
  const KEY_PATTERNS: [RegExp, string][] = [
    [/composition|concentration/i, '⚗'],
    [/indication|effet/i, '✎'],
    [/zone/i, '◎'],
    [/conditionnement|volume|packaging/i, '▤'],
  ];
  const keyCards: { label: string; value: string; icon: string }[] = [];
  const used = new Set<number>();
  for (const [re, icon] of KEY_PATTERNS) {
    const i = product.features.findIndex((f, idx) => !used.has(idx) && re.test(f.name));
    if (i >= 0) { used.add(i); keyCards.push({ label: product.features[i].name, value: product.features[i].value, icon }); }
  }
  for (let i = 0; i < product.features.length && keyCards.length < 4; i++) {
    if (!used.has(i)) { used.add(i); keyCards.push({ label: product.features[i].name, value: product.features[i].value, icon: '◈' }); }
  }

  const techRows: { k: string; v: string }[] = [
    { k: t('specReference'), v: product.reference || '—' },
    ...(product.ean13 ? [{ k: t('specEan'), v: product.ean13 }] : []),
    { k: t('specBrand'), v: product.brand || '—' },
    ...product.features.map((f) => ({ k: f.name, v: f.value })),
    { k: t('specMarking'), v: t('specMarkingValue') },
  ];

  // Contenus générés par l'IA uniquement : pas de FAQ/composition/points génériques
  // recyclés sur tous les produits — l'onglet ou la section disparaît si vide.
  const FAQ = product.faq;
  const points = product.keyPoints;
  const compositionRows = product.composition;

  const TABS: { key: TabKey; label: string }[] = [
    { key: 'description', label: t('tabDescription') },
    { key: 'tech', label: t('tabTechSheet') },
    ...(compositionRows.length ? [{ key: 'composition' as TabKey, label: t('tabComposition') }] : []),
    ...(FAQ.length ? [{ key: 'faq' as TabKey, label: t('tabFaq') }] : []),
    { key: 'reviews', label: `${t('tabReviews')} (${product.reviews?.count ?? 0})` },
  ];

  const canBuy = product.availabilityState !== 'out';
  const availability = product.availabilityState === 'in'
    ? <span style={{ display: 'inline-flex', alignItems: 'center', gap: '7px', fontSize: '13px', fontWeight: 600, color: '#3F7256' }}><span style={{ width: '7px', height: '7px', borderRadius: '50%', background: '#5FA33C' }} />{t('inStockShipToday')}</span>
    : product.availabilityState === 'backorder'
      ? <span style={{ display: 'inline-flex', alignItems: 'center', gap: '7px', fontSize: '13px', fontWeight: 600, color: '#B07B2A' }}><span style={{ width: '7px', height: '7px', borderRadius: '50%', background: '#D89B3D' }} />{t('onOrderDelay')}</span>
      : <span style={{ display: 'inline-flex', alignItems: 'center', gap: '7px', fontSize: '13px', fontWeight: 600, color: '#A8503A' }}><span style={{ width: '7px', height: '7px', borderRadius: '50%', background: '#C0664E' }} />{t('unavailableLine')}</span>;

  const card: React.CSSProperties = { background: '#fff', border: '1px solid #ECEAE3', borderRadius: '10px' };

  return (
    <main data-screen-label="Fiche produit" className="hfm-wrap" style={{ maxWidth: '1340px', margin: '0 auto', padding: '34px 28px 70px' }}>
      <div style={{ fontSize: '12.5px', color: '#9A9A9A', marginBottom: '24px' }}><Link href="/" style={{ cursor: 'pointer' }}>{tc('home')}</Link>  /  <Link href="/catalogue" style={{ cursor: 'pointer' }}>{tc('catalogue')}</Link>  /  {product.name}</div>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit,minmax(320px,1fr))', gap: '54px', alignItems: 'start' }}>
        {/* Galerie */}
        <div className="hfm-sticky" style={{ position: 'sticky', top: '130px' }}>
          <div style={{ display: 'block', width: '100%', aspectRatio: '1/1', borderRadius: '10px', overflow: 'hidden', background: 'repeating-linear-gradient(135deg,#F7F6F2,#F7F6F2 9px,#F1EFE8 9px,#F1EFE8 18px)', border: '1px solid #ECEAE3' }}>
            {img ? (
              // eslint-disable-next-line @next/next/no-img-element
              <img src={img} alt={product.name} fetchPriority="high" decoding="async" style={{ display: 'block', width: '100%', aspectRatio: '1/1', objectFit: 'cover', background: '#fff' }} />
            ) : null}
          </div>
          {product.images.length ? (
            <div style={{ display: 'flex', gap: '12px', marginTop: '12px', flexWrap: 'wrap' }}>
              {product.images.map((src, i) => (
                <button key={i} type="button" onClick={() => setImgIdx(i)} aria-label={`${product.name} — ${i + 1}`} style={{ padding: 0, width: '74px', height: '74px', borderRadius: '7px', overflow: 'hidden', cursor: 'pointer', background: '#F7F6F2', border: i === imgIdx ? '1.5px solid #8CC63F' : '1px solid #ECEAE3' }}>
                  {/* eslint-disable-next-line @next/next/no-img-element */}
                  <img src={src} alt="" loading="lazy" decoding="async" style={{ display: 'block', width: '100%', height: '100%', objectFit: 'cover', background: '#fff' }} />
                </button>
              ))}
            </div>
          ) : null}
        </div>

        {/* Colonne infos */}
        <div>
          {product.brand ? <div style={{ fontSize: '11.5px', fontWeight: 600, letterSpacing: '.1em', textTransform: 'uppercase', color: '#8A8170' }}>{product.brand}</div> : null}
          <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: '34px', lineHeight: 1.2, color: '#2B2B2B', margin: '8px 0 0' }}>{product.name}</h1>
          {product.reviews && product.reviews.count > 0 ? (
            <button type="button" onClick={() => setTab('reviews')} style={{ display: 'inline-flex', alignItems: 'center', gap: '8px', marginTop: '12px', background: 'none', border: 'none', padding: 0, cursor: 'pointer' }}>
              {/* eslint-disable-next-line @next/next/no-img-element */}
              <img src="/reviews/sag-cocarde.svg" alt="Société des Avis Garantis" width={11} height={21} style={{ display: 'block', flex: 'none' }} />
              <span style={{ display: 'inline-flex', gap: '1px' }} aria-label={`${product.reviews.rate}/5`}>
                {[1, 2, 3, 4, 5].map((i) => (
                  <svg key={i} width="16" height="16" viewBox="0 0 24 24" fill={i <= Math.round(product.reviews!.rate) ? '#f5c518' : '#E2DECF'} stroke="none"><path d="M12 3.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 17l-5.2 2.6 1-5.8L3.5 9.7l5.9-.9z" /></svg>
                ))}
              </span>
              <span style={{ fontSize: '14px', fontWeight: 700, color: '#1B2433' }}>{product.reviews.rate10}/10</span>
              <span style={{ fontSize: '13px', fontWeight: 600, color: '#5E8E1F', textDecoration: 'underline' }}>{t('reviewsCount', { count: product.reviews.count })}</span>
            </button>
          ) : null}
          {product.descriptionShort ? (
            <div style={{ fontSize: '15px', lineHeight: 1.6, color: '#55606F', margin: '14px 0 0' }} dangerouslySetInnerHTML={{ __html: product.descriptionShort }} />
          ) : null}

          {/* Panneau prix */}
          <div style={{ ...card, padding: '20px 22px', marginTop: '22px' }}>
            <div style={{ display: 'flex', alignItems: 'baseline', justifyContent: 'space-between', gap: '12px' }}>
              <span style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontWeight: 700, fontSize: '34px', color: '#2B2B2B' }}>{fmt(product.ttc)} €</span>
              <span style={{ fontSize: '12.5px', color: '#8A8170', fontWeight: 600 }}>{t('ttcPerUnit')}</span>
            </div>
            <div style={{ fontSize: '13.5px', color: '#6E7585', marginTop: '4px' }}>{t('htB2b', { amount: fmt(product.ht) })}</div>
            <div style={{ marginTop: '12px' }}>{availability}</div>
          </div>

          {product.availabilityState === 'in' ? (
            <div style={{ display: 'inline-flex', alignItems: 'center', gap: '9px', marginTop: '14px', padding: '9px 14px', background: 'rgba(140,198,63,.1)', border: '1px solid rgba(140,198,63,.28)', borderRadius: '999px', fontSize: '12.5px', fontWeight: 600, color: '#3F7256' }}>
              <span style={{ display: 'inline-flex', color: '#5E8E1F' }}>{icons.truck}</span>{t('deliveredTomorrow')}
            </div>
          ) : null}

          {product.rpps_required ? (
            <div style={{ display: 'flex', gap: '10px', alignItems: 'flex-start', marginTop: '16px', padding: '12px 14px', background: 'rgba(168,80,58,.08)', border: '1px solid rgba(168,80,58,.22)', borderRadius: '8px' }}>
              <span style={{ color: '#A8503A', fontSize: '15px', lineHeight: 1.3, flex: 'none' }} aria-hidden="true">⚕</span>
              <span style={{ fontSize: '13px', lineHeight: 1.5, color: '#A8503A', fontWeight: 500 }}>{t('rppsNotice')}</span>
            </div>
          ) : null}

          {/* Quantité + panier */}
          <div style={{ display: 'flex', alignItems: 'center', gap: '14px', marginTop: '22px' }}>
            <div style={{ display: 'flex', alignItems: 'center', border: '1.5px solid #E2DECF', borderRadius: '7px', overflow: 'hidden' }}><button onClick={() => setQty((q) => Math.max(1, q - 1))} style={{ width: '46px', height: '52px', background: '#fff', border: 'none', fontSize: '18px', color: '#434343', cursor: 'pointer' }}>−</button><span style={{ width: '46px', textAlign: 'center', fontSize: '15px', fontWeight: 600 }}>{qty}</span><button onClick={() => setQty((q) => q + 1)} style={{ width: '46px', height: '52px', background: '#fff', border: 'none', fontSize: '18px', color: '#434343', cursor: 'pointer' }}>+</button></div>
            {/* En stock ou précommande : achat (bouton vert identique, la ligne de
                disponibilité porte le délai). Indisponible : alerte retour. */}
            {canBuy ? (
              <button onClick={() => addToCart({ id: product.id, quantity: qty })} style={{ flex: 1, height: '52px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '15px', fontWeight: 600, color: '#fff', background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))', border: '1px solid rgba(255,255,255,.42)', boxShadow: '0 12px 26px -10px rgba(116,176,51,.55)', backdropFilter: 'blur(8px) saturate(140%)', WebkitBackdropFilter: 'blur(8px) saturate(140%)', borderRadius: '999px', cursor: 'pointer', transition: 'background .2s ease' }}>{tc('addToCart')}</button>
            ) : (
              <button disabled style={{ flex: 1, height: '52px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '15px', fontWeight: 600, color: '#6E7585', background: 'rgba(242,240,234,.7)', border: '1px solid rgba(226,222,207,.9)', borderRadius: '999px', cursor: 'default' }}>{t('notifyOnReturn')}</button>
            )}
          </div>

          {/* Tuiles réassurance */}
          <div style={{ ...card, display: 'grid', gridTemplateColumns: 'repeat(3,1fr)', marginTop: '22px', overflow: 'hidden' }}>
            {[
              { icon: icons.truck, title: t('tile1Title'), sub: t('tile1Sub') },
              { icon: icons.box, title: t('tile2Title'), sub: t('tile2Sub') },
              { icon: icons.shield, title: t('tile3Title'), sub: t('tile3Sub') },
            ].map((tile, i) => (
              <div key={i} style={{ padding: '16px 14px', textAlign: 'center', borderLeft: i ? '1px solid #F1EFE8' : 'none' }}>
                <span style={{ display: 'inline-flex', color: '#434343' }}>{tile.icon}</span>
                <div style={{ fontSize: '12.5px', fontWeight: 700, color: '#2B2B2B', marginTop: '7px' }}>{tile.title}</div>
                <div style={{ fontSize: '11.5px', color: '#8A8170', marginTop: '3px', lineHeight: 1.4 }}>{tile.sub}</div>
              </div>
            ))}
          </div>

          {/* Conformité */}
          <div style={{ ...card, padding: '16px 20px', marginTop: '14px', display: 'flex', flexDirection: 'column', gap: '10px' }}>
            {[t('comp1'), t('comp2'), t('comp3'), t('comp4'), t('comp5')].map((line, i) => (
              <div key={i} style={{ display: 'flex', gap: '10px', alignItems: 'flex-start', fontSize: '13px', color: '#3A3A36' }}>
                <span style={{ color: '#5E8E1F', flex: 'none', fontSize: '13px', lineHeight: 1.5 }}>✓</span>{line}
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Caractéristiques principales */}
      {keyCards.length ? (
        <section style={{ ...card, padding: '26px 30px', marginTop: '56px' }}>
          <div style={{ display: 'flex', alignItems: 'baseline', justifyContent: 'space-between', gap: '16px', flexWrap: 'wrap' }}>
            <h2 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: '24px', color: '#2B2B2B', margin: 0 }}>{t('keyFeaturesTitle')}</h2>
            <span style={{ fontSize: '11px', fontWeight: 700, letterSpacing: '.1em', textTransform: 'uppercase', color: '#9A9A9A' }}>{t('manufacturerData')}</span>
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit,minmax(220px,1fr))', gap: '14px', marginTop: '20px' }}>
            {keyCards.map((c, i) => (
              <div key={i} style={{ background: '#fff', border: '1px solid #ECEAE3', borderRadius: '10px', padding: '16px 18px' }}>
                <div style={{ fontSize: '11px', fontWeight: 700, letterSpacing: '.1em', textTransform: 'uppercase', color: '#8A8170', display: 'flex', alignItems: 'center', gap: '7px' }}><span aria-hidden="true">{c.icon}</span>{c.label}</div>
                <div style={{ fontSize: '13.5px', lineHeight: 1.55, color: '#2B2B2B', marginTop: '9px' }}>{c.value}</div>
              </div>
            ))}
          </div>
        </section>
      ) : null}

      {/* Points clés (uniquement si générés pour ce produit) */}
      {points.length ? (
        <section style={{ ...card, padding: '26px 30px', marginTop: '18px' }}>
          <h2 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: '24px', color: '#2B2B2B', margin: 0 }}>{t('keyPointsTitle')}</h2>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit,minmax(300px,1fr))', gap: '12px 40px', marginTop: '18px' }}>
            {points.map((p, i) => (
              <div key={i} style={{ display: 'flex', gap: '12px', alignItems: 'flex-start', fontSize: '14px', color: '#3A3A36', borderLeft: '3px solid rgba(140,198,63,.5)', paddingLeft: '14px', lineHeight: 1.5 }}>
                <span style={{ color: '#5E8E1F', flex: 'none' }}>✓</span>{p}
              </div>
            ))}
          </div>
        </section>
      ) : null}

      {/* Onglets */}
      <section style={{ marginTop: '40px' }}>
        <div style={{ display: 'flex', flexWrap: 'wrap', gap: '8px' }}>
          {TABS.map(({ key, label }) => (
            <button key={key} onClick={() => setTab(key)} style={{ flex: 'none', padding: '12px 20px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14px', fontWeight: 600, cursor: 'pointer', background: tab === key ? '#fff' : 'transparent', color: tab === key ? '#2B2B2B' : '#6E7585', borderTop: tab === key ? '1px solid #E7E3DA' : '1px solid transparent', borderLeft: tab === key ? '1px solid #E7E3DA' : '1px solid transparent', borderRight: tab === key ? '1px solid #E7E3DA' : '1px solid transparent', borderBottom: 'none', borderRadius: '8px 8px 0 0' }}>{label}</button>
          ))}
        </div>
        <div style={{ ...card, borderTopLeftRadius: 0, padding: '30px 34px' }}>
          {tab === 'description' ? (
            product.description
              ? <div className="hfm-richtext" style={{ fontSize: '15px', lineHeight: 1.7, color: '#3A3A36' }} dangerouslySetInnerHTML={{ __html: product.description }} />
              : <div style={{ color: '#8A8170', fontSize: '14px' }}>—</div>
          ) : null}

          {tab === 'tech' ? (
            <div style={{ border: '1px solid #F1EFE8', borderRadius: '8px', overflow: 'hidden' }}>
              {techRows.map((row, i) => (
                <div key={i} style={{ display: 'flex', gap: '20px', padding: '13px 18px', background: i % 2 ? '#FBFAF7' : '#fff', fontSize: '13.5px' }}>
                  <span style={{ flex: '0 0 220px', fontSize: '11.5px', fontWeight: 700, letterSpacing: '.08em', textTransform: 'uppercase', color: '#8A8170', paddingTop: '1px' }}>{row.k}</span>
                  <span style={{ color: '#1B2433', fontWeight: 500 }}>{row.v}</span>
                </div>
              ))}
            </div>
          ) : null}

          {tab === 'composition' ? (
            <div>
              <h3 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: '20px', color: '#2B2B2B', margin: '0 0 16px' }}>{t('compoTitle')}</h3>
              {compositionRows.map((row, i) => (
                <div key={i} style={{ display: 'flex', gap: '20px', padding: '12px 0', borderBottom: '1px solid #F1EFE8', fontSize: '14px' }}>
                  <span style={{ flex: '0 0 200px', fontWeight: 600, color: '#2B2B2B' }}>{row.k}</span>
                  <span style={{ color: '#55606F' }}>{row.v}</span>
                </div>
              ))}
              <p style={{ fontSize: '12.5px', color: '#8A8170', marginTop: '16px' }}>{t('compoDisclaimer')}</p>
            </div>
          ) : null}

          {tab === 'faq' ? (
            <div>
              <h3 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: '20px', color: '#2B2B2B', margin: 0 }}>{t('faqTitle')}</h3>
              <p style={{ fontSize: '13px', color: '#6E7585', margin: '8px 0 20px', lineHeight: 1.6 }}>{t('faqIntro')}</p>
              <div style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
                {FAQ.map((f, i) => (
                  <div key={i} style={{ border: '1px solid #ECEAE3', borderRadius: '10px', background: '#fff', overflow: 'hidden' }}>
                    <button onClick={() => setOpenFaq(openFaq === i ? null : i)} aria-expanded={openFaq === i} style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '14px', width: '100%', textAlign: 'left', padding: '15px 20px', background: 'transparent', border: 'none', cursor: 'pointer', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14.5px', fontWeight: 600, color: '#2B2B2B' }}>
                      {f.q}
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" style={{ flex: 'none', transition: 'transform .2s ease', transform: openFaq === i ? 'rotate(180deg)' : 'none', color: '#8A8170' }}><path d="M6 9l6 6 6-6" /></svg>
                    </button>
                    {openFaq === i ? (
                      <div style={{ padding: '0 20px 16px', fontSize: '14px', lineHeight: 1.65, color: '#55606F' }}>{f.a}</div>
                    ) : null}
                  </div>
                ))}
              </div>
            </div>
          ) : null}

          {tab === 'reviews' ? (
            product.reviews && product.reviews.count > 0 ? (
              <ReviewsBlock reviews={product.reviews} />
            ) : (
              <div style={{ textAlign: 'center', padding: '30px 20px', color: '#8A8170' }}>
                <div style={{ fontSize: '15px', fontWeight: 600, color: '#55606F' }}>{t('reviewsEmpty')}</div>
                <div style={{ fontSize: '13px', marginTop: '8px' }}>{t('reviewsEmptyHint')}</div>
              </div>
            )
          ) : null}
        </div>
      </section>

      {/* Cross-selling + encadré réglementaire */}
      <RelatedSections related={related} />
    </main>
  );
}

/** Étoiles pleines/vides sur 5. */
function Stars({ rate, size = 15 }: { rate: number; size?: number }) {
  return (
    <span style={{ display: 'inline-flex', gap: '1px' }} aria-label={`${rate}/5`}>
      {[1, 2, 3, 4, 5].map((i) => (
        <svg key={i} width={size} height={size} viewBox="0 0 24 24" fill={i <= rate ? '#f5c518' : '#E2DECF'} stroke="none">
          <path d="M12 3.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 17l-5.2 2.6 1-5.8L3.5 9.7l5.9-.9z" />
        </svg>
      ))}
    </span>
  );
}

/** Bloc d'avis « Société des Avis Garantis » : résumé (note /10 + distribution) + liste. */
function ReviewsBlock({ reviews }: { reviews: ProductReviews }) {
  const t = useTranslations('product');
  const locale = useLocale();
  const fmtDate = (raw: string | null) => {
    if (!raw) return '';
    const d = new Date(raw.replace(' ', 'T'));
    if (isNaN(d.getTime())) return '';
    try {
      return new Intl.DateTimeFormat(locale, { day: '2-digit', month: '2-digit', year: 'numeric' }).format(d);
    } catch {
      return new Intl.DateTimeFormat('fr', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(d);
    }
  };
  const max = Math.max(1, ...reviews.distribution);

  return (
    <div>
      <div style={{ display: 'flex', flexWrap: 'wrap', gap: '24px', alignItems: 'center', justifyContent: 'space-between', padding: '22px', background: '#F7F6F2', border: '1px solid #ECEAE3', borderRadius: '12px', marginBottom: '26px' }}>
        <div style={{ minWidth: '160px' }}>
          {/* eslint-disable-next-line @next/next/no-img-element */}
          <img src="/reviews/sag-logo.png" alt="Société des Avis Garantis" width={72} height={65} style={{ display: 'block', marginBottom: '10px' }} />
          {reviews.certificateUrl ? (
            <a href={reviews.certificateUrl} target="_blank" rel="noopener noreferrer" style={{ fontSize: '12.5px', color: '#5E8E1F', fontWeight: 600, textDecoration: 'none' }}>{t('reviewsCertificate')} →</a>
          ) : null}
        </div>
        <div style={{ flex: '1 1 220px', maxWidth: '320px' }}>
          {[5, 4, 3, 2, 1].map((star) => {
            const n = reviews.distribution[star - 1] ?? 0;
            return (
              <div key={star} style={{ display: 'flex', alignItems: 'center', gap: '9px', margin: '3px 0' }}>
                <span style={{ fontSize: '12px', color: '#8A8170', width: '26px' }}>{star}★</span>
                <span style={{ flex: 1, height: '7px', borderRadius: '999px', background: '#E7E3DA', overflow: 'hidden' }}>
                  <span style={{ display: 'block', height: '100%', width: `${(n / max) * 100}%`, background: '#f5c518' }} />
                </span>
                <span style={{ fontSize: '12px', color: '#8A8170', width: '20px', textAlign: 'right' }}>{n}</span>
              </div>
            );
          })}
        </div>
        <div style={{ textAlign: 'center', minWidth: '110px' }}>
          <div style={{ fontSize: '36px', fontWeight: 800, color: '#1B2433', lineHeight: 1 }}>{reviews.rate10}<span style={{ fontSize: '16px', color: '#8A8170', fontWeight: 600 }}>/10</span></div>
          <div style={{ marginTop: '6px' }}><Stars rate={Math.round(reviews.rate)} /></div>
          <div style={{ fontSize: '12.5px', color: '#6E7062', marginTop: '5px' }}>{t('reviewsBasedOn', { count: reviews.count })}</div>
        </div>
      </div>

      <div style={{ display: 'flex', flexDirection: 'column' }}>
        {reviews.items.map((r, i) => (
          <div key={i} style={{ padding: '18px 4px', borderTop: i ? '1px solid #F0EEE7' : 'none' }}>
            <div style={{ display: 'flex', flexWrap: 'wrap', alignItems: 'center', justifyContent: 'space-between', gap: '10px' }}>
              <div style={{ fontSize: '14px', fontWeight: 700, color: '#1B2433' }}>{r.name}</div>
              <Stars rate={r.rate} />
            </div>
            <div style={{ fontSize: '11.5px', color: '#8A8170', margin: '3px 0 8px' }}>
              {r.date ? `${t('reviewsPublishedOn')} ${fmtDate(r.date)}` : ''}
              {r.orderDate ? ` · ${t('reviewsOrderedOn')} ${fmtDate(r.orderDate)}` : ''}
            </div>
            <div style={{ fontSize: '14px', color: '#3A3A36', lineHeight: 1.55 }}>
              {r.review}
              {r.translated ? <span style={{ fontStyle: 'italic', color: '#9A9A9A', marginLeft: '6px' }}>({t('reviewsTranslated')})</span> : null}
            </div>
            {r.answer ? (
              <div style={{ marginTop: '10px', padding: '11px 14px', background: '#F7F6F2', borderRadius: '9px', fontSize: '13px', color: '#55606F' }}>
                <b style={{ color: '#1B2433' }}>{t('reviewsReply')} :</b> {r.answer}
              </div>
            ) : null}
          </div>
        ))}
      </div>
    </div>
  );
}
