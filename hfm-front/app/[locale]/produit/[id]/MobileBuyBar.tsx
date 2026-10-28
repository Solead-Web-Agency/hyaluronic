'use client';

import { useTranslations } from 'next-intl';
import { fmt } from '@/lib/cardModel';
import { TaxLine } from '../../../components/Availability';

// Barre d'achat fixe en bas de la fiche, MOBILE uniquement (parité ancien site) : prix HT,
// TTC en petit, bouton d'ajout qui suit le statut de stock. Les règles CSS ne vivent que tant
// que la fiche est montée : réserve d'espace sous le contenu (body) et barre du comparateur
// remontée au-dessus de celle-ci.
const BAR_H = 68;

// `canBuy` vient de la fiche (statut de stock unique + pays) : en stock ou sur commande -> actif.
export default function MobileBuyBar({ ht, ttc, canBuy, onAdd }: {
  ht: number;
  ttc: number;
  canBuy: boolean;
  onAdd: () => void;
}) {
  const tc = useTranslations('common');
  const to = useTranslations('offer');

  return (
    <>
      <style>{`
        .hfm-buybar{ display:none; }
        @media (max-width:760px){
          .hfm-buybar{ display:flex; }
          body{ padding-bottom:calc(${BAR_H}px + env(safe-area-inset-bottom)); }
          .hfm-comparebar{ bottom:calc(${BAR_H + 12}px + env(safe-area-inset-bottom)) !important; }
        }
      `}</style>
      <div
        className="hfm-buybar"
        role="region"
        aria-label={tc('addToCart')}
        style={{
          position: 'fixed', left: 0, right: 0, bottom: 0, zIndex: 50,
          alignItems: 'center', gap: '12px',
          minHeight: `${BAR_H}px`,
          padding: '10px 15px calc(10px + env(safe-area-inset-bottom))',
          background: 'rgba(255,255,255,.97)', borderTop: '1px solid #E7E3DA',
          boxShadow: '0 -14px 30px -18px rgba(30,40,15,.35)',
          fontFamily: "'Hanken Grotesk',sans-serif",
        }}
      >
        <div style={{ flex: 'none', minWidth: 0, lineHeight: 1.2 }}>
          <div style={{ whiteSpace: 'nowrap' }}>
            <span style={{ fontWeight: 700, fontSize: '18px', color: '#2B2B2B' }}>{fmt(ht)} €</span>
            <span style={{ fontSize: '11px', fontWeight: 700, color: '#8A8170', marginLeft: '5px', letterSpacing: '.04em' }}>{tc('exclTax')}</span>
          </div>
          <TaxLine ht={ht} ttc={ttc} style={{ display: 'block', fontSize: '11.5px', color: '#8A8170', marginTop: '2px', whiteSpace: 'nowrap' }} />
        </div>
        {canBuy ? (
          <button
            type="button"
            onClick={onAdd}
            style={{ flex: 1, minWidth: 0, height: '46px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14.5px', fontWeight: 600, color: '#fff', background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))', border: '1px solid rgba(255,255,255,.42)', boxShadow: '0 10px 22px -12px rgba(116,176,51,.55)', borderRadius: '999px', cursor: 'pointer' }}
          >
            {tc('addToCart')}
          </button>
        ) : (
          <button
            type="button"
            disabled
            style={{ flex: 1, minWidth: 0, height: '46px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14px', fontWeight: 600, color: '#6E7585', background: 'rgba(242,240,234,.9)', border: '1px solid rgba(226,222,207,.9)', borderRadius: '999px', cursor: 'default' }}
          >
            {to('unavailable')}
          </button>
        )}
      </div>
    </>
  );
}
