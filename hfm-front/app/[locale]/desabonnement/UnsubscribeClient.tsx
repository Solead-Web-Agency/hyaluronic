'use client';

import { useState } from 'react';
import { useTranslations } from 'next-intl';

// Désinscription en deux temps (affichage puis clic) : les robots des messageries qui ouvrent
// les liens à l'avance ne désinscrivent donc personne. Le lien est signé (bridge : HMAC).
export default function UnsubscribeClient() {
  const t = useTranslations('unsubscribe');
  const [state, setState] = useState<'idle' | 'busy' | 'done' | 'error'>('idle');
  const params = typeof window !== 'undefined' ? new URLSearchParams(window.location.search) : null;
  const e = params?.get('e') ?? '';
  const s = params?.get('s') ?? '';
  const valid = !!e && !!s;

  const confirm = async () => {
    setState('busy');
    try {
      const r = await fetch('/api/newsletter', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'unsubscribe', e, s }),
      });
      setState(r.ok ? 'done' : 'error');
    } catch {
      setState('error');
    }
  };

  return (
    <main className="hfm-wrap" style={{ maxWidth: '560px', margin: '0 auto', padding: '80px 28px', textAlign: 'center' }}>
      <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: '32px', color: '#2B2B2B', margin: 0 }}>{t('title')}</h1>
      {state === 'done' ? (
        <p role="status" style={{ fontSize: '15px', lineHeight: 1.6, color: '#3F7256', margin: '20px 0 0' }}>{t('done')}</p>
      ) : !valid || state === 'error' ? (
        <p role="alert" style={{ fontSize: '15px', lineHeight: 1.6, color: '#B42318', margin: '20px 0 0' }}>{t('invalid')}</p>
      ) : (
        <>
          <p style={{ fontSize: '15px', lineHeight: 1.6, color: '#55606F', margin: '20px 0 0' }}>{t('text')}</p>
          <button
            onClick={confirm}
            disabled={state === 'busy'}
            style={{ marginTop: '26px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '15px', fontWeight: 600, color: '#fff', background: '#4F7A1A', border: 'none', borderRadius: '999px', padding: '14px 30px', cursor: state === 'busy' ? 'default' : 'pointer', opacity: state === 'busy' ? 0.7 : 1 }}
          >
            {t('confirm')}
          </button>
        </>
      )}
    </main>
  );
}
