'use client';

import { useCallback, useEffect, useState } from 'react';
import { useTranslations, useLocale } from 'next-intl';
import { Link } from '@/i18n/navigation';
import { useRouter } from '@/i18n/navigation';
import { useStore } from '../../store';
import { fmt } from '@/lib/cardModel';
import AddressForm, { type Address } from '../../components/AddressForm';

type Carrier = {
  id_carrier: number;
  name: string;
  delay: string;
  price_incl_tax: number;
  price_excl_tax: number;
  logo: string | null;
};

type Totals = {
  products_excl_tax: number;
  products_incl_tax: number;
  shipping_incl_tax: number;
  total_excl_tax: number;
  total_incl_tax: number;
  total_discounts?: number;
};

const PAYMENTS = ['Virement bancaire', 'Chèque'];
const VIVA_PAYMENT = 'Carte bancaire (Viva Wallet)';

const stepBadge = (n: number, active: boolean): React.CSSProperties => ({
  width: '24px',
  height: '24px',
  borderRadius: '50%',
  background: active ? '#434343' : '#C9C2AF',
  color: '#fff',
  fontSize: '12px',
  fontWeight: 700,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  flex: 'none',
});

const cardStyle: React.CSSProperties = {
  background: '#fff',
  border: '1px solid #ECEAE3',
  borderRadius: '8px',
  padding: '24px',
};

const titleStyle: React.CSSProperties = {
  fontFamily: "'Spectral',serif",
  fontSize: '18px',
  color: '#2B2B2B',
};

export default function CheckoutClient() {
  const { cart, customer, authReady, refreshCart, guestCheckout } = useStore();
  const router = useRouter();
  const t = useTranslations('checkout');
  const tc = useTranslations('common');
  const locale = useLocale();

  const paymentLabel = (p: string) =>
    p === 'Virement bancaire' ? t('paymentTransfer') : p === 'Chèque' ? t('paymentCheck') : p;

  const [addresses, setAddresses] = useState<Address[]>([]);
  const [selAddress, setSelAddress] = useState<number | null>(null);
  const [showAddrForm, setShowAddrForm] = useState(false);

  const [carriers, setCarriers] = useState<Carrier[]>([]);
  const [selCarrier, setSelCarrier] = useState<number | null>(null);
  const [loadingCarriers, setLoadingCarriers] = useState(false);

  // Mode invité (commande sans compte)
  const [guestEmail, setGuestEmail] = useState('');
  const [guestFirst, setGuestFirst] = useState('');
  const [guestLast, setGuestLast] = useState('');
  const [guestBusy, setGuestBusy] = useState(false);
  const [guestErr, setGuestErr] = useState<string | null>(null);

  const [payment, setPayment] = useState<string>(PAYMENTS[0]);
  const [totals, setTotals] = useState<Totals | null>(null);

  // RPPS / ADELI — requis seulement si le panier contient un produit réservé praticiens.
  const [rpps, setRpps] = useState('');
  const [rppsInfo, setRppsInfo] = useState<{ nom?: string; prenom?: string; profession?: string } | null>(null);
  const [rppsChecking, setRppsChecking] = useState(false);
  const [rppsSoftNote, setRppsSoftNote] = useState(false); // numéro accepté mais non rapproché au registre
  const rppsNeeded = cart.rpps_required;
  const rppsValid = /^\d{9,13}$/.test(rpps.trim()); // permissif : 9 à 13 chiffres

  // Rapprochement INFORMATIF au registre (n'empêche jamais la commande : la validité
  // est confirmée en back-office). On affiche le praticien si trouvé.
  const verifyRppsField = async () => {
    const v = rpps.trim();
    setRppsInfo(null);
    setRppsSoftNote(false);
    if (!v || !/^\d{9,13}$/.test(v)) return;
    setRppsChecking(true);
    try {
      const r = await fetch('/api/customer', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'set-rpps', rpps: v }),
      });
      const d = await r.json();
      if (d.ok) {
        setRppsErr(null);
        if (d.practitioner) setRppsInfo(d.practitioner);
        else setRppsSoftNote(true); // non trouvé au registre -> vérifié ensuite par l'équipe
      }
    } catch {
      /* silencieux */
    } finally {
      setRppsChecking(false);
    }
  };

  const [placing, setPlacing] = useState(false);
  const [orderErr, setOrderErr] = useState<string | null>(null);
  const [rppsErr, setRppsErr] = useState<string | null>(null);
  const [confirmation, setConfirmation] = useState<{ reference: string; id_order: number; total_paid: number } | null>(null);

  // Code promo
  const [voucherCode, setVoucherCode] = useState('');
  const [voucherDiscount, setVoucherDiscount] = useState<number>(0);
  const [voucherMsg, setVoucherMsg] = useState<{ kind: 'ok' | 'err'; text: string } | null>(null);
  const [applyingVoucher, setApplyingVoucher] = useState(false);

  // Paiement CB (Viva Wallet) — affiché si le PSP est configuré côté serveur.
  const [cardConfigured, setCardConfigured] = useState(false);
  const [cardMode, setCardMode] = useState<string>('live');
  const [cardError, setCardError] = useState<string | null>(null);

  const idCart = cart.id_cart;

  // Charge les adresses du client.
  const loadAddresses = useCallback(() => {
    fetch('/api/customer?action=addresses')
      .then((r) => (r.ok ? r.json() : { addresses: [] }))
      .then((d) => {
        const list: Address[] = d.addresses ?? [];
        setAddresses(list);
        if (!list.length) setShowAddrForm(true);
      })
      .catch(() => {});
  }, []);

  useEffect(() => {
    if (customer) loadAddresses();
  }, [customer, loadAddresses]);

  // Préremplit le RPPS si le client connecté en a déjà un enregistré.
  useEffect(() => {
    if (!customer) return;
    let alive = true;
    fetch('/api/customer?action=me')
      .then((r) => (r.ok ? r.json() : null))
      .then((d) => {
        if (!alive) return;
        const stored = d?.customer?.rpps;
        if (stored) setRpps(String(stored));
      })
      .catch(() => {});
    return () => {
      alive = false;
    };
  }, [customer]);

  // Recharge les totaux (incl. transport) depuis le panier.
  const reloadTotals = useCallback(async () => {
    if (!idCart) return;
    try {
      const r = await fetch(`/api/cart?id_cart=${idCart}`);
      const d = await r.json();
      if (d.totals) setTotals(d.totals as Totals);
    } catch {
      /* ignore */
    }
  }, [idCart]);

  useEffect(() => {
    reloadTotals();
  }, [reloadTotals]);

  // Config paiement : détecte si le PSP CB (Viva Wallet) est activé côté serveur.
  useEffect(() => {
    let alive = true;
    fetch('/api/payment')
      .then((r) => (r.ok ? r.json() : { configured: false }))
      .then((d) => {
        if (!alive) return;
        setCardConfigured(!!d.configured);
        setCardMode(d.mode || 'live');
      })
      .catch(() => {});
    return () => {
      alive = false;
    };
  }, []);

  // Retour depuis Viva : /api/payment/return a vérifié + créé la commande, puis nous a
  // redirigés vers /{langue}/checkout avec le résultat en query (viva_paid / viva_failed).
  useEffect(() => {
    if (typeof window === 'undefined') return;
    const params = new URLSearchParams(window.location.search);
    const clean = () => window.history.replaceState({}, '', window.location.pathname);
    if (params.get('viva_failed')) {
      setOrderErr(t('paymentNotConfirmed'));
      clean();
      return;
    }
    if (params.get('viva_cancel')) {
      setOrderErr(t('paymentCancelled'));
      clean();
      return;
    }
    if (params.get('viva_paid')) {
      setConfirmation({
        reference: params.get('ref') || '',
        id_order: Number(params.get('order') || 0),
        total_paid: Number(params.get('total') || 0),
      });
      localStorage.removeItem('id_cart');
      refreshCart();
      window.scrollTo({ top: 0 });
      clean();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // Applique un code promo, puis rafraîchit le panier et les totaux.
  const applyVoucher = async () => {
    const code = voucherCode.trim();
    if (!idCart || !code || applyingVoucher) return;
    setApplyingVoucher(true);
    setVoucherMsg(null);
    try {
      const r = await fetch('/api/checkout', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'voucher', id_cart: idCart, code }),
      });
      const d = await r.json();
      if (!r.ok || !d.ok) {
        const err = d.error || d.detail?.error || d.detail || 'invalid_voucher';
        setVoucherMsg({ kind: 'err', text: err === 'voucher_not_applicable' ? t('voucherNotApplicable') : t('voucherInvalid') });
        return;
      }
      const discount = Number(d.discount) || 0;
      setVoucherDiscount(discount);
      setVoucherMsg({ kind: 'ok', text: discount ? t('voucherDiscount', { amount: fmt(discount) }) : t('voucherApplied') });
      await refreshCart();
      await reloadTotals();
    } catch {
      setVoucherMsg({ kind: 'err', text: t('voucherInvalid') });
    } finally {
      setApplyingVoucher(false);
    }
  };

  // Charge les transporteurs (après qu'une adresse soit posée sur le panier).
  const loadCarriers = useCallback(async () => {
    if (!idCart) return;
    setLoadingCarriers(true);
    try {
      const r = await fetch(`/api/checkout?action=shipping&id_cart=${idCart}`);
      const d = await r.json();
      setCarriers(d.carriers ?? []);
    } catch {
      setCarriers([]);
    } finally {
      setLoadingCarriers(false);
    }
  }, [idCart]);

  // Sélection d'une adresse -> set-address puis (re)chargement des transporteurs.
  const chooseAddress = async (id_address: number) => {
    if (!idCart) return;
    setSelAddress(id_address);
    setSelCarrier(null);
    setCarriers([]);
    try {
      await fetch('/api/checkout', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'set-address', id_cart: idCart, id_address_delivery: id_address }),
      });
      await loadCarriers();
      await reloadTotals();
    } catch {
      /* ignore */
    }
  };

  // Sélection d'un transporteur -> set-carrier puis maj des totaux.
  const chooseCarrier = async (id_carrier: number) => {
    if (!idCart) return;
    setSelCarrier(id_carrier);
    try {
      const r = await fetch('/api/checkout', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'set-carrier', id_cart: idCart, id_carrier }),
      });
      await r.json();
      await reloadTotals();
    } catch {
      /* ignore */
    }
  };

  const onAddressCreated = (list: Address[], id_address: number) => {
    setAddresses(list);
    setShowAddrForm(false);
    chooseAddress(id_address);
  };

  // Finalise la commande côté serveur (POST order) — partagé offline + Stripe.
  const finalizeOrder = async (paymentMethod: string) => {
    const trimmedRpps = rpps.trim();
    const r = await fetch('/api/checkout', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'order', id_cart: idCart, payment_method: paymentMethod, ...(trimmedRpps ? { rpps: trimmedRpps } : {}) }),
    });
    const d = await r.json();
    if (!r.ok || !d.ok) {
      const err = d.detail?.error || d.detail || d.error || 'cart_incomplete';
      if (err === 'rpps_required') {
        setRppsErr(t('rppsRequiredError'));
        return false;
      }
      setOrderErr(err);
      return false;
    }
    setConfirmation({ reference: d.reference, id_order: d.id_order, total_paid: d.total_paid });
    localStorage.removeItem('id_cart');
    await refreshCart();
    window.scrollTo({ top: 0 });
    return true;
  };

  const placeOrder = async () => {
    if (!idCart || !selAddress || !selCarrier) return;
    if (rppsNeeded && !rppsValid) {
      setRppsErr(t('rppsInvalid'));
      return;
    }
    setPlacing(true);
    setOrderErr(null);
    setRppsErr(null);
    setCardError(null);
    try {
      // Chemin carte bancaire (Viva Wallet) : on crée l'order puis on REDIRIGE vers Smart Checkout.
      if (cardConfigured && payment === VIVA_PAYMENT) {
        // La commande Viva est créée plus tard côté serveur (/api/payment/return), qui lit
        // le RPPS STOCKÉ : on l'enregistre donc AVANT de rediriger.
        const trimmedRpps = rpps.trim();
        if (trimmedRpps) {
          try {
            await fetch('/api/customer', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ action: 'set-rpps', rpps: trimmedRpps }),
            });
          } catch {
            /* ignore — la validation finale a lieu côté serveur au retour */
          }
        }
        const pr = await fetch('/api/payment', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id_cart: idCart, locale }),
        });
        const pay = await pr.json();
        if (!pr.ok || !pay.checkout_url) {
          setCardError(t('cardUnavailable'));
          return;
        }
        window.location.href = pay.checkout_url;
        return;
      }

      // Chemin offline (Virement / Chèque) — commande immédiate "en préparation".
      await finalizeOrder(payment);
    } catch {
      setOrderErr('network_error');
    } finally {
      setPlacing(false);
    }
  };

  // ---------- États bloquants ----------
  if (!authReady) {
    return <main className="hfm-wrap" style={{ maxWidth: '620px', margin: '0 auto', padding: '90px 28px', textAlign: 'center', color: '#8A8170' }}>{t('loading')}</main>;
  }


  if (confirmation) {
    return (
      <main data-screen-label="Confirmation" className="hfm-wrap" style={{ maxWidth: '620px', margin: '0 auto', padding: '90px 28px', textAlign: 'center' }}>
        <div style={{ width: '72px', height: '72px', borderRadius: '50%', background: '#3F7256', color: '#fff', fontSize: '34px', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto' }}>✓</div>
        <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: 'clamp(25px,3.6vw,34px)', color: '#2B2B2B', margin: '26px 0 0' }}>{t('confirmedTitle', { reference: confirmation.reference })}</h1>
        <p style={{ fontSize: '15px', color: '#55606F', margin: '14px 0 0' }}>{t.rich('confirmedBody', { id: confirmation.id_order, reference: confirmation.reference, amount: fmt(confirmation.total_paid), b: (c) => <b style={{ color: '#434343' }}>{c}</b> })}</p>
        <div style={{ display: 'flex', gap: '12px', justifyContent: 'center', marginTop: '30px' }}>
          <button onClick={() => router.push('/catalogue')} style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14px', fontWeight: 600, color: '#fff', background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))', border: '1px solid rgba(255,255,255,.42)', boxShadow: '0 12px 26px -10px rgba(116,176,51,.55)', borderRadius: '999px', padding: '14px 26px', cursor: 'pointer' }}>{t('continueShopping')}</button>
          <button onClick={() => router.push('/')} style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14px', fontWeight: 600, color: '#5E8E1F', background: 'rgba(140,198,63,0.08)', border: '1px solid rgba(155,209,89,.7)', borderRadius: '999px', padding: '14px 26px', cursor: 'pointer' }}>{tc('home')}</button>
        </div>
      </main>
    );
  }

  if (!customer) {
    const submitGuest = async (e: React.FormEvent) => {
      e.preventDefault();
      setGuestErr(null);
      if (!guestEmail.trim() || !guestFirst.trim() || !guestLast.trim()) {
        setGuestErr(t('missingFields'));
        return;
      }
      setGuestBusy(true);
      const res = await guestCheckout({ email: guestEmail.trim(), firstname: guestFirst.trim(), lastname: guestLast.trim() });
      setGuestBusy(false);
      if (!res.ok) {
        setGuestErr(
          res.error === 'email_already_exists' ? t('guestEmailExists')
          : res.error === 'invalid_name' ? t('guestInvalidName')
          : res.error === 'invalid_email' ? t('guestInvalidEmail')
          : tc('networkError')
        );
      }
    };
    const gInput: React.CSSProperties = { height: '46px', padding: '0 14px', border: '1px solid #E2DECF', borderRadius: '6px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14px', outline: 'none', background: '#fff', width: '100%', boxSizing: 'border-box', color: '#34352F' };
    return (
      <main data-screen-label="Commande" className="hfm-wrap" style={{ maxWidth: '560px', margin: '0 auto', padding: '50px 28px 70px' }}>
        <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: 'clamp(24px,3.4vw,32px)', color: '#2B2B2B', margin: '0 0 8px', textAlign: 'center' }}>{t('guestTitle')}</h1>
        <p style={{ fontSize: '14px', color: '#55606F', margin: '0 0 26px', textAlign: 'center' }}>{t('guestSubtitle')}</p>
        <form onSubmit={submitGuest} style={{ background: '#fff', border: '1px solid #ECEAE3', borderRadius: '8px', padding: '24px', display: 'flex', flexDirection: 'column', gap: '14px' }}>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
            <input value={guestFirst} onChange={(e) => setGuestFirst(e.target.value)} placeholder={t('firstname')} style={gInput} />
            <input value={guestLast} onChange={(e) => setGuestLast(e.target.value)} placeholder={t('lastname')} style={gInput} />
          </div>
          <input type="email" value={guestEmail} onChange={(e) => setGuestEmail(e.target.value)} placeholder={t('emailLabel')} style={gInput} />
          {guestErr ? <div style={{ fontSize: '13px', color: '#A8503A' }}>{guestErr}</div> : null}
          <button type="submit" disabled={guestBusy} style={{ height: '50px', borderRadius: '999px', color: '#fff', background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))', border: '1px solid rgba(255,255,255,.42)', boxShadow: '0 12px 26px -10px rgba(116,176,51,.55)', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '15px', fontWeight: 600, cursor: guestBusy ? 'default' : 'pointer', opacity: guestBusy ? 0.7 : 1 }}>
            {guestBusy ? tc('loading') : t('guestContinue')}
          </button>
        </form>
        <div style={{ display: 'flex', alignItems: 'center', gap: '12px', margin: '22px 0' }}>
          <span style={{ flex: 1, height: '1px', background: '#E7E3DA' }} />
          <span style={{ fontSize: '12px', color: '#9A9A9A', textTransform: 'uppercase', letterSpacing: '.1em' }}>{t('orSeparator')}</span>
          <span style={{ flex: 1, height: '1px', background: '#E7E3DA' }} />
        </div>
        <div style={{ display: 'flex', gap: '12px', justifyContent: 'center', flexWrap: 'wrap' }}>
          <button onClick={() => router.push('/compte?next=/checkout')} style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14px', fontWeight: 600, color: '#5E8E1F', background: 'rgba(140,198,63,0.08)', border: '1px solid rgba(155,209,89,.7)', borderRadius: '999px', padding: '13px 24px', cursor: 'pointer' }}>{t('haveAccount')}</button>
          <button onClick={() => router.push('/compte?next=/checkout')} style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14px', fontWeight: 600, color: '#5E8E1F', background: 'rgba(140,198,63,0.08)', border: '1px solid rgba(155,209,89,.7)', borderRadius: '999px', padding: '13px 24px', cursor: 'pointer' }}>{t('createAccount')}</button>
        </div>
      </main>
    );
  }

  if (!cart.products.length) {
    return (
      <main data-screen-label="Commande" className="hfm-wrap" style={{ maxWidth: '620px', margin: '0 auto', padding: '90px 28px', textAlign: 'center' }}>
        <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: 'clamp(24px,3.4vw,32px)', color: '#2B2B2B', margin: 0 }}>{t('emptyTitle')}</h1>
        <p style={{ fontSize: '15px', color: '#55606F', margin: '14px 0 28px' }}>{t('emptyLead')}</p>
        <Link href="/catalogue" style={{ display: 'inline-block', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '15px', fontWeight: 600, color: '#fff', background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))', border: '1px solid rgba(255,255,255,.42)', boxShadow: '0 12px 26px -10px rgba(116,176,51,.55)', borderRadius: '999px', padding: '15px 30px', cursor: 'pointer' }}>{tc('seeCatalogue')}</Link>
      </main>
    );
  }

  // Totaux affichés : transport + TTC global dès qu'un transporteur est choisi.
  const subtotalHT = totals?.products_excl_tax ?? cart.total_excl_tax;
  const shipping = selCarrier ? (totals?.shipping_incl_tax ?? 0) : null;
  const grandTTC = selCarrier ? (totals?.total_incl_tax ?? cart.total_incl_tax) : (totals?.products_incl_tax ?? cart.total_incl_tax);
  // Remise : préfère le total réel du panier, sinon le retour de l'API voucher.
  const discount = (totals?.total_discounts ?? 0) || voucherDiscount;
  const canOrder = !!selAddress && !!selCarrier && !!payment && !placing && (!rppsNeeded || rppsValid);

  return (
    <main data-screen-label="Commande" className="hfm-wrap" style={{ maxWidth: '1180px', margin: '0 auto', padding: '34px 28px 70px' }}>
      <div style={{ fontSize: '12.5px', color: '#9A9A9A', marginBottom: '18px' }}><Link href="/" style={{ cursor: 'pointer' }}>{tc('home')}</Link>  /  <Link href="/catalogue" style={{ cursor: 'pointer' }}>{tc('catalogue')}</Link>  /  {t('breadcrumb')}</div>
      <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: 'clamp(25px,3.6vw,34px)', color: '#2B2B2B', margin: '0 0 28px' }}>{t('finalize')}</h1>
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit,minmax(300px,1fr))', gap: '40px', alignItems: 'start' }}>
        <div style={{ display: 'flex', flexDirection: 'column', gap: '22px' }}>

          {/* Étape 1 — Coordonnées */}
          <div style={cardStyle}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '18px' }}><span style={stepBadge(1, true)}>1</span><span style={titleStyle}>{t('stepContact')}</span></div>
            <div style={{ fontSize: '15px', color: '#34352F', fontWeight: 600 }}>{customer.firstname} {customer.lastname}</div>
            <div style={{ fontSize: '14px', color: '#6E7585', marginTop: '4px' }}>{customer.email}</div>

            {/* Numéro RPPS / ADELI — requis si le panier contient un produit réservé praticiens */}
            <div style={{ marginTop: '18px', borderTop: '1px solid #ECEAE3', paddingTop: '18px' }}>
              {rppsNeeded ? (
                <div style={{ display: 'flex', gap: '10px', alignItems: 'flex-start', marginBottom: '12px', padding: '11px 13px', background: 'rgba(168,80,58,.08)', border: '1px solid rgba(168,80,58,.22)', borderRadius: '7px' }}>
                  <span style={{ color: '#A8503A', fontSize: '14px', lineHeight: 1.3, flex: 'none' }} aria-hidden="true">⚕</span>
                  <span style={{ fontSize: '12.5px', lineHeight: 1.5, color: '#A8503A', fontWeight: 500 }}>{t('rppsRequiredNotice')}</span>
                </div>
              ) : null}
              <label htmlFor="rpps" style={{ display: 'block', fontSize: '12.5px', fontWeight: 600, color: '#34352F', marginBottom: '8px' }}>
                {t('rppsLabel')}{rppsNeeded ? <span style={{ color: '#A8503A' }}> *</span> : null}
              </label>
              <input
                id="rpps"
                type="text"
                inputMode="numeric"
                value={rpps}
                onChange={(e) => { setRpps(e.target.value.replace(/\D/g, '').slice(0, 13)); if (rppsErr) setRppsErr(null); if (rppsInfo) setRppsInfo(null); if (rppsSoftNote) setRppsSoftNote(false); }}
                onBlur={verifyRppsField}
                placeholder={t('rppsPlaceholder')}
                style={{ width: '100%', boxSizing: 'border-box', height: '46px', padding: '0 14px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14px', color: '#34352F', background: '#fff', border: `1px solid ${rppsErr ? '#A8503A' : rppsInfo ? '#3F7256' : '#E2DECF'}`, borderRadius: '6px', outline: 'none' }}
              />
              {rppsChecking ? (
                <div style={{ fontSize: '12px', color: '#9A9A9A', marginTop: '8px' }}>{t('rppsChecking')}</div>
              ) : rppsInfo ? (
                <div style={{ fontSize: '12.5px', color: '#3F7256', marginTop: '8px', fontWeight: 600 }}>✓ {[rppsInfo.prenom, rppsInfo.nom].filter(Boolean).join(' ')}{rppsInfo.profession ? ` — ${rppsInfo.profession}` : ''}</div>
              ) : rppsErr ? (
                <div style={{ fontSize: '12px', color: '#A8503A', marginTop: '8px' }}>{rppsErr}</div>
              ) : rppsSoftNote ? (
                <div style={{ fontSize: '11.5px', color: '#9A9A9A', marginTop: '8px' }}>{t('rppsWillBeVerified')}</div>
              ) : (
                <div style={{ fontSize: '11.5px', color: '#9A9A9A', marginTop: '8px' }}>{t('rppsPlaceholder')}</div>
              )}
            </div>
          </div>

          {/* Étape 2 — Livraison (adresse) */}
          <div style={cardStyle}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '18px' }}><span style={stepBadge(2, true)}>2</span><span style={titleStyle}>{t('stepAddress')}</span></div>
            {addresses.length ? (
              <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                {addresses.map((a) => {
                  const active = selAddress === a.id_address;
                  return (
                    <button
                      key={a.id_address}
                      onClick={() => chooseAddress(a.id_address)}
                      style={{ textAlign: 'left', cursor: 'pointer', background: active ? 'rgba(140,198,63,0.06)' : '#fff', border: active ? '1.5px solid #434343' : '1px solid #E2DECF', borderRadius: '7px', padding: '14px 16px', display: 'flex', gap: '12px', alignItems: 'flex-start' }}
                    >
                      <span style={{ marginTop: '2px', width: '15px', height: '15px', borderRadius: '50%', border: active ? '5px solid #434343' : '1.5px solid #C9C2AF', flex: 'none' }} />
                      <span>
                        <span style={{ display: 'block', fontSize: '13.5px', fontWeight: 600, color: '#1B2433' }}>{a.alias || `${a.firstname} ${a.lastname}`.trim() || 'Adresse'}</span>
                        <span style={{ display: 'block', fontSize: '12.5px', color: '#6E7585', marginTop: '3px', lineHeight: 1.45 }}>{a.address1}, {a.postcode} {a.city} · {a.country}</span>
                      </span>
                    </button>
                  );
                })}
              </div>
            ) : null}
            <div style={{ marginTop: addresses.length ? '14px' : 0 }}>
              {!showAddrForm ? (
                <button onClick={() => setShowAddrForm(true)} style={{ background: 'rgba(140,198,63,0.1)', border: '1px solid rgba(155,209,89,.7)', borderRadius: '999px', padding: '9px 16px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '13px', fontWeight: 600, color: '#5E8E1F', cursor: 'pointer' }}>{t('addAddress')}</button>
              ) : (
                <div style={{ borderTop: addresses.length ? '1px solid #ECEAE3' : 'none', paddingTop: addresses.length ? '18px' : 0 }}>
                  <AddressForm onCreated={onAddressCreated} />
                </div>
              )}
            </div>
          </div>

          {/* Étape 3 — Transporteur */}
          <div style={cardStyle}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '18px' }}><span style={stepBadge(3, !!selAddress)}>3</span><span style={titleStyle}>{t('stepCarrier')}</span></div>
            {!selAddress ? (
              <div style={{ fontSize: '13.5px', color: '#8A8170' }}>{t('chooseAddressFirst')}</div>
            ) : loadingCarriers ? (
              <div style={{ fontSize: '13.5px', color: '#8A8170' }}>{t('loadingCarriers')}</div>
            ) : carriers.length ? (
              <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                {carriers.map((c) => {
                  const active = selCarrier === c.id_carrier;
                  return (
                    <button
                      key={c.id_carrier}
                      onClick={() => chooseCarrier(c.id_carrier)}
                      style={{ textAlign: 'left', cursor: 'pointer', background: active ? 'rgba(140,198,63,0.06)' : '#fff', border: active ? '1.5px solid #434343' : '1px solid #E2DECF', borderRadius: '7px', padding: '14px 16px', display: 'flex', gap: '12px', alignItems: 'center' }}
                    >
                      <span style={{ width: '15px', height: '15px', borderRadius: '50%', border: active ? '5px solid #434343' : '1.5px solid #C9C2AF', flex: 'none' }} />
                      <span style={{ flex: 1, minWidth: 0 }}>
                        <span style={{ display: 'block', fontSize: '13.5px', fontWeight: 600, color: '#1B2433' }}>{c.name.trim()}</span>
                        {c.delay ? <span style={{ display: 'block', fontSize: '12px', color: '#8A8170', marginTop: '2px', lineHeight: 1.4 }}>{c.delay}</span> : null}
                      </span>
                      <span style={{ fontSize: '13.5px', fontWeight: 700, color: c.price_incl_tax ? '#434343' : '#3F7256', flex: 'none' }}>{c.price_incl_tax ? `${fmt(c.price_incl_tax)} €` : t('free')}</span>
                    </button>
                  );
                })}
              </div>
            ) : (
              <div style={{ fontSize: '13.5px', color: '#8A8170' }}>{t('noCarriers')}</div>
            )}
          </div>

          {/* Étape 4 — Paiement */}
          <div style={cardStyle}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '18px' }}><span style={stepBadge(4, !!selCarrier)}>4</span><span style={titleStyle}>{t('stepPayment')}</span></div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
              {PAYMENTS.map((p) => {
                const active = payment === p;
                return (
                  <button
                    key={p}
                    onClick={() => setPayment(p)}
                    style={{ textAlign: 'left', cursor: 'pointer', background: active ? 'rgba(140,198,63,0.06)' : '#fff', border: active ? '1.5px solid #434343' : '1px solid #E2DECF', borderRadius: '7px', padding: '14px 16px', display: 'flex', gap: '12px', alignItems: 'center' }}
                  >
                    <span style={{ width: '15px', height: '15px', borderRadius: '50%', border: active ? '5px solid #434343' : '1.5px solid #C9C2AF', flex: 'none' }} />
                    <span style={{ fontSize: '13.5px', fontWeight: 600, color: '#1B2433' }}>{paymentLabel(p)}</span>
                  </button>
                );
              })}
              {cardConfigured ? (
                <>
                  <button
                    onClick={() => setPayment(VIVA_PAYMENT)}
                    style={{ textAlign: 'left', cursor: 'pointer', background: payment === VIVA_PAYMENT ? 'rgba(140,198,63,0.06)' : '#fff', border: payment === VIVA_PAYMENT ? '1.5px solid #434343' : '1px solid #E2DECF', borderRadius: '7px', padding: '14px 16px', display: 'flex', gap: '12px', alignItems: 'center' }}
                  >
                    <span style={{ width: '15px', height: '15px', borderRadius: '50%', border: payment === VIVA_PAYMENT ? '5px solid #434343' : '1.5px solid #C9C2AF', flex: 'none' }} />
                    <span style={{ flex: 1, fontSize: '13.5px', fontWeight: 600, color: '#1B2433' }}>{t('paymentCard')}</span>
                    {cardMode === 'demo' ? <span style={{ fontSize: '10.5px', fontWeight: 700, color: '#A8503A', background: 'rgba(168,80,58,.1)', padding: '2px 7px', borderRadius: '999px' }}>{t('demoBadge')}</span> : null}
                  </button>
                  {payment === VIVA_PAYMENT ? (
                    <div style={{ background: '#FAFAF7', border: '1px solid #E2DECF', borderRadius: '7px', padding: '14px 16px' }}>
                      {cardError ? <div style={{ fontSize: '12.5px', color: '#A8503A', marginBottom: '8px' }}>{cardError}</div> : null}
                      <div style={{ fontSize: '12px', color: '#6E7585' }}>{t.rich('cardRedirect', { b: (c) => <b>{c}</b> })}</div>
                    </div>
                  ) : null}
                </>
              ) : (
                <div style={{ fontSize: '12px', color: '#9A9A9A', background: '#FAFAF7', border: '1px dashed #E2DECF', borderRadius: '7px', padding: '12px 14px' }}>{t('cardSoon')}</div>
              )}
            </div>
          </div>
        </div>

        {/* Récapitulatif */}
        <div className="hfm-sticky" style={{ position: 'sticky', top: '130px', background: '#fff', border: '1px solid #ECEAE3', borderRadius: '8px', padding: '24px' }}>
          <div style={{ fontFamily: "'Spectral',serif", fontSize: '18px', color: '#2B2B2B', marginBottom: '16px' }}>{t('summary')}</div>
          <div style={{ display: 'flex', flexDirection: 'column', gap: '12px', maxHeight: '280px', overflowY: 'auto' }}>
            {cart.products.map((ci) => (
              <div key={ci.id_product} style={{ display: 'flex', gap: '12px', alignItems: 'center' }}><div style={{ width: '46px', height: '46px', flex: 'none', borderRadius: '6px', overflow: 'hidden', background: '#F7F6F2', border: '1px solid #ECEAE3' }}>{ci.image ? <img src={ci.image} alt={ci.name} style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block' }} /> : null}</div><div style={{ flex: 1, minWidth: 0 }}><div style={{ fontSize: '13px', color: '#34352F', lineHeight: 1.3 }}>{ci.name}</div><div style={{ fontSize: '11.5px', color: '#8A8170' }}>× {ci.quantity}</div></div><span style={{ fontSize: '13px', fontWeight: 600, color: '#434343' }}>{fmt(ci.total_excl_tax)} €</span></div>
            ))}
          </div>
          {/* Code promo */}
          <div style={{ borderTop: '1px solid #E7E3DA', marginTop: '16px', paddingTop: '16px' }}>
            <label style={{ display: 'block', fontSize: '12.5px', fontWeight: 600, color: '#34352F', marginBottom: '8px' }}>{t('voucherLabel')}</label>
            <div style={{ display: 'flex', gap: '8px' }}>
              <input
                type="text"
                value={voucherCode}
                onChange={(e) => setVoucherCode(e.target.value)}
                onKeyDown={(e) => { if (e.key === 'Enter') { e.preventDefault(); applyVoucher(); } }}
                placeholder={t('voucherPlaceholder')}
                style={{ flex: 1, minWidth: 0, fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '13px', color: '#2B2B2B', background: '#fff', border: '1px solid #E2DECF', borderRadius: '7px', padding: '10px 12px', outline: 'none' }}
              />
              <button
                onClick={applyVoucher}
                disabled={applyingVoucher || !voucherCode.trim()}
                style={{ flex: 'none', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '13px', fontWeight: 600, color: '#5E8E1F', background: 'rgba(140,198,63,0.1)', border: '1px solid rgba(155,209,89,.7)', borderRadius: '7px', padding: '10px 16px', cursor: applyingVoucher || !voucherCode.trim() ? 'not-allowed' : 'pointer', opacity: applyingVoucher || !voucherCode.trim() ? 0.5 : 1 }}
              >
                {applyingVoucher ? '…' : t('apply')}
              </button>
            </div>
            {voucherMsg ? <div style={{ fontSize: '12px', color: voucherMsg.kind === 'ok' ? '#3F7256' : '#A8503A', marginTop: '8px' }}>{voucherMsg.text}</div> : null}
          </div>

          <div style={{ borderTop: '1px solid #E7E3DA', marginTop: '16px', paddingTop: '16px' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '13px', color: '#55606F' }}><span>{t('subtotalExcl')}</span><span style={{ color: '#1B2433', fontWeight: 600 }}>{fmt(subtotalHT)} €</span></div>
            {discount > 0 ? <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '13px', color: '#3F7256', marginTop: '6px' }}><span>{t('discount')}</span><span style={{ fontWeight: 600 }}>−{fmt(discount)} €</span></div> : null}
            <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '13px', color: '#55606F', marginTop: '6px' }}><span>{t('shipping')}</span><span style={{ color: shipping ? '#1B2433' : '#8A8170', fontWeight: 600 }}>{shipping === null ? t('shippingToCalculate') : shipping ? `${fmt(shipping)} €` : t('shippingFree')}</span></div>
            <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '17px', marginTop: '12px', paddingTop: '12px', borderTop: '1px solid #E7E3DA' }}><span style={{ fontWeight: 600, color: '#2B2B2B' }}>{t('totalIncl')}</span><span style={{ fontFamily: "'Hanken Grotesk',sans-serif", fontWeight: 700, color: '#434343' }}>{fmt(grandTTC)} €</span></div>
          </div>
          {orderErr ? <div style={{ fontSize: '13px', color: '#A8503A', marginTop: '14px', textAlign: 'center' }}>{t('orderError', { error: orderErr })}</div> : null}
          <button
            onClick={placeOrder}
            disabled={!canOrder}
            style={{ width: '100%', marginTop: '18px', height: '52px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '15px', fontWeight: 600, color: '#fff', background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))', border: '1px solid rgba(255,255,255,.42)', boxShadow: '0 12px 26px -10px rgba(116,176,51,.55)', borderRadius: '999px', cursor: canOrder ? 'pointer' : 'not-allowed', opacity: canOrder ? 1 : 0.5, transition: 'background .2s ease' }}
          >
            {placing ? t('validating') : t('placeOrder', { amount: fmt(grandTTC) })}
          </button>
          {!selAddress || !selCarrier ? <div style={{ textAlign: 'center', fontSize: '11.5px', color: '#9A9A9A', marginTop: '12px' }}>{t('selectAddressCarrier')}</div> : <div style={{ textAlign: 'center', fontSize: '11.5px', color: '#9A9A9A', marginTop: '12px' }}>{t('secureOrder')}</div>}
        </div>
      </div>
    </main>
  );
}
