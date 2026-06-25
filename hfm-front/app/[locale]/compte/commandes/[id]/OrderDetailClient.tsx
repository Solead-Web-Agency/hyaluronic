'use client';

import { useEffect, useState } from 'react';
import { useTranslations, useLocale } from 'next-intl';
import { Link } from '@/i18n/navigation';
import { useParams } from 'next/navigation';
import { useStore } from '../../../../store';
import { fmt } from '@/lib/cardModel';

type OrderProduct = {
  id_product: number;
  name: string;
  reference: string;
  quantity: number;
  unit_price_incl_tax: number;
  total_incl_tax: number;
};

type OrderAddress = {
  firstname: string;
  lastname: string;
  address1: string;
  postcode: string;
  city: string;
  country: string;
  phone: string;
};

type OrderDetail = {
  id_order: number;
  reference: string;
  date: string;
  state: string;
  state_color: string;
  payment: string;
  products: OrderProduct[];
  total_products: number;
  total_shipping: number;
  total_discounts: number;
  total_paid: number;
  carrier: string;
  address: OrderAddress;
};

const cardStyle: React.CSSProperties = {
  background: '#fff',
  border: '1px solid #ECEAE3',
  borderRadius: '12px',
  padding: '28px',
  boxShadow: '0 18px 40px -30px rgba(40,50,25,.4)',
};

const sectionTitle: React.CSSProperties = {
  fontFamily: "'Spectral',serif",
  fontSize: '19px',
  color: '#2B2B2B',
  marginBottom: '16px',
};

export default function OrderDetailClient() {
  const t = useTranslations('orders.detail');
  const to = useTranslations('orders');
  const tc = useTranslations('common');
  const locale = useLocale();
  const formatDate = (d: string) => {
    const dt = new Date(d);
    if (Number.isNaN(dt.getTime())) return d;
    return dt.toLocaleDateString(locale, { day: '2-digit', month: 'long', year: 'numeric' });
  };
  const params = useParams();
  const rawId = Array.isArray(params?.id) ? params.id[0] : params?.id;
  const id = rawId ?? '';
  const { customer, authReady } = useStore();

  const [order, setOrder] = useState<OrderDetail | null>(null);
  const [loading, setLoading] = useState(true);
  const [notFound, setNotFound] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!authReady || !customer || !id) return;
    setLoading(true);
    setNotFound(false);
    setError(null);
    fetch(`/api/orders?action=detail&id_order=${encodeURIComponent(id)}`)
      .then((r) => (r.ok ? r.json() : Promise.reject(new Error('http'))))
      .then((d) => {
        if (d.error === 'order_not_found' || !d.order) {
          setNotFound(true);
        } else {
          setOrder(d.order);
        }
      })
      .catch(() => setError(t('loadError')))
      .finally(() => setLoading(false));
  }, [authReady, customer, id]);

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
          <div style={{ fontFamily: "'Spectral',serif", fontSize: '19px', color: '#2B2B2B', marginBottom: '8px' }}>{to('loginRequired')}</div>
          <p style={{ fontSize: '14px', color: '#6E7585', margin: '0 0 18px' }}>{t('loginToView')}</p>
          <Link href={`/compte?next=/compte/commandes/${id}`} style={{ display: 'inline-flex', alignItems: 'center', height: '46px', padding: '0 22px', borderRadius: '999px', color: '#fff', background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))', border: '1px solid rgba(255,255,255,.42)', fontWeight: 600, fontSize: '14px' }}>{tc('login')}</Link>
        </div>
      </main>
    );
  }

  const backLink = (
    <Link href="/compte/commandes" style={{ display: 'inline-flex', alignItems: 'center', gap: '6px', fontSize: '13.5px', fontWeight: 600, color: '#5E8E1F', textDecoration: 'none' }}>
      {t('back')}
    </Link>
  );

  return (
    <main data-screen-label="Détail commande" className="hfm-wrap" style={{ maxWidth: '900px', margin: '0 auto', padding: '46px 28px 80px' }}>
      <div style={{ fontSize: '12.5px', color: '#9A9A9A', marginBottom: '18px' }}>
        <Link href="/" style={{ cursor: 'pointer' }}>{tc('home')}</Link>  /  <Link href="/compte" style={{ cursor: 'pointer' }}>{tc('myAccount')}</Link>  /  <Link href="/compte/commandes" style={{ cursor: 'pointer' }}>{to('title')}</Link>  /  {t('breadcrumb')}
      </div>

      {loading ? (
        <div style={{ ...cardStyle, color: '#8A8170', fontSize: '14px' }}>{tc('loading')}</div>
      ) : error ? (
        <>
          <div style={{ ...cardStyle, color: '#A8503A', fontSize: '14px', marginBottom: '18px' }}>{error}</div>
          {backLink}
        </>
      ) : notFound || !order ? (
        <>
          <div style={{ ...cardStyle, marginBottom: '18px' }}>
            <div style={{ fontFamily: "'Spectral',serif", fontSize: '19px', color: '#2B2B2B', marginBottom: '6px' }}>{t('notFound')}</div>
            <p style={{ fontSize: '14px', color: '#6E7585', margin: 0 }}>{t('notFoundLead')}</p>
          </div>
          {backLink}
        </>
      ) : (
        <>
          <div style={{ marginBottom: '20px' }}>{backLink}</div>

          <div style={{ display: 'flex', alignItems: 'flex-end', justifyContent: 'space-between', flexWrap: 'wrap', gap: '14px', marginBottom: '26px' }}>
            <div>
              <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: '32px', color: '#2B2B2B', margin: 0 }}>{t('orderTitle', { reference: order.reference })}</h1>
              <div style={{ fontSize: '13px', color: '#8A8170', marginTop: '6px' }}>{t('placedOn', { date: formatDate(order.date) })}</div>
            </div>
            <span style={{ display: 'inline-flex', alignItems: 'center', gap: '8px', background: '#FAFAF7', border: '1px solid #ECEAE3', borderRadius: '999px', padding: '9px 16px', fontSize: '13px', fontWeight: 600, color: '#34352F' }}>
              <span style={{ width: '9px', height: '9px', borderRadius: '999px', background: order.state_color || '#8CC63F' }} />
              {order.state}
            </span>
          </div>

          <div style={cardStyle}>
            <div style={sectionTitle}>{t('itemsTitle')}</div>
            <div style={{ overflowX: 'auto' }}>
              <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: '14px' }}>
                <thead>
                  <tr style={{ textAlign: 'left', color: '#8A8170', fontSize: '11px', textTransform: 'uppercase', letterSpacing: '.06em' }}>
                    <th style={{ padding: '0 8px 10px 0', fontWeight: 700 }}>{t('colProduct')}</th>
                    <th style={{ padding: '0 8px 10px', fontWeight: 700, textAlign: 'center' }}>{t('colQty')}</th>
                    <th style={{ padding: '0 8px 10px', fontWeight: 700, textAlign: 'right' }}>{t('colUnitPrice')}</th>
                    <th style={{ padding: '0 0 10px 8px', fontWeight: 700, textAlign: 'right' }}>{t('colTotal')}</th>
                  </tr>
                </thead>
                <tbody>
                  {order.products.map((p, i) => (
                    <tr key={`${p.id_product}-${i}`} style={{ borderTop: '1px solid #ECEAE3' }}>
                      <td style={{ padding: '12px 8px 12px 0' }}>
                        <div style={{ color: '#34352F', fontWeight: 600 }}>{p.name}</div>
                        {p.reference ? <div style={{ fontSize: '12px', color: '#8A8170', marginTop: '2px' }}>{p.reference}</div> : null}
                      </td>
                      <td style={{ padding: '12px 8px', textAlign: 'center', color: '#55606F' }}>{p.quantity}</td>
                      <td style={{ padding: '12px 8px', textAlign: 'right', color: '#55606F' }}>{fmt(p.unit_price_incl_tax)} €</td>
                      <td style={{ padding: '12px 0 12px 8px', textAlign: 'right', color: '#34352F', fontWeight: 600 }}>{fmt(p.total_incl_tax)} €</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>

          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit,minmax(260px,1fr))', gap: '20px', marginTop: '20px' }}>
            <div style={cardStyle}>
              <div style={sectionTitle}>{t('shippingAddress')}</div>
              <div style={{ fontSize: '14px', color: '#55606F', lineHeight: 1.6 }}>
                <div style={{ color: '#34352F', fontWeight: 600 }}>{order.address.firstname} {order.address.lastname}</div>
                {order.address.address1}<br />
                {order.address.postcode} {order.address.city}<br />
                {order.address.country}
                {order.address.phone ? <><br />{order.address.phone}</> : null}
              </div>
            </div>

            <div style={cardStyle}>
              <div style={sectionTitle}>{t('summary')}</div>
              <div style={{ fontSize: '13px', color: '#6E7585', marginBottom: '16px' }}>
                {t('carrier')} <span style={{ color: '#34352F', fontWeight: 600 }}>{order.carrier || '—'}</span><br />
                {t('payment')} <span style={{ color: '#34352F', fontWeight: 600 }}>{order.payment || '—'}</span>
              </div>
              <div style={{ display: 'flex', flexDirection: 'column', gap: '8px', fontSize: '14px' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', color: '#55606F' }}>
                  <span>{t('products')}</span><span>{fmt(order.total_products)} €</span>
                </div>
                <div style={{ display: 'flex', justifyContent: 'space-between', color: '#55606F' }}>
                  <span>{t('shipping')}</span><span>{fmt(order.total_shipping)} €</span>
                </div>
                {order.total_discounts > 0 ? (
                  <div style={{ display: 'flex', justifyContent: 'space-between', color: '#A8503A' }}>
                    <span>{t('discounts')}</span><span>−{fmt(order.total_discounts)} €</span>
                  </div>
                ) : null}
                <div style={{ display: 'flex', justifyContent: 'space-between', borderTop: '1px solid #ECEAE3', paddingTop: '10px', marginTop: '4px' }}>
                  <span style={{ fontFamily: "'Spectral',serif", fontSize: '16px', color: '#2B2B2B' }}>{t('totalPaid')}</span>
                  <span style={{ fontFamily: "'Spectral',serif", fontSize: '18px', color: '#5E8E1F', fontWeight: 500 }}>{fmt(order.total_paid)} €</span>
                </div>
              </div>
            </div>
          </div>
        </>
      )}
    </main>
  );
}
