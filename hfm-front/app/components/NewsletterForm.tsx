'use client';

import { useState } from 'react';
import { useTranslations, useLocale } from 'next-intl';

// Formulaire newsletter RÉEL (le bloc était purement décoratif : un <input> et un <button> sans
// <form> ni handler -> le canal d'acquisition ne captait rien, alors que l'ancien site collectait
// sur chaque page). Poste vers /api/newsletter -> bridge -> table native ps_emailsubscription.
type State = 'idle' | 'loading' | 'ok' | 'already' | 'error';

export default function NewsletterForm() {
  const t = useTranslations('home');
  const locale = useLocale();
  const [email, setEmail] = useState('');
  const [state, setState] = useState<State>('idle');

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (state === 'loading') return;
    setState('loading');
    try {
      const r = await fetch('/api/newsletter', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, locale }),
      });
      const d = await r.json();
      if (!r.ok || d.error) {
        setState('error');
        return;
      }
      setState(d.status === 'already' ? 'already' : 'ok');
      setEmail('');
    } catch {
      setState('error');
    }
  };

  const msg = state === 'ok' ? t('newsOk')
    : state === 'already' ? t('newsAlready')
    : state === 'error' ? t('newsError')
    : null;

  return (
    <>
      <form onSubmit={submit} style={{ display: 'flex', gap: '10px', maxWidth: '480px', margin: '28px auto 0', background: 'rgba(255,255,255,.10)', border: '1px solid rgba(255,255,255,.16)', borderRadius: '999px', padding: '6px', backdropFilter: 'blur(10px)', WebkitBackdropFilter: 'blur(10px)' }}>
        <input
          type="email"
          required
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          placeholder={t('newsPlaceholder')}
          aria-label={t('newsPlaceholder')}
          style={{ flex: 1, height: '46px', padding: '0 20px', border: 'none', borderRadius: '999px', background: 'transparent', color: '#fff', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14px', outline: 'none' }}
        />
        <button
          type="submit"
          disabled={state === 'loading'}
          style={{ color: '#fff', background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))', border: '1px solid rgba(255,255,255,.42)', boxShadow: '0 12px 24px -10px rgba(140,198,63,.6)', backdropFilter: 'blur(8px) saturate(140%)', WebkitBackdropFilter: 'blur(8px) saturate(140%)', borderRadius: '999px', padding: '0 28px', height: '46px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14px', fontWeight: 600, cursor: state === 'loading' ? 'wait' : 'pointer', opacity: state === 'loading' ? 0.7 : 1 }}
        >
          {state === 'loading' ? '…' : t('newsSubmit')}
        </button>
      </form>
      {msg && (
        <div
          role="status"
          style={{ marginTop: '12px', fontSize: '13.5px', color: state === 'error' ? '#F3B0B0' : '#B7E486' }}
        >
          {msg}
        </div>
      )}
    </>
  );
}
