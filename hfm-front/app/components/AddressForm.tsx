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
  company?: string;
  vat_number?: string;
  vat_valid?: boolean;
  vat_invalid?: boolean;
  vat_checkable?: boolean;
};

type Country = { id_country: number; name: string; iso_code: string };
// Statut renvoyé par le bridge après validation VIES/GOV.UK (module Advanced VAT Manager).
type VatStatus = { number: string; valid: boolean; invalid: boolean; exempt: boolean; available: boolean };

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

// Formulaire d'adresse, réutilisé par /compte et /checkout.
// - sans `address` : mode AJOUT (action add-address).
// - avec `address`  : mode ÉDITION, champs préremplis (action update-address).
// onCreated reçoit la liste d'adresses renvoyée par le bridge.
export default function AddressForm({
  onCreated,
  address,
}: {
  onCreated?: (addresses: Address[], id_address: number) => void;
  address?: Address;
}) {
  const t = useTranslations('addressForm');
  const tc = useTranslations('common');
  const editing = !!address;
  const [countries, setCountries] = useState<Country[]>([]);
  const [address1, setAddress1] = useState(address?.address1 ?? '');
  const [postcode, setPostcode] = useState(address?.postcode ?? '');
  const [city, setCity] = useState(address?.city ?? '');
  const [idCountry, setIdCountry] = useState<number | ''>(address?.id_country ?? '');
  const [phone, setPhone] = useState(address?.phone ?? '');
  const [alias, setAlias] = useState(address?.alias ?? '');
  const [company, setCompany] = useState(address?.company ?? '');
  const [vatNumber, setVatNumber] = useState(address?.vat_number ?? '');
  const [vatStatus, setVatStatus] = useState<VatStatus | null>(null);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    fetch('/api/taxonomy?action=countries')
      .then((r) => r.json())
      .then((d) => {
        const list: Country[] = d.countries ?? [];
        setCountries(list);
        // Défaut France uniquement en mode ajout (en édition, on garde le pays de l'adresse).
        if (!address) {
          const fr = list.find((c) => c.iso_code === 'FR');
          if (fr) setIdCountry(fr.id_country);
        }
      })
      .catch(() => {});
  }, [address]);

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
    setVatStatus(null);
    try {
      const r = await fetch('/api/customer', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: editing ? 'update-address' : 'add-address',
          id_address: editing ? address!.id_address : undefined,
          address1,
          postcode,
          city,
          id_country: idCountry,
          phone: phone.trim(),
          alias: alias || undefined,
          company: company.trim() || '',
          vat_number: vatNumber.trim() || '',
        }),
      });
      const d = await r.json();
      if (!r.ok || !(d.created || d.updated)) {
        setError(d.error === 'invalid_phone' ? t('phoneRequired') : (d.error || t('saveFailed')));
        return;
      }
      // Statut de validation TVA (VIES/GOV.UK) renvoyé par le bridge ; l'exonération éventuelle
      // est déjà appliquée côté PrestaShop (les totaux du panier la refléteront).
      if (d.vat) setVatStatus(d.vat as VatStatus);
      setAddress1('');
      setPostcode('');
      setCity('');
      setPhone('');
      setAlias('');
      setCompany('');
      setVatNumber('');
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
      {/* Bloc B2B : société + n° de TVA intracommunautaire (validation VIES/GOV.UK + exonération). */}
      <div style={{ border: '1px solid #E7E3DA', borderRadius: '10px', padding: '14px', background: '#FAFAF7', display: 'flex', flexDirection: 'column', gap: '12px' }}>
        <div style={{ fontSize: '12px', fontWeight: 700, letterSpacing: '.04em', color: '#5E6B45', display: 'flex', alignItems: 'center', gap: '7px' }}>
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round"><path d="M3 21h18M6 21V8l6-4 6 4v13M9 21v-5h6v5" /></svg>
          {t('b2bTitle')}
        </div>
        <div>
          <label style={labelStyle}>{t('company')}</label>
          <input value={company} onChange={(e) => setCompany(e.target.value)} placeholder={t('companyPlaceholder')} style={inputStyle} />
        </div>
        <div>
          <label style={labelStyle}>{t('vatNumber')}</label>
          <input value={vatNumber} onChange={(e) => setVatNumber(e.target.value.toUpperCase())} placeholder={t('vatPlaceholder')} style={inputStyle} autoCapitalize="characters" />
          <span style={{ display: 'block', fontSize: '11.5px', color: '#9A9A8C', marginTop: '6px', lineHeight: 1.45 }}>{t('vatHint')}</span>
        </div>
        {vatStatus && vatStatus.number ? (
          vatStatus.valid ? (
            <div style={{ display: 'flex', alignItems: 'center', gap: '8px', fontSize: '12.5px', fontWeight: 600, color: '#4B6B1B', background: 'rgba(140,198,63,.12)', border: '1px solid rgba(140,198,63,.3)', borderRadius: '8px', padding: '9px 11px' }}>
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#5E8E1F" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round"><path d="M20 6L9 17l-5-5" /></svg>{t('vatValid')}
            </div>
          ) : (
            <div style={{ display: 'flex', alignItems: 'center', gap: '8px', fontSize: '12.5px', fontWeight: 600, color: '#A8503A', background: 'rgba(168,80,58,.08)', border: '1px solid rgba(168,80,58,.25)', borderRadius: '8px', padding: '9px 11px' }}>
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#A8503A" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round"><path d="M12 8v5M12 16.5v.5M10.3 3.9 2.4 18a2 2 0 0 0 1.7 3h15.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z" /></svg>{t('vatInvalid')}
            </div>
          )
        ) : null}
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
        {busy ? t('saving') : (editing ? t('save') : t('add'))}
      </button>
    </form>
  );
}
