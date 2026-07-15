'use client';

import { useEffect, useState } from 'react';
import { updateConsent } from '@/lib/gtm';

const KEY = 'hfm_consent';

// Bandeau de consentement (RGPD + Google Consent Mode v2). Par défaut le consentement
// est REFUSÉ (défini dans le layout) ; ce bandeau permet d'accepter/refuser et met à jour GTM.
export default function ConsentBanner() {
  const [show, setShow] = useState(false);

  useEffect(() => {
    const choice = localStorage.getItem(KEY);
    if (choice === 'granted') updateConsent(true);
    else if (choice === 'denied') updateConsent(false);
    else setShow(true); // pas encore de choix -> on affiche le bandeau
  }, []);

  if (!show) return null;

  const decide = (granted: boolean) => {
    localStorage.setItem(KEY, granted ? 'granted' : 'denied');
    updateConsent(granted);
    setShow(false);
  };

  return (
    <div
      role="dialog"
      aria-label="Consentement aux cookies"
      style={{
        position: 'fixed', left: 16, right: 16, bottom: 16, zIndex: 1000, maxWidth: '720px', margin: '0 auto',
        background: '#fff', border: '1px solid #E7E3DA', borderRadius: '14px',
        boxShadow: '0 24px 60px -24px rgba(40,50,25,.45)', padding: '20px 22px',
        display: 'flex', flexWrap: 'wrap', alignItems: 'center', gap: '16px',
        fontFamily: "'Hanken Grotesk',sans-serif",
      }}
    >
      <div style={{ flex: '1 1 320px', fontSize: '13.5px', lineHeight: 1.55, color: '#4A4A44' }}>
        Nous utilisons des cookies de mesure d’audience et de personnalisation publicitaire pour améliorer votre
        expérience. Vous pouvez accepter ou refuser. Voir notre{' '}
        <a href="/fr/content/politique-de-confidentialite" style={{ color: '#5E8E1F', fontWeight: 600 }}>politique de confidentialité</a>.
      </div>
      <div style={{ display: 'flex', gap: '10px', flex: 'none' }}>
        <button
          type="button"
          onClick={() => decide(false)}
          style={{ padding: '11px 18px', borderRadius: '999px', border: '1px solid #E7E3DA', background: '#fff', color: '#5E6152', fontSize: '13.5px', fontWeight: 600, cursor: 'pointer' }}
        >
          Refuser
        </button>
        <button
          type="button"
          onClick={() => decide(true)}
          style={{ padding: '11px 20px', borderRadius: '999px', border: '1px solid rgba(255,255,255,.42)', background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))', color: '#fff', fontSize: '13.5px', fontWeight: 600, cursor: 'pointer' }}
        >
          Accepter
        </button>
      </div>
    </div>
  );
}
