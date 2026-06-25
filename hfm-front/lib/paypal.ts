// Client serveur PayPal — REST Orders v2 (flux par redirection "approve").
// Clés reprises du module PrestaShop 'paypal' (PAYPAL_API_CLIENT_ID/SECRET).
const ENV = process.env.PAYPAL_ENV === 'sandbox' ? 'sandbox' : 'live';
const CLIENT_ID = process.env.PAYPAL_CLIENT_ID || '';
const CLIENT_SECRET = process.env.PAYPAL_CLIENT_SECRET || '';

const API = ENV === 'sandbox' ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';

export const paypalConfigured = !!(CLIENT_ID && CLIENT_SECRET);
export const paypalEnv = ENV;

// ---- Jeton d'accès OAuth2 (client_credentials), mis en cache jusqu'à expiration ----
let tokenCache: { token: string; exp: number } | null = null;

async function getAccessToken(): Promise<string> {
  const now = Date.now();
  if (tokenCache && tokenCache.exp > now + 60_000) return tokenCache.token;
  const basic = Buffer.from(`${CLIENT_ID}:${CLIENT_SECRET}`).toString('base64');
  const r = await fetch(`${API}/v1/oauth2/token`, {
    method: 'POST',
    headers: { Authorization: `Basic ${basic}`, 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'grant_type=client_credentials',
    cache: 'no-store',
  });
  const d = await r.json().catch(() => ({}));
  if (!r.ok || !d.access_token) throw new Error('paypal_token_failed: ' + JSON.stringify(d));
  tokenCache = { token: d.access_token, exp: now + Number(d.expires_in || 32400) * 1000 };
  return d.access_token;
}

type CreateOrderArgs = {
  amountEUR: number; // montant TTC en euros
  customId: string; // "cart:123;cust:45;lang:fr" (relu à la capture)
  returnUrl: string;
  cancelUrl: string;
  description?: string;
};

/** Crée une commande PayPal (intent CAPTURE) et renvoie l'id + l'URL d'approbation à ouvrir. */
export async function createOrder(a: CreateOrderArgs): Promise<{ orderId: string; approveUrl: string }> {
  const token = await getAccessToken();
  const body = {
    intent: 'CAPTURE',
    purchase_units: [
      {
        amount: { currency_code: 'EUR', value: a.amountEUR.toFixed(2) },
        custom_id: a.customId,
        description: (a.description || 'Hyaluronic Filler Market').slice(0, 127),
      },
    ],
    application_context: {
      brand_name: 'Hyaluronic Filler Market',
      user_action: 'PAY_NOW',
      shipping_preference: 'NO_SHIPPING',
      return_url: a.returnUrl,
      cancel_url: a.cancelUrl,
    },
  };
  const r = await fetch(`${API}/v2/checkout/orders`, {
    method: 'POST',
    headers: { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' },
    body: JSON.stringify(body),
    cache: 'no-store',
  });
  const d = await r.json().catch(() => ({}));
  if (!r.ok || !d.id) throw new Error('paypal_create_failed: ' + JSON.stringify(d));
  const link = (d.links || []).find((l: { rel: string; href: string }) => l.rel === 'approve' || l.rel === 'payer-action');
  if (!link?.href) throw new Error('paypal_no_approve_link: ' + JSON.stringify(d.links));
  return { orderId: String(d.id), approveUrl: String(link.href) };
}

type CaptureResult = {
  ok: boolean;
  status?: string; // COMPLETED attendu
  captureId?: string;
  amount?: number;
  customId?: string;
  raw: unknown;
};

/** Capture une commande PayPal approuvée. Renvoie le statut + l'id de capture (= transaction). */
export async function captureOrder(orderId: string): Promise<CaptureResult> {
  const token = await getAccessToken();
  const r = await fetch(`${API}/v2/checkout/orders/${encodeURIComponent(orderId)}/capture`, {
    method: 'POST',
    headers: { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' },
    cache: 'no-store',
  });
  const d = await r.json().catch(() => ({}));
  if (!r.ok) return { ok: false, raw: d };
  const pu = d.purchase_units?.[0];
  const cap = pu?.payments?.captures?.[0];
  return {
    ok: true,
    status: d.status,
    captureId: cap?.id ? String(cap.id) : undefined,
    amount: cap?.amount?.value ? Number(cap.amount.value) : undefined,
    customId: cap?.custom_id || pu?.custom_id || undefined,
    raw: d,
  };
}

/** Remboursement (total/partiel) d'une capture PayPal. */
export async function refundCapture(captureId: string, amountEUR?: number): Promise<{ ok: boolean; data: unknown }> {
  const token = await getAccessToken();
  const body = amountEUR != null ? JSON.stringify({ amount: { currency_code: 'EUR', value: amountEUR.toFixed(2) } }) : undefined;
  const r = await fetch(`${API}/v2/payments/captures/${encodeURIComponent(captureId)}/refund`, {
    method: 'POST',
    headers: { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' },
    body,
    cache: 'no-store',
  });
  const data = await r.json().catch(() => ({}));
  return { ok: r.ok, data };
}
