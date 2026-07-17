// 404 du segment [locale] : rendu quand une page appelle `notFound()` (ressource réellement
// inexistante — le bridge la sert en 200 + `{ error: 'not_found' }`) ou pour une URL non routée.
// Renvoie un vrai statut 404. Composant serveur autonome (aucun appel bridge) : ne peut pas jeter.
import { getTranslations } from 'next-intl/server';
import { Link } from '@/i18n/navigation';

const PAGE: React.CSSProperties = {
  fontFamily: "'Hanken Grotesk',sans-serif", color: '#34352F',
  background:
    'radial-gradient(1100px 560px at 82% -6%, rgba(140,198,63,0.13), transparent 58%), radial-gradient(820px 520px at -8% 14%, rgba(95,184,154,0.10), transparent 55%), radial-gradient(700px 600px at 50% 118%, rgba(140,198,63,0.08), transparent 60%), #F3F4EF',
  minHeight: '100vh', display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center',
  textAlign: 'center', padding: '48px 28px',
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

export default async function LocaleNotFound() {
  const t = await getTranslations('notFound');

  return (
    <main style={PAGE}>
      <div style={{ maxWidth: '560px' }}>
        <div style={{ fontFamily: "'Spectral',serif", fontSize: '20px', color: '#2B2B2B', letterSpacing: '.02em' }}>
          Hyaluronic <span style={{ fontStyle: 'italic', color: '#5E8E1F' }}>Filler Market</span>
        </div>
        <div style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: 'clamp(64px,14vw,120px)', lineHeight: 1, color: 'rgba(94,142,31,.16)', marginTop: '28px' }}>
          404
        </div>
        <div style={{ fontSize: '11.5px', fontWeight: 700, letterSpacing: '.13em', textTransform: 'uppercase', color: '#8CC63F', marginTop: '8px' }}>
          {t('eyebrow')}
        </div>
        <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: 'clamp(28px,5vw,42px)', lineHeight: 1.1, color: '#2B2B2B', margin: '10px 0 0' }}>
          {t('title')}
        </h1>
        <p style={{ fontSize: '16px', lineHeight: 1.6, color: '#55606F', margin: '18px auto 0', maxWidth: '460px' }}>
          {t('lead')}
        </p>
        <div style={{ display: 'flex', gap: '14px', marginTop: '32px', flexWrap: 'wrap', justifyContent: 'center' }}>
          <Link href="/" style={PRIMARY}>{t('home')}</Link>
          <Link href="/catalogue" style={SECONDARY}>{t('catalogue')}</Link>
        </div>
      </div>
    </main>
  );
}
