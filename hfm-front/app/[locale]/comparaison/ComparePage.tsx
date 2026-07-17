'use client';

import { useEffect, useState } from 'react';
import { useTranslations, useLocale } from 'next-intl';
import { Link } from '@/i18n/navigation';
import { fmt, productHref } from '@/lib/cardModel';
import { idLangFor } from '@/lib/i18n-config';
import { COMPARE_EVENT, clearCompare, readCompare, removeCompare } from '@/lib/compare';
import { useStore } from '../../store';

// Tableau comparatif. Rendu côté client : la sélection vit en localStorage, donc la page reste
// statique/cacheable et aucune donnée de navigation ne part au serveur.
type Item = {
  id_product: number;
  name: string;
  reference: string;
  link_rewrite: string;
  category: string | null;
  brand: string | null;
  image: string | null;
  price_incl_tax: number;
  price_excl_tax: number;
  availability: string;
  available: boolean;
  features: { name: string; value: string }[];
};

const th: React.CSSProperties = {
  fontSize: '12.5px', fontWeight: 700, color: '#5E6152', textAlign: 'left',
  padding: '12px 14px', background: '#FAFAF7', borderBottom: '1px solid #ECEAE3',
  verticalAlign: 'top', width: '190px',
};
const td: React.CSSProperties = {
  fontSize: '13.5px', color: '#34352F', padding: '12px 14px',
  borderBottom: '1px solid #ECEAE3', borderLeft: '1px solid #ECEAE3', verticalAlign: 'top',
};

export default function ComparePage() {
  const t = useTranslations('compare');
  const tc = useTranslations('common');
  const locale = useLocale();
  const { addToCart } = useStore();
  const [items, setItems] = useState<Item[]>([]);
  const [loaded, setLoaded] = useState(false);

  useEffect(() => {
    const load = () => {
      const ids = readCompare();
      if (!ids.length) {
        setItems([]);
        setLoaded(true);
        return;
      }
      fetch(`/api/products?action=compare&ids=${ids.join(',')}&id_lang=${idLangFor(locale)}`)
        .then((r) => r.json())
        .then((d) => setItems(Array.isArray(d?.items) ? d.items : []))
        .catch(() => setItems([]))
        .finally(() => setLoaded(true));
    };
    load();
    window.addEventListener(COMPARE_EVENT, load);
    return () => window.removeEventListener(COMPARE_EVENT, load);
  }, [locale]);

  if (loaded && !items.length) {
    return (
      <p style={{ fontSize: '15px', color: '#55606F' }}>
        {t('empty')} <Link href="/catalogue" style={{ color: '#5E8E1F', fontWeight: 600 }}>{t('browse')} →</Link>
      </p>
    );
  }
  if (!loaded) return null;

  // Union ordonnée des caractéristiques présentes : une ligne par caractéristique, « — » si
  // le produit ne la porte pas. Le tableau se remplit tout seul quand les fiches sont enrichies.
  const featureNames: string[] = [];
  for (const it of items) {
    for (const f of it.features) {
      if (!featureNames.includes(f.name)) featureNames.push(f.name);
    }
  }
  const featureValue = (it: Item, name: string) => it.features.find((f) => f.name === name)?.value || '—';

  return (
    <>
      <div style={{ overflowX: 'auto', border: '1px solid #ECEAE3', borderRadius: '12px', background: '#fff' }}>
        <table style={{ borderCollapse: 'collapse', width: '100%', minWidth: `${190 + items.length * 210}px` }}>
          <tbody>
            <tr>
              <th style={th} />
              {items.map((it) => (
                <td key={it.id_product} style={{ ...td, textAlign: 'center' }}>
                  <button
                    type="button"
                    onClick={() => removeCompare(it.id_product)}
                    aria-label={`${t('remove')} ${it.name}`}
                    style={{ float: 'right', border: 0, background: 'none', cursor: 'pointer', color: '#8A8170', fontSize: '16px', lineHeight: 1 }}
                  >
                    ×
                  </button>
                  <Link href={productHref(it as never)} style={{ textDecoration: 'none', color: 'inherit' }}>
                    {it.image ? (
                      /* eslint-disable-next-line @next/next/no-img-element */
                      <img src={it.image} alt={it.name} style={{ width: '100%', maxWidth: '150px', aspectRatio: '1', objectFit: 'contain', display: 'block', margin: '0 auto 10px' }} />
                    ) : null}
                    <span style={{ fontSize: '13.5px', fontWeight: 600, color: '#2B2B2B' }}>{it.name}</span>
                  </Link>
                </td>
              ))}
            </tr>
            <tr>
              <th style={th}>{t('price')}</th>
              {items.map((it) => (
                <td key={it.id_product} style={td}>
                  <div style={{ fontSize: '18px', fontWeight: 700, color: '#2B2B2B' }}>{fmt(it.price_incl_tax)} €</div>
                  <div style={{ fontSize: '12.5px', color: '#6E7585' }}>{fmt(it.price_excl_tax)} € HT</div>
                </td>
              ))}
            </tr>
            <tr>
              <th style={th}>{t('brand')}</th>
              {items.map((it) => <td key={it.id_product} style={td}>{it.brand || '—'}</td>)}
            </tr>
            <tr>
              <th style={th}>{t('reference')}</th>
              {items.map((it) => <td key={it.id_product} style={td}>{it.reference || '—'}</td>)}
            </tr>
            <tr>
              <th style={th}>{t('availability')}</th>
              {items.map((it) => (
                <td key={it.id_product} style={td}>
                  <span style={{ fontWeight: 600, color: it.available ? '#3F7256' : '#A8503A' }}>
                    {it.available ? t('inStock') : t('outOfStock')}
                  </span>
                </td>
              ))}
            </tr>
            {featureNames.map((name) => (
              <tr key={name}>
                <th style={th}>{name}</th>
                {items.map((it) => <td key={it.id_product} style={td}>{featureValue(it, name)}</td>)}
              </tr>
            ))}
            <tr>
              <th style={th} />
              {items.map((it) => (
                <td key={it.id_product} style={td}>
                  {it.available ? (
                    <button
                      type="button"
                      onClick={() => addToCart({ id: it.id_product, quantity: 1 })}
                      style={{ width: '100%', color: '#fff', background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))', border: '1px solid rgba(255,255,255,.42)', borderRadius: '999px', padding: '11px 14px', fontSize: '13.5px', fontWeight: 600, cursor: 'pointer' }}
                    >
                      {tc('addToCart')}
                    </button>
                  ) : null}
                </td>
              ))}
            </tr>
          </tbody>
        </table>
      </div>
      <button type="button" onClick={clearCompare} style={{ marginTop: '18px', border: '1px solid #E2DECF', background: '#fff', borderRadius: '999px', padding: '9px 18px', fontSize: '13px', color: '#5E6152', cursor: 'pointer' }}>
        {t('clear')}
      </button>
    </>
  );
}
