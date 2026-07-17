import type { Metadata } from 'next';
import { setRequestLocale, getTranslations } from 'next-intl/server';
import { alternatesFor, socialMeta } from '@/lib/seo';
import Chrome from '../../components/Chrome';
import Footer from '../../components/Footer';
import ContactForm from '../../components/ContactForm';

// Page de contact (parité ancien site, qui avait un vrai formulaire : choix du service, message
// et pièce jointe). Le headless l'avait remplacée par un simple `mailto:`.
// Les URLs historiques localisées (/fr/nous-contacter, /en/contact-us, /de/kontakt…) sont
// redirigées ici en 301 par next.config : on ne peut pas les servir directement sans activer les
// `pathnames` de next-intl, qui rendraient la navigation typée stricte pour TOUTES les routes.
export const dynamic = 'force-dynamic';

export async function generateMetadata({ params }: { params: Promise<{ locale: string }> }): Promise<Metadata> {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: 'contact' });
  const title = `${t('title')} — Hyaluronic Filler Market`;
  const description = t('lead');
  return {
    title,
    description,
    alternates: alternatesFor(locale, '/contact'),
    ...socialMeta(locale, '/contact', title, description),
  };
}

export default async function ContactPage({ params }: { params: Promise<{ locale: string }> }) {
  const { locale } = await params;
  setRequestLocale(locale);
  const t = await getTranslations('contact');

  return (
    <div style={{ fontFamily: "'Hanken Grotesk',sans-serif", color: '#34352F', background: 'radial-gradient(1100px 560px at 82% -6%, rgba(140,198,63,0.13), transparent 58%), radial-gradient(820px 520px at -8% 14%, rgba(95,184,154,0.10), transparent 55%), radial-gradient(700px 600px at 50% 118%, rgba(140,198,63,0.08), transparent 60%), #F3F4EF', minHeight: '100vh' }}>
      <Chrome />
      <main className="hfm-wrap" style={{ maxWidth: '1100px', margin: '0 auto', padding: '52px 28px 90px' }}>
        <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: 'clamp(28px,4vw,40px)', color: '#1B2433', margin: 0 }}>{t('title')}</h1>
        <p style={{ fontSize: '15px', color: '#55606F', margin: '12px 0 30px', maxWidth: '620px', lineHeight: 1.6 }}>{t('lead')}</p>
        <ContactForm />
      </main>
      <Footer />
    </div>
  );
}
