'use client';

import { useEffect, useRef, useState } from 'react';
import { useTranslations } from 'next-intl';

// Pays européens concernés (UE + EEE + Suisse + Royaume-Uni) : rappel que les dispositifs
// médicaux sont réservés aux professionnels de santé. Affiché UNE fois par visiteur.
const EUROPE = new Set([
  'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT',
  'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', // UE
  'IS', 'LI', 'NO', // EEE
  'CH', 'GB', 'MC', 'AD', 'SM', 'VA',
]);
const STORAGE_KEY = 'hfm_pro_notice_ack';

function acknowledged(): boolean {
  try {
    return localStorage.getItem(STORAGE_KEY) === '1';
  } catch {
    return false;
  }
}

export default function ProNoticePopup() {
  const t = useTranslations('proNotice');
  const [open, setOpen] = useState(false);
  const btnRef = useRef<HTMLButtonElement>(null);

  useEffect(() => {
    if (acknowledged()) return;
    const force = new URLSearchParams(window.location.search).get('country');
    fetch(`/api/geo${force ? `?country=${encodeURIComponent(force)}` : ''}`)
      .then((r) => r.json())
      .then((d) => {
        if (d?.country && EUROPE.has(String(d.country))) setOpen(true);
      })
      .catch(() => {});
  }, []);

  useEffect(() => {
    if (!open) return;
    btnRef.current?.focus();
    const onKey = (e: KeyboardEvent) => {
      if (e.key === 'Escape') close();
    };
    window.addEventListener('keydown', onKey);
    return () => window.removeEventListener('keydown', onKey);
  }, [open]);

  const close = () => {
    try {
      localStorage.setItem(STORAGE_KEY, '1');
    } catch {
      /* stockage bloqué : le pop-up réapparaîtra, sans casser la page */
    }
    setOpen(false);
  };

  if (!open) return null;

  return (
    <div style={{ position: 'fixed', inset: 0, zIndex: 80, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '16px' }}>
      <div onClick={close} style={{ position: 'absolute', inset: 0, background: 'rgba(30,30,30,.55)' }} />
      <div
        role="dialog"
        aria-modal="true"
        aria-labelledby="hfm-pro-notice-title"
        aria-describedby="hfm-pro-notice-text"
        style={{ position: 'relative', width: '100%', maxWidth: '560px', background: '#fff', borderRadius: '14px', padding: '40px 32px 34px', textAlign: 'center', boxShadow: '0 30px 70px -30px rgba(30,40,15,.55)' }}
      >
        <button onClick={close} aria-label={t('close')} style={{ position: 'absolute', top: '14px', right: '16px', background: 'none', border: 'none', fontSize: '26px', lineHeight: 1, color: '#9A9A9A', cursor: 'pointer' }}>×</button>
        <div aria-hidden="true" style={{ width: '72px', height: '72px', borderRadius: '50%', background: 'rgba(140,198,63,.16)', color: '#5E8E1F', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto', fontFamily: "'Spectral',serif", fontStyle: 'italic', fontWeight: 700, fontSize: '34px' }}>i</div>
        <h2 id="hfm-pro-notice-title" style={{ fontFamily: "'Spectral',serif", fontWeight: 500, fontSize: '22px', letterSpacing: '.04em', textTransform: 'uppercase', color: '#3F6A14', margin: '22px 0 0' }}>{t('title')}</h2>
        <p id="hfm-pro-notice-text" style={{ fontSize: '16px', lineHeight: 1.55, color: '#34352F', margin: '16px auto 0', maxWidth: '440px' }}>{t('text')}</p>
        <button
          ref={btnRef}
          onClick={close}
          style={{ marginTop: '28px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '15px', fontWeight: 600, color: '#fff', background: '#4F7A1A', border: 'none', borderRadius: '999px', padding: '14px 32px', cursor: 'pointer', boxShadow: '0 12px 26px -12px rgba(79,122,26,.7)' }}
        >
          {t('ok')}
        </button>
      </div>
    </div>
  );
}
