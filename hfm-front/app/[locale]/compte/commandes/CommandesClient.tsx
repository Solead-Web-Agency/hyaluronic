'use client';

import { useEffect, useState } from 'react';
import { useTranslations, useLocale } from 'next-intl';
import { Link } from '@/i18n/navigation';
import { useStore } from '../../../store';
import { fmt } from '@/lib/cardModel';

type OrderRow = {
  id_order: number;
  reference: string;
  date: string;
  total_paid: number;
  nb_products: number;
  state: string;
  state_color: string;
  payment: string;
};

const cardStyle: React.CSSProperties = {
  background: '#fff',
  border: '1px solid #ECEAE3',
  borderRadius: '12px',
  padding: '28px',
  boxShadow: '0 18px 40px -30px rgba(40,50,25,.4)',
};

function StateBadge({ label, color }: { label: string; color: string }) {
  return (
    <span style={{ display: 'inline-flex', alignItems: 'center', gap: '7px', fontSize: '13px', color: '#34352F', fontWeight: 600 }}>
      <span style={{ width: '9px', height: '9px', borderRadius: '999px', background: color || '#8CC63F', flexShrink: 0 }} />
      {label}
    </span>
  );
}

export default function CommandesClient() {
  const t = useTranslations('orders');
  const tc = useTranslations('common');
  const locale = useLocale();
  const formatDate = (d: string) => {
    const dt = new Date(d);
    if (Number.isNaN(dt.getTime())) return d;
    return dt.toLocaleDateString(locale, { day: '2-digit', month: 'short', year: 'numeric' });
  };
  const { customer, authReady } = useStore();
  const [orders, setOrders] = useState<OrderRow[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!authReady || !customer) return;
    setLoading(true);
    fetch('/api/orders?action=list')
      .then((r) => (r.ok ? r.json() : Promise.reject(new Error('http'))))
      .then((d) => setOrders(d.orders ?? []))
      .catch(() => setError(t('loadError')))
      .finally(() => setLoading(false));
  }, [authReady, customer]);

  if (!authReady) {
    return (
      <main className="hfm-wrap" style={{ maxWidth: '900px', margin: '0 auto', padding: '60px 28px', textAlign: 'center', color: '#8A8170' }}>
        {tc('loading')}
      </main>
    );
  }

  if (!customer) {
    return (
      <main className="hfm-wrap" style={{ maxWidth: '480px', margin: '0 auto', padding: '46px 28px 80px' }}>
        <div style={cardStyle}>
          <div style={{ fontFamily: "'Spectral',serif", fontSize: '19px', color: '#2B2B2B', marginBottom: '8px' }}>{t('loginRequired')}</div>
          <p style={{ fontSize: '14px', color: '#6E7585', margin: '0 0 18px' }}>{t('loginToView')}</p>
          <Link href="/compte?next=/compte/commandes" style={{ display: 'inline-flex', alignItems: 'center', height: '46px', padding: '0 22px', borderRadius: '999px', color: '#fff', background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))', border: '1px solid rgba(255,255,255,.42)', fontWeight: 600, fontSize: '14px' }}>{tc('login')}</Link>
        </div>
      </main>
    );
  }

  return (
    <main data-screen-label="Mes commandes" className="hfm-wrap" style={{ maxWidth: '900px', margin: '0 auto', padding: '46px 28px 80px' }}>
      <div style={{ fontSize: '12.5px', color: '#9A9A9A', marginBottom: '18px' }}>
        <Link href="/" style={{ cursor: 'pointer' }}>{tc('home')}</Link>  /  <Link href="/compte" style={{ cursor: 'pointer' }}>{tc('myAccount')}</Link>  /  {t('breadcrumb')}
      </div>
      <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: '34px', color: '#2B2B2B', margin: '0 0 26px' }}>{t('title')}</h1>

      {loading ? (
        <div style={{ ...cardStyle, color: '#8A8170', fontSize: '14px' }}>{tc('loading')}</div>
      ) : error ? (
        <div style={{ ...cardStyle, color: '#A8503A', fontSize: '14px' }}>{error}</div>
      ) : orders.length === 0 ? (
        <div style={{ ...cardStyle, textAlign: 'center' }}>
          <div style={{ fontSize: '15px', color: '#34352F', fontWeight: 600, marginBottom: '6px' }}>{t('none')}</div>
          <p style={{ fontSize: '14px', color: '#6E7585', margin: '0 0 18px' }}>{t('noneLead')}</p>
          <Link href="/catalogue" style={{ display: 'inline-flex', alignItems: 'center', height: '46px', padding: '0 22px', borderRadius: '999px', color: '#fff', background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))', border: '1px solid rgba(255,255,255,.42)', fontWeight: 600, fontSize: '14px' }}>{tc('discoverCatalogue')}</Link>
        </div>
      ) : (
        <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
          {orders.map((o) => (
            <Link
              key={o.id_order}
              href={`/compte/commandes/${o.id_order}`}
              style={{ display: 'block', background: '#fff', border: '1px solid #ECEAE3', borderRadius: '12px', padding: '20px 24px', boxShadow: '0 18px 40px -34px rgba(40,50,25,.4)', textDecoration: 'none', color: 'inherit' }}
            >
              <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: '12px' }}>
                <div style={{ minWidth: '160px' }}>
                  <div style={{ fontFamily: "'Spectral',serif", fontSize: '17px', color: '#2B2B2B' }}>{o.reference}</div>
                  <div style={{ fontSize: '12.5px', color: '#8A8170', marginTop: '3px' }}>{formatDate(o.date)} · {t('itemCount', { count: o.nb_products })}</div>
                </div>
                <div style={{ display: 'flex', alignItems: 'center', gap: '24px', flexWrap: 'wrap' }}>
                  <StateBadge label={o.state} color={o.state_color} />
                  <div style={{ fontSize: '12.5px', color: '#6E7585' }}>{o.payment}</div>
                  <div style={{ fontFamily: "'Spectral',serif", fontSize: '18px', color: '#5E8E1F', fontWeight: 500, minWidth: '92px', textAlign: 'right' }}>{fmt(o.total_paid)} €</div>
                </div>
              </div>
            </Link>
          ))}
        </div>
      )}
    </main>
  );
}
