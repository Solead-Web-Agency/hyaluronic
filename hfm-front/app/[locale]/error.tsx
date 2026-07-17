'use client';

// Frontière d'erreur du segment [locale] : capture toute panne de rendu serveur des pages
// (ex. bridge PrestaShop indisponible -> `bridgeGet` jette). Le rendu de cette frontière renvoie
// un vrai 5xx sur le document initial (et NON un 404), ce qui évite la désindexation d'une page
// saine sur un incident transitoire. Autonome (aucun appel bridge) pour ne jamais re-jeter.
import { useEffect } from 'react';
import { useTranslations } from 'next-intl';
import { Link } from '@/i18n/navigation';

const PAGE: React.CSSProperties = {
  fontFamily: "'Hanken Grotesk',sans-serif", color: '#34352F',
  background:
    'radial-gradient(1100px 560px at 82% -6%, rgba(140,198,63,0.13), transparent 58%), radial-gradient(820px 520px at -8% 14%, rgba(95,184,154,0.10), transparent 55%), radial-gradient(700px 600px at 50% 118%, rgba(140,198,63,0.08), transparent 60%), #F3F4EF',
  minHeight: '100vh', display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center',
  textAlign: 'center', padding: '48px 28px',
};

const PRIMARY: React.CSSProperties = {
  fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14.5px', fontWeight: 600, color: '#fff',
  background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))',
  border: '1px solid rgba(255,255,255,.42)', boxShadow: '0 12px 26px -10px rgba(116,176,51,.55)',
  borderRadius: '999px', padding: '15px 28px', cursor: 'pointer', textDecoration: 'none', display: 'inline-block',
};

const SECONDARY: React.CSSProperties = {
  fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14.5px', fontWeight: 600, color: '#5E8E1F',
  background: 'rgba(140,198,63,0.08)', border: '1px solid rgba(155,209,89,.7)',
  borderRadius: '999px', padding: '15px 28px', cursor: 'pointer', textDecoration: 'none', display: 'inline-block',
};

export default function LocaleError({
  error,
  reset,
  unstable_retry,
}: {
  error: Error & { digest?: string };
  reset: () => void;
  // Next 16.2 : re-fetch + re-rendu du segment (recouvre une panne transitoire du bridge).
  // On garde `reset` en repli si l'API venait à changer.
  unstable_retry?: () => void;
}) {
  const t = useTranslations('error');

  useEffect(() => {
    // Journalisé côté client uniquement (le détail serveur reste dans les logs du bridge/Next).
    console.error(error);
  }, [error]);

  const retry = unstable_retry ?? reset;

  return (
    <main style={PAGE}>
      <div style={{ maxWidth: '560px' }}>
        <div style={{ fontFamily: "'Spectral',serif", fontSize: '20px', color: '#2B2B2B', letterSpacing: '.02em' }}>
          Hyaluronic <span style={{ fontStyle: 'italic', color: '#5E8E1F' }}>Filler Market</span>
        </div>
        <div style={{ fontSize: '11.5px', fontWeight: 700, letterSpacing: '.13em', textTransform: 'uppercase', color: '#8CC63F', marginTop: '34px' }}>
          {t('eyebrow')}
        </div>
        <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: 'clamp(28px,5vw,42px)', lineHeight: 1.1, color: '#2B2B2B', margin: '10px 0 0' }}>
          {t('title')}
        </h1>
        <p style={{ fontSize: '16px', lineHeight: 1.6, color: '#55606F', margin: '18px auto 0', maxWidth: '460px' }}>
          {t('lead')}
        </p>
        <div style={{ display: 'flex', gap: '14px', marginTop: '32px', flexWrap: 'wrap', justifyContent: 'center' }}>
          <button type="button" onClick={() => retry?.()} style={PRIMARY}>{t('retry')}</button>
          <Link href="/" style={SECONDARY}>{t('home')}</Link>
        </div>
        {error.digest ? (
          <div style={{ fontSize: '11.5px', color: '#9A9A9A', marginTop: '26px' }}>
            {t('reference', { digest: error.digest })}
          </div>
        ) : null}
      </div>
    </main>
  );
}
