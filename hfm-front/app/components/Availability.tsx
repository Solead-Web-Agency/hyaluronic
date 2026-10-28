'use client';

import { useTranslations } from 'next-intl';
import type { StockStatus } from '@/lib/cardModel';
import { fmt, isVatFree } from '@/lib/cardModel';
import { deliveryKey, stockLabelKey } from '@/lib/delivery';

// Petits composants partagés par la carte, l'aperçu rapide, la fiche et le comparateur :
// mêmes statuts, même promesse de livraison, même ligne TVA partout.

const STATUS_COLOR: Record<StockStatus, { text: string; dot: string }> = {
  in: { text: '#3F7256', dot: '#5FA33C' },
  backorder: { text: '#B07B2A', dot: '#D89B3D' },
  out: { text: '#A8503A', dot: '#C0664E' },
};

/** « En stock » / « Sur commande — expédition sous 10 à 15 jours » / « Indisponible ». */
export function StockStatusLine({ status, short = false, size = 13 }: { status: StockStatus; short?: boolean; size?: number }) {
  const t = useTranslations('offer');
  const c = STATUS_COLOR[status];
  return (
    <span style={{ display: 'inline-flex', alignItems: 'center', gap: '7px', fontSize: `${size}px`, fontWeight: 600, color: c.text, lineHeight: 1.4 }}>
      <span aria-hidden="true" style={{ width: '7px', height: '7px', borderRadius: '50%', background: c.dot, flex: 'none' }} />
      {t(stockLabelKey(status, short))}
    </span>
  );
}

/**
 * Promesse de livraison selon le statut (rien si indisponible). `variant="pill"` : encadré.
 * `afterStatus` : affichée juste sous <StockStatusLine> -> rien pour « sur commande », dont le
 * libellé de statut porte déjà le délai (pas de doublon).
 */
export function DeliveryPromise({ status, variant = 'text', size = 12.5, afterStatus = false }: { status: StockStatus; variant?: 'text' | 'pill'; size?: number; afterStatus?: boolean }) {
  const t = useTranslations('offer');
  const key = deliveryKey(status);
  if (!key || (afterStatus && status === 'backorder')) return null;
  const truck = (
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true" style={{ flex: 'none', marginTop: '1px' }}>
      <path d="M1 9h13v8H1zM14 12h4l3 3v2h-7z" /><circle cx="6" cy="19" r="1.6" /><circle cx="17.5" cy="19" r="1.6" />
    </svg>
  );
  if (variant === 'pill') {
    const inStock = status === 'in';
    return (
      <div style={{ display: 'inline-flex', alignItems: 'flex-start', gap: '9px', padding: '9px 14px', background: inStock ? 'rgba(140,198,63,.1)' : 'rgba(216,155,61,.1)', border: inStock ? '1px solid rgba(140,198,63,.28)' : '1px solid rgba(216,155,61,.3)', borderRadius: '14px', fontSize: `${size}px`, fontWeight: 600, lineHeight: 1.45, color: inStock ? '#3F7256' : '#8A6020' }}>
        <span style={{ display: 'inline-flex', color: inStock ? '#5E8E1F' : '#B07B2A' }}>{truck}</span>
        <span>{t(key)}</span>
      </div>
    );
  }
  return (
    <span style={{ display: 'inline-flex', alignItems: 'flex-start', gap: '7px', fontSize: `${size}px`, lineHeight: 1.45, color: '#55606F' }}>
      {truck}
      <span>{t(key)}</span>
    </span>
  );
}

/** Ligne sous le prix HT : « soit X € TTC », ou « hors TVA » quand aucune TVA n'est facturée. */
export function TaxLine({ ht, ttc, style }: { ht: number; ttc: number; style?: React.CSSProperties }) {
  const tc = useTranslations('common');
  const t = useTranslations('offer');
  return <span style={style}>{isVatFree(ht, ttc) ? t('vatFree') : tc('inclTaxShort', { amount: fmt(ttc) })}</span>;
}
