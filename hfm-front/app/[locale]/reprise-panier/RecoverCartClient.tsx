'use client';

import { useEffect, useRef, useState } from 'react';
import { useLocale, useTranslations } from 'next-intl';
import { useRouter } from 'next/navigation';
import { useStore } from '../../store';

// Lien « reprendre mon panier » des emails de relance (module hfmrelance) :
//   ?p=ID:QTE,ID:QTE[&c=CODE]
// Remplit le panier du visiteur puis ouvre le tunnel, code promo pré-rempli (appliqué par le
// checkout seulement s'il est valide). Même logique que l'ancien recover-cart.php.
export default function RecoverCartClient() {
  const t = useTranslations('recoverCart');
  const locale = useLocale();
  const router = useRouter();
  const { addToCart, authReady } = useStore();
  const [failed, setFailed] = useState(false);
  const started = useRef(false);

  useEffect(() => {
    if (!authReady || started.current) return;
    started.current = true;
    const params = new URLSearchParams(window.location.search);
    const p = params.get('p') ?? '';
    const c = params.get('c') ?? '';
    if (!/^(\d+:\d+)(,\d+:\d+)*$/.test(p)) {
      setFailed(true);
      return;
    }
    const code = /^[A-Za-z0-9-]{1,40}$/.test(c) ? c : '';
    (async () => {
      for (const pair of p.split(',').slice(0, 30)) {
        const [id, qty] = pair.split(':').map(Number);
        if (id > 0) await addToCart({ id, quantity: Math.max(1, Math.min(99, qty || 1)) });
      }
      router.replace(`/${locale}/checkout${code ? `?code=${encodeURIComponent(code)}` : ''}`);
    })();
  }, [authReady, addToCart, locale, router]);

  return (
    <main className="hfm-wrap" style={{ maxWidth: '560px', margin: '0 auto', padding: '90px 28px', textAlign: 'center', color: failed ? '#B42318' : '#8A8170', fontSize: '15px' }}>
      {failed ? t('invalid') : t('loading')}
    </main>
  );
}
