'use client';

import { useEffect } from 'react';
import { useTranslations } from 'next-intl';
import { Link } from '@/i18n/navigation';
import { toCard } from '@/lib/cardModel';
import ProductCard from '../../components/ProductCard';
import { useStore } from '../../store';
import { useWishlist } from '../../wishlist';

export default function FavorisClient() {
  const t = useTranslations('favorites');
  const tc = useTranslations('common');
  const { customer, authReady } = useStore();
  const { products, refresh } = useWishlist();

  useEffect(() => {
    refresh();
  }, [refresh]);

  // Carte de connexion si l'utilisateur n'est pas connecté.
  if (authReady && !customer) {
    return (
      <main className="hfm-wrap" style={{ maxWidth: '1340px', margin: '0 auto', padding: '34px 28px 70px' }}>
        <div
          style={{
            maxWidth: '520px',
            margin: '40px auto',
            background: '#FFFFFF',
            border: '1px solid #ECEAE3',
            borderRadius: '10px',
            padding: '40px 34px',
            textAlign: 'center',
          }}
        >
          <h1 style={{ fontFamily: "'Spectral',serif", fontSize: '24px', color: '#2B2B2B', marginBottom: '12px' }}>
            {t('loginTitle')}
          </h1>
          <p style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14px', color: '#8A8170', marginBottom: '24px', lineHeight: 1.5 }}>
            {t('loginLead')}
          </p>
          <Link
            href="/compte?next=/favoris"
            style={{
              display: 'inline-block',
              fontFamily: "'Hanken Grotesk',sans-serif",
              fontSize: '13px',
              fontWeight: 600,
              letterSpacing: '.02em',
              color: '#FFFFFF',
              background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))',
              border: '1px solid rgba(255,255,255,.42)',
              borderRadius: '999px',
              padding: '12px 26px',
              textDecoration: 'none',
            }}
          >
            {tc('login')}
          </Link>
        </div>
      </main>
    );
  }

  return (
    <main className="hfm-wrap" style={{ maxWidth: '1340px', margin: '0 auto', padding: '34px 28px 70px' }}>
      <h1 style={{ fontFamily: "'Spectral',serif", fontSize: '30px', color: '#2B2B2B', marginBottom: '6px' }}>
        {t('title')}
      </h1>
      <p style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '13px', color: '#8A8170', marginBottom: '26px' }}>
        {products.length > 0
          ? t('count', { count: products.length })
          : t('emptyLead')}
      </p>

      {products.length === 0 ? (
        <div
          style={{
            background: '#FFFFFF',
            border: '1px solid #ECEAE3',
            borderRadius: '10px',
            padding: '46px 34px',
            textAlign: 'center',
          }}
        >
          <p style={{ fontFamily: "'Spectral',serif", fontSize: '18px', color: '#34352F', marginBottom: '18px' }}>
            {t('emptyTitle')}
          </p>
          <Link
            href="/catalogue"
            style={{
              display: 'inline-block',
              fontFamily: "'Hanken Grotesk',sans-serif",
              fontSize: '13px',
              fontWeight: 600,
              letterSpacing: '.02em',
              color: '#5E8E1F',
              border: '1px solid #C7D9A8',
              background: '#F7F6F2',
              borderRadius: '999px',
              padding: '11px 24px',
              textDecoration: 'none',
            }}
          >
            {tc('browseCatalogue')}
          </Link>
        </div>
      ) : (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill,minmax(210px,1fr))', gap: '18px' }}>
          {products.map((p) => (
            <ProductCard key={p.id_product} product={toCard(p)} />
          ))}
        </div>
      )}
    </main>
  );
}
