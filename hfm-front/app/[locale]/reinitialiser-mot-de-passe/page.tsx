import { Suspense } from 'react';
import type { Metadata } from 'next';
import { setRequestLocale, getTranslations } from 'next-intl/server';
import Chrome from '../../components/Chrome';
import Footer from '../../components/Footer';
import ResetPasswordClient from './ResetPasswordClient';

// Page de réinitialisation : reçoit le jeton (reset_token) + id_customer dans l'URL depuis l'e-mail
// natif PS « password_query ». Utilitaire de compte : hors index Google.
export const dynamic = 'force-dynamic';

export async function generateMetadata({ params }: { params: Promise<{ locale: string }> }): Promise<Metadata> {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: 'password' });
  return { title: `${t('reset.title')} — Hyaluronic Filler Market`, robots: { index: false, follow: false } };
}

export default async function ResetPasswordPage({ params }: { params: Promise<{ locale: string }> }) {
  const { locale } = await params;
  setRequestLocale(locale);
  const t = await getTranslations('common');
  return (
    <div style={{ fontFamily: "'Hanken Grotesk',sans-serif", color: '#34352F', background: 'radial-gradient(1100px 560px at 82% -6%, rgba(140,198,63,0.13), transparent 58%), radial-gradient(820px 520px at -8% 14%, rgba(95,184,154,0.10), transparent 55%), radial-gradient(700px 600px at 50% 118%, rgba(140,198,63,0.08), transparent 60%), #F3F4EF', minHeight: '100vh' }}>
      <Chrome />
      <Suspense fallback={<main className="hfm-wrap" style={{ maxWidth: '480px', margin: '0 auto', padding: '60px 28px', color: '#8A8170' }}>{t('loading')}</main>}>
        <ResetPasswordClient />
      </Suspense>
      <Footer />
    </div>
  );
}
