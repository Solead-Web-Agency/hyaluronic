import { Suspense } from 'react';
import { setRequestLocale, getTranslations } from 'next-intl/server';
import Chrome from '../../../components/Chrome';
import Footer from '../../../components/Footer';
import ZoneClient from './ZoneClient';

export default async function ZonePage({ params }: { params: Promise<{ locale: string }> }) {
  const { locale } = await params;
  setRequestLocale(locale);
  const t = await getTranslations('common');

  return (
    <div style={{ fontFamily: "'Hanken Grotesk',sans-serif", color: '#34352F', background: 'radial-gradient(1100px 560px at 82% -6%, rgba(140,198,63,0.13), transparent 58%), radial-gradient(820px 520px at -8% 14%, rgba(95,184,154,0.10), transparent 55%), radial-gradient(700px 600px at 50% 118%, rgba(140,198,63,0.08), transparent 60%), #F3F4EF', minHeight: '100vh' }}>
      <Chrome />
      <Suspense fallback={<main className="hfm-wrap" style={{ maxWidth: '1340px', margin: '0 auto', padding: '34px 28px 70px', color: '#8A8170' }}>{t('loading')}</main>}>
        <ZoneClient />
      </Suspense>
      <Footer />
    </div>
  );
}
