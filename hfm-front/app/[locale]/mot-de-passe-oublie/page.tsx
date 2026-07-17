import type { Metadata } from 'next';
import { setRequestLocale, getTranslations } from 'next-intl/server';
import Chrome from '../../components/Chrome';
import Footer from '../../components/Footer';
import ForgotPasswordClient from './ForgotPasswordClient';

// Page « mot de passe oublié » (parité ancien site). Utilitaire de compte : hors index Google.
export const dynamic = 'force-dynamic';

export async function generateMetadata({ params }: { params: Promise<{ locale: string }> }): Promise<Metadata> {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: 'password' });
  return { title: `${t('forgot.title')} — Hyaluronic Filler Market`, robots: { index: false, follow: true } };
}

export default async function ForgotPasswordPage({ params }: { params: Promise<{ locale: string }> }) {
  const { locale } = await params;
  setRequestLocale(locale);
  return (
    <div style={{ fontFamily: "'Hanken Grotesk',sans-serif", color: '#34352F', background: 'radial-gradient(1100px 560px at 82% -6%, rgba(140,198,63,0.13), transparent 58%), radial-gradient(820px 520px at -8% 14%, rgba(95,184,154,0.10), transparent 55%), radial-gradient(700px 600px at 50% 118%, rgba(140,198,63,0.08), transparent 60%), #F3F4EF', minHeight: '100vh' }}>
      <Chrome />
      <ForgotPasswordClient />
      <Footer />
    </div>
  );
}
