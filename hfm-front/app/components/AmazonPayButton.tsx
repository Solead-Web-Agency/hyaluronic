'use client';

import { useEffect, useRef } from 'react';
import { useLocale } from 'next-intl';

const SCRIPT_SRC = 'https://static-eu.payments-amazon.com/checkout.js';

// Charge checkout.js une seule fois.
function loadAmazonScript(): Promise<boolean> {
  return new Promise((resolve) => {
    if (typeof window === 'undefined') return resolve(false);
    const w = window as unknown as { amazon?: { Pay?: unknown } };
    if (w.amazon?.Pay) return resolve(true);
    const existing = document.querySelector(`script[src="${SCRIPT_SRC}"]`);
    if (existing) {
      existing.addEventListener('load', () => resolve(true));
      existing.addEventListener('error', () => resolve(false));
      if (w.amazon?.Pay) resolve(true);
      return;
    }
    const s = document.createElement('script');
    s.src = SCRIPT_SRC;
    s.async = true;
    s.onload = () => resolve(true);
    s.onerror = () => resolve(false);
    document.head.appendChild(s);
  });
}

/** Bouton officiel Amazon Pay (région EU). Rend le bouton avec une config signée côté serveur. */
export default function AmazonPayButton({ idCart }: { idCart: number }) {
  const locale = useLocale();
  const ref = useRef<HTMLDivElement>(null);
  const done = useRef(false);

  useEffect(() => {
    let alive = true;
    (async () => {
      if (done.current || !ref.current) return;
      const r = await fetch('/api/payment/amazon/config', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_cart: idCart, locale }),
      }).catch(() => null);
      if (!r || !r.ok) return;
      const cfg = await r.json();
      const ok = await loadAmazonScript();
      const w = window as unknown as { amazon?: { Pay?: { renderButton: (sel: string, opts: unknown) => void } } };
      if (!alive || !ok || !w.amazon?.Pay || done.current || !ref.current) return;
      done.current = true;
      w.amazon.Pay.renderButton('#amazon-pay-btn', {
        merchantId: cfg.merchantId,
        ledgerCurrency: cfg.ledgerCurrency,
        sandbox: cfg.sandbox,
        checkoutLanguage: cfg.checkoutLanguage,
        productType: cfg.productType,
        placement: cfg.placement,
        buttonColor: 'Gold',
        createCheckoutSessionConfig: { payloadJSON: cfg.payloadJSON, signature: cfg.signature, publicKeyId: cfg.publicKeyId },
      });
    })();
    return () => { alive = false; };
  }, [idCart, locale]);

  return <div ref={ref} id="amazon-pay-btn" />;
}
