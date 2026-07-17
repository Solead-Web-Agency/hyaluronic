'use client';

import { useState } from 'react';
import { useTranslations, useLocale } from 'next-intl';
import { Link } from '@/i18n/navigation';

const inputStyle: React.CSSProperties = {
  height: '46px',
  padding: '0 14px',
  border: '1px solid #E2DECF',
  borderRadius: '6px',
  fontFamily: "'Hanken Grotesk',sans-serif",
  fontSize: '14px',
  outline: 'none',
  background: '#fff',
  width: '100%',
  boxSizing: 'border-box',
  color: '#34352F',
};

const labelStyle: React.CSSProperties = {
  fontSize: '11px',
  fontWeight: 700,
  letterSpacing: '.08em',
  textTransform: 'uppercase',
  color: '#8A8170',
  marginBottom: '6px',
  display: 'block',
};

const primaryBtn: React.CSSProperties = {
  height: '50px',
  borderRadius: '999px',
  color: '#fff',
  background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))',
  border: '1px solid rgba(255,255,255,.42)',
  boxShadow: '0 12px 26px -10px rgba(116,176,51,.55)',
  fontFamily: "'Hanken Grotesk',sans-serif",
  fontSize: '15px',
  fontWeight: 600,
  cursor: 'pointer',
  width: '100%',
};

const cardStyle: React.CSSProperties = {
  background: '#fff',
  border: '1px solid #ECEAE3',
  borderRadius: '12px',
  padding: '28px',
  boxShadow: '0 18px 40px -30px rgba(40,50,25,.4)',
};

export default function ForgotPasswordClient() {
  const t = useTranslations('password');
  const tc = useTranslations('common');
  const locale = useLocale();

  const [email, setEmail] = useState('');
  const [busy, setBusy] = useState(false);
  const [sent, setSent] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setBusy(true);
    try {
      const r = await fetch('/api/auth', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'forgot-password', email, locale }),
      });
      if (!r.ok) {
        const d = await r.json().catch(() => ({}));
        // Seul cas d'erreur possible : format d'e-mail invalide (non-oracle).
        setError(t(d.error === 'invalid_email' ? 'forgot.errors.invalid_email' : 'forgot.errors.generic'));
        return;
      }
      // Réponse NON-ORACLE : message identique que le compte existe ou non.
      setSent(true);
    } catch {
      setError(t('forgot.errors.network'));
    } finally {
      setBusy(false);
    }
  };

  return (
    <main data-screen-label="Mot de passe oublié" className="hfm-wrap" style={{ maxWidth: '480px', margin: '0 auto', padding: '46px 28px 80px' }}>
      <div style={{ fontSize: '12.5px', color: '#9A9A9A', marginBottom: '18px' }}>
        <Link href="/" style={{ cursor: 'pointer' }}>{tc('home')}</Link>  /  <Link href="/compte" style={{ cursor: 'pointer' }}>{t('forgot.breadcrumbAccount')}</Link>  /  {t('forgot.breadcrumb')}
      </div>
      <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: '34px', color: '#2B2B2B', margin: '0 0 8px' }}>{t('forgot.title')}</h1>
      <p style={{ fontSize: '14px', color: '#6E7585', margin: '0 0 22px', lineHeight: 1.6 }}>{t('forgot.lead')}</p>

      <div style={cardStyle}>
        {sent ? (
          <div>
            <div style={{ fontSize: '14px', color: '#5E8E1F', lineHeight: 1.6 }}>{t('forgot.sent')}</div>
            <div style={{ marginTop: '20px' }}>
              <Link href="/compte" style={{ fontSize: '13.5px', fontWeight: 600, color: '#5E8E1F', textDecoration: 'none' }}>{t('forgot.backToLogin')}</Link>
            </div>
          </div>
        ) : (
          <form onSubmit={submit} style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
            <div>
              <label style={labelStyle}>{t('forgot.email')}</label>
              <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} placeholder={t('forgot.emailPlaceholder')} style={inputStyle} required />
            </div>
            {error ? <div style={{ fontSize: '13px', color: '#A8503A' }}>{error}</div> : null}
            <button type="submit" disabled={busy} style={{ ...primaryBtn, opacity: busy ? 0.7 : 1 }}>{busy ? t('forgot.sending') : t('forgot.submit')}</button>
            <div style={{ textAlign: 'center' }}>
              <Link href="/compte" style={{ fontSize: '13px', color: '#8A8170', textDecoration: 'none' }}>{t('forgot.backToLogin')}</Link>
            </div>
          </form>
        )}
      </div>
    </main>
  );
}
