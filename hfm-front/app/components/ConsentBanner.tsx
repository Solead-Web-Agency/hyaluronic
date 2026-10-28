'use client';

import { useEffect, useState } from 'react';
import { useTranslations } from 'next-intl';
import { Link } from '@/i18n/navigation';
import { updateConsent } from '@/lib/gtm';

const KEY = 'hfm_consent';
// Évènement global pour rouvrir le bandeau (retrait/modification du consentement depuis le footer).
export const CONSENT_EVENT = 'hfm:manage-consent';
// Émis quand le visiteur a fait son choix : la fenêtre « Information professionnelle » attend ce
// choix pour s'afficher (une seule fenêtre à la fois à la première visite).
export const CONSENT_DECIDED_EVENT = 'hfm:consent-decided';

function readChoice(): string | null {
  try {
    return localStorage.getItem(KEY);
  } catch {
    return null;
  }
}

/** Le visiteur a-t-il déjà accepté ou refusé les cookies ? */
export function hasConsentChoice(): boolean {
  const c = readChoice();
  return c === 'granted' || c === 'denied';
}

// Bandeau de consentement (RGPD + Google Consent Mode v2). Par défaut le consentement
// est REFUSÉ (défini dans le layout) ; ce bandeau permet d'accepter/refuser et met à jour GTM.
export default function ConsentBanner() {
  const t = useTranslations('consent');
  const [show, setShow] = useState(false);

  useEffect(() => {
    const choice = readChoice();
    if (choice === 'granted') updateConsent(true);
    else if (choice === 'denied') updateConsent(false);
    else setShow(true); // pas encore de choix -> on affiche le bandeau
  }, []);

  // Retrait du consentement : le lien « Gérer les cookies » du footer rouvre le bandeau.
  useEffect(() => {
    const open = () => setShow(true);
    window.addEventListener(CONSENT_EVENT, open);
    return () => window.removeEventListener(CONSENT_EVENT, open);
  }, []);

  if (!show) return null;

  const decide = (granted: boolean) => {
    try {
      localStorage.setItem(KEY, granted ? 'granted' : 'denied');
    } catch {
      /* stockage bloqué : le choix vaut pour cette page */
    }
    updateConsent(granted);
    setShow(false);
    window.dispatchEvent(new Event(CONSENT_DECIDED_EVENT));
  };

  return (
    <div
      role="dialog"
      aria-label={t('label')}
      style={{
        position: 'fixed', left: 16, right: 16, bottom: 16, zIndex: 1000, maxWidth: '720px', margin: '0 auto',
        background: '#fff', border: '1px solid #E7E3DA', borderRadius: '14px',
        boxShadow: '0 24px 60px -24px rgba(40,50,25,.45)', padding: '20px 22px',
        display: 'flex', flexWrap: 'wrap', alignItems: 'center', gap: '16px',
        fontFamily: "'Hanken Grotesk',sans-serif",
      }}
    >
      <div style={{ flex: '1 1 320px', fontSize: '13.5px', lineHeight: 1.55, color: '#4A4A44' }}>
        {t.rich('text', { a: (c) => <Link href="/content/politique-de-confidentialite" style={{ color: '#5E8E1F', fontWeight: 600 }}>{c}</Link> })}
      </div>
      <div style={{ display: 'flex', gap: '10px', flex: 'none' }}>
        <button
          type="button"
          onClick={() => decide(false)}
          style={{ padding: '11px 18px', borderRadius: '999px', border: '1px solid #E7E3DA', background: '#fff', color: '#5E6152', fontSize: '13.5px', fontWeight: 600, cursor: 'pointer' }}
        >
          {t('refuse')}
        </button>
        <button
          type="button"
          onClick={() => decide(true)}
          style={{ padding: '11px 20px', borderRadius: '999px', border: '1px solid rgba(255,255,255,.42)', background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))', color: '#fff', fontSize: '13.5px', fontWeight: 600, cursor: 'pointer' }}
        >
          {t('accept')}
        </button>
      </div>
    </div>
  );
}
