import { setRequestLocale, getTranslations } from 'next-intl/server';
import { bridgeGetCached, type ProductCard } from '@/lib/ps';
import { CACHE_TAGS, CACHE_TTL } from '@/lib/cacheContract';
import { toCard, type Card } from '@/lib/cardModel';
import { idLangFor } from '@/lib/i18n-config';
import Chrome from '../components/Chrome';
import Footer from '../components/Footer';
import HomeTabs from '../components/HomeTabs';
import DragCarousel from '../components/DragCarousel';

const MARQUEE = ['Juvéderm', 'Restylane', 'Teoxane', 'Vivacy', 'Belotero', 'Radiesse', 'Profhilo', 'Revolax', 'Neauvia', 'Fillmed', 'Croma', 'Sinclair', 'Juvéderm', 'Restylane', 'Teoxane', 'Vivacy', 'Belotero', 'Radiesse', 'Profhilo', 'Revolax', 'Neauvia', 'Fillmed', 'Croma', 'Sinclair'];

const ZONES = [
  { labelKey: 'zoneLevres', count: 35, key: 'levres' },
  { labelKey: 'zonePommettes', count: 21, key: 'pommettes' },
  { labelKey: 'zoneCernes', count: 25, key: 'cernes' },
  { labelKey: 'zoneRides', count: 88, key: 'rides' },
  { labelKey: 'zoneOvale', count: 17, key: 'ovale' },
  { labelKey: 'zoneSkinbooster', count: 54, key: 'skinbooster' },
] as const;

const REVIEW_KEYS = [1, 2, 3] as const;

const BLOG_KEYS = [1, 2, 3] as const;

const greenBtn: React.CSSProperties = {
  fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14.5px', fontWeight: 600, color: '#fff',
  background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))',
  border: '1px solid rgba(255,255,255,.42)', boxShadow: '0 12px 26px -10px rgba(116,176,51,.55)',
  backdropFilter: 'blur(8px) saturate(140%)', WebkitBackdropFilter: 'blur(8px) saturate(140%)',
  borderRadius: '999px', padding: '15px 28px', cursor: 'pointer', transition: 'background .2s ease',
  display: 'inline-block', textDecoration: 'none',
};

function imgSlot(src: string, href: string, label: string, sub?: string) {
  return (
    <a href={href} aria-label={label} style={{ display: 'block', position: 'relative', width: '100%', height: '100%', minHeight: '120px', background: '#F7F6F2', textDecoration: 'none' }}>
      <img src={src} alt={label} style={{ position: 'absolute', inset: 0, width: '100%', height: '100%', objectFit: 'cover' }} />
      <div style={{ position: 'absolute', left: 0, right: 0, bottom: 0, padding: sub ? '20px' : '14px', pointerEvents: 'none', background: 'linear-gradient(0deg,rgba(25,25,25,.82),rgba(25,25,25,0))' }}>
        {sub ? <div style={{ fontSize: '10.5px', fontWeight: 700, letterSpacing: '.12em', textTransform: 'uppercase', color: '#CDE8A6' }}>{sub}</div> : null}
        <div style={{ fontFamily: "'Spectral',serif", fontSize: sub ? '22px' : '17px', color: '#fff', marginTop: sub ? '4px' : 0 }}>{label}</div>
      </div>
    </a>
  );
}

export default async function Home({ params }: { params: Promise<{ locale: string }> }) {
  const { locale } = await params;
  setRequestLocale(locale);
  const t = await getTranslations('home');
  const tc = await getTranslations('common');

  const data = await bridgeGetCached(
    'products',
    { limit: 24, id_lang: idLangFor(locale) },
    { ttl: CACHE_TTL.products, tags: [CACHE_TAGS.products] },
  );
  const live: ProductCard[] = data.products ?? [];
  const cards: Card[] = live.map(toCard);

  // Onglets : faute de métadonnées best/nouveau/promo en live, on répartit simplement la liste.
  const tabs = {
    best: cards.slice(0, 8),
    nouveautes: cards.slice(8, 16).length ? cards.slice(8, 16) : cards.slice(0, 8),
    promos: cards.slice(16, 24).length ? cards.slice(16, 24) : cards.slice(0, 8),
  };

  const TRUST = [
    { el: <svg width={19} height={19} viewBox="0 0 24 24" fill="none" stroke="#5E8E1F" strokeWidth={1.8} strokeLinecap="round" strokeLinejoin="round"><path d="M13 2L4 14h7l-1 8 9-12h-7z" /></svg>, title: t('trustShipTitle'), sub: t('trustShipSub') },
    { el: <svg width={19} height={19} viewBox="0 0 24 24" fill="none" stroke="#5E8E1F" strokeWidth={1.8} strokeLinecap="round" strokeLinejoin="round"><path d="M12 3l8 3.5v5c0 4.8-3.4 7.8-8 9-4.6-1.2-8-4.2-8-9v-5z" /><path d="M9 12l2 2 4-4" /></svg>, title: t('trustCeTitle'), sub: t('trustCeSub') },
    { el: <svg width={19} height={19} viewBox="0 0 24 24" fill="#5E8E1F" stroke="#5E8E1F" strokeWidth={1.2} strokeLinejoin="round"><path d="M12 3.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 17l-5.2 2.6 1-5.8L3.5 9.7l5.9-.9z" /></svg>, title: t('trustReviewsTitle'), sub: t('trustReviewsSub') },
    { el: <svg width={19} height={19} viewBox="0 0 24 24" fill="none" stroke="#5E8E1F" strokeWidth={1.8} strokeLinecap="round" strokeLinejoin="round"><path d="M4 13v-1a8 8 0 0 1 16 0v1" /><path d="M4 13a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2 1 1 0 0 1-1-1v-4a1 1 0 0 1 1-1z" /><path d="M20 13a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2 1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1z" /><path d="M18 19a4 4 0 0 1-4 3h-2" /></svg>, title: t('trustSupportTitle'), sub: t('trustSupportSub') },
  ];

  return (
    <div style={{ fontFamily: "'Hanken Grotesk',sans-serif", color: '#34352F', background: 'radial-gradient(1100px 560px at 82% -6%, rgba(140,198,63,0.13), transparent 58%), radial-gradient(820px 520px at -8% 14%, rgba(95,184,154,0.10), transparent 55%), radial-gradient(700px 600px at 50% 118%, rgba(140,198,63,0.08), transparent 60%), #F3F4EF', minHeight: '100vh' }}>
      <Chrome />
      <main>
        {/* HERO A : éditorial split */}
        <section style={{ background: 'linear-gradient(180deg,#FFFFFF 0%,#F7F6F2 100%)', borderBottom: '1px solid #E7E3DA' }}>
          <div className="hfm-wrap" style={{ maxWidth: '1340px', margin: '0 auto', padding: '62px 28px 70px', display: 'grid', gridTemplateColumns: 'repeat(auto-fit,minmax(330px,1fr))', gap: '60px', alignItems: 'center' }}>
            <div>
              <div style={{ display: 'inline-flex', alignItems: 'center', gap: '9px', padding: '7px 14px', background: 'rgba(255,255,255,.45)', backdropFilter: 'blur(10px) saturate(150%)', WebkitBackdropFilter: 'blur(10px) saturate(150%)', border: '1px solid rgba(255,255,255,.7)', boxShadow: '0 6px 20px -12px rgba(40,50,25,.4)', borderRadius: '999px', fontSize: '11.5px', fontWeight: 600, letterSpacing: '.07em', textTransform: 'uppercase', color: '#3F5E1C' }}>
                <span style={{ width: '6px', height: '6px', borderRadius: '50%', background: '#8CC63F' }} />{t('badge')}
              </div>
              <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: 'clamp(36px,6.2vw,60px)', lineHeight: 1.05, letterSpacing: '-.02em', color: '#2B2B2B', margin: '22px 0 0' }}>{t('heroTitle1')}<br /><span style={{ fontStyle: 'italic', color: '#434343' }}>{t('heroTitle2')}</span></h1>
              <p style={{ fontSize: '17px', lineHeight: 1.6, color: '#55606F', maxWidth: '480px', margin: '22px 0 0' }}>{t('heroLead')}</p>
              <div style={{ display: 'flex', gap: '14px', marginTop: '30px', flexWrap: 'wrap' }}>
                <a href="/catalogue" style={greenBtn}>{tc('discoverCatalogue')}</a>
                <a href="/catalogue" style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14.5px', fontWeight: 600, color: '#5E8E1F', background: 'rgba(140,198,63,0.08)', backdropFilter: 'blur(6px)', WebkitBackdropFilter: 'blur(6px)', border: '1px solid rgba(155,209,89,.7)', borderRadius: '999px', padding: '15px 28px', cursor: 'pointer', textDecoration: 'none' }}>{t('ctaPractitionerRates')}</a>
              </div>
              <div style={{ display: 'flex', alignItems: 'center', gap: '26px', marginTop: '34px', flexWrap: 'wrap' }}>
                <div style={{ display: 'flex', flexDirection: 'column', gap: '3px' }}>
                  <span style={{ color: '#8CC63F', fontSize: '14px', letterSpacing: '1px' }}>★★★★★</span>
                  <span style={{ fontSize: '12.5px', color: '#6E7585' }}><b style={{ color: '#1B2433' }}>4,8/5</b>{t('reviewsLine').replace(/^4,8\/5\s*/, ' ')}</span>
                </div>
                <div style={{ width: '1px', height: '34px', background: '#E2DECF' }} />
                <div style={{ display: 'flex', flexDirection: 'column', gap: '2px' }}>
                  <span style={{ fontFamily: "'Spectral',serif", fontSize: '21px', color: '#434343' }}>{t('brandsLine')}</span>
                  <span style={{ fontSize: '12.5px', color: '#6E7585' }}>{t('brandsSub')}</span>
                </div>
              </div>
            </div>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gridTemplateRows: '1fr 1fr', gap: '14px', height: '480px' }}>
              <div style={{ gridRow: 'span 2', position: 'relative', borderRadius: '8px', overflow: 'hidden', border: '1px solid #E2DECF' }}>{imgSlot('/hero/hero-1.png', '/produit/6409', t('heroImgTop'), t('heroImgTopSub'))}</div>
              <div style={{ position: 'relative', borderRadius: '8px', overflow: 'hidden', border: '1px solid #E2DECF' }}>{imgSlot('/hero/hero-2.png', '/produit/6408', t('heroImgLips'))}</div>
              <div style={{ position: 'relative', borderRadius: '8px', overflow: 'hidden', border: '1px solid #E2DECF' }}>{imgSlot('/hero/hero-3.png', '/produit/449', t('heroImgSkin'))}</div>
            </div>
          </div>
        </section>

        {/* TRUST STRIP */}
        <section style={{ background: 'rgba(255,255,255,.55)', backdropFilter: 'blur(14px) saturate(140%)', WebkitBackdropFilter: 'blur(14px) saturate(140%)', borderTop: '1px solid rgba(255,255,255,.6)', borderBottom: '1px solid rgba(231,227,218,.7)' }}>
          <div className="hfm-wrap" style={{ maxWidth: '1340px', margin: '0 auto', padding: '24px 28px', display: 'grid', gridTemplateColumns: 'repeat(auto-fit,minmax(200px,1fr))', gap: '28px' }}>
            {TRUST.map((t, i) => (
              <div key={i} style={{ display: 'flex', gap: '13px', alignItems: 'flex-start' }}>
                <span style={{ flex: 'none', width: '34px', height: '34px', display: 'flex', alignItems: 'center', justifyContent: 'center', background: 'rgba(140,198,63,.12)', borderRadius: '9px' }}>{t.el}</span>
                <div><div style={{ fontSize: '13.5px', fontWeight: 600, color: '#1B2433' }}>{t.title}</div><div style={{ fontSize: '12px', color: '#7A8290', marginTop: '2px' }}>{t.sub}</div></div>
              </div>
            ))}
          </div>
        </section>

        {/* BRAND MARQUEE */}
        <section style={{ background: '#F7F6F2', borderBottom: '1px solid #E7E3DA', padding: '22px 0', overflow: 'hidden' }}>
          <div style={{ display: 'flex', width: 'max-content', gap: '54px', animation: 'fdMarquee 34s linear infinite', paddingLeft: '54px', alignItems: 'center' }}>
            {MARQUEE.map((b, i) => <span key={i} style={{ fontFamily: "'Spectral',serif", fontSize: '21px', fontWeight: 500, letterSpacing: '.04em', color: '#A8A293', whiteSpace: 'nowrap' }}>{b}</span>)}
          </div>
        </section>

        {/* PRODUCT TABS (live) */}
        <HomeTabs tabs={tabs} />

        {/* SHOP BY ZONE */}
        <section className="hfm-wrap" style={{ maxWidth: '1340px', margin: '0 auto', padding: '60px 28px 8px' }}>
          <div style={{ display: 'flex', alignItems: 'flex-end', justifyContent: 'space-between', gap: '20px' }}>
            <div><div style={{ fontSize: '11.5px', fontWeight: 700, letterSpacing: '.13em', textTransform: 'uppercase', color: '#8CC63F' }}>{t('zonesEyebrow')}</div><h2 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: 'clamp(25px,3.6vw,34px)', color: '#2B2B2B', margin: '8px 0 0' }}>{t('zonesTitle')}</h2></div>
            <div style={{ display: 'flex', alignItems: 'center', gap: '14px' }}>
              <a href="/catalogue" style={{ cursor: 'pointer', fontSize: '13.5px', fontWeight: 600, color: '#434343', textDecoration: 'none' }}>{t('allZones')}</a>
            </div>
          </div>
          <DragCarousel className="hfm-carousel" style={{ display: 'flex', gap: '16px', marginTop: '28px', overflowX: 'auto', padding: '4px 2px 16px' }}>
            {ZONES.map((z) => (
              <a key={z.key} href={`/zone/${z.key}`} style={{ flex: 'none', width: '236px', cursor: 'pointer', background: '#fff', border: '1px solid #ECEAE3', borderRadius: '14px', overflow: 'hidden', boxShadow: '0 14px 34px -28px rgba(40,50,25,.5)', textDecoration: 'none' }}>
                <div style={{ position: 'relative', height: '148px', overflow: 'hidden', background: '#F7F6F2' }}><img src={`/zones/${z.key}.png`} alt={t(z.labelKey)} style={{ position: 'absolute', inset: 0, width: '100%', height: '100%', objectFit: 'cover' }} /></div>
                <div style={{ padding: '15px 17px 17px' }}><div style={{ fontFamily: "'Spectral',serif", fontSize: '17px', color: '#2B2B2B' }}>{t(z.labelKey)}</div><div style={{ fontSize: '11.5px', color: '#9A9A9A', marginTop: '3px' }}>{t('zoneRefs', { count: z.count })}</div></div>
              </a>
            ))}
          </DragCarousel>
        </section>

        {/* EDITORIAL / LIFESTYLE BAND */}
        <section className="hfm-wrap" style={{ maxWidth: '1340px', margin: '0 auto', padding: '64px 28px 0' }}>
          <div className="hfm-edito" style={{ display: 'grid', gridTemplateColumns: '1fr 1.05fr', gap: '54px', alignItems: 'center' }}>
            <div>
              <div style={{ display: 'inline-flex', alignItems: 'center', gap: '8px', fontSize: '11px', fontWeight: 700, letterSpacing: '.13em', textTransform: 'uppercase', color: '#5E8E1F' }}><span style={{ width: '6px', height: '6px', borderRadius: '50%', background: '#8CC63F' }} />{t('editoEyebrow')}</div>
              <h2 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: 'clamp(28px,3.4vw,40px)', lineHeight: 1.1, letterSpacing: '-.01em', color: '#2B2B2B', margin: '14px 0 0' }}>{t('editoTitlePart1')} <span style={{ fontStyle: 'italic', color: '#5E8E1F' }}>{t('editoTitleEmphasis')}</span> {t('editoTitlePart2')}</h2>
              <p style={{ fontSize: '16px', lineHeight: 1.65, color: '#55606F', maxWidth: '480px', margin: '18px 0 0' }}>{t('editoLead')}</p>
              <div style={{ display: 'flex', flexDirection: 'column', gap: '14px', marginTop: '26px' }}>
                <div style={{ display: 'flex', gap: '13px', alignItems: 'flex-start' }}><span style={{ flex: 'none', width: '30px', height: '30px', borderRadius: '8px', background: 'rgba(140,198,63,.14)', color: '#5E8E1F', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '15px' }}>✓</span><div><div style={{ fontSize: '14.5px', fontWeight: 600, color: '#2B2B2B' }}>{t('editoFeat1Title')}</div><div style={{ fontSize: '13px', color: '#7A8290' }}>{t('editoFeat1Sub')}</div></div></div>
                <div style={{ display: 'flex', gap: '13px', alignItems: 'flex-start' }}><span style={{ flex: 'none', width: '30px', height: '30px', borderRadius: '8px', background: 'rgba(140,198,63,.14)', color: '#5E8E1F', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '15px' }}>✓</span><div><div style={{ fontSize: '14.5px', fontWeight: 600, color: '#2B2B2B' }}>{t('editoFeat2Title')}</div><div style={{ fontSize: '13px', color: '#7A8290' }}>{t('editoFeat2Sub')}</div></div></div>
              </div>
              <a href="/catalogue" style={{ ...greenBtn, marginTop: '28px' }}>{t('editoCta')}</a>
            </div>
            <div style={{ position: 'relative' }}>
              {/* Export Figma avec coins arrondis + ombre portée intégrés : rendu tel quel. */}
              <img src="/edito/cabinet.png" alt="" style={{ display: 'block', width: '100%', height: 'auto' }} />
              <div className="hfm-badge-bl" style={{ position: 'absolute', left: '-22px', bottom: '-22px', background: 'rgba(255,255,255,.65)', backdropFilter: 'blur(16px) saturate(150%)', WebkitBackdropFilter: 'blur(16px) saturate(150%)', border: '1px solid rgba(255,255,255,.7)', boxShadow: '0 22px 44px -26px rgba(40,50,25,.5)', borderRadius: '16px', padding: '18px 22px' }}>
                <div style={{ display: 'flex', alignItems: 'baseline', gap: '8px' }}><span style={{ fontFamily: "'Spectral',serif", fontSize: '30px', color: '#2B2B2B' }}>{t('editoBadgeDelay')}</span></div>
                <div style={{ fontSize: '12.5px', color: '#55606F', marginTop: '2px' }}>{t('editoBadgeDelaySub')}</div>
              </div>
              <div className="hfm-badge-tr" style={{ position: 'absolute', right: '-16px', top: '24px', background: 'rgba(255,255,255,.65)', backdropFilter: 'blur(16px) saturate(150%)', WebkitBackdropFilter: 'blur(16px) saturate(150%)', border: '1px solid rgba(255,255,255,.7)', boxShadow: '0 22px 44px -26px rgba(40,50,25,.5)', borderRadius: '14px', padding: '13px 16px', display: 'flex', alignItems: 'center', gap: '10px' }}>
                <span style={{ color: '#8CC63F', fontSize: '13px', letterSpacing: '1px' }}>★★★★★</span><span style={{ fontSize: '12.5px', color: '#2B2B2B', fontWeight: 600 }}>4,8/5</span>
              </div>
            </div>
          </div>
        </section>

        {/* REVIEWS */}
        <section style={{ background: '#fff', borderTop: '1px solid #E7E3DA', borderBottom: '1px solid #E7E3DA', marginTop: '64px' }}>
          <div className="hfm-wrap" style={{ maxWidth: '1340px', margin: '0 auto', padding: '56px 28px' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '16px' }}><span style={{ color: '#8CC63F', fontSize: '18px', letterSpacing: '2px' }}>★★★★★</span><span style={{ fontSize: '14px', color: '#1B2433' }}>{t.rich('reviewsHeader', { b: (c) => <b>{c}</b>, span: (c) => <span style={{ color: '#9A9A9A' }}>{c}</span> })}</span></div>
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit,minmax(260px,1fr))', gap: '18px', marginTop: '28px' }}>
              {REVIEW_KEYS.map((i) => (
                <div key={i} style={{ border: '1px solid #ECEAE3', borderRadius: '8px', padding: '24px' }}><span style={{ color: '#8CC63F', fontSize: '13px', letterSpacing: '1px' }}>★★★★★</span><p style={{ fontFamily: "'Spectral',serif", fontSize: '16px', lineHeight: 1.5, color: '#2A3447', margin: '14px 0 16px' }}>« {t(`review${i}Text`)} »</p><div style={{ fontSize: '12.5px', color: '#6E7585' }}><b style={{ color: '#1B2433' }}>{t(`review${i}Name`)}</b> · {t(`review${i}Role`)}</div></div>
              ))}
            </div>
          </div>
        </section>

        {/* EXPERTISE / BLOG */}
        <section className="hfm-wrap" style={{ maxWidth: '1340px', margin: '0 auto', padding: '64px 28px 8px' }}>
          <div style={{ display: 'flex', alignItems: 'flex-end', justifyContent: 'space-between', gap: '20px' }}>
            <div><div style={{ fontSize: '11.5px', fontWeight: 700, letterSpacing: '.13em', textTransform: 'uppercase', color: '#8CC63F' }}>{t('expertiseEyebrow')}</div><h2 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: 'clamp(25px,3.6vw,34px)', color: '#2B2B2B', margin: '8px 0 0' }}>{t('expertiseTitle')}</h2></div>
            <span style={{ cursor: 'pointer', fontSize: '13.5px', fontWeight: 600, color: '#434343' }}>{t('allBlog')}</span>
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit,minmax(260px,1fr))', gap: '18px', marginTop: '28px' }}>
            {BLOG_KEYS.map((i) => (
              <div key={i} style={{ cursor: 'pointer', background: '#fff', border: '1px solid #ECEAE3', borderRadius: '8px', overflow: 'hidden' }}>
                <div style={{ aspectRatio: '16/9', overflow: 'hidden', background: '#F7F6F2' }}><img src={`/blog/blog${i}.png`} alt={t(`blog${i}Title`)} style={{ display: 'block', width: '100%', height: '100%', objectFit: 'cover' }} /></div>
                <div style={{ padding: '20px' }}><div style={{ display: 'flex', gap: '10px', fontSize: '11px', color: '#8A8170', textTransform: 'uppercase', letterSpacing: '.06em' }}><span style={{ color: '#8CC63F', fontWeight: 600 }}>{t(`blog${i}Cat`)}</span><span>·</span><span>{t(`blog${i}Date`)}</span></div><div style={{ fontFamily: "'Spectral',serif", fontSize: '18px', lineHeight: 1.3, color: '#1B2433', marginTop: '10px' }}>{t(`blog${i}Title`)}</div><div style={{ fontSize: '13px', fontWeight: 600, color: '#434343', marginTop: '14px' }}>{t('readArticle')}</div></div>
              </div>
            ))}
          </div>
        </section>

        {/* NEWSLETTER */}
        <section className="hfm-wrap" style={{ maxWidth: '1340px', margin: '0 auto', padding: '64px 28px' }}>
          <div className="hfm-banner hfm-news" style={{ position: 'relative', overflow: 'hidden', background: 'radial-gradient(620px 360px at 12% -25%, rgba(140,198,63,0.28), transparent 60%), radial-gradient(560px 400px at 92% 130%, rgba(95,184,154,0.20), transparent 60%), linear-gradient(135deg,#1E2914 0%,#2A3A1C 52%,#36481E 100%)', border: '1px solid rgba(255,255,255,.08)', boxShadow: '0 30px 60px -34px rgba(30,45,15,.7)', borderRadius: '18px', padding: '56px', textAlign: 'center' }}>
            <div style={{ display: 'inline-flex', alignItems: 'center', gap: '8px', padding: '7px 14px', background: 'rgba(140,198,63,.16)', border: '1px solid rgba(183,228,134,.3)', borderRadius: '999px', fontSize: '11px', fontWeight: 700, letterSpacing: '.13em', textTransform: 'uppercase', color: '#B7E486' }}>{t('newsBadge')}</div>
            <h2 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: 'clamp(26px,3.2vw,34px)', color: '#fff', margin: '16px 0 0' }}>{t('newsTitlePart1')} <span style={{ fontStyle: 'italic', color: '#B7E486' }}>{t('newsTitleEmphasis')}</span></h2>
            <p style={{ fontSize: '14.5px', color: '#CBD8BC', margin: '12px 0 0' }}>{t('newsLead')}</p>
            <div style={{ display: 'flex', gap: '10px', maxWidth: '480px', margin: '28px auto 0', background: 'rgba(255,255,255,.10)', border: '1px solid rgba(255,255,255,.16)', borderRadius: '999px', padding: '6px', backdropFilter: 'blur(10px)', WebkitBackdropFilter: 'blur(10px)' }}><input placeholder={t('newsPlaceholder')} style={{ flex: 1, height: '46px', padding: '0 20px', border: 'none', borderRadius: '999px', background: 'transparent', color: '#fff', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14px', outline: 'none' }} /><button style={{ color: '#fff', background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))', border: '1px solid rgba(255,255,255,.42)', boxShadow: '0 12px 24px -10px rgba(140,198,63,.6)', backdropFilter: 'blur(8px) saturate(140%)', WebkitBackdropFilter: 'blur(8px) saturate(140%)', borderRadius: '999px', padding: '0 28px', height: '46px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14px', fontWeight: 600, cursor: 'pointer' }}>{t('newsSubmit')}</button></div>
            <div style={{ fontSize: '11.5px', color: '#94A580', marginTop: '14px', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '7px' }}>
              <svg width={12} height={12} viewBox="0 0 24 24" fill="none" stroke="#94A580" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2" /><path d="M8 11V7a4 4 0 0 1 8 0v4" /></svg>
              <span>{t('newsGdpr')}</span>
            </div>
          </div>
        </section>
      </main>
      <Footer />
    </div>
  );
}
