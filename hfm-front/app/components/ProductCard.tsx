'use client';

import { useRef, useState } from 'react';
import { useTranslations } from 'next-intl';
import { Link, useRouter } from '@/i18n/navigation';
import type { Card } from '@/lib/cardModel';
import { fmt, productHref } from '@/lib/cardModel';
import { useStore } from '../store';
import { useWishlist } from '../wishlist';

export default function ProductCard({ product }: { product: Card }) {
  const { addToCart, openQuick, customer } = useStore();
  const { has, toggle } = useWishlist();
  const router = useRouter();
  const tp = useTranslations('product');
  const tc = useTranslations('common');
  const fav = has(product.id);

  const onFav = (e: React.MouseEvent) => {
    e.stopPropagation();
    e.preventDefault();
    if (!customer) {
      router.push('/compte?next=/favoris');
      return;
    }
    toggle(product.id);
  };
  const [added, setAdded] = useState(false);
  const t = useRef<ReturnType<typeof setTimeout> | null>(null);

  // 3 états : en stock (achat), sur commande (achat + badge), indisponible (alerte retour).
  const out = product.stock === 'out';
  const backorder = product.stock === 'backorder';
  const canBuy = !out;
  const href = productHref(product);

  const onAdd = (e: React.MouseEvent) => {
    e.stopPropagation();
    e.preventDefault();
    addToCart({ id: product.id });
    setAdded(true);
    if (t.current) clearTimeout(t.current);
    t.current = setTimeout(() => setAdded(false), 1400);
  };

  // Produit en rupture : on N'AJOUTE PAS au panier (juste un retour visuel "alerte enregistrée").
  const [notified, setNotified] = useState(false);
  const onNotify = (e: React.MouseEvent) => {
    e.stopPropagation();
    e.preventDefault();
    setNotified(true);
  };

  const onQuick = (e: React.MouseEvent) => {
    e.stopPropagation();
    e.preventDefault();
    openQuick(product);
  };

  return (
    <div
      onClick={() => router.push(href)}
      className="hfm-pcard"
      style={{
        cursor: 'pointer',
        background: '#FFFFFF',
        border: '1px solid #ECEAE3',
        borderRadius: '6px',
        overflow: 'hidden',
        display: 'flex',
        flexDirection: 'column',
        height: '100%',
        transition: 'box-shadow .28s ease, transform .28s ease, border-color .28s ease',
      }}
    >
      <div
        style={{
          position: 'relative',
          aspectRatio: '1 / 1',
          background:
            'repeating-linear-gradient(135deg,#F7F6F2,#F7F6F2 9px,#F1EFE8 9px,#F1EFE8 18px)',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          overflow: 'hidden',
        }}
      >
        {product.img ? (
          // eslint-disable-next-line @next/next/no-img-element
          <img
            src={product.img}
            alt={product.name}
            loading="lazy"
            decoding="async"
            style={{ position: 'absolute', inset: 0, width: '100%', height: '100%', objectFit: 'cover', background: '#fff' }}
          />
        ) : null}
        <button
          onClick={onFav}
          title={fav ? tp('removeFromFavorites') : tp('addToFavorites')}
          aria-label={fav ? tp('removeFromFavorites') : tp('addToFavorites')}
          aria-pressed={fav}
          style={{
            position: 'absolute',
            top: '10px',
            left: '10px',
            zIndex: 2,
            width: '34px',
            height: '34px',
            borderRadius: '50%',
            background: 'rgba(255,255,255,.94)',
            border: '1px solid #E2DECF',
            color: fav ? '#A8503A' : '#9C9686',
            fontSize: '15px',
            lineHeight: 1,
            cursor: 'pointer',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            transition: 'color .2s ease, background .2s ease',
          }}
        >
          <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"
            fill={fav ? '#A8503A' : 'none'} stroke="currentColor" strokeWidth="2"
            strokeLinecap="round" strokeLinejoin="round">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
          </svg>
        </button>
        <button
          onClick={onQuick}
          title={tp('quickView')}
          style={{
            position: 'absolute',
            top: '10px',
            right: '10px',
            zIndex: 2,
            width: '34px',
            height: '34px',
            borderRadius: '50%',
            background: 'rgba(255,255,255,.94)',
            border: '1px solid #E2DECF',
            color: '#434343',
            fontSize: '14px',
            cursor: 'pointer',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            transition: 'background .2s ease, color .2s ease',
          }}
        >
          ⤢
        </button>
        {out || backorder ? (
          <span
            style={{
              position: 'absolute',
              bottom: '11px',
              left: '11px',
              zIndex: 2,
              fontFamily: "'Hanken Grotesk',sans-serif",
              fontSize: '10.5px',
              fontWeight: 600,
              color: out ? '#A8503A' : '#6E7585',
              background: 'rgba(255,255,255,.94)',
              border: out ? '1px solid rgba(168,80,58,.35)' : '1px solid #E2DECF',
              padding: '4px 9px',
              borderRadius: '999px',
            }}
          >
            {out ? tp('unavailableBadge') : tp('onOrderBadge')}
          </span>
        ) : null}
        {product.rpps_required ? (
          <span
            style={{
              position: 'absolute',
              bottom: '11px',
              right: '11px',
              zIndex: 2,
              fontFamily: "'Hanken Grotesk',sans-serif",
              fontSize: '9.5px',
              fontWeight: 700,
              letterSpacing: '.08em',
              textTransform: 'uppercase',
              color: '#A8503A',
              background: 'rgba(168,80,58,.1)',
              border: '1px solid rgba(168,80,58,.22)',
              padding: '4px 9px',
              borderRadius: '999px',
            }}
          >
            {tp('rppsBadge')}
          </span>
        ) : null}
      </div>
      <div style={{ padding: '15px 16px 17px', display: 'flex', flexDirection: 'column', gap: '7px', flex: 1 }}>
        {product.brand ? (
          <div
            style={{
              fontFamily: "'Hanken Grotesk',sans-serif",
              fontSize: '10.5px',
              fontWeight: 600,
              letterSpacing: '.1em',
              textTransform: 'uppercase',
              color: '#8A8170',
            }}
          >
            {product.brand}
          </div>
        ) : null}
        <Link
          href={href}
          onClick={(e) => e.stopPropagation()}
          style={{ fontFamily: "'Spectral',serif", fontSize: '16px', lineHeight: 1.28, color: '#34352F', flex: 1, minHeight: '41px' }}
        >
          {product.name}
        </Link>
        {product.rating ? (
          <div style={{ display: 'flex', alignItems: 'center', gap: '5px', marginTop: '1px' }}>
            <span style={{ display: 'inline-flex', gap: '1px' }} aria-label={`${product.rating.rate}/5`}>
              {[1, 2, 3, 4, 5].map((i) => (
                <svg key={i} width="13" height="13" viewBox="0 0 24 24" fill={i <= Math.round(product.rating!.rate) ? '#f5c518' : '#E2DECF'} stroke="none">
                  <path d="M12 3.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 17l-5.2 2.6 1-5.8L3.5 9.7l5.9-.9z" />
                </svg>
              ))}
            </span>
            <span style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '11.5px', color: '#8A8170' }}>({product.rating.count})</span>
          </div>
        ) : null}
        <div style={{ display: 'flex', alignItems: 'baseline', gap: '8px', marginTop: '3px' }}>
          <span style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontWeight: 700, fontSize: '19px', color: '#434343', letterSpacing: '-.01em' }}>
            {fmt(product.ht)} €
          </span>
          <span style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '10.5px', fontWeight: 700, color: '#8A8170', letterSpacing: '.06em' }}>
            {tc('exclTax')}
          </span>
        </div>
        <div style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '11.5px', color: '#9C9686' }}>
          {tc('inclTaxShort', { amount: fmt(product.ttc) })}
        </div>
        {canBuy ? (
          added ? (
            <button
              style={{
                marginTop: '11px',
                width: '100%',
                fontFamily: "'Hanken Grotesk',sans-serif",
                fontSize: '12.5px',
                fontWeight: 600,
                letterSpacing: '.02em',
                color: '#FFFFFF',
                background: 'linear-gradient(135deg,rgba(92,170,110,.95),rgba(63,114,86,.92))',
                border: '1px solid rgba(255,255,255,.4)',
                boxShadow: 'inset 0 1px 0 rgba(255,255,255,.5)',
                borderRadius: '999px',
                padding: '11px 12px',
                cursor: 'default',
              }}
            >
              {tp('addedToCart')}
            </button>
          ) : (
            <button
              onClick={onAdd}
              style={{
                marginTop: '11px',
                width: '100%',
                fontFamily: "'Hanken Grotesk',sans-serif",
                fontSize: '12.5px',
                fontWeight: 600,
                letterSpacing: '.02em',
                color: '#FFFFFF',
                background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))',
                border: '1px solid rgba(255,255,255,.42)',
                boxShadow: '0 10px 22px -12px rgba(116,176,51,.55), inset 0 1px 0 rgba(255,255,255,.55)',
                borderRadius: '999px',
                padding: '11px 12px',
                cursor: 'pointer',
                transition: 'filter .2s ease, transform .2s ease',
              }}
            >
              {tc('addToCart')}
            </button>
          )
        ) : (
          <button
            onClick={onNotify}
            disabled={notified}
            style={{
              marginTop: '11px',
              width: '100%',
              fontFamily: "'Hanken Grotesk',sans-serif",
              fontSize: '12.5px',
              fontWeight: 600,
              letterSpacing: '.02em',
              color: notified ? '#3F7256' : '#6E7585',
              background: 'rgba(242,240,234,.6)',
              border: '1px solid rgba(226,222,207,.8)',
              borderRadius: '999px',
              padding: '11px 12px',
              cursor: notified ? 'default' : 'pointer',
            }}
          >
            {notified ? tp('notifyRegistered') : tp('notifyOnReturn')}
          </button>
        )}
      </div>
    </div>
  );
}
