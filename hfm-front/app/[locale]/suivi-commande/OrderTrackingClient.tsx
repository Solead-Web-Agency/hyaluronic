'use client';

import { useState } from 'react';
import { useTranslations, useLocale } from 'next-intl';
import { useSearchParams } from 'next/navigation';
import { Link } from '@/i18n/navigation';
import { fmt } from '@/lib/cardModel';

type TrackedOrder = {
  reference: string;
  date_add: string;
  status: string;
  shipped: boolean;
  delivered: boolean;
  total_paid_incl_tax: number;
  carrier: string;
  tracking_number: string;
  tracking_url: string;
  products: { name: string; quantity: number }[];
};

const inputStyle: React.CSSProperties = {
  height: '46px', padding: '0 14px', border: '1px solid #E2DECF', borderRadius: '6px',
  fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14px', outline: 'none', background: '#fff',
  width: '100%', boxSizing: 'border-box', color: '#34352F',
};
const labelStyle: React.CSSProperties = {
  fontSize: '11px', fontWeight: 700, letterSpacing: '.08em', textTransform: 'uppercase',
  color: '#8A8170', marginBottom: '6px', display: 'block',
};
const primaryBtn: React.CSSProperties = {
  height: '50px', borderRadius: '999px', color: '#fff',
  background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))',
  border: '1px solid rgba(255,255,255,.42)', boxShadow: '0 12px 26px -10px rgba(116,176,51,.55)',
  fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '15px', fontWeight: 600, cursor: 'pointer', width: '100%',
};
const cardStyle: React.CSSProperties = {
  background: '#fff', border: '1px solid #ECEAE3', borderRadius: '12px', padding: '28px',
  boxShadow: '0 18px 40px -30px rgba(40,50,25,.4)',
};

export default function OrderTrackingClient() {
  const t = useTranslations('orderTracking');
  const tc = useTranslations('common');
  const locale = useLocale();
  const search = useSearchParams();
  // Pré-remplissage possible depuis un lien (?ref=XXXXXXXXX) ; l'e-mail n'est jamais passé en URL.
  const [reference, setReference] = useState((search.get('ref') || '').toUpperCase().slice(0, 12));
  const [email, setEmail] = useState('');
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [order, setOrder] = useState<TrackedOrder | null>(null);

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (busy) return;
    setError(null);
    setBusy(true);
    try {
      const r = await fetch('/api/order-tracking', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ reference, email, locale }),
      });
      const d = await r.json().catch(() => ({}));
      if (r.ok && d?.order) {
        setOrder(d.order as TrackedOrder);
        return;
      }
      setOrder(null);
      setError(
        d?.error === 'rate_limited' ? tc('rateLimited')
          : d?.error === 'invalid_request' ? t('errInvalid')
          : d?.error === 'not_found' ? t('errNotFound')
          : t('errGeneric'),
      );
    } catch {
      setError(t('errGeneric'));
    } finally {
      setBusy(false);
    }
  };

  const dateLabel = (raw: string) => {
    const d = new Date((raw || '').replace(' ', 'T'));
    if (isNaN(d.getTime())) return raw;
    try {
      return new Intl.DateTimeFormat(locale, { day: 'numeric', month: 'long', year: 'numeric' }).format(d);
    } catch {
      return raw;
    }
  };

  // Étapes : reçue -> expédiée -> livrée. NB : dans cette boutique l'état « En cours de préparation »
  // porte le drapeau PrestaShop `shipped` : seul, il ne prouve pas l'expédition. On n'allume donc
  // « Expédiée » qu'avec un n° de suivi (ou une livraison) ; le libellé exact reste dans la pastille.
  const steps = order
    ? [
        { label: t('stepReceived'), done: true },
        { label: t('stepShipped'), done: order.delivered || (order.shipped && !!order.tracking_number) },
        { label: t('stepDelivered'), done: order.delivered },
      ]
    : [];

  return (
    <main data-screen-label="Suivi de commande" className="hfm-wrap" style={{ maxWidth: '640px', margin: '0 auto', padding: '46px 28px 80px' }}>
      <div style={{ fontSize: '12.5px', color: '#9A9A9A', marginBottom: '18px' }}><Link href="/" style={{ cursor: 'pointer' }}>{tc('home')}</Link>  /  {t('title')}</div>
      <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: '34px', color: '#2B2B2B', margin: '0 0 8px' }}>{t('title')}</h1>
      <p style={{ fontSize: '14.5px', color: '#6E7585', margin: '0 0 22px', lineHeight: 1.55 }}>{t('lead')}</p>

      <div style={cardStyle}>
        <form onSubmit={submit} style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
          <div>
            <label htmlFor="trk-ref" style={labelStyle}>{t('reference')}</label>
            <input id="trk-ref" required autoComplete="off" value={reference} onChange={(e) => setReference(e.target.value.toUpperCase())} placeholder={t('referencePlaceholder')} style={{ ...inputStyle, letterSpacing: '.06em' }} />
            <span style={{ display: 'block', fontSize: '12px', color: '#8A8170', marginTop: '5px' }}>{t('referenceHint')}</span>
          </div>
          <div>
            <label htmlFor="trk-email" style={labelStyle}>{t('email')}</label>
            <input id="trk-email" type="email" required autoComplete="email" value={email} onChange={(e) => setEmail(e.target.value)} placeholder={t('emailPlaceholder')} style={inputStyle} />
          </div>
          {error ? <div role="alert" style={{ fontSize: '13px', color: '#A8503A' }}>{error}</div> : null}
          <button type="submit" disabled={busy} style={{ ...primaryBtn, opacity: busy ? 0.7 : 1 }}>{busy ? t('searching') : t('submit')}</button>
        </form>
      </div>

      {order ? (
        <div style={{ ...cardStyle, marginTop: '20px' }} aria-live="polite">
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', gap: '12px', flexWrap: 'wrap' }}>
            <div style={{ fontFamily: "'Spectral',serif", fontSize: '20px', color: '#2B2B2B' }}>{t('orderTitle', { reference: order.reference })}</div>
            {order.date_add ? <div style={{ fontSize: '13px', color: '#8A8170' }}>{t('placedOn', { date: dateLabel(order.date_add) })}</div> : null}
          </div>

          {order.status ? (
            <div style={{ marginTop: '14px', display: 'inline-flex', alignItems: 'center', gap: '8px', padding: '7px 14px', borderRadius: '999px', background: 'rgba(140,198,63,.12)', color: '#3F5E1C', fontSize: '13px', fontWeight: 600 }}>
              <span style={{ width: '7px', height: '7px', borderRadius: '50%', background: '#8CC63F' }} />{order.status}
            </div>
          ) : null}

          <ol style={{ listStyle: 'none', padding: 0, margin: '22px 0 0', display: 'grid', gridTemplateColumns: 'repeat(3,1fr)', gap: '8px' }}>
            {steps.map((s, i) => (
              <li key={i} style={{ textAlign: 'center' }}>
                <div style={{ height: '4px', borderRadius: '2px', background: s.done ? '#8CC63F' : '#E7E3DA' }} />
                <div style={{ fontSize: '12.5px', marginTop: '8px', color: s.done ? '#2B2B2B' : '#9A9A9A', fontWeight: s.done ? 600 : 400 }}>{s.label}</div>
              </li>
            ))}
          </ol>

          <dl style={{ margin: '24px 0 0', display: 'grid', gridTemplateColumns: 'minmax(120px,auto) 1fr', rowGap: '10px', columnGap: '16px', fontSize: '14px' }}>
            <dt style={{ color: '#8A8170' }}>{t('carrier')}</dt>
            <dd style={{ margin: 0, color: '#34352F' }}>{order.carrier || '—'}</dd>
            <dt style={{ color: '#8A8170' }}>{t('trackingNumber')}</dt>
            <dd style={{ margin: 0, color: '#34352F', wordBreak: 'break-all' }}>
              {order.tracking_number ? (
                order.tracking_url ? (
                  <a href={order.tracking_url} target="_blank" rel="noopener noreferrer" style={{ color: '#5E8E1F', fontWeight: 600 }}>{order.tracking_number} ↗</a>
                ) : order.tracking_number
              ) : (
                <span style={{ color: '#8A8170' }}>{t('trackingPending')}</span>
              )}
            </dd>
            <dt style={{ color: '#8A8170' }}>{t('total')}</dt>
            <dd style={{ margin: 0, color: '#34352F', fontWeight: 600 }}>{fmt(order.total_paid_incl_tax)} € {t('ttc')}</dd>
          </dl>

          {order.tracking_url ? (
            <a href={order.tracking_url} target="_blank" rel="noopener noreferrer" style={{ ...primaryBtn, display: 'flex', alignItems: 'center', justifyContent: 'center', textDecoration: 'none', marginTop: '22px' }}>{t('trackParcel')}</a>
          ) : null}

          {order.products.length ? (
            <div style={{ marginTop: '24px', borderTop: '1px solid #ECEAE3', paddingTop: '18px' }}>
              <div style={{ ...labelStyle, marginBottom: '10px' }}>{t('products')}</div>
              <ul style={{ listStyle: 'none', padding: 0, margin: 0, display: 'flex', flexDirection: 'column', gap: '8px' }}>
                {order.products.map((p, i) => (
                  <li key={i} style={{ display: 'flex', justifyContent: 'space-between', gap: '12px', fontSize: '14px', color: '#34352F' }}>
                    <span>{p.name}</span><span style={{ color: '#8A8170', flex: 'none' }}>× {p.quantity}</span>
                  </li>
                ))}
              </ul>
            </div>
          ) : null}
        </div>
      ) : null}

      <p style={{ fontSize: '13.5px', color: '#6E7585', marginTop: '22px', textAlign: 'center', lineHeight: 1.6 }}>
        {t.rich('haveAccount', { a: (c) => <Link href="/compte/commandes" style={{ color: '#5E8E1F', fontWeight: 600 }}>{c}</Link> })}
        <br />
        {t.rich('needHelp', { a: (c) => <Link href="/contact" style={{ color: '#5E8E1F', fontWeight: 600 }}>{c}</Link> })}
      </p>
    </main>
  );
}
