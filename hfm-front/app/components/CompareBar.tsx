'use client';

import { useEffect, useState } from 'react';
import { useTranslations, useLocale } from 'next-intl';
import { Link } from '@/i18n/navigation';
import { idLangFor } from '@/lib/i18n-config';
import { COMPARE_EVENT, clearCompare, readCompare, removeCompare } from '@/lib/compare';

// Barre flottante du comparateur (parité ancien site). Montée dans le layout : elle suit le
// visiteur sur toutes les pages, et ne s'affiche qu'une fois une sélection faite.
type Row = { id_product: number; name: string; image: string | null };

export default function CompareBar() {
  const t = useTranslations('compare');
  const locale = useLocale();
  const [rows, setRows] = useState<Row[]>([]);

  useEffect(() => {
    const load = () => {
      const ids = readCompare();
      if (!ids.length) {
        setRows([]);
        return;
      }
      fetch(`/api/products?ids=${ids.join(',')}&id_lang=${idLangFor(locale)}`)
        .then((r) => r.json())
        .then((d) => setRows(Array.isArray(d?.products) ? d.products : []))
        .catch(() => setRows([]));
    };
    load();
    window.addEventListener(COMPARE_EVENT, load);
    return () => window.removeEventListener(COMPARE_EVENT, load);
  }, [locale]);

  if (!rows.length) return null;

  return (
    <div
      role="region"
      aria-label={t('title')}
      style={{
        position: 'fixed', left: 16, right: 16, bottom: 16, zIndex: 900, maxWidth: '900px',
        margin: '0 auto', background: '#fff', border: '1px solid #E7E3DA', borderRadius: '14px',
        boxShadow: '0 20px 50px -22px rgba(40,50,25,.45)', padding: '12px 16px',
        display: 'flex', alignItems: 'center', gap: '14px', flexWrap: 'wrap',
        fontFamily: "'Hanken Grotesk',sans-serif",
      }}
    >
      <span style={{ fontSize: '12.5px', fontWeight: 700, color: '#5E6152' }}>{t('title')}</span>
      <div style={{ display: 'flex', gap: '8px', flex: 1, flexWrap: 'wrap' }}>
        {rows.map((p) => (
          <div key={p.id_product} style={{ display: 'flex', alignItems: 'center', gap: '7px', border: '1px solid #E2DECF', borderRadius: '8px', padding: '4px 8px 4px 4px', background: '#FAFAF7' }}>
            {p.image ? (
              /* eslint-disable-next-line @next/next/no-img-element */
              <img src={p.image} alt="" width={28} height={28} style={{ objectFit: 'contain', borderRadius: '4px' }} />
            ) : null}
            <span style={{ fontSize: '12px', color: '#434343', maxWidth: '130px', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{p.name}</span>
            <button
              type="button"
              onClick={() => removeCompare(p.id_product)}
              aria-label={`${t('remove')} ${p.name}`}
              style={{ border: 0, background: 'none', cursor: 'pointer', color: '#8A8170', fontSize: '15px', lineHeight: 1, padding: '0 2px' }}
            >
              ×
            </button>
          </div>
        ))}
      </div>
      <button type="button" onClick={clearCompare} style={{ border: 0, background: 'none', cursor: 'pointer', fontSize: '12.5px', color: '#8A8170', textDecoration: 'underline' }}>
        {t('clear')}
      </button>
      <Link
        href="/comparaison"
        style={{ color: '#fff', background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))', border: '1px solid rgba(255,255,255,.42)', borderRadius: '999px', padding: '10px 20px', fontSize: '13.5px', fontWeight: 600, textDecoration: 'none' }}
      >
        {t('cta', { n: rows.length })}
      </Link>
    </div>
  );
}
