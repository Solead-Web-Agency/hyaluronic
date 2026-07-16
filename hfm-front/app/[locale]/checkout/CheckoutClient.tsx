'use client';

import { useCallback, useEffect, useState } from 'react';
import { useTranslations, useLocale } from 'next-intl';
import { Link } from '@/i18n/navigation';
import { useRouter } from '@/i18n/navigation';
import { useStore } from '../../store';
import { fmt } from '@/lib/cardModel';
import { trackPurchase, trackAdsConversion, type EcItem } from '@/lib/gtm';
import AddressForm, { type Address } from '../../components/AddressForm';
import AmazonPayButton from '../../components/AmazonPayButton';

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
const PAYPAL_PAYMENT = 'PayPal';
const AMAZON_PAYMENT = 'Amazon Pay';

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

// ---------- Logos des moyens de paiement (images officielles dans public/pay/) ----------
const CardLogos: React.ReactElement = (
  <span style={{ display: 'inline-flex', gap: '5px', flex: 'none', alignItems: 'center' }}>
    <img src="/pay/visa.svg" alt="Visa" loading="lazy" decoding="async" height={23} style={{ height: '23px', width: 'auto', display: 'block' }} />
    <img src="/pay/mastercard.svg" alt="Mastercard" loading="lazy" decoding="async" height={23} style={{ height: '23px', width: 'auto', display: 'block' }} />
    <img src="/pay/cb.svg" alt="CB" loading="lazy" decoding="async" height={23} style={{ height: '23px', width: 'auto', display: 'block' }} />
  </span>
);
const PaypalLogo: React.ReactElement = (
  <img src="/pay/paypal.svg" alt="PayPal" style={{ height: '22px', width: 'auto', display: 'block', flex: 'none' }} />
);
const AmazonLogo: React.ReactElement = (
  <img src="/pay/amazonpay.png" alt="Amazon Pay" style={{ height: '20px', width: 'auto', display: 'block', flex: 'none' }} />
);
const BankIcon: React.ReactElement = (
  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#55606F" strokeWidth={1.6} strokeLinecap="round" strokeLinejoin="round" style={{ flex: 'none' }} aria-hidden="true"><path d="M3 21h18M5 21V10M19 21V10M9 21v-7h6v7M3 10l9-6 9 6z" /></svg>
);
const ChequeIcon: React.ReactElement = (
  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#55606F" strokeWidth={1.6} strokeLinecap="round" strokeLinejoin="round" style={{ flex: 'none' }} aria-hidden="true"><rect x="3" y="6" width="18" height="12" rx="2" /><path d="M7 11h7M7 14h4M16.4 13.6l1.1 1.1 2-2.2" /></svg>
);
// Fallback transporteur sans logo (colis)
const ParcelIcon: React.ReactElement = (
  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#9AA391" strokeWidth={1.5} strokeLinecap="round" strokeLinejoin="round" aria-hidden="true"><path d="M3 8l9-5 9 5v8l-9 5-9-5z" /><path d="M3 8l9 5 9-5M12 13v8" /></svg>
);

export default function CheckoutClient() {
  const { cart, customer, authReady, refreshCart, guestCheckout, updateLine, removeLine } = useStore();
  const router = useRouter();
  const t = useTranslations('checkout');
  const tc = useTranslations('common');
  const tcart = useTranslations('cart');
  const locale = useLocale();

  const paymentLabel = (p: string) =>
    p === 'Virement bancaire' ? t('paymentTransfer') : p === 'Chèque' ? t('paymentCheck') : p;

  const [addresses, setAddresses] = useState<Address[]>([]);
  const [selAddress, setSelAddress] = useState<number | null>(null);
  const [showAddrForm, setShowAddrForm] = useState(false);
  const [editAddr, setEditAddr] = useState<Address | null>(null);

  const [carriers, setCarriers] = useState<Carrier[]>([]);
  const [selCarrier, setSelCarrier] = useState<number | null>(null);
  const [loadingCarriers, setLoadingCarriers] = useState(false);

  // Mode invité (commande sans compte)
  const [guestEmail, setGuestEmail] = useState('');
  const [guestFirst, setGuestFirst] = useState('');
  const [guestLast, setGuestLast] = useState('');
  const [guestBusy, setGuestBusy] = useState(false);
  const [guestErr, setGuestErr] = useState<string | null>(null);
  const [enteredAsGuest, setEnteredAsGuest] = useState(false); // identité saisie en invité → propose « Modifier »
  const [editIdentity, setEditIdentity] = useState(false); // rouvre le formulaire pour modifier les coordonnées invité

  const [payment, setPayment] = useState<string>(PAYMENTS[0]);
  const [acceptedTerms, setAcceptedTerms] = useState(false); // CGV + politique de confidentialité (obligatoire)
  const [totals, setTotals] = useState<Totals | null>(null);

  // RPPS / ADELI — requis seulement si le panier contient un produit réservé praticiens.
  const [rpps, setRpps] = useState('');
  const [rppsInfo, setRppsInfo] = useState<{ nom?: string; prenom?: string; profession?: string } | null>(null);
  const [rppsChecking, setRppsChecking] = useState(false);
  const [rppsSoftNote, setRppsSoftNote] = useState(false); // numéro accepté mais non rapproché au registre
  // Deux voies pour les produits réservés praticiens : numéro RPPS, OU attestation "pro" (moins de friction).
  const [rppsMode, setRppsMode] = useState<'number' | 'pro'>('number');
  const [attestation, setAttestation] = useState(false);
  const [proDoc, setProDoc] = useState<{ name: string; data: string } | null>(null);
  const [rppsValidated, setRppsValidated] = useState(false); // déjà validé pour ce compte / cet email
  const [editPro, setEditPro] = useState(false); // rouvrir le formulaire malgré une validation existante
  const rppsNeeded = cart.rpps_required;
  const rppsValid = /^\d{9,13}$/.test(rpps.trim()); // permissif : 9 à 13 chiffres
  // Exigence satisfaite : déjà validé (compte/email) OU numéro RPPS valide OU attestation cochée.
  const rppsSatisfied = rppsValidated || (rppsMode === 'pro' ? attestation : rppsValid);
  const PRO_CONTACT_EMAIL = 'sales@hyaluronicfillermarket.com';

  // Enregistre l'attestation pro (+ justificatif optionnel) sur le client, façon set-rpps.
  const submitProAttestation = async () => {
    try {
      await fetch('/api/customer', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'set-pro-attestation', attestation: 1, ...(proDoc ? { doc_name: proDoc.name, doc_data: proDoc.data } : {}) }),
      });
    } catch {
      /* ignore — la commande reste possible, vérif back-office */
    }
  };

  // Lit un fichier choisi en base64 (pour l'envoyer au bridge via JSON).
  const onProDocChange = (file: File | null) => {
    if (!file) { setProDoc(null); return; }
    const reader = new FileReader();
    reader.onload = () => setProDoc({ name: file.name, data: String(reader.result || '') });
    reader.readAsDataURL(file);
  };

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
  const [confirmation, setConfirmation] = useState<{ reference: string; id_order: number; total_paid: number; paid?: boolean; items?: EcItem[] } | null>(null);

  // Snapshot des lignes panier, à écrire AVANT toute redirection PSP : au retour, la page est
  // rechargée et le panier déjà converti -> sans ça le purchase GA4 partirait sans items.
  // Clé fixe (un seul checkout en vol) : on l'écrit à chaque départ et on la purge à chaque issue,
  // pour qu'un paiement abandonné ne laisse jamais ses items contaminer la commande suivante.
  const stashPendingItems = () => {
    try {
      const pending: EcItem[] = cart.products.map((l) => ({
        id: l.id_product,
        name: l.name,
        price: l.unit_price_incl_tax,
        quantity: l.quantity,
      }));
      localStorage.setItem('hfm_pending_items', JSON.stringify(pending));
    } catch {
      /* localStorage indisponible : le purchase partira sans items détaillés */
    }
  };
  const clearPendingItems = () => {
    try {
      localStorage.removeItem('hfm_pending_items');
    } catch {
      /* ignore */
    }
  };

  // GA4 purchase + conversion Google Ads à la confirmation. Dédup par référence (évite le
  // double-comptage sur un rafraîchissement de la page de retour paiement viva/paypal/amazon).
  useEffect(() => {
    if (!confirmation?.reference) return;
    const k = 'hfm_purchase_' + confirmation.reference;
    // localStorage peut lever (Safari « bloquer les cookies », politique d'entreprise, extension) :
    // on NE laisse JAMAIS la dédup faire échouer l'envoi. En cas d'indisponibilité on tire quand
    // même (une conversion en double vaut mieux qu'une conversion perdue — et Google dédoublonne
    // de toute façon sur transaction_id).
    let alreadySent = false;
    try {
      alreadySent = !!localStorage.getItem(k);
    } catch {
      /* stockage indisponible : on tire, quitte à doublonner (Google dédoublonne par transaction_id) */
    }
    if (alreadySent) return;

    trackPurchase({ reference: confirmation.reference, value: confirmation.total_paid, items: confirmation.items });
    // Conversion Google Ads en direct (parité ancien site, indépendant du conteneur GTM).
    // UNIQUEMENT si la commande est ENCAISSÉE : l'ancien module ne taguait que les commandes
    // `valid` -> un virement/chèque en attente de paiement n'a JAMAIS généré de conversion.
    // Sans ce garde, le Smart Bidding enchérit sur du CA qui peut ne jamais rentrer.
    // transaction_id = id_order numérique (parité stricte avec l'ancien module).
    if (confirmation.paid) {
      trackAdsConversion({ transactionId: confirmation.id_order, value: confirmation.total_paid });
    }

    // Marque APRÈS envoi : si l'écriture échoue, le tracking est déjà parti.
    try {
      localStorage.setItem(k, '1');
    } catch {
      /* ignore */
    }
  }, [confirmation]);

  // Code promo
  const [voucherCode, setVoucherCode] = useState('');
  const [voucherDiscount, setVoucherDiscount] = useState<number>(0);
  const [voucherMsg, setVoucherMsg] = useState<{ kind: 'ok' | 'err'; text: string } | null>(null);
  const [applyingVoucher, setApplyingVoucher] = useState(false);

  // Paiement CB (Viva Wallet) — affiché si le PSP est configuré côté serveur.
  const [cardConfigured, setCardConfigured] = useState(false);
  const [cardMode, setCardMode] = useState<string>('live');
  const [cardError, setCardError] = useState<string | null>(null);
  // PayPal — affiché si configuré côté serveur.
  const [paypalConfigured, setPaypalConfigured] = useState(false);
  // Amazon Pay — affiché si configuré côté serveur.
  const [amazonConfigured, setAmazonConfigured] = useState(false);

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

  // Préremplit le statut "pro" si déjà validé pour ce compte OU cet email (anti-friction).
  useEffect(() => {
    if (!customer) return;
    let alive = true;
    fetch('/api/customer?action=me')
      .then((r) => (r.ok ? r.json() : null))
      .then((d) => {
        if (!alive) return;
        const c = d?.customer;
        if (!c) return;
        // Réinitialise selon le statut RÉEL du client courant (email actuel) — sinon le "déjà validé"
        // resterait collé après un changement d'email vers une adresse jamais validée.
        setRpps(c.rpps ? String(c.rpps) : '');
        setAttestation(!!c.pro_attestation);
        setRppsMode(c.rpps ? 'number' : c.pro_attestation ? 'pro' : 'number');
        setRppsValidated(!!c.rpps_validated);
        setEditPro(false);
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

  // Modif quantité / suppression d'un produit depuis le récapitulatif. On rafraîchit les totaux
  // ET la liste des transporteurs : le franco (offert/payant par transporteur) dépend du total
  // qui peut repasser au-dessus/au-dessous du seuil de 400 € HT.
  const changeQty = async (id_product: number, quantity: number) => {
    if (quantity < 1) return;
    await updateLine(id_product, quantity);
    await Promise.all([reloadTotals(), loadCarriers()]);
  };
  const removeProduct = async (id_product: number) => {
    await removeLine(id_product);
    await Promise.all([reloadTotals(), loadCarriers()]);
  };

  // Config paiement : détecte si le PSP CB (Viva Wallet) est activé côté serveur.
  useEffect(() => {
    let alive = true;
    fetch('/api/payment')
      .then((r) => (r.ok ? r.json() : { configured: false }))
      .then((d) => {
        if (!alive) return;
        const vivaOk = !!d.configured;
        const ppOk = !!d.paypal?.configured;
        const azOk = !!d.amazon?.configured;
        setCardConfigured(vivaOk);
        setCardMode(d.mode || 'live');
        setPaypalConfigured(ppOk);
        setAmazonConfigured(azOk);
        // Sélection par défaut préférée : CB > PayPal > Amazon Pay > (virement/chèque), sans écraser un choix manuel.
        setPayment((cur) => (cur === PAYMENTS[0] ? (vivaOk ? VIVA_PAYMENT : ppOk ? PAYPAL_PAYMENT : azOk ? AMAZON_PAYMENT : cur) : cur));
      })
      .catch(() => {});
    return () => {
      alive = false;
    };
  }, []);

  // Amazon Pay : le bouton SDK redirige directement (sans passer par placeOrder). La commande
  // est créée au RETOUR serveur, qui lit le RPPS / l'attestation STOCKÉS → on les persiste ici.
  // (Le numéro RPPS est déjà enregistré au blur du champ ; ici on couvre l'attestation pro.)
  useEffect(() => {
    if (payment === AMAZON_PAYMENT && rppsNeeded && rppsMode === 'pro' && attestation) {
      submitProAttestation();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [payment, attestation, rppsMode, rppsNeeded]);

  // Amazon Pay : le bouton SDK redirige SANS passer par placeOrder -> le snapshot des lignes n'y
  // serait jamais écrit, et le purchase du retour lirait un stash périmé laissé par un paiement
  // précédent (items d'une AUTRE commande). On le réécrit donc dès qu'Amazon est sélectionné.
  useEffect(() => {
    if (payment === AMAZON_PAYMENT && cart.products.length) {
      stashPendingItems();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [payment, cart.products]);

  // Retour depuis Viva : /api/payment/return a vérifié + créé la commande, puis nous a
  // redirigés vers /{langue}/checkout avec le résultat en query (viva_paid / viva_failed).
  useEffect(() => {
    if (typeof window === 'undefined') return;
    const params = new URLSearchParams(window.location.search);
    const clean = () => window.history.replaceState({}, '', window.location.pathname);
    if (params.get('viva_failed')) {
      setOrderErr(t('paymentNotConfirmed'));
      clearPendingItems();
      clean();
      return;
    }
    if (params.get('viva_cancel')) {
      setOrderErr(t('paymentCancelled'));
      clearPendingItems();
      clean();
      return;
    }
    if (params.get('paypal_failed')) {
      setOrderErr(t('paymentNotConfirmed'));
      clearPendingItems();
      clean();
      return;
    }
    if (params.get('paypal_cancel')) {
      setOrderErr(t('paymentCancelled'));
      clearPendingItems();
      clean();
      return;
    }
    if (params.get('amazon_failed')) {
      setOrderErr(t('paymentNotConfirmed'));
      clearPendingItems();
      clean();
      return;
    }
    if (params.get('viva_paid') || params.get('paypal_paid') || params.get('amazon_paid')) {
      // Items GA4 stashés avant la redirection PSP (best effort).
      let items: EcItem[] | undefined;
      try {
        const raw = localStorage.getItem('hfm_pending_items');
        if (raw) items = JSON.parse(raw) as EcItem[];
      } catch {
        /* ignore */
      }
      clearPendingItems();
      setConfirmation({
        reference: params.get('ref') || '',
        id_order: Number(params.get('order') || 0),
        total_paid: Number(params.get('total') || 0),
        // Retour PSP `*_paid` = paiement réellement capté -> conversion Ads légitime.
        paid: true,
        items,
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
    setEditAddr(null);
    chooseAddress(id_address);
  };

  // Suppression d'une adresse (soft-delete côté PrestaShop).
  const deleteAddress = async (id_address: number) => {
    try {
      const r = await fetch('/api/customer', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete-address', id_address }),
      });
      const d = await r.json();
      if (d.deleted) {
        setAddresses(d.addresses ?? []);
        if (selAddress === id_address) setSelAddress(null);
      }
    } catch {
      /* ignore */
    }
  };

  // Finalise la commande côté serveur (POST order) — partagé offline + Stripe.
  const finalizeOrder = async (paymentMethod: string) => {
    const trimmedRpps = rpps.trim();
    const pro = rppsNeeded && rppsMode === 'pro';
    if (pro) await submitProAttestation();
    const r = await fetch('/api/checkout', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'order', id_cart: idCart, payment_method: paymentMethod, ...(pro ? { pro_attestation: 1 } : trimmedRpps ? { rpps: trimmedRpps } : {}) }),
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
    // Snapshot des lignes AVANT vidage du panier -> items GA4 du purchase (prix TTC, cohérent view_item).
    const purchaseItems: EcItem[] = cart.products.map((l) => ({
      id: l.id_product,
      name: l.name,
      price: l.unit_price_incl_tax,
      quantity: l.quantity,
    }));
    // `paid` vient du bridge (état « Paiement accepté » uniquement) : virement/chèque -> false,
    // donc pas de conversion Ads tant que l'argent n'est pas encaissé (parité ancien module).
    setConfirmation({ reference: d.reference, id_order: d.id_order, total_paid: d.total_paid, paid: !!d.paid, items: purchaseItems });
    // Chemin offline : pas de redirection, les items viennent directement d'ici. On purge quand
    // même le stash (posé au début de placeOrder) pour ne rien laisser traîner.
    clearPendingItems();
    localStorage.removeItem('id_cart');
    await refreshCart();
    window.scrollTo({ top: 0 });
    return true;
  };

  const placeOrder = async () => {
    if (!idCart || !selAddress || !selCarrier) return;
    if (rppsNeeded && !rppsSatisfied) {
      setRppsErr(t(rppsMode === 'pro' ? 'attestationRequired' : 'rppsInvalid'));
      return;
    }
    setPlacing(true);
    setOrderErr(null);
    setRppsErr(null);
    setCardError(null);
    try {
      // Snapshot des lignes AVANT toute redirection PSP -> items GA4 relus au retour.
      stashPendingItems();
      // Chemin carte bancaire (Viva Wallet) : on crée l'order puis on REDIRIGE vers Smart Checkout.
      if (cardConfigured && payment === VIVA_PAYMENT) {
        // La commande Viva est créée plus tard côté serveur (/api/payment/return), qui lit
        // le RPPS / l'attestation STOCKÉS : on les enregistre donc AVANT de rediriger.
        if (rppsNeeded && rppsMode === 'pro') {
          await submitProAttestation();
        } else {
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

      // Chemin PayPal : on stocke le RPPS / l'attestation (relus côté serveur au retour) puis on redirige.
      if (paypalConfigured && payment === PAYPAL_PAYMENT) {
        if (rppsNeeded && rppsMode === 'pro') {
          await submitProAttestation();
        } else {
          const trimmedRpps = rpps.trim();
          if (trimmedRpps) {
            try {
              await fetch('/api/customer', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'set-rpps', rpps: trimmedRpps }),
              });
            } catch {
              /* ignore — validation finale côté serveur au retour */
            }
          }
        }
        const pr = await fetch('/api/payment/paypal', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id_cart: idCart, locale }),
        });
        const pay = await pr.json();
        if (!pr.ok || !pay.checkout_url) {
          setOrderErr(t('cardUnavailable'));
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
  // Identité invité — créée DANS le tunnel (étape 1), sans page-portail bloquante.
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
    } else {
      setEnteredAsGuest(true);
      setEditIdentity(false);
    }
  };
  const gInput: React.CSSProperties = { height: '46px', padding: '0 14px', border: '1px solid #E2DECF', borderRadius: '6px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14px', outline: 'none', background: '#fff', width: '100%', boxSizing: 'border-box', color: '#34352F' };

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

  // (Plus de page-portail invité : l'identité invité est collectée DANS le tunnel, en étape 1.)

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
  // Compteur "livraison offerte" (seuil 400 € HT, identique au tiroir panier).
  const freeShipRemain = Math.max(0, 400 - subtotalHT);
  const freeShipPct = Math.min(100, (subtotalHT / 400) * 100);
  const shipping = selCarrier ? (totals?.shipping_incl_tax ?? 0) : null;
  const grandTTC = selCarrier ? (totals?.total_incl_tax ?? cart.total_incl_tax) : (totals?.products_incl_tax ?? cart.total_incl_tax);
  // Remise : préfère le total réel du panier, sinon le retour de l'API voucher.
  const discount = (totals?.total_discounts ?? 0) || voucherDiscount;
  const canOrder = !!selAddress && !!selCarrier && !!payment && !placing && (!rppsNeeded || rppsSatisfied) && acceptedTerms;

  return (
    <main data-screen-label="Commande" className="hfm-wrap" style={{ maxWidth: '1180px', margin: '0 auto', padding: '34px 28px 70px' }}>
      <div style={{ fontSize: '12.5px', color: '#9A9A9A', marginBottom: '18px' }}><Link href="/" style={{ cursor: 'pointer' }}>{tc('home')}</Link>  /  <Link href="/catalogue" style={{ cursor: 'pointer' }}>{tc('catalogue')}</Link>  /  {t('breadcrumb')}</div>
      <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: 'clamp(25px,3.6vw,34px)', color: '#2B2B2B', margin: '0 0 28px' }}>{t('finalize')}</h1>
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit,minmax(300px,1fr))', gap: '40px', alignItems: 'start' }}>
        <div style={{ display: 'flex', flexDirection: 'column', gap: '22px' }}>

          {/* Étape 1 — Coordonnées */}
          <div style={cardStyle}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '18px' }}><span style={stepBadge(1, !!customer)}>1</span><span style={titleStyle}>{t('stepContact')}</span></div>
            {customer && !editIdentity ? (
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: '12px' }}>
                <div>
                  <div style={{ fontSize: '15px', color: '#34352F', fontWeight: 600 }}>{customer.firstname} {customer.lastname}</div>
                  <div style={{ fontSize: '14px', color: '#6E7585', marginTop: '4px' }}>{customer.email}</div>
                </div>
                {enteredAsGuest || customer.is_guest ? (
                  <button type="button" onClick={() => { setGuestFirst(customer.firstname); setGuestLast(customer.lastname); setGuestEmail(customer.email); setGuestErr(null); setEditIdentity(true); }} style={{ background: 'none', border: 'none', padding: 0, color: '#5E8E1F', fontSize: '12.5px', fontWeight: 600, cursor: 'pointer', textDecoration: 'underline', flex: 'none' }}>{t('rppsModify')}</button>
                ) : null}
              </div>
            ) : (
              <>
                <p style={{ fontSize: '13.5px', color: '#55606F', margin: '0 0 14px' }}>{t('guestSubtitle')}</p>
                <form onSubmit={submitGuest} style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                  <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
                    <input value={guestFirst} onChange={(e) => setGuestFirst(e.target.value)} placeholder={t('firstname')} style={gInput} />
                    <input value={guestLast} onChange={(e) => setGuestLast(e.target.value)} placeholder={t('lastname')} style={gInput} />
                  </div>
                  <input type="email" value={guestEmail} onChange={(e) => setGuestEmail(e.target.value)} placeholder={t('emailLabel')} style={gInput} />
                  {guestErr ? <div style={{ fontSize: '13px', color: '#A8503A' }}>{guestErr}</div> : null}
                  <button type="submit" disabled={guestBusy} style={{ height: '48px', borderRadius: '999px', color: '#fff', background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))', border: '1px solid rgba(255,255,255,.42)', boxShadow: '0 12px 26px -10px rgba(116,176,51,.55)', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '15px', fontWeight: 600, cursor: guestBusy ? 'default' : 'pointer', opacity: guestBusy ? 0.7 : 1 }}>
                    {guestBusy ? tc('loading') : t('guestContinue')}
                  </button>
                </form>
                <div style={{ marginTop: '12px', display: 'flex', gap: '18px', flexWrap: 'wrap', alignItems: 'center' }}>
                  <button type="button" onClick={() => router.push('/compte?next=/checkout')} style={{ background: 'none', border: 'none', padding: 0, color: '#5E8E1F', fontSize: '12.5px', fontWeight: 600, cursor: 'pointer', textDecoration: 'underline' }}>{t('haveAccount')}</button>
                  {customer && editIdentity ? (
                    <button type="button" onClick={() => setEditIdentity(false)} style={{ background: 'none', border: 'none', padding: 0, color: '#9A9A9A', fontSize: '12.5px', fontWeight: 600, cursor: 'pointer', textDecoration: 'underline' }}>{t('cancel')}</button>
                  ) : null}
                </div>
              </>
            )}

            {/* Produits réservés praticiens : numéro RPPS OU attestation professionnelle (moins de friction) */}
            {customer ? (
            <div style={{ marginTop: '18px', borderTop: '1px solid #ECEAE3', paddingTop: '18px' }}>
              {rppsNeeded && rppsValidated && !editPro ? (
                <div style={{ display: 'flex', gap: '10px', alignItems: 'flex-start', padding: '12px 14px', background: 'rgba(63,114,86,.08)', border: '1px solid rgba(63,114,86,.25)', borderRadius: '7px' }}>
                  <span style={{ color: '#3F7256', fontSize: '14px', lineHeight: 1.3, flex: 'none' }} aria-hidden="true">✓</span>
                  <span style={{ flex: 1, fontSize: '12.5px', lineHeight: 1.5, color: '#34352F' }}>
                    {t('rppsAlreadyValidated')}
                    <button type="button" onClick={() => setEditPro(true)} style={{ marginLeft: '8px', background: 'none', border: 'none', padding: 0, color: '#5E8E1F', fontSize: '12.5px', fontWeight: 600, cursor: 'pointer', textDecoration: 'underline' }}>{t('rppsModify')}</button>
                  </span>
                </div>
              ) : (
              <>
              {rppsNeeded ? (
                <div style={{ display: 'flex', gap: '10px', alignItems: 'flex-start', marginBottom: '12px', padding: '11px 13px', background: 'rgba(168,80,58,.08)', border: '1px solid rgba(168,80,58,.22)', borderRadius: '7px' }}>
                  <span style={{ color: '#A8503A', fontSize: '14px', lineHeight: 1.3, flex: 'none' }} aria-hidden="true">⚕</span>
                  <span style={{ fontSize: '12.5px', lineHeight: 1.5, color: '#A8503A', fontWeight: 500 }}>{t('rppsRequiredNotice')}</span>
                </div>
              ) : null}

              {/* Choix de la voie (seulement si un produit l'exige) */}
              {rppsNeeded ? (
                <div style={{ display: 'flex', gap: '8px', marginBottom: '14px', flexWrap: 'wrap' }}>
                  {(['number', 'pro'] as const).map((m) => {
                    const on = rppsMode === m;
                    return (
                      <button
                        key={m}
                        type="button"
                        onClick={() => { setRppsMode(m); setRppsErr(null); }}
                        style={{ cursor: 'pointer', flex: '1 1 0', minWidth: '170px', textAlign: 'left', background: on ? 'rgba(140,198,63,0.08)' : '#fff', border: on ? '1.5px solid #434343' : '1px solid #E2DECF', borderRadius: '7px', padding: '11px 13px', display: 'flex', gap: '10px', alignItems: 'center' }}
                      >
                        <span style={{ width: '14px', height: '14px', borderRadius: '50%', border: on ? '4.5px solid #434343' : '1.5px solid #C9C2AF', flex: 'none' }} />
                        <span style={{ fontSize: '12.5px', fontWeight: 600, color: '#34352F' }}>{m === 'number' ? t('rppsModeNumber') : t('rppsModePro')}</span>
                      </button>
                    );
                  })}
                </div>
              ) : null}

              {/* Voie 1 — numéro RPPS / ADELI (toujours proposé hors-exigence, en optionnel) */}
              {!rppsNeeded || rppsMode === 'number' ? (
                <>
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
                    style={{ width: '100%', boxSizing: 'border-box', height: '46px', padding: '0 14px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14px', color: '#34352F', background: '#fff', border: `1px solid ${rppsErr && rppsMode === 'number' ? '#A8503A' : rppsInfo ? '#3F7256' : '#E2DECF'}`, borderRadius: '6px', outline: 'none' }}
                  />
                  {rppsChecking ? (
                    <div style={{ fontSize: '12px', color: '#9A9A9A', marginTop: '8px' }}>{t('rppsChecking')}</div>
                  ) : rppsInfo ? (
                    <div style={{ fontSize: '12.5px', color: '#3F7256', marginTop: '8px', fontWeight: 600 }}>✓ {[rppsInfo.prenom, rppsInfo.nom].filter(Boolean).join(' ')}{rppsInfo.profession ? ` — ${rppsInfo.profession}` : ''}</div>
                  ) : rppsErr && rppsMode === 'number' ? (
                    <div style={{ fontSize: '12px', color: '#A8503A', marginTop: '8px' }}>{rppsErr}</div>
                  ) : rppsSoftNote ? (
                    <div style={{ fontSize: '11.5px', color: '#9A9A9A', marginTop: '8px' }}>{t('rppsWillBeVerified')}</div>
                  ) : (
                    <div style={{ fontSize: '11.5px', color: '#9A9A9A', marginTop: '8px' }}>{t('rppsPlaceholder')}</div>
                  )}
                </>
              ) : null}

              {/* Voie 2 — attestation professionnelle */}
              {rppsNeeded && rppsMode === 'pro' ? (
                <div style={{ background: '#FAFAF7', border: '1px solid #E2DECF', borderRadius: '7px', padding: '14px 16px' }}>
                  <label style={{ display: 'flex', gap: '10px', alignItems: 'flex-start', cursor: 'pointer' }}>
                    <input type="checkbox" checked={attestation} onChange={(e) => { setAttestation(e.target.checked); if (rppsErr) setRppsErr(null); }} style={{ marginTop: '2px', width: '16px', height: '16px', flex: 'none', accentColor: '#5E8E1F' }} />
                    <span style={{ fontSize: '12.5px', lineHeight: 1.5, color: '#34352F' }}>{t('attestationText')}</span>
                  </label>
                  <div style={{ marginTop: '14px' }}>
                    <div style={{ fontSize: '12.5px', fontWeight: 700, color: '#34352F', marginBottom: '10px' }}>{t('attestationDoc')}</div>

                    {/* Option A — téléverser un fichier (bouton stylé, input natif masqué) */}
                    <div style={{ display: 'flex', alignItems: 'center', gap: '12px', flexWrap: 'wrap' }}>
                      <label style={{ display: 'inline-flex', alignItems: 'center', gap: '8px', cursor: 'pointer', background: 'rgba(140,198,63,0.08)', border: '1px solid rgba(155,209,89,.8)', color: '#5E8E1F', borderRadius: '8px', padding: '10px 16px', fontSize: '13px', fontWeight: 600 }}>
                        <span aria-hidden="true" style={{ display: 'inline-flex' }}><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8} strokeLinecap="round" strokeLinejoin="round"><path d="M12 15V4M8 8l4-4 4 4" /><path d="M5 16v2a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2" /></svg></span>{t('attestationDocChoose')}
                        <input type="file" accept=".pdf,.jpg,.jpeg,.png,.webp,.heic,image/*,application/pdf" onChange={(e) => onProDocChange(e.target.files?.[0] || null)} style={{ display: 'none' }} />
                      </label>
                      {proDoc ? (
                        <span style={{ display: 'inline-flex', alignItems: 'center', gap: '8px', fontSize: '12.5px', color: '#3F7256', fontWeight: 600, background: 'rgba(63,114,86,.08)', border: '1px solid rgba(63,114,86,.25)', borderRadius: '999px', padding: '6px 8px 6px 12px' }}>
                          <span style={{ overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', maxWidth: '170px' }}>✓ {proDoc.name}</span>
                          <button type="button" onClick={() => setProDoc(null)} aria-label="Retirer" style={{ background: 'none', border: 'none', padding: 0, color: '#3F7256', cursor: 'pointer', fontSize: '13px', lineHeight: 1, flex: 'none' }}>✕</button>
                        </span>
                      ) : null}
                    </div>

                    {/* séparateur OU */}
                    <div style={{ display: 'flex', alignItems: 'center', gap: '10px', margin: '14px 0' }}>
                      <span style={{ flex: 1, height: '1px', background: '#E7E3DA' }} />
                      <span style={{ fontSize: '11px', color: '#9A9A9A', textTransform: 'uppercase', letterSpacing: '.1em' }}>{t('orSeparator')}</span>
                      <span style={{ flex: 1, height: '1px', background: '#E7E3DA' }} />
                    </div>

                    {/* Option B — par email (encart distinct, mis en évidence) */}
                    <a href={`mailto:${PRO_CONTACT_EMAIL}`} style={{ display: 'flex', alignItems: 'center', gap: '11px', textDecoration: 'none', background: '#fff', border: '1px solid #E2DECF', borderRadius: '8px', padding: '11px 14px' }}>
                      <span aria-hidden="true" style={{ flex: 'none', color: '#5E8E1F', display: 'inline-flex' }}><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.7} strokeLinecap="round" strokeLinejoin="round"><rect x="3" y="5" width="18" height="14" rx="2" /><path d="m3 7 9 6 9-6" /></svg></span>
                      <span style={{ lineHeight: 1.35 }}>
                        <span style={{ display: 'block', fontSize: '12.5px', fontWeight: 600, color: '#34352F' }}>{t('attestationEmailTitle')}</span>
                        <span style={{ fontSize: '13px', color: '#5E8E1F', fontWeight: 700 }}>{PRO_CONTACT_EMAIL}</span>
                      </span>
                    </a>
                    <div style={{ fontSize: '11.5px', color: '#9A9A9A', marginTop: '10px', lineHeight: 1.5 }}>{t('attestationAfterOrder')}</div>
                  </div>
                  {rppsErr && rppsMode === 'pro' ? <div style={{ fontSize: '12px', color: '#A8503A', marginTop: '8px' }}>{rppsErr}</div> : null}
                </div>
              ) : null}
              </>
              )}
            </div>
            ) : null}
          </div>

          {/* Étapes 2-4 : visibles mais verrouillées tant que l'étape 1 (identité) n'est pas validée */}
          <div style={{ display: 'flex', flexDirection: 'column', gap: '22px', ...(customer ? {} : { opacity: 0.5, pointerEvents: 'none', userSelect: 'none' }) }} aria-disabled={!customer}>
          {/* Étape 2 — Livraison (adresse) */}
          <div style={cardStyle}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '18px' }}><span style={stepBadge(2, !!customer)}>2</span><span style={titleStyle}>{t('stepAddress')}</span></div>
            {addresses.length ? (
              <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                {addresses.map((a) => {
                  const active = selAddress === a.id_address;
                  return (
                    <div key={a.id_address} style={{ background: active ? 'rgba(140,198,63,0.06)' : '#fff', border: active ? '1.5px solid #434343' : '1px solid #E2DECF', borderRadius: '7px', overflow: 'hidden' }}>
                      <button
                        onClick={() => chooseAddress(a.id_address)}
                        style={{ width: '100%', textAlign: 'left', cursor: 'pointer', background: 'transparent', border: 'none', padding: '14px 16px 10px', display: 'flex', gap: '12px', alignItems: 'flex-start' }}
                      >
                        <span style={{ marginTop: '2px', width: '15px', height: '15px', borderRadius: '50%', border: active ? '5px solid #434343' : '1.5px solid #C9C2AF', flex: 'none' }} />
                        <span>
                          <span style={{ display: 'block', fontSize: '13.5px', fontWeight: 600, color: '#1B2433' }}>{a.alias || `${a.firstname} ${a.lastname}`.trim() || 'Adresse'}</span>
                          {a.company ? <span style={{ display: 'block', fontSize: '12.5px', color: '#1B2433', marginTop: '2px' }}>{a.company}</span> : null}
                          <span style={{ display: 'block', fontSize: '12.5px', color: '#6E7585', marginTop: '3px', lineHeight: 1.45 }}>{a.address1}, {a.postcode} {a.city} · {a.country}</span>
                          {a.vat_number ? (
                            a.vat_checkable === false ? (
                              // Pays non soumis à validation (ex. livraison France) : pas d'exonération possible.
                              <span style={{ display: 'inline-flex', alignItems: 'center', gap: '6px', marginTop: '6px', fontSize: '11px', fontWeight: 600, borderRadius: '999px', padding: '3px 9px', color: '#7A7568', background: '#F1EFE9', border: '1px solid #E2DECF' }}>
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><circle cx="12" cy="12" r="9" /><path d="M12 8v.5M12 11v5" /></svg>
                                {a.vat_number} · {t('vatNotApplicable')}
                              </span>
                            ) : (
                              <span style={{
                                display: 'inline-flex', alignItems: 'center', gap: '6px', marginTop: '6px', fontSize: '11px', fontWeight: 700, borderRadius: '999px', padding: '3px 9px',
                                ...(a.vat_valid
                                  ? { color: '#4B6B1B', background: 'rgba(140,198,63,.14)', border: '1px solid rgba(140,198,63,.32)' }
                                  : a.vat_invalid
                                    ? { color: '#A8503A', background: 'rgba(168,80,58,.08)', border: '1px solid rgba(168,80,58,.28)' }
                                    : { color: '#8A7A3A', background: 'rgba(196,160,46,.12)', border: '1px solid rgba(196,160,46,.32)' }),
                              }}>
                                {a.vat_valid ? (
                                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round"><path d="M20 6L9 17l-5-5" /></svg>
                                ) : a.vat_invalid ? (
                                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round"><path d="M18 6L6 18M6 6l12 12" /></svg>
                                ) : (
                                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></svg>
                                )}
                                {a.vat_number} · {a.vat_valid ? t('vatValidBadge') : a.vat_invalid ? t('vatInvalidBadge') : t('vatPendingBadge')}
                              </span>
                            )
                          ) : null}
                        </span>
                      </button>
                      <div style={{ display: 'flex', gap: '16px', padding: '0 16px 11px 43px' }}>
                        <button onClick={() => { setEditAddr(a); setShowAddrForm(false); }} style={{ background: 'none', border: 'none', cursor: 'pointer', fontSize: '12px', fontWeight: 600, color: '#5E8E1F', display: 'inline-flex', alignItems: 'center', gap: '5px', padding: 0 }}>
                          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinecap="round" strokeLinejoin="round"><path d="M12 20h9" /><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4z" /></svg>{t('edit')}
                        </button>
                        <button onClick={() => deleteAddress(a.id_address)} style={{ background: 'none', border: 'none', cursor: 'pointer', fontSize: '12px', fontWeight: 600, color: '#A8503A', display: 'inline-flex', alignItems: 'center', gap: '5px', padding: 0 }}>
                          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinecap="round" strokeLinejoin="round"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6" /></svg>{t('delete')}
                        </button>
                      </div>
                    </div>
                  );
                })}
              </div>
            ) : null}
            <div style={{ marginTop: addresses.length ? '14px' : 0 }}>
              {!(showAddrForm || editAddr) ? (
                <button onClick={() => { setEditAddr(null); setShowAddrForm(true); }} style={{ background: 'rgba(140,198,63,0.1)', border: '1px solid rgba(155,209,89,.7)', borderRadius: '999px', padding: '9px 16px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '13px', fontWeight: 600, color: '#5E8E1F', cursor: 'pointer' }}>{t('addAddress')}</button>
              ) : (
                <div style={{ borderTop: addresses.length ? '1px solid #ECEAE3' : 'none', paddingTop: addresses.length ? '18px' : 0 }}>
                  <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '12px' }}>
                    <span style={{ fontSize: '13px', fontWeight: 700, color: '#34352F' }}>{editAddr ? t('editAddress') : t('addAddress')}</span>
                    <button onClick={() => { setShowAddrForm(false); setEditAddr(null); }} style={{ background: 'none', border: 'none', cursor: 'pointer', fontSize: '12.5px', color: '#8A8170', textDecoration: 'underline' }}>{tc('cancel')}</button>
                  </div>
                  <AddressForm key={editAddr?.id_address ?? 'new'} address={editAddr ?? undefined} onCreated={onAddressCreated} />
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
                      <span style={{ width: '58px', height: '34px', flex: 'none', display: 'inline-flex', alignItems: 'center', justifyContent: 'center' }}>{c.logo ? <img src={c.logo} alt="" style={{ maxWidth: '100%', maxHeight: '100%', objectFit: 'contain', display: 'block' }} /> : ParcelIcon}</span>
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
              {/* 1. Carte bancaire (Viva Wallet) — en premier */}
              {cardConfigured ? (
                <>
                  <button
                    onClick={() => setPayment(VIVA_PAYMENT)}
                    style={{ textAlign: 'left', cursor: 'pointer', background: payment === VIVA_PAYMENT ? 'rgba(140,198,63,0.06)' : '#fff', border: payment === VIVA_PAYMENT ? '1.5px solid #434343' : '1px solid #E2DECF', borderRadius: '7px', padding: '14px 16px', display: 'flex', gap: '12px', alignItems: 'center' }}
                  >
                    <span style={{ width: '15px', height: '15px', borderRadius: '50%', border: payment === VIVA_PAYMENT ? '5px solid #434343' : '1.5px solid #C9C2AF', flex: 'none' }} />
                    {CardLogos}
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

              {/* 2. PayPal */}
              {paypalConfigured ? (
                <>
                  <button
                    onClick={() => setPayment(PAYPAL_PAYMENT)}
                    style={{ textAlign: 'left', cursor: 'pointer', background: payment === PAYPAL_PAYMENT ? 'rgba(140,198,63,0.06)' : '#fff', border: payment === PAYPAL_PAYMENT ? '1.5px solid #434343' : '1px solid #E2DECF', borderRadius: '7px', padding: '14px 16px', display: 'flex', gap: '12px', alignItems: 'center' }}
                  >
                    <span style={{ width: '15px', height: '15px', borderRadius: '50%', border: payment === PAYPAL_PAYMENT ? '5px solid #434343' : '1.5px solid #C9C2AF', flex: 'none' }} />
                    {PaypalLogo}
                    <span style={{ flex: 1 }} />
                  </button>
                  {payment === PAYPAL_PAYMENT ? (
                    <div style={{ background: '#FAFAF7', border: '1px solid #E2DECF', borderRadius: '7px', padding: '14px 16px' }}>
                      <div style={{ fontSize: '12px', color: '#6E7585' }}>{t('paypalRedirect')}</div>
                    </div>
                  ) : null}
                </>
              ) : null}

              {/* 3. Amazon Pay */}
              {amazonConfigured ? (
                <>
                  <button
                    onClick={() => setPayment(AMAZON_PAYMENT)}
                    style={{ textAlign: 'left', cursor: 'pointer', background: payment === AMAZON_PAYMENT ? 'rgba(140,198,63,0.06)' : '#fff', border: payment === AMAZON_PAYMENT ? '1.5px solid #434343' : '1px solid #E2DECF', borderRadius: '7px', padding: '14px 16px', display: 'flex', gap: '12px', alignItems: 'center' }}
                  >
                    <span style={{ width: '15px', height: '15px', borderRadius: '50%', border: payment === AMAZON_PAYMENT ? '5px solid #434343' : '1.5px solid #C9C2AF', flex: 'none' }} />
                    {AmazonLogo}
                    <span style={{ flex: 1 }} />
                  </button>
                  {payment === AMAZON_PAYMENT ? (
                    <div style={{ background: '#FAFAF7', border: '1px solid #E2DECF', borderRadius: '7px', padding: '14px 16px' }}>
                      <div style={{ fontSize: '12px', color: '#6E7585' }}>{t('amazonRedirect')}</div>
                    </div>
                  ) : null}
                </>
              ) : null}

              {/* 4. Hors-ligne : virement / chèque */}
              {PAYMENTS.map((p) => {
                const active = payment === p;
                return (
                  <button
                    key={p}
                    onClick={() => setPayment(p)}
                    style={{ textAlign: 'left', cursor: 'pointer', background: active ? 'rgba(140,198,63,0.06)' : '#fff', border: active ? '1.5px solid #434343' : '1px solid #E2DECF', borderRadius: '7px', padding: '14px 16px', display: 'flex', gap: '12px', alignItems: 'center' }}
                  >
                    <span style={{ width: '15px', height: '15px', borderRadius: '50%', border: active ? '5px solid #434343' : '1.5px solid #C9C2AF', flex: 'none' }} />
                    {p === 'Chèque' ? ChequeIcon : BankIcon}
                    <span style={{ flex: 1, fontSize: '13.5px', fontWeight: 600, color: '#1B2433' }}>{paymentLabel(p)}</span>
                  </button>
                );
              })}
            </div>
            {/* Acceptation CGV + politique de confidentialité (obligatoire pour commander) */}
            <label style={{ display: 'flex', gap: '10px', alignItems: 'flex-start', marginTop: '18px', paddingTop: '16px', borderTop: '1px solid #ECEAE3', cursor: 'pointer' }}>
              <input type="checkbox" checked={acceptedTerms} onChange={(e) => setAcceptedTerms(e.target.checked)} style={{ marginTop: '1px', width: '16px', height: '16px', flex: 'none', accentColor: '#5E8E1F' }} />
              <span style={{ fontSize: '12.5px', lineHeight: 1.5, color: '#55606F' }}>
                {t.rich('acceptTerms', {
                  cgv: (c) => <Link href="/content/conditions-generales-de-ventes" target="_blank" style={{ color: '#5E8E1F', textDecoration: 'underline' }}>{c}</Link>,
                  pp: (c) => <Link href="/content/politique-de-confidentialite" target="_blank" style={{ color: '#5E8E1F', textDecoration: 'underline' }}>{c}</Link>,
                })}
              </span>
            </label>
          </div>
          </div>
        </div>

        {/* Récapitulatif */}
        <div className="hfm-sticky" style={{ position: 'sticky', top: '130px', background: '#fff', border: '1px solid #ECEAE3', borderRadius: '8px', padding: '24px' }}>
          <div style={{ fontFamily: "'Spectral',serif", fontSize: '18px', color: '#2B2B2B', marginBottom: '16px' }}>{t('summary')}</div>
          {/* Compteur livraison offerte (seuil 400 € HT) */}
          <div style={{ background: 'rgba(168,80,58,0.06)', border: '1px solid rgba(168,80,58,0.2)', borderRadius: '10px', padding: '12px 14px', marginBottom: '16px' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
              <span style={{ width: '30px', height: '30px', flex: 'none', borderRadius: '8px', background: '#fff', border: '1px solid rgba(168,80,58,0.25)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#A8503A' }} aria-hidden="true"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.6} strokeLinecap="round" strokeLinejoin="round"><path d="M3 6.5h10v9H3z" /><path d="M13 9.5h4l3 3v3h-7z" /><circle cx="7" cy="17.5" r="1.7" /><circle cx="17" cy="17.5" r="1.7" /></svg></span>
              <span style={{ fontSize: '12.5px', lineHeight: 1.4, color: subtotalHT >= 400 ? '#3F7256' : '#7A3A2C' }}>
                {subtotalHT >= 400
                  ? tcart('freeShippingUnlocked')
                  : tcart.rich('freeShippingRemaining', { amount: fmt(freeShipRemain), b: (c) => <b style={{ color: '#A8503A' }}>{c}</b> })}
              </span>
            </div>
            <div style={{ height: '7px', background: 'rgba(168,80,58,0.12)', borderRadius: '999px', marginTop: '10px', overflow: 'hidden' }}>
              <div style={{ height: '100%', width: `${freeShipPct}%`, background: subtotalHT >= 400 ? '#3F7256' : 'linear-gradient(90deg,#E98A7A,#A8503A)', borderRadius: '999px', transition: 'width .4s ease' }} />
            </div>
          </div>
          <div style={{ display: 'flex', flexDirection: 'column', gap: '12px', maxHeight: '280px', overflowY: 'auto' }}>
            {cart.products.map((ci) => (
              <div key={ci.id_product} style={{ display: 'flex', gap: '12px', alignItems: 'flex-start' }}>
                <Link href={`/produit/${ci.id_product}`} style={{ width: '46px', height: '46px', flex: 'none', borderRadius: '6px', overflow: 'hidden', background: '#F7F6F2', border: '1px solid #ECEAE3', display: 'block', cursor: 'pointer' }}>
                  {ci.image ? <img src={ci.image} alt={ci.name} style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block' }} /> : null}
                </Link>
                <div style={{ flex: 1, minWidth: 0 }}>
                  <Link href={`/produit/${ci.id_product}`} style={{ display: 'block', fontSize: '13px', color: '#34352F', lineHeight: 1.3, textDecoration: 'none', cursor: 'pointer' }}>{ci.name}</Link>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '9px', marginTop: '7px' }}>
                    <div style={{ display: 'flex', alignItems: 'center', border: '1px solid #E2DECF', borderRadius: '6px', overflow: 'hidden' }}>
                      <button type="button" onClick={() => changeQty(ci.id_product, ci.quantity - 1)} disabled={ci.quantity <= 1} aria-label="−" style={{ width: '24px', height: '24px', background: '#fff', border: 'none', color: ci.quantity <= 1 ? '#D2CDBE' : '#434343', cursor: ci.quantity <= 1 ? 'default' : 'pointer', fontSize: '15px', lineHeight: 1, padding: 0 }}>−</button>
                      <span style={{ minWidth: '24px', textAlign: 'center', fontSize: '12.5px', fontWeight: 600, color: '#34352F' }}>{ci.quantity}</span>
                      <button type="button" onClick={() => changeQty(ci.id_product, ci.quantity + 1)} aria-label="+" style={{ width: '24px', height: '24px', background: '#fff', border: 'none', color: '#434343', cursor: 'pointer', fontSize: '15px', lineHeight: 1, padding: 0 }}>+</button>
                    </div>
                    <button type="button" onClick={() => removeProduct(ci.id_product)} title={t('delete')} aria-label={t('delete')} style={{ background: 'none', border: 'none', color: '#B0A99A', cursor: 'pointer', padding: 0, display: 'inline-flex', alignItems: 'center' }}>
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6" /></svg>
                    </button>
                  </div>
                </div>
                <span style={{ fontSize: '13px', fontWeight: 600, color: '#434343', flex: 'none' }}>{fmt(ci.total_excl_tax)} €</span>
              </div>
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
          {rppsNeeded && !rppsSatisfied ? (
            <div style={{ display: 'flex', gap: '9px', alignItems: 'flex-start', marginTop: '14px', padding: '10px 12px', background: 'rgba(168,80,58,.08)', border: '1px solid rgba(168,80,58,.22)', borderRadius: '7px' }}>
              <span style={{ color: '#A8503A', fontSize: '13px', lineHeight: 1.3, flex: 'none' }} aria-hidden="true">⚕</span>
              <span style={{ fontSize: '11.5px', lineHeight: 1.45, color: '#A8503A', fontWeight: 500 }}>{t('rppsRequiredNotice')}</span>
            </div>
          ) : null}
          {payment === AMAZON_PAYMENT && amazonConfigured ? (
            selAddress && selCarrier && idCart && (!rppsNeeded || rppsSatisfied) && acceptedTerms ? (
              <div style={{ marginTop: '18px' }}><AmazonPayButton idCart={idCart} /></div>
            ) : (
              <div style={{ width: '100%', marginTop: '18px', minHeight: '52px', boxSizing: 'border-box', display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '12px 16px', textAlign: 'center', borderRadius: '999px', background: '#EFEDE5', color: '#9A9A9A', fontSize: '13px', lineHeight: 1.4 }}>{rppsNeeded && !rppsSatisfied ? t('rppsRequiredNotice') : !acceptedTerms ? t('acceptTermsShort') : t('selectAddressCarrier')}</div>
            )
          ) : (
            <button
              onClick={placeOrder}
              disabled={!canOrder}
              style={{ width: '100%', marginTop: '18px', height: '52px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '15px', fontWeight: 600, color: '#fff', background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))', border: '1px solid rgba(255,255,255,.42)', boxShadow: '0 12px 26px -10px rgba(116,176,51,.55)', borderRadius: '999px', cursor: canOrder ? 'pointer' : 'not-allowed', opacity: canOrder ? 1 : 0.5, transition: 'background .2s ease' }}
            >
              {placing ? t('validating') : t('placeOrder', { amount: fmt(grandTTC) })}
            </button>
          )}
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '5px', fontSize: '11.5px', color: '#9A9A9A', marginTop: '12px' }}>{!selAddress || !selCarrier ? t('selectAddressCarrier') : !acceptedTerms ? t('acceptTermsShort') : (<><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" style={{ flex: 'none' }} aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2" /><path d="M8 11V7a4 4 0 0 1 8 0v4" /></svg>{t('secureOrder')}</>)}</div>
        </div>
      </div>
    </main>
  );
}
