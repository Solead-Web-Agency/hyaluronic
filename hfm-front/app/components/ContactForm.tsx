'use client';

import { useEffect, useState } from 'react';
import { useTranslations, useLocale } from 'next-intl';

// Formulaire de contact (parité ancien site : choix du service + message + pièce jointe).
// Le headless l'avait remplacé par un `mailto:` -> leads et SAV perdus sur une boutique B2B.
type Contact = { id_contact: number; name: string };
type State = 'idle' | 'loading' | 'ok' | 'error';

const field: React.CSSProperties = {
  width: '100%', padding: '12px 14px', border: '1.5px solid #E2DECF', borderRadius: '8px',
  fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14.5px', color: '#34352F',
  background: '#fff', outline: 'none',
};

export default function ContactForm() {
  const t = useTranslations('contact');
  const locale = useLocale();
  const [contacts, setContacts] = useState<Contact[]>([]);
  const [idContact, setIdContact] = useState<number>(0);
  const [email, setEmail] = useState('');
  const [message, setMessage] = useState('');
  const [file, setFile] = useState<File | null>(null);
  const [state, setState] = useState<State>('idle');
  const [errKey, setErrKey] = useState<string>('error');

  useEffect(() => {
    fetch(`/api/contact?locale=${locale}`)
      .then((r) => r.json())
      .then((d) => {
        const list: Contact[] = Array.isArray(d?.contacts) ? d.contacts : [];
        setContacts(list);
        if (list.length) setIdContact(list[0].id_contact);
      })
      .catch(() => {});
  }, [locale]);

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (state === 'loading') return;
    setState('loading');
    try {
      const fd = new FormData();
      fd.set('email', email);
      fd.set('message', message);
      fd.set('id_contact', String(idContact));
      fd.set('locale', locale);
      if (file) fd.set('file', file);
      const r = await fetch('/api/contact', { method: 'POST', body: fd });
      const d = await r.json();
      if (!r.ok || d.error) {
        // Messages d'erreur explicites (fichier trop lourd / extension refusée) : sinon
        // l'utilisateur ne sait pas quoi corriger.
        setErrKey(d.error === 'file_too_big' ? 'errFileBig'
          : d.error === 'invalid_file_extension' ? 'errFileType'
          : 'error');
        setState('error');
        return;
      }
      setState('ok');
      setMessage(''); setFile(null);
    } catch {
      setErrKey('error');
      setState('error');
    }
  };

  if (state === 'ok') {
    return (
      <div role="status" style={{ background: 'rgba(140,198,63,.08)', border: '1px solid #C7E3A0', borderRadius: '10px', padding: '20px 22px', color: '#3F7256', fontSize: '14.5px' }}>
        {t('sent')}
      </div>
    );
  }

  return (
    <form onSubmit={submit} style={{ display: 'flex', flexDirection: 'column', gap: '14px', maxWidth: '620px' }}>
      {contacts.length > 1 ? (
        <label style={{ display: 'block' }}>
          <span style={{ display: 'block', fontSize: '13px', fontWeight: 600, color: '#5E6152', marginBottom: '6px' }}>{t('subject')}</span>
          <select value={idContact} onChange={(e) => setIdContact(Number(e.target.value))} style={field}>
            {contacts.map((c) => <option key={c.id_contact} value={c.id_contact}>{c.name}</option>)}
          </select>
        </label>
      ) : null}

      <label style={{ display: 'block' }}>
        <span style={{ display: 'block', fontSize: '13px', fontWeight: 600, color: '#5E6152', marginBottom: '6px' }}>{t('email')}</span>
        <input type="email" required value={email} onChange={(e) => setEmail(e.target.value)} style={field} />
      </label>

      <label style={{ display: 'block' }}>
        <span style={{ display: 'block', fontSize: '13px', fontWeight: 600, color: '#5E6152', marginBottom: '6px' }}>{t('message')}</span>
        <textarea required rows={7} value={message} onChange={(e) => setMessage(e.target.value)} style={{ ...field, resize: 'vertical' }} />
      </label>

      <label style={{ display: 'block' }}>
        <span style={{ display: 'block', fontSize: '13px', fontWeight: 600, color: '#5E6152', marginBottom: '6px' }}>{t('attachment')}</span>
        <input
          type="file"
          accept=".txt,.rtf,.doc,.docx,.pdf,.zip,.png,.jpeg,.gif,.jpg"
          onChange={(e) => setFile(e.target.files?.[0] ?? null)}
          style={{ ...field, padding: '9px 12px' }}
        />
        <span style={{ display: 'block', fontSize: '12px', color: '#8A8170', marginTop: '5px' }}>{t('attachmentHint')}</span>
      </label>

      {state === 'error' ? (
        <div role="alert" style={{ fontSize: '13.5px', color: '#A8503A' }}>{t(errKey)}</div>
      ) : null}

      <button
        type="submit"
        disabled={state === 'loading'}
        style={{ alignSelf: 'flex-start', color: '#fff', background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))', border: '1px solid rgba(255,255,255,.42)', borderRadius: '999px', padding: '13px 30px', fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14.5px', fontWeight: 600, cursor: state === 'loading' ? 'wait' : 'pointer', opacity: state === 'loading' ? 0.7 : 1 }}
      >
        {state === 'loading' ? '…' : t('submit')}
      </button>
    </form>
  );
}
