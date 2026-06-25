'use client';

import { useRef, useState } from 'react';
import { useTranslations } from 'next-intl';
import type { Card } from '@/lib/cardModel';
import ProductCard from './ProductCard';

type Tabs = { best: Card[]; nouveautes: Card[]; promos: Card[] };

const navBtn: React.CSSProperties = {
  width: '40px', height: '40px', borderRadius: '50%', background: '#fff',
  border: '1px solid #E2DECF', color: '#434343', fontSize: '17px', cursor: 'pointer',
  display: 'flex', alignItems: 'center', justifyContent: 'center',
  transition: 'border-color .2s ease, background .2s ease',
};

export default function HomeTabs({ tabs }: { tabs: Tabs }) {
  const t = useTranslations('home');
  const [active, setActive] = useState<keyof Tabs>('best');
  const ref = useRef<HTMLDivElement>(null);

  const nudge = (dir: number) => {
    const e = ref.current;
    if (!e) return;
    const first = e.firstElementChild as HTMLElement | null;
    const step = first ? (first.getBoundingClientRect().width + 18) * 2 : 480;
    e.scrollTo({ left: e.scrollLeft + dir * step, behavior: 'smooth' });
  };

  const labels: [keyof Tabs, string][] = [['best', t('tabBest')], ['nouveautes', t('tabNew')], ['promos', t('tabPromos')]];
  const list = tabs[active];

  return (
    <section className="hfm-wrap" style={{ maxWidth: '1340px', margin: '0 auto', padding: '64px 28px 8px' }}>
      <div style={{ display: 'flex', alignItems: 'flex-end', justifyContent: 'space-between', gap: '20px', flexWrap: 'wrap' }}>
        <div>
          <div style={{ fontSize: '11.5px', fontWeight: 700, letterSpacing: '.13em', textTransform: 'uppercase', color: '#8CC63F' }}>{t('tabsEyebrow')}</div>
          <h2 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: 'clamp(25px,3.6vw,34px)', color: '#2B2B2B', margin: '8px 0 0', letterSpacing: '-.01em' }}>{t('tabsTitle')}</h2>
        </div>
        <div style={{ display: 'flex', alignItems: 'center', gap: '14px', flexWrap: 'wrap' }}>
          <div style={{ display: 'flex', gap: '6px', background: '#fff', border: '1px solid #E7E3DA', borderRadius: '999px', padding: '5px' }}>
            {labels.map(([k, label]) => active === k ? (
              <button key={k} style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '13px', fontWeight: 600, color: '#fff', background: '#434343', border: 'none', borderRadius: '999px', padding: '9px 18px', cursor: 'pointer' }}>{label}</button>
            ) : (
              <button key={k} onClick={() => setActive(k)} style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '13px', fontWeight: 600, color: '#55606F', background: 'transparent', border: 'none', borderRadius: '999px', padding: '9px 18px', cursor: 'pointer' }}>{label}</button>
            ))}
          </div>
          <div style={{ display: 'flex', gap: '8px' }}>
            <button onClick={() => nudge(-1)} aria-label={t('prev')} style={navBtn}>←</button>
            <button onClick={() => nudge(1)} aria-label={t('next')} style={navBtn}>→</button>
          </div>
        </div>
      </div>
      <div ref={ref} className="hfm-carousel" style={{ display: 'flex', gap: '18px', marginTop: '30px', overflowX: 'auto', padding: '4px 2px 16px' }}>
        {list.map((item) => (
          <div key={item.id} style={{ flex: 'none', width: '262px', scrollSnapAlign: 'start' }}><ProductCard product={item} /></div>
        ))}
      </div>
    </section>
  );
}
