'use client';

import { useState } from 'react';
import { useTranslations, useLocale } from 'next-intl';
import { useSearchParams } from 'next/navigation';
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

// Codes d'erreur remontés par le bridge (traduits via password.reset.errors.*).
const KNOWN_ERRORS = new Set(['invalid_or_expired_token', 'password_too_short', 'missing_fields', 'reset_failed']);

export default function ResetPasswordClient() {
  const t = useTranslations('password');
  const tc = useTranslations('common');
  const locale = useLocale();
  const search = useSearchParams();

  // Jeton + identité portés par l'URL de l'e-mail natif PS (id_customer + reset_token).
  const idCustomer = Number(search.get('id_customer') || 0);
  const resetToken = search.get('reset_token') || '';
  const linkOk = idCustomer > 0 && resetToken !== '';

  const [password, setPassword] = useState('');
  const [confirm, setConfirm] = useState('');
  const [busy, setBusy] = useState(false);
  const [done, setDone] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const mapError = (e?: string) => (e && KNOWN_ERRORS.has(e) ? t(`reset.errors.${e}`) : t('reset.errors.generic'));

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    if (password.length < 8) {
      setError(t('reset.errors.password_too_short'));
      return;
    }
    if (password !== confirm) {
      setError(t('reset.errors.password_mismatch'));
      return;
    }
    setBusy(true);
    try {
      const r = await fetch('/api/auth', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'reset-password', id_customer: idCustomer, reset_token: resetToken, password }),
      });
      const d = await r.json().catch(() => ({}));
      if (!r.ok || !d.ok) {
        setError(mapError(d.error));
        return;
      }
      // Succès : la session est ouverte (cookie posé côté serveur). On force un rechargement complet
      // vers le compte pour que le store relise /api/auth et reflète l'état connecté.
      setDone(true);
      setTimeout(() => { window.location.assign(`/${locale}/compte`); }, 1400);
    } catch {
      setError(t('reset.errors.network'));
    } finally {
      setBusy(false);
    }
  };

  return (
    <main data-screen-label="Réinitialiser le mot de passe" className="hfm-wrap" style={{ maxWidth: '480px', margin: '0 auto', padding: '46px 28px 80px' }}>
      <div style={{ fontSize: '12.5px', color: '#9A9A9A', marginBottom: '18px' }}>
        <Link href="/" style={{ cursor: 'pointer' }}>{tc('home')}</Link>  /  <Link href="/compte" style={{ cursor: 'pointer' }}>{t('reset.breadcrumbAccount')}</Link>  /  {t('reset.breadcrumb')}
      </div>
      <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: '34px', color: '#2B2B2B', margin: '0 0 8px' }}>{t('reset.title')}</h1>

      {!linkOk ? (
        <div style={cardStyle}>
          <div style={{ fontSize: '14px', color: '#A8503A', lineHeight: 1.6 }}>{t('reset.invalidLink')}</div>
          <div style={{ marginTop: '20px' }}>
            <Link href="/mot-de-passe-oublie" style={{ fontSize: '13.5px', fontWeight: 600, color: '#5E8E1F', textDecoration: 'none' }}>{t('reset.requestNew')}</Link>
          </div>
        </div>
      ) : done ? (
        <div style={cardStyle}>
          <div style={{ fontSize: '14px', color: '#5E8E1F', lineHeight: 1.6 }}>{t('reset.success')}</div>
          <div style={{ marginTop: '20px' }}>
            <a href={`/${locale}/compte`} style={{ fontSize: '13.5px', fontWeight: 600, color: '#5E8E1F', textDecoration: 'none' }}>{t('reset.goToAccount')}</a>
          </div>
        </div>
      ) : (
        <>
          <p style={{ fontSize: '14px', color: '#6E7585', margin: '0 0 22px', lineHeight: 1.6 }}>{t('reset.lead')}</p>
          <div style={cardStyle}>
            <form onSubmit={submit} style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
              <div>
                <label style={labelStyle}>{t('reset.password')}</label>
                <input type="password" value={password} onChange={(e) => setPassword(e.target.value)} placeholder={t('reset.passwordPlaceholder')} style={inputStyle} required />
              </div>
              <div>
                <label style={labelStyle}>{t('reset.confirm')}</label>
                <input type="password" value={confirm} onChange={(e) => setConfirm(e.target.value)} placeholder={t('reset.passwordPlaceholder')} style={inputStyle} required />
              </div>
              {error ? <div style={{ fontSize: '13px', color: '#A8503A' }}>{error}</div> : null}
              <button type="submit" disabled={busy} style={{ ...primaryBtn, opacity: busy ? 0.7 : 1 }}>{busy ? t('reset.saving') : t('reset.submit')}</button>
            </form>
          </div>
        </>
      )}
    </main>
  );
}
