'use client';

import { useEffect, useId, useState } from 'react';
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
  id_state?: number;
  state?: string;
  dni?: string;
  company?: string;
  vat_number?: string;
  vat_valid?: boolean;
  vat_invalid?: boolean;
  vat_checkable?: boolean;
};

type State = { id_state: number; name: string; iso_code: string };
// Règles d'adresse par pays, servies par le bridge (taxonomy action=countries).
type Country = {
  id_country: number;
  name: string;
  iso_code: string;
  contains_states?: boolean;
  states?: State[];
  need_zip_code?: boolean;
  zip_code_format?: string;
  need_identification_number?: boolean;
  vat_applicable?: boolean;
};

// Exemples de n° de TVA intracommunautaire par pays (préfixe officiel ; la Grèce utilise « EL »).
const VAT_EXAMPLES: Record<string, string> = {
  AT: 'ATU12345678', BE: 'BE0123456789', BG: 'BG123456789', CY: 'CY12345678X', CZ: 'CZ12345678',
  DE: 'DE123456789', DK: 'DK12345678', EE: 'EE123456789', GR: 'EL123456789', ES: 'ESX1234567X',
  FI: 'FI12345678', FR: 'FR12345678901', HR: 'HR12345678901', HU: 'HU12345678', IE: 'IE1234567X',
  IT: 'IT12345678901', LT: 'LT123456789', LU: 'LU12345678', LV: 'LV12345678901', MT: 'MT12345678',
  NL: 'NL123456789B01', PL: 'PL1234567890', PT: 'PT123456789', RO: 'RO1234567890', SE: 'SE123456789001',
  SI: 'SI12345678', SK: 'SK1234567890', GB: 'GB123456789', XI: 'XI123456789',
};
const vatPrefix = (iso: string) => (iso === 'GR' ? 'EL' : iso);

// Codes postaux d'exemple RÉALISTES par pays (audit 27/10 : « 11111 » faisait faux). Utilisés en
// placeholder et dans le message d'erreur, uniquement s'ils respectent le format PS du pays.
const ZIP_EXAMPLES: Record<string, string> = {
  FR: '75004', cos: '20000', MC: '98000', DE: '10115', AT: '1010', BE: '1000', BG: '1000', CH: '1201',
  CY: '1010', CZ: '110 00', DK: '1050', EE: '10111', ES: '28001', FI: '00100', GR: '105 57', HR: '10000',
  HU: '1051', IE: 'D02 X285', IT: '00184', LI: '9490', LT: '01100', LU: '1611', LV: 'LV-1050', MT: 'VLT 1117',
  NL: '1012 AB', PL: '00-001', PT: '1100-148', RO: '010011', SE: '111 22', SI: '1000', SK: '811 01',
  GB: 'SW1A 1AA', US: '10001', CA: 'H2X 1Y4', JP: '100-0001', AU: '2000', AD: 'AD500', TR: '34000',
  MX: '06000', AR: 'C1002AAP', CL: '832-0000', CO: '110111', CR: '10101', IN: '110 001', MY: '50000',
  PR: '00901', RS: '11000', UA: '01001', ZA: '2000', NO: '0150',
};

// Format de code postal PrestaShop (N = chiffre, L = lettre, C = code ISO du pays) -> regex + exemple.
// L'exemple est réaliste (ZIP_EXAMPLES) s'il est connu et conforme ; sinon on le génère à partir du
// format avec des chiffres variés (jamais « 11111 »), pour le seul message d'erreur.
function zipRule(format: string, iso: string): { re: RegExp; example: string; realistic: boolean } | null {
  if (!format) return null;
  let re = '';
  let ex = '';
  let n = 0;
  for (const ch of format) {
    if (ch === 'N') { re += '\\d'; ex += String((n++ % 9) + 1); }
    else if (ch === 'L') { re += '[A-Za-z]'; ex += 'A'; }
    else if (ch === 'C') { re += iso; ex += iso; }
    else { re += ch.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); ex += ch; }
  }
  const rx = new RegExp(`^${re}$`);
  const real = ZIP_EXAMPLES[iso];
  return real && rx.test(real) ? { re: rx, example: real, realistic: true } : { re: rx, example: ex, realistic: false };
}

// Noms de pays saisis tout en capitales / minuscules dans le back-office (ex. « CORSE ») : on les
// présente proprement (première lettre de chaque mot en capitale). Les noms déjà « propres » sont gardés.
export function prettyCountry(name: string): string {
  const n = (name || '').trim();
  if (!n || (n !== n.toUpperCase() && n !== n.toLowerCase())) return n;
  return n.toLowerCase().replace(/(^|[\s(\-'’])(\p{L})/gu, (_m, sep: string, c: string) => sep + c.toUpperCase());
}

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

const errStyle: React.CSSProperties = { display: 'block', fontSize: '12px', color: '#B42318', marginTop: '6px', lineHeight: 1.45 };
const errBorder = '#B42318';

// Champs validés côté front, dans l'ordre d'affichage (le focus va au premier en erreur).
type Field = 'address1' | 'postcode' | 'city' | 'country' | 'phone' | 'state' | 'dni';
const FIELD_ORDER: Field[] = ['address1', 'postcode', 'city', 'country', 'phone', 'state', 'dni'];

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
  const uid = useId();
  const fid = (f: string) => `${uid}-${f}`;
  const editing = !!address;
  const [countries, setCountries] = useState<Country[]>([]);
  const [address1, setAddress1] = useState(address?.address1 ?? '');
  const [postcode, setPostcode] = useState(address?.postcode ?? '');
  const [city, setCity] = useState(address?.city ?? '');
  const [idCountry, setIdCountry] = useState<number | ''>(address?.id_country ?? '');
  const [phone, setPhone] = useState(address?.phone ?? '');
  const [idState, setIdState] = useState<number | ''>(address?.id_state || '');
  const [dni, setDni] = useState(address?.dni ?? '');
  const [alias, setAlias] = useState(address?.alias ?? '');
  const [company, setCompany] = useState(address?.company ?? '');
  const [vatNumber, setVatNumber] = useState(address?.vat_number ?? '');
  const [vatStatus, setVatStatus] = useState<VatStatus | null>(null);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState<string | null>(null);
  // Erreurs PAR CHAMP (audit 27/10 : un seul message global quel que soit le problème).
  const [fieldErr, setFieldErr] = useState<Partial<Record<Field, string>>>({});

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

  // Pays sélectionné et ses règles (région, code postal, identifiant fiscal, TVA).
  const country = countries.find((c) => c.id_country === idCountry);
  const iso = country?.iso_code ?? '';
  const states = country?.contains_states ? country.states ?? [] : [];
  const zip = country?.need_zip_code ? zipRule(country.zip_code_format ?? '', iso) : null;
  // Code postal exigé par le pays (certains l'exigent sans format défini : États-Unis, Royaume-Uni…).
  const zipRequired = !country || !!country.need_zip_code;
  // Placeholder : exemple réaliste du pays (75004 pour la France), sinon vide (jamais « 11111 »).
  const zipPlaceholder = zip ? (zip.realistic ? zip.example : '') : (ZIP_EXAMPLES[iso] ?? '');
  const showVat = !!country && (country.vat_applicable || iso === 'FR');
  const vatExample = VAT_EXAMPLES[iso] ?? '';
  const vatPrefixWrong = showVat && !!vatNumber.trim() && !!vatExample && !vatNumber.trim().toUpperCase().startsWith(vatPrefix(iso));
  // Territoire de TVA française (France, Corse, Monaco) : jamais d'exonération (TVA domestique).
  const domesticVat = iso === 'FR' || iso === 'cos' || iso === 'MC';

  // Changement de pays : la région d'un autre pays n'a plus de sens.
  const changeCountry = (id: number) => {
    setIdCountry(id);
    setIdState('');
    clearErr('country', 'state', 'postcode', 'dni');
  };

  function clearErr(...fs: Field[]) {
    setFieldErr((cur) => {
      if (!fs.some((f) => cur[f])) return cur;
      const n = { ...cur };
      for (const f of fs) delete n[f];
      return n;
    });
  }

  // Affiche les erreurs sous les champs et place le focus sur le premier champ en erreur.
  const showErrors = (errs: Partial<Record<Field, string>>) => {
    setFieldErr(errs);
    const first = FIELD_ORDER.find((f) => errs[f]);
    if (first) requestAnimationFrame(() => document.getElementById(fid(first))?.focus());
  };

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    const errs: Partial<Record<Field, string>> = {};
    if (!address1.trim()) errs.address1 = t('fieldRequired');
    if (!postcode.trim()) {
      if (zipRequired) errs.postcode = t('fieldRequired');
    } else if (zip && !zip.re.test(postcode.trim())) {
      errs.postcode = t('postcodeInvalid', { example: zip.example });
    }
    if (!city.trim()) errs.city = t('fieldRequired');
    if (!idCountry) errs.country = t('fieldRequired');
    if (!phone.trim()) errs.phone = t('phoneRequired');
    if (states.length && !idState) errs.state = t('stateRequired');
    if (country?.need_identification_number && !dni.trim()) errs.dni = t('dniRequired');
    if (Object.keys(errs).length) {
      showErrors(errs);
      return;
    }
    setFieldErr({});
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
          id_state: states.length ? idState : 0,
          dni: country?.need_identification_number ? dni.trim() : '',
          phone: phone.trim(),
          alias: alias || undefined,
          company: company.trim() || '',
          vat_number: showVat ? vatNumber.trim() : '',
        }),
      });
      const d = await r.json();
      if (!r.ok || !(d.created || d.updated)) {
        // Erreurs du bridge rattachées au champ concerné quand c'est possible.
        if (d.error === 'invalid_phone') showErrors({ phone: t('phoneRequired') });
        else if (d.error === 'state_required') showErrors({ state: t('stateRequired') });
        else if (d.error === 'invalid_postcode') showErrors({ postcode: t('postcodeInvalid', { example: zip?.example ?? '' }) });
        else if (d.error === 'dni_required') showErrors({ dni: t('dniRequired') });
        else setError(d.error || t('saveFailed'));
        return;
      }
      // Statut de validation TVA (VIES/GOV.UK) renvoyé par le bridge ; l'exonération éventuelle
      // est déjà appliquée côté PrestaShop (les totaux du panier la refléteront).
      if (d.vat) setVatStatus(d.vat as VatStatus);
      setAddress1('');
      setPostcode('');
      setCity('');
      setPhone('');
      setIdState('');
      setDni('');
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

  // Props d'accessibilité communes : id relié au <label>, état invalide + message décrit.
  const a11y = (f: Field) => ({
    id: fid(f),
    'aria-invalid': fieldErr[f] ? true : undefined,
    'aria-describedby': fieldErr[f] ? fid(f) + '-err' : undefined,
  });
  const withErr = (f: Field, base: React.CSSProperties): React.CSSProperties => (fieldErr[f] ? { ...base, borderColor: errBorder } : base);
  const ErrMsg = ({ f }: { f: Field }) => (fieldErr[f] ? <span id={fid(f) + '-err'} role="alert" style={errStyle}>{fieldErr[f]}</span> : null);

  return (
    <form onSubmit={submit} noValidate style={{ display: 'flex', flexDirection: 'column', gap: '14px' }}>
      <div>
        <label htmlFor={fid('address1')} style={labelStyle}>{t('address')} *</label>
        <input {...a11y('address1')} value={address1} onChange={(e) => { setAddress1(e.target.value); clearErr('address1'); }} placeholder={t('addressPlaceholder')} autoComplete="address-line1" style={withErr('address1', inputStyle)} />
        <ErrMsg f="address1" />
      </div>
      <div style={{ display: 'grid', gridTemplateColumns: 'minmax(0,1fr) minmax(0,2fr)', gap: '12px' }}>
        <div>
          <label htmlFor={fid('postcode')} style={labelStyle}>{t('postcode')}{zipRequired ? ' *' : ''}</label>
          <input {...a11y('postcode')} value={postcode} onChange={(e) => { setPostcode(e.target.value); clearErr('postcode'); }} placeholder={zipPlaceholder} autoComplete="postal-code" style={withErr('postcode', inputStyle)} />
          <ErrMsg f="postcode" />
        </div>
        <div>
          <label htmlFor={fid('city')} style={labelStyle}>{t('city')} *</label>
          <input {...a11y('city')} value={city} onChange={(e) => { setCity(e.target.value); clearErr('city'); }} placeholder={t('cityPlaceholder')} autoComplete="address-level2" style={withErr('city', inputStyle)} />
          <ErrMsg f="city" />
        </div>
      </div>
      <div style={{ display: 'grid', gridTemplateColumns: 'minmax(0,1fr) minmax(0,1fr)', gap: '12px' }}>
        <div>
          <label htmlFor={fid('country')} style={labelStyle}>{t('country')} *</label>
          <select {...a11y('country')} value={idCountry} onChange={(e) => changeCountry(Number(e.target.value))} autoComplete="country" style={withErr('country', { ...inputStyle, padding: '0 10px' })}>
            {countries.map((c) => (
              <option key={c.id_country} value={c.id_country}>{prettyCountry(c.name)}</option>
            ))}
          </select>
          <ErrMsg f="country" />
        </div>
        <div>
          <label htmlFor={fid('phone')} style={labelStyle}>{t('phone')} *</label>
          <input {...a11y('phone')} type="tel" value={phone} onChange={(e) => { setPhone(e.target.value); clearErr('phone'); }} placeholder={t('phonePlaceholder')} autoComplete="tel" style={withErr('phone', inputStyle)} />
          <ErrMsg f="phone" />
        </div>
      </div>
      {/* Champs dépendants du pays : région (États-Unis, Canada, Japon, Espagne…) et identifiant fiscal. */}
      {states.length || country?.need_identification_number ? (
        <div style={{ display: 'grid', gridTemplateColumns: states.length && country?.need_identification_number ? 'minmax(0,1fr) minmax(0,1fr)' : 'minmax(0,1fr)', gap: '12px' }}>
          {states.length ? (
            <div>
              <label htmlFor={fid('state')} style={labelStyle}>{t('state')} *</label>
              <select {...a11y('state')} value={idState} onChange={(e) => { setIdState(e.target.value ? Number(e.target.value) : ''); clearErr('state'); }} style={withErr('state', { ...inputStyle, padding: '0 10px' })}>
                <option value="">{t('statePlaceholder')}</option>
                {states.map((st) => <option key={st.id_state} value={st.id_state}>{st.name}</option>)}
              </select>
              <ErrMsg f="state" />
            </div>
          ) : null}
          {country?.need_identification_number ? (
            <div>
              <label htmlFor={fid('dni')} style={labelStyle}>{t('dni')} *</label>
              <input {...a11y('dni')} value={dni} onChange={(e) => { setDni(e.target.value.toUpperCase()); clearErr('dni'); }} placeholder={t('dniPlaceholder')} style={withErr('dni', inputStyle)} />
              <ErrMsg f="dni" />
            </div>
          ) : null}
        </div>
      ) : null}
      <div>
        <label htmlFor={fid('alias')} style={labelStyle}>{t('alias')}</label>
        <input id={fid('alias')} value={alias} onChange={(e) => setAlias(e.target.value)} placeholder={t('aliasPlaceholder')} style={inputStyle} />
      </div>
      {/* Bloc B2B : société + n° de TVA intracommunautaire (validation VIES/GOV.UK + exonération).
          Le n° de TVA n'est proposé que là où il a un sens (UE, Royaume-Uni) ; ailleurs, société seule. */}
      <div style={{ border: '1px solid #E7E3DA', borderRadius: '10px', padding: '14px', background: '#FAFAF7', display: 'flex', flexDirection: 'column', gap: '12px' }}>
        <div style={{ fontSize: '12px', fontWeight: 700, letterSpacing: '.04em', color: '#5E6B45', display: 'flex', alignItems: 'center', gap: '7px' }}>
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true"><path d="M3 21h18M6 21V8l6-4 6 4v13M9 21v-5h6v5" /></svg>
          {t('b2bTitle')}
        </div>
        <div>
          <label htmlFor={fid('company')} style={labelStyle}>{t('company')}</label>
          <input id={fid('company')} value={company} onChange={(e) => setCompany(e.target.value)} placeholder={t('companyPlaceholder')} autoComplete="organization" style={inputStyle} />
        </div>
        {showVat ? (
          <div>
            <label htmlFor={fid('vat')} style={labelStyle}>{t('vatNumber')}</label>
            <input id={fid('vat')} aria-describedby={fid('vat') + '-hint'} value={vatNumber} onChange={(e) => setVatNumber(e.target.value.toUpperCase().replace(/\s+/g, ''))} placeholder={vatExample || t('vatPlaceholder')} style={inputStyle} autoCapitalize="characters" />
            {vatPrefixWrong ? (
              <span id={fid('vat') + '-hint'} style={{ display: 'block', fontSize: '11.5px', color: '#B42318', marginTop: '6px', lineHeight: 1.45 }}>{t('vatPrefixMismatch', { prefix: vatPrefix(iso) })}</span>
            ) : (
              <span id={fid('vat') + '-hint'} style={{ display: 'block', fontSize: '11.5px', color: '#9A9A8C', marginTop: '6px', lineHeight: 1.45 }}>{t('vatHint')}</span>
            )}
          </div>
        ) : null}
        {vatStatus && vatStatus.number ? (
          vatStatus.valid ? (
            <div style={{ display: 'flex', alignItems: 'center', gap: '8px', fontSize: '12.5px', fontWeight: 600, color: '#4B6B1B', background: 'rgba(140,198,63,.12)', border: '1px solid rgba(140,198,63,.3)', borderRadius: '8px', padding: '9px 11px' }}>
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#5E8E1F" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5" /></svg>
              {/* B1 : la mention d'exonération n'apparaît que si elle s'applique (livraison hors France
                  et exonération effectivement accordée) ; sinon simple confirmation du numéro. */}
              {vatStatus.exempt && !domesticVat ? t('vatValid') : t('vatValidNoExempt')}
            </div>
          ) : (
            <div style={{ display: 'flex', alignItems: 'center', gap: '8px', fontSize: '12.5px', fontWeight: 600, color: '#A8503A', background: 'rgba(168,80,58,.08)', border: '1px solid rgba(168,80,58,.25)', borderRadius: '8px', padding: '9px 11px' }}>
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#A8503A" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true"><path d="M12 8v5M12 16.5v.5M10.3 3.9 2.4 18a2 2 0 0 0 1.7 3h15.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z" /></svg>{t('vatInvalid')}
            </div>
          )
        ) : null}
      </div>
      {error ? <div role="alert" style={{ fontSize: '13px', color: '#B42318' }}>{error}</div> : null}
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
