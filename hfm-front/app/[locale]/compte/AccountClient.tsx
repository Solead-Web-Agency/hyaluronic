'use client';

import { useEffect, useState } from 'react';
import { useTranslations } from 'next-intl';
import { useRouter } from '@/i18n/navigation';
import { useSearchParams } from 'next/navigation';
import { Link } from '@/i18n/navigation';
import { useStore } from '../../store';
import AddressForm, { type Address } from '../../components/AddressForm';

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

const KNOWN_ERROR_CODES = new Set([
  'bad_credentials',
  'auth_failed',
  'email_already_exists',
  'register_failed',
  'invalid_name',
  'invalid_email',
  'password_too_short',
]);

const navLinkStyle: React.CSSProperties = {
  display: 'inline-flex',
  alignItems: 'center',
  gap: '8px',
  background: '#fff',
  border: '1px solid #E2DECF',
  borderRadius: '999px',
  padding: '10px 18px',
  fontFamily: "'Hanken Grotesk',sans-serif",
  fontSize: '13.5px',
  fontWeight: 600,
  color: '#5E8E1F',
  cursor: 'pointer',
  textDecoration: 'none',
};

export default function AccountClient() {
  const t = useTranslations('account');
  const tc = useTranslations('common');
  const mapError = (e?: string) => {
    if (!e) return t('errors.generic');
    if (KNOWN_ERROR_CODES.has(e)) return t(`errors.${e}`);
    return e;
  };
  const { customer, authReady, login, register, logout } = useStore();
  const router = useRouter();
  const search = useSearchParams();
  const next = search.get('next');

  const [tab, setTab] = useState<'login' | 'register'>('login');
  // login
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  // register
  const [rFirst, setRFirst] = useState('');
  const [rLast, setRLast] = useState('');
  const [rEmail, setREmail] = useState('');
  const [rPassword, setRPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [busy, setBusy] = useState(false);

  const [addresses, setAddresses] = useState<Address[]>([]);
  const [showAddrForm, setShowAddrForm] = useState(false);

  // Profil affiché (peut être mis à jour localement après "Modifier mon profil"
  // car le store n'expose pas de setter sur le customer).
  const [profile, setProfile] = useState<{ firstname: string; lastname: string; email: string } | null>(null);
  const [pFirst, setPFirst] = useState('');
  const [pLast, setPLast] = useState('');
  const [pEmail, setPEmail] = useState('');
  const [pPassword, setPPassword] = useState('');
  const [showProfileForm, setShowProfileForm] = useState(false);
  const [profileBusy, setProfileBusy] = useState(false);
  const [profileError, setProfileError] = useState<string | null>(null);
  const [profileOk, setProfileOk] = useState(false);

  useEffect(() => {
    if (customer) {
      setProfile({ firstname: customer.firstname, lastname: customer.lastname, email: customer.email });
      setPFirst(customer.firstname);
      setPLast(customer.lastname);
      setPEmail(customer.email);
    }
  }, [customer]);

  const saveProfile = async (e: React.FormEvent) => {
    e.preventDefault();
    setProfileError(null);
    setProfileOk(false);
    setProfileBusy(true);
    try {
      const r = await fetch('/api/customer', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'update',
          firstname: pFirst,
          lastname: pLast,
          email: pEmail,
          ...(pPassword ? { password: pPassword } : {}),
        }),
      });
      const d = await r.json();
      if (!r.ok || !d.updated) {
        setProfileError(mapError(d.error));
        return;
      }
      const c = d.customer ?? {};
      setProfile({
        firstname: c.firstname ?? pFirst,
        lastname: c.lastname ?? pLast,
        email: c.email ?? pEmail,
      });
      setPPassword('');
      setProfileOk(true);
      setShowProfileForm(false);
    } catch {
      setProfileError(t('errors.network'));
    } finally {
      setProfileBusy(false);
    }
  };

  const loadAddresses = () => {
    fetch('/api/customer?action=addresses')
      .then((r) => (r.ok ? r.json() : { addresses: [] }))
      .then((d) => setAddresses(d.addresses ?? []))
      .catch(() => {});
  };

  useEffect(() => {
    if (customer) loadAddresses();
  }, [customer]);

  // Redirige vers ?next après connexion réussie.
  const afterAuth = () => {
    if (next) router.push(next);
  };

  const doLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setBusy(true);
    const res = await login(email, password);
    setBusy(false);
    if (!res.ok) setError(mapError(res.error));
    else afterAuth();
  };

  const doRegister = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setBusy(true);
    const res = await register({ email: rEmail, password: rPassword, firstname: rFirst, lastname: rLast });
    setBusy(false);
    if (!res.ok) setError(mapError(res.error));
    else afterAuth();
  };

  // --- État de chargement de l'auth ---
  if (!authReady) {
    return (
      <main className="hfm-wrap" style={{ maxWidth: '1100px', margin: '0 auto', padding: '60px 28px', textAlign: 'center', color: '#8A8170' }}>
        {tc('loading')}
      </main>
    );
  }

  // --- NON connecté : carte Connexion / Inscription ---
  // Un invité (is_guest) n'a pas de vrai compte → on lui présente la connexion / création, pas le tableau de bord.
  if (!customer || customer.is_guest) {
    return (
      <main data-screen-label="Compte" className="hfm-wrap" style={{ maxWidth: '480px', margin: '0 auto', padding: '46px 28px 80px' }}>
        <div style={{ fontSize: '12.5px', color: '#9A9A9A', marginBottom: '18px' }}><Link href="/" style={{ cursor: 'pointer' }}>{tc('home')}</Link>  /  {t('breadcrumb')}</div>
        <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: '34px', color: '#2B2B2B', margin: '0 0 8px' }}>{t('title')}</h1>
        {next ? <p style={{ fontSize: '14px', color: '#6E7585', margin: '0 0 22px' }}>{t('loginToContinue')}</p> : <div style={{ marginBottom: '22px' }} />}
        <div style={cardStyle}>
          <div style={{ display: 'flex', gap: '6px', background: '#F3F4EF', borderRadius: '999px', padding: '5px', marginBottom: '24px' }}>
            {(['login', 'register'] as const).map((tabKey) => (
              <button
                key={tabKey}
                onClick={() => { setTab(tabKey); setError(null); }}
                style={{
                  flex: 1,
                  height: '40px',
                  borderRadius: '999px',
                  border: 'none',
                  cursor: 'pointer',
                  fontFamily: "'Hanken Grotesk',sans-serif",
                  fontSize: '13.5px',
                  fontWeight: 600,
                  color: tab === tabKey ? '#fff' : '#55606F',
                  background: tab === tabKey ? '#434343' : 'transparent',
                  transition: 'background .2s ease',
                }}
              >
                {tabKey === 'login' ? t('tabLogin') : t('tabRegister')}
              </button>
            ))}
          </div>

          {tab === 'login' ? (
            <form onSubmit={doLogin} style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
              <div>
                <label style={labelStyle}>{t('email')}</label>
                <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} placeholder={t('emailPlaceholder')} style={inputStyle} />
              </div>
              <div>
                <label style={labelStyle}>{t('password')}</label>
                <input type="password" value={password} onChange={(e) => setPassword(e.target.value)} placeholder={t('passwordPlaceholder')} style={inputStyle} />
              </div>
              {error ? <div style={{ fontSize: '13px', color: '#A8503A' }}>{error}</div> : null}
              <button type="submit" disabled={busy} style={{ ...primaryBtn, opacity: busy ? 0.7 : 1 }}>{busy ? t('loggingIn') : t('signIn')}</button>
              <div style={{ textAlign: 'center' }}>
                <Link href="/mot-de-passe-oublie" style={{ fontSize: '13px', color: '#8A8170', textDecoration: 'none' }}>{t('forgotPassword')}</Link>
              </div>
            </form>
          ) : (
            <form onSubmit={doRegister} style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
                <div>
                  <label style={labelStyle}>{t('firstname')}</label>
                  <input value={rFirst} onChange={(e) => setRFirst(e.target.value)} style={inputStyle} />
                </div>
                <div>
                  <label style={labelStyle}>{t('lastname')}</label>
                  <input value={rLast} onChange={(e) => setRLast(e.target.value)} style={inputStyle} />
                </div>
              </div>
              <div>
                <label style={labelStyle}>{t('email')}</label>
                <input type="email" value={rEmail} onChange={(e) => setREmail(e.target.value)} placeholder={t('emailPlaceholder')} style={inputStyle} />
              </div>
              <div>
                <label style={labelStyle}>{t('password')}</label>
                <input type="password" value={rPassword} onChange={(e) => setRPassword(e.target.value)} placeholder={t('passwordMin')} style={inputStyle} />
              </div>
              {error ? <div style={{ fontSize: '13px', color: '#A8503A' }}>{error}</div> : null}
              <button type="submit" disabled={busy} style={{ ...primaryBtn, opacity: busy ? 0.7 : 1 }}>{busy ? t('creating') : t('createAccount')}</button>
            </form>
          )}
        </div>
      </main>
    );
  }

  // --- Connecté : profil + adresses ---
  return (
    <main data-screen-label="Compte" className="hfm-wrap" style={{ maxWidth: '880px', margin: '0 auto', padding: '46px 28px 80px' }}>
      <div style={{ fontSize: '12.5px', color: '#9A9A9A', marginBottom: '18px' }}><Link href="/" style={{ cursor: 'pointer' }}>{tc('home')}</Link>  /  {t('breadcrumb')}</div>
      <div style={{ display: 'flex', alignItems: 'flex-end', justifyContent: 'space-between', flexWrap: 'wrap', gap: '14px' }}>
        <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: '34px', color: '#2B2B2B', margin: 0 }}>{t('title')}</h1>
        <button onClick={() => logout()} style={{ display: 'flex', alignItems: 'center', gap: '8px', background: '#fff', border: '1px solid #E2DECF', borderRadius: '999px', padding: '10px 18px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '13.5px', fontWeight: 600, color: '#A8503A', cursor: 'pointer' }}>{tc('logout')}</button>
      </div>

      <div style={{ display: 'flex', gap: '10px', flexWrap: 'wrap', marginTop: '22px' }}>
        <Link href="/compte/commandes" style={navLinkStyle}>{t('myOrders')}</Link>
        <Link href="/favoris" style={navLinkStyle}>{tc('favorites')}</Link>
      </div>

      <div style={{ ...cardStyle, marginTop: '20px' }}>
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '14px' }}>
          <div style={{ fontFamily: "'Spectral',serif", fontSize: '19px', color: '#2B2B2B' }}>{t('profile')}</div>
          <button onClick={() => { setShowProfileForm((o) => !o); setProfileError(null); setProfileOk(false); }} style={{ background: 'rgba(140,198,63,0.1)', border: '1px solid rgba(155,209,89,.7)', borderRadius: '999px', padding: '9px 16px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '13px', fontWeight: 600, color: '#5E8E1F', cursor: 'pointer' }}>{showProfileForm ? t('close') : t('editProfile')}</button>
        </div>
        <div style={{ fontSize: '15px', color: '#34352F', fontWeight: 600 }}>{profile?.firstname} {profile?.lastname}</div>
        <div style={{ fontSize: '14px', color: '#6E7585', marginTop: '4px' }}>{profile?.email}</div>
        {profileOk && !showProfileForm ? <div style={{ fontSize: '13px', color: '#5E8E1F', marginTop: '10px' }}>{t('profileUpdated')}</div> : null}

        {showProfileForm ? (
          <form onSubmit={saveProfile} style={{ marginTop: '22px', borderTop: '1px solid #ECEAE3', paddingTop: '22px', display: 'flex', flexDirection: 'column', gap: '16px' }}>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
              <div>
                <label style={labelStyle}>{t('firstname')}</label>
                <input value={pFirst} onChange={(e) => setPFirst(e.target.value)} style={inputStyle} />
              </div>
              <div>
                <label style={labelStyle}>{t('lastname')}</label>
                <input value={pLast} onChange={(e) => setPLast(e.target.value)} style={inputStyle} />
              </div>
            </div>
            <div>
              <label style={labelStyle}>{t('email')}</label>
              <input type="email" value={pEmail} onChange={(e) => setPEmail(e.target.value)} style={inputStyle} />
            </div>
            <div>
              <label style={labelStyle}>{t('newPasswordOptional')}</label>
              <input type="password" value={pPassword} onChange={(e) => setPPassword(e.target.value)} placeholder={t('newPasswordPlaceholder')} style={inputStyle} />
            </div>
            {profileError ? <div style={{ fontSize: '13px', color: '#A8503A' }}>{profileError}</div> : null}
            <button type="submit" disabled={profileBusy} style={{ ...primaryBtn, opacity: profileBusy ? 0.7 : 1 }}>{profileBusy ? t('saving') : t('save')}</button>
          </form>
        ) : null}
      </div>

      <div style={{ ...cardStyle, marginTop: '20px' }}>
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '16px' }}>
          <div style={{ fontFamily: "'Spectral',serif", fontSize: '19px', color: '#2B2B2B' }}>{t('myAddresses')}</div>
          <button onClick={() => setShowAddrForm((o) => !o)} style={{ background: 'rgba(140,198,63,0.1)', border: '1px solid rgba(155,209,89,.7)', borderRadius: '999px', padding: '9px 16px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '13px', fontWeight: 600, color: '#5E8E1F', cursor: 'pointer' }}>{showAddrForm ? t('close') : t('addAddress')}</button>
        </div>

        {addresses.length ? (
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit,minmax(220px,1fr))', gap: '12px' }}>
            {addresses.map((a) => (
              <div key={a.id_address} style={{ border: '1px solid #ECEAE3', borderRadius: '8px', padding: '16px', background: '#FAFAF7' }}>
                <div style={{ fontSize: '13.5px', fontWeight: 600, color: '#34352F' }}>{a.alias || `${a.firstname} ${a.lastname}`.trim() || t('addressFallback')}</div>
                <div style={{ fontSize: '13px', color: '#55606F', marginTop: '6px', lineHeight: 1.5 }}>
                  {a.address1}<br />
                  {a.postcode} {a.city}<br />
                  {a.country}
                  {a.phone ? <><br />{a.phone}</> : null}
                </div>
              </div>
            ))}
          </div>
        ) : (
          <div style={{ fontSize: '14px', color: '#8A8170' }}>{t('noAddresses')}</div>
        )}

        {showAddrForm ? (
          <div style={{ marginTop: '22px', borderTop: '1px solid #ECEAE3', paddingTop: '22px' }}>
            <AddressForm onCreated={(list) => { setAddresses(list); setShowAddrForm(false); }} />
          </div>
        ) : null}
      </div>
    </main>
  );
}
