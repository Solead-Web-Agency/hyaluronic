'use client';

import { useEffect, useState } from 'react';
import { useTranslations } from 'next-intl';

export type Address = {
  id_address: number;
  alias: string;
  firstname: string;
  lastname: string;
  address1: string;
  postcode: string;
  city: string;
  country: string;
  id_country: number;
  phone: string;
};

type Country = { id_country: number; name: string; iso_code: string };

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

// Formulaire d'ajout d'adresse, réutilisé par /compte et /checkout.
// onCreated reçoit la liste d'adresses renvoyée par le bridge (created:true).
export default function AddressForm({
  onCreated,
}: {
  onCreated?: (addresses: Address[], id_address: number) => void;
}) {
  const t = useTranslations('addressForm');
  const tc = useTranslations('common');
  const [countries, setCountries] = useState<Country[]>([]);
  const [address1, setAddress1] = useState('');
  const [postcode, setPostcode] = useState('');
  const [city, setCity] = useState('');
  const [idCountry, setIdCountry] = useState<number | ''>('');
  const [phone, setPhone] = useState('');
  const [alias, setAlias] = useState('');
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    fetch('/api/taxonomy?action=countries')
      .then((r) => r.json())
      .then((d) => {
        const list: Country[] = d.countries ?? [];
        setCountries(list);
        const fr = list.find((c) => c.iso_code === 'FR');
        if (fr) setIdCountry(fr.id_country);
      })
      .catch(() => {});
  }, []);

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    if (!address1 || !postcode || !city || !idCountry) {
      setError(t('missingFields'));
      return;
    }
    if (!phone.trim()) {
      setError(t('phoneRequired'));
      return;
    }
    setBusy(true);
    try {
      const r = await fetch('/api/customer', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'add-address',
          address1,
          postcode,
          city,
          id_country: idCountry,
          phone: phone.trim(),
          alias: alias || undefined,
        }),
      });
      const d = await r.json();
      if (!r.ok || !d.created) {
        setError(d.error === 'invalid_phone' ? t('phoneRequired') : (d.error || t('saveFailed')));
        return;
      }
      setAddress1('');
      setPostcode('');
      setCity('');
      setPhone('');
      setAlias('');
      onCreated?.(d.addresses ?? [], d.id_address);
    } catch {
      setError(tc('networkError'));
    } finally {
      setBusy(false);
    }
  };

  return (
    <form onSubmit={submit} style={{ display: 'flex', flexDirection: 'column', gap: '14px' }}>
      <div>
        <label style={labelStyle}>{t('address')}</label>
        <input value={address1} onChange={(e) => setAddress1(e.target.value)} placeholder={t('addressPlaceholder')} style={inputStyle} />
      </div>
      <div style={{ display: 'grid', gridTemplateColumns: '1fr 2fr', gap: '12px' }}>
        <div>
          <label style={labelStyle}>{t('postcode')}</label>
          <input value={postcode} onChange={(e) => setPostcode(e.target.value)} placeholder={t('postcodePlaceholder')} style={inputStyle} />
        </div>
        <div>
          <label style={labelStyle}>{t('city')}</label>
          <input value={city} onChange={(e) => setCity(e.target.value)} placeholder={t('cityPlaceholder')} style={inputStyle} />
        </div>
      </div>
      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
        <div>
          <label style={labelStyle}>{t('country')}</label>
          <select value={idCountry} onChange={(e) => setIdCountry(Number(e.target.value))} style={{ ...inputStyle, padding: '0 10px' }}>
            {countries.map((c) => (
              <option key={c.id_country} value={c.id_country}>{c.name}</option>
            ))}
          </select>
        </div>
        <div>
          <label style={labelStyle}>{t('phone')} *</label>
          <input value={phone} onChange={(e) => setPhone(e.target.value)} placeholder={t('phonePlaceholder')} style={inputStyle} required />
        </div>
      </div>
      <div>
        <label style={labelStyle}>{t('alias')}</label>
        <input value={alias} onChange={(e) => setAlias(e.target.value)} placeholder={t('aliasPlaceholder')} style={inputStyle} />
      </div>
      {error ? <div style={{ fontSize: '13px', color: '#A8503A' }}>{error}</div> : null}
      <button
        type="submit"
        disabled={busy}
        style={{
          height: '48px',
          borderRadius: '999px',
          color: '#fff',
          background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))',
          border: '1px solid rgba(255,255,255,.42)',
          boxShadow: '0 10px 22px -12px rgba(140,198,63,.7)',
          fontFamily: "'Hanken Grotesk',sans-serif",
          fontSize: '14.5px',
          fontWeight: 600,
          cursor: busy ? 'default' : 'pointer',
          opacity: busy ? 0.7 : 1,
        }}
      >
        {busy ? t('saving') : t('add')}
      </button>
    </form>
  );
}
