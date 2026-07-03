'use client';

import { useEffect, useRef, useState, useTransition } from 'react';
import { useLocale } from 'next-intl';
import { usePathname, useRouter } from '@/i18n/navigation';
import { locales, localeMeta, type Locale } from '@/lib/i18n-config';

export default function LocaleSwitcher() {
  const current = useLocale() as Locale;
  const pathname = usePathname();
  const router = useRouter();
  const [open, setOpen] = useState(false);
  const [, startTransition] = useTransition();
  const ref = useRef<HTMLDivElement>(null);

  // Ferme le menu au clic extérieur.
  useEffect(() => {
    if (!open) return;
    const onClick = (e: MouseEvent) => {
      if (ref.current && !ref.current.contains(e.target as Node)) setOpen(false);
    };
    document.addEventListener('mousedown', onClick);
    return () => document.removeEventListener('mousedown', onClick);
  }, [open]);

  const choose = (loc: Locale) => {
    setOpen(false);
    if (loc === current) return;
    // Conserve le chemin courant en changeant uniquement la locale.
    startTransition(() => {
      router.replace(pathname, { locale: loc });
    });
  };

  const meta = localeMeta[current];

  return (
    <div ref={ref} style={{ position: 'relative', flex: 'none' }}>
      <button
        onClick={() => setOpen((o) => !o)}
        aria-haspopup="listbox"
        aria-expanded={open}
        aria-label={meta.native}
        title={meta.native}
        style={{
          display: 'flex',
          alignItems: 'center',
          gap: '3px',
          height: '32px',
          padding: '0 2px',
          background: 'transparent',
          border: 'none',
          color: '#6E7585',
          cursor: 'pointer',
        }}
      >
        <img src={`/flags/${meta.country}.svg`} alt="" width={20} height={15} style={{ display: 'block', borderRadius: '2px', border: '1px solid rgba(0,0,0,.08)', objectFit: 'cover' }} />
        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" style={{ transition: 'transform .2s ease', transform: open ? 'rotate(180deg)' : 'none' }}>
          <path d="M6 9l6 6 6-6" />
        </svg>
      </button>
      {open ? (
        <div
          role="listbox"
          style={{
            position: 'absolute',
            top: 'calc(100% + 8px)',
            right: 0,
            zIndex: 50,
            width: '210px',
            maxHeight: '360px',
            overflowY: 'auto',
            background: 'rgba(250,250,247,.98)',
            backdropFilter: 'blur(16px) saturate(150%)',
            WebkitBackdropFilter: 'blur(16px) saturate(150%)',
            border: '1px solid #E7E3DA',
            borderRadius: '14px',
            boxShadow: '0 24px 44px -22px rgba(40,50,25,.45)',
            padding: '6px',
          }}
        >
          {locales.map((loc) => {
            const m = localeMeta[loc];
            const active = loc === current;
            return (
              <button
                key={loc}
                role="option"
                aria-selected={active}
                onClick={() => choose(loc)}
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  gap: '11px',
                  width: '100%',
                  textAlign: 'left',
                  padding: '9px 11px',
                  background: active ? 'rgba(140,198,63,.12)' : 'transparent',
                  border: 'none',
                  borderRadius: '9px',
                  fontFamily: "'Hanken Grotesk',sans-serif",
                  fontSize: '13.5px',
                  fontWeight: active ? 600 : 500,
                  color: active ? '#3F5E1C' : '#3A3A36',
                  cursor: 'pointer',
                  transition: 'background .15s ease',
                }}
              >
                <img src={`/flags/${m.country}.svg`} alt="" width={21} height={16} style={{ display: 'block', flex: 'none', borderRadius: '2px', border: '1px solid rgba(0,0,0,.08)', objectFit: 'cover' }} />
                <span style={{ flex: 1, minWidth: 0, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{m.native}</span>
                {active ? (
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#5E8E1F" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round" style={{ flex: 'none' }}>
                    <path d="M20 6L9 17l-5-5" />
                  </svg>
                ) : null}
              </button>
            );
          })}
        </div>
      ) : null}
    </div>
  );
}
