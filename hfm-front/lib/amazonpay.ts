// Client serveur Amazon Pay — Checkout v2 (région EU), requêtes signées AMZN-PAY-RSASSA-PSS-V2.
// Clés reprises du module PrestaShop 'amazonpay'. Flux par BOUTON JS (checkout.js) :
//  bouton signé -> Amazon -> review (UpdateCheckoutSession) -> Amazon -> result (GetCheckoutSession) -> commande.
import crypto from 'crypto';

const ENV = process.env.AMAZONPAY_ENV === 'sandbox' ? 'sandbox' : 'live';
const REGION_TLD = 'eu'; // AMAZONPAY_REGION=EU
const HOST = 'pay-api.amazon.eu';
const MERCHANT_ID = process.env.AMAZONPAY_MERCHANT_ID || '';
const PUBLIC_KEY_ID = process.env.AMAZONPAY_PUBLIC_KEY_ID || '';
const STORE_ID = process.env.AMAZONPAY_STORE_ID || '';
const PRIVATE_KEY = process.env.AMAZONPAY_PRIVATE_KEY_B64
  ? Buffer.from(process.env.AMAZONPAY_PRIVATE_KEY_B64, 'base64').toString('utf8')
  : '';

const ALGO = 'AMZN-PAY-RSASSA-PSS-V2';
const SALT = 32;

export const amazonConfigured = !!(MERCHANT_ID && PUBLIC_KEY_ID && STORE_ID && PRIVATE_KEY);
export const amazonEnv = ENV;
export const amazonStoreId = STORE_ID;
export const amazonMerchantId = MERCHANT_ID;
export const amazonPublicKeyId = PUBLIC_KEY_ID;

function hexSha(s: string): string {
  return crypto.createHash('sha256').update(s, 'utf8').digest('hex');
}

function rsaPssSign(stringToSign: string): string {
  return crypto
    .sign('sha256', Buffer.from(stringToSign, 'utf8'), { key: PRIVATE_KEY, padding: crypto.constants.RSA_PKCS1_PSS_PADDING, saltLength: SALT })
    .toString('base64');
}

function isoDate(): string {
  return new Date().toISOString().replace(/[:-]/g, '').replace(/\.\d{3}Z$/, 'Z');
}

/** Signature de bouton (createCheckoutSessionConfig) : signe le payload JSON. */
export function buttonSignature(payloadJSON: string): string {
  return rsaPssSign(ALGO + '\n' + hexSha(payloadJSON));
}

/** En-têtes signés pour un appel API (canonical request AMZN-PAY-RSASSA-PSS-V2 — ligne vide avant signedHeaders). */
function signedHeaders(method: string, uri: string, payload: string): Record<string, string> {
  const headers: Record<string, string> = {
    accept: 'application/json',
    'content-type': 'application/json',
    'x-amz-pay-date': isoDate(),
    'x-amz-pay-host': HOST,
    'x-amz-pay-region': REGION_TLD,
  };
  if (method === 'POST' || method === 'PATCH') headers['x-amz-pay-idempotency-key'] = crypto.randomUUID();
  const keys = Object.keys(headers).sort();
  const canonicalHeaders = keys.map((k) => `${k}:${String(headers[k]).trim()}`).join('\n') + '\n';
  const signed = keys.join(';');
  const canonicalRequest = method + '\n' + uri + '\n' + '\n' + canonicalHeaders + '\n' + signed + '\n' + hexSha(payload || '');
  const sig = rsaPssSign(ALGO + '\n' + hexSha(canonicalRequest));
  headers['authorization'] = `${ALGO} PublicKeyId=${PUBLIC_KEY_ID}, SignedHeaders=${signed}, Signature=${sig}`;
  return headers;
}

async function api(method: string, fragment: string, body?: unknown): Promise<{ status: number; data: any }> {
  const uri = `/${ENV}/v2/${fragment}`;
  const payload = body ? JSON.stringify(body) : '';
  const r = await fetch(`https://${HOST}${uri}`, { method, headers: signedHeaders(method, uri, payload), body: payload || undefined, cache: 'no-store' });
  const data = await r.json().catch(() => ({}));
  return { status: r.status, data };
}

/** Config du bouton Amazon Pay pour le front (payload signé). PayOnly : on a déjà l'adresse. */
export function buttonConfig(checkoutReviewReturnUrl: string) {
  const payload = {
    storeId: STORE_ID,
    webCheckoutDetails: { checkoutReviewReturnUrl },
    chargePermissionType: 'OneTime',
  };
  const payloadJSON = JSON.stringify(payload);
  return {
    merchantId: MERCHANT_ID,
    publicKeyId: PUBLIC_KEY_ID,
    ledgerCurrency: 'EUR',
    payloadJSON,
    signature: buttonSignature(payloadJSON),
    sandbox: ENV === 'sandbox',
  };
}

/** Après le retour "review" : fixe le montant + l'intention de capture, renvoie l'URL de redirection finale. */
export async function updateCheckoutSession(sessionId: string, amountEUR: number, resultReturnUrl: string): Promise<{ ok: boolean; redirectUrl?: string; data: any }> {
  const { status, data } = await api('PATCH', `checkoutSessions/${encodeURIComponent(sessionId)}`, {
    webCheckoutDetails: { checkoutResultReturnUrl: resultReturnUrl },
    paymentDetails: {
      paymentIntent: 'AuthorizeWithCapture',
      canHandlePendingAuthorization: false,
      chargeAmount: { amount: amountEUR.toFixed(2), currencyCode: 'EUR' },
    },
    merchantMetadata: { merchantStoreName: 'Hyaluronic Filler Market' },
  });
  return { ok: status >= 200 && status < 300, redirectUrl: data?.webCheckoutDetails?.amazonPayRedirectUrl, data };
}

/** Après le retour "result" : lit l'état de la session (Completed = payé). */
export async function getCheckoutSession(sessionId: string): Promise<{ ok: boolean; state?: string; chargeId?: string; data: any }> {
  const { status, data } = await api('GET', `checkoutSessions/${encodeURIComponent(sessionId)}`);
  return { ok: status >= 200 && status < 300, state: data?.statusDetails?.state, chargeId: data?.chargeId, data };
}
