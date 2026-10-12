// Client serveur Viva Wallet — API "classique" en Basic auth (Merchant ID + API Key).
// AUCUN identifiant dédié PrestaShop / canal partenaire => PAS de commission 0,3 %.
// Cette voie ne nécessite ni Client Secret ni Source Code (contrairement au Smart Checkout v2 OAuth).
// LIVE uniquement si demandé EXPLICITEMENT (VIVA_ENV=live) : une variable absente ou mal saisie
// ne doit jamais faire encaisser de l'argent réel (ex. sur la pré-prod).
const ENV = process.env.VIVA_ENV === 'live' ? 'live' : 'demo';
const MERCHANT_ID = process.env.VIVA_MERCHANT_ID || '';
const API_KEY = process.env.VIVA_API_KEY || '';
// Source de paiement optionnelle (sinon la source par défaut du compte est utilisée).
const SOURCE_CODE = process.env.VIVA_SOURCE_CODE || '';

// Host "web" Viva (héberge l'API classique /api/* + la page de paiement /web/checkout).
const WEB = ENV === 'demo' ? 'https://demo.vivapayments.com' : 'https://www.vivapayments.com';

const BASIC = 'Basic ' + Buffer.from(`${MERCHANT_ID}:${API_KEY}`).toString('base64');

// Statuts de transaction Viva (API classique) : F = finalisée (payée), C = en compensation
// (clearing, fonds acquis), A = PRÉ-AUTORISÉE NON CAPTURÉE, E = échec, X = annulée, R = remboursée.
// `A` n'est PAS un paiement : une pré-autorisation jamais capturée expire sans fonds. On ne crée
// donc la commande « payée » que sur F/C.
export const VIVA_SUCCESS_STATUS = ['F', 'C'];
export const VIVA_FAILED_STATUS = ['E'];

// Tout passe par Merchant ID + API Key (qu'on possède déjà).
export const vivaConfigured = !!(MERCHANT_ID && API_KEY);
export const vivaEnv = ENV;

type CreateOrderArgs = {
  amountCents: number;
  email: string;
  fullName: string;
  merchantTrns: string; // ex. "cart:123;cust:45;lang:fr" (relu à la vérification)
  customerTrns: string; // libellé visible par le client
};

/** Crée une payment order (API classique, Basic auth) et renvoie l'orderCode + l'URL de paiement. */
export async function createOrder(a: CreateOrderArgs): Promise<{ orderCode: string; checkoutUrl: string }> {
  const body: Record<string, unknown> = {
    amount: a.amountCents,
    currencyCode: 978, // EUR
    customerTrns: a.customerTrns,
    merchantTrns: a.merchantTrns,
    paymentTimeout: 1800,
    requestLang: 'fr-FR',
    email: a.email,
    fullName: a.fullName,
    allowRecurring: false,
  };
  if (SOURCE_CODE) body.sourceCode = SOURCE_CODE;

  const r = await fetch(`${WEB}/api/orders`, {
    method: 'POST',
    headers: { Authorization: BASIC, 'Content-Type': 'application/json' },
    body: JSON.stringify(body),
    cache: 'no-store',
  });
  const data = await r.json().catch(() => ({}));
  if (!r.ok || !data.OrderCode || data.Success === false) {
    throw new Error('viva_order_failed: ' + (data.ErrorText || JSON.stringify(data)));
  }
  const orderCode = String(data.OrderCode);
  return { orderCode, checkoutUrl: `${WEB}/web/checkout?ref=${orderCode}` };
}

/** Récupère une transaction (API classique, Basic auth) pour vérifier le paiement. */
export async function getTransaction(transactionId: string): Promise<{
  ok: boolean;
  statusId?: string;
  amount?: number;
  merchantTrns?: string;
  orderCode?: string | number;
  raw: unknown;
}> {
  const r = await fetch(`${WEB}/api/transactions/${encodeURIComponent(transactionId)}`, {
    headers: { Authorization: BASIC },
    cache: 'no-store',
  });
  const data = await r.json().catch(() => ({}));
  if (!r.ok) return { ok: false, raw: data };
  // L'API classique renvoie une ENVELOPPE : { ErrorCode, ErrorText, Success, Transactions: [ {...} ] }
  // (cf. module officiel modules/vivawallet/controllers/front/success.php). Une transaction
  // inconnue répond HTTP 200 avec ErrorCode 404 et Transactions vide. Lire StatusId à la racine
  // (ancien code) donnait toujours `undefined` -> tout paiement CB finissait en « non confirmé ».
  // Repli sur la racine si un jour la réponse n'est plus enveloppée.
  const errorCode = Number(data?.ErrorCode ?? 0);
  const list: unknown[] = Array.isArray(data?.Transactions) ? data.Transactions : [];
  const t = (list.find(
    (x) => String((x as { TransactionId?: string })?.TransactionId || '').toLowerCase() === transactionId.toLowerCase()
  ) ?? list[0] ?? (data?.StatusId ? data : null)) as Record<string, unknown> | null;
  if (errorCode !== 0 || !t) return { ok: false, raw: data };
  const amount = Number(t.Amount);
  return {
    ok: true,
    statusId: typeof t.StatusId === 'string' ? t.StatusId : undefined,
    amount: Number.isFinite(amount) ? amount : undefined,
    merchantTrns: typeof t.MerchantTrns === 'string' ? t.MerchantTrns : undefined,
    orderCode: t.OrderCode as string | number | undefined,
    raw: data,
  };
}

/** Clé de vérification du webhook : Viva GET notre URL et attend { "Key": "<clé>" }. */
let webhookKeyCache: string | null = null;
export async function getWebhookKey(): Promise<string> {
  if (process.env.VIVA_WEBHOOK_KEY) return process.env.VIVA_WEBHOOK_KEY;
  if (webhookKeyCache) return webhookKeyCache;
  if (!vivaConfigured) return '';
  const r = await fetch(`${WEB}/api/messages/config/token`, {
    headers: { Authorization: BASIC },
    cache: 'no-store',
  });
  const data = await r.json().catch(() => ({}));
  webhookKeyCache = data?.Key || '';
  return webhookKeyCache as string;
}

/** Remboursement (total/partiel) — API classique, Basic auth. */
export async function refundTransaction(transactionId: string, amountCents: number): Promise<{ ok: boolean; data: unknown }> {
  const r = await fetch(`${WEB}/api/transactions/${encodeURIComponent(transactionId)}?amount=${amountCents}`, {
    method: 'DELETE',
    headers: { Authorization: BASIC, Accept: 'application/json' },
    cache: 'no-store',
  });
  const data = await r.json().catch(() => ({}));
  return { ok: r.ok, data };
}
