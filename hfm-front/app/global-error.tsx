'use client';

// Dernier rempart : capture les erreurs qui échappent à `app/[locale]/error.tsx`, c.-à-d. celles
// jetées par `app/[locale]/layout.tsx` lui-même (le `<html>/<body>` y est rendu ; le layout racine
// est un simple pass-through). `global-error` REMPLACE le layout racine : il doit donc fournir ses
// propres balises <html>/<body> (convention Next 16). Hors du provider next-intl -> libellés FR en
// dur, et autonome (aucun appel bridge) pour ne jamais re-jeter.
import { useEffect } from 'react';

const PAGE: React.CSSProperties = {
  fontFamily: "'Hanken Grotesk',sans-serif", color: '#34352F',
  background:
    'radial-gradient(1100px 560px at 82% -6%, rgba(140,198,63,0.13), transparent 58%), radial-gradient(700px 600px at 50% 118%, rgba(140,198,63,0.08), transparent 60%), #F3F4EF',
  minHeight: '100vh', display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center',
  textAlign: 'center', padding: '48px 28px', margin: 0,
};

const PRIMARY: React.CSSProperties = {
  fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14.5px', fontWeight: 600, color: '#fff',
  background: 'linear-gradient(135deg,rgba(150,206,75,.95),rgba(116,176,51,.92))',
  border: '1px solid rgba(255,255,255,.42)', boxShadow: '0 12px 26px -10px rgba(116,176,51,.55)',
  borderRadius: '999px', padding: '15px 28px', cursor: 'pointer', textDecoration: 'none', display: 'inline-block',
};

const SECONDARY: React.CSSProperties = {
  fontFamily: "'Hanken Grotesk',sans-serif", fontSize: '14.5px', fontWeight: 600, color: '#5E8E1F',
  background: 'rgba(140,198,63,0.08)', border: '1px solid rgba(155,209,89,.7)',
  borderRadius: '999px', padding: '15px 28px', cursor: 'pointer', textDecoration: 'none', display: 'inline-block',
};

export default function GlobalError({
  error,
  reset,
  unstable_retry,
}: {
  error: Error & { digest?: string };
  reset: () => void;
  unstable_retry?: () => void;
}) {
  useEffect(() => {
    console.error(error);
  }, [error]);

  const retry = unstable_retry ?? reset;

  return (
    <html lang="fr">
      <body style={{ margin: 0 }}>
        <title>Une erreur est survenue — Hyaluronic Filler Market</title>
        <main style={PAGE}>
          <div style={{ maxWidth: '560px', fontFamily: "'Hanken Grotesk',sans-serif" }}>
            <div style={{ fontFamily: "'Spectral',serif", fontSize: '20px', color: '#2B2B2B' }}>
              Hyaluronic <span style={{ fontStyle: 'italic', color: '#5E8E1F' }}>Filler Market</span>
            </div>
            <div style={{ fontSize: '11.5px', fontWeight: 700, letterSpacing: '.13em', textTransform: 'uppercase', color: '#8CC63F', marginTop: '34px' }}>
              Incident temporaire
            </div>
            <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: 'clamp(28px,5vw,42px)', lineHeight: 1.1, color: '#2B2B2B', margin: '10px 0 0' }}>
              Une erreur est survenue
            </h1>
            <p style={{ fontSize: '16px', lineHeight: 1.6, color: '#55606F', margin: '18px auto 0', maxWidth: '460px' }}>
              Nous n&apos;avons pas pu afficher cette page. Il s&apos;agit le plus souvent d&apos;un incident temporaire&nbsp;: réessayez dans un instant.
            </p>
            <div style={{ display: 'flex', gap: '14px', marginTop: '32px', flexWrap: 'wrap', justifyContent: 'center' }}>
              <button type="button" onClick={() => retry?.()} style={PRIMARY}>Réessayer</button>
              <a href="/" style={SECONDARY}>Retour à l&apos;accueil</a>
            </div>
            {error.digest ? (
              <div style={{ fontSize: '11.5px', color: '#9A9A9A', marginTop: '26px' }}>
                Référence de l&apos;incident&nbsp;: {error.digest}
              </div>
            ) : null}
          </div>
        </main>
      </body>
    </html>
  );
}
