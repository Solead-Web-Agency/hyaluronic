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
  // Coordonnées de la société : mêmes libellés que le pied de page (source unique).
  const tf = await getTranslations('footer');
  const phoneLabel = tf('phone');
  const phoneNumber = (phoneLabel.match(/\+?\d[\d\s]{6,}\d/) || [''])[0];
  const [street, city] = tf('address').split('·').map((x) => x.trim());

  return (
    <div style={{ fontFamily: "'Hanken Grotesk',sans-serif", color: '#34352F', background: 'radial-gradient(1100px 560px at 82% -6%, rgba(140,198,63,0.13), transparent 58%), radial-gradient(820px 520px at -8% 14%, rgba(95,184,154,0.10), transparent 55%), radial-gradient(700px 600px at 50% 118%, rgba(140,198,63,0.08), transparent 60%), #F3F4EF', minHeight: '100vh' }}>
      <Chrome />
      <main className="hfm-wrap" style={{ maxWidth: '1100px', margin: '0 auto', padding: '52px 28px 90px' }}>
        <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: 'clamp(28px,4vw,40px)', color: '#1B2433', margin: 0 }}>{t('title')}</h1>
        <p style={{ fontSize: '15px', color: '#55606F', margin: '12px 0 30px', maxWidth: '620px', lineHeight: 1.6 }}>{t('lead')}</p>
        <div style={{ display: 'flex', flexWrap: 'wrap', gap: '34px', alignItems: 'flex-start' }}>
          <div style={{ flex: '1 1 520px', minWidth: 0 }}>
            <ContactForm />
          </div>
          <aside style={{ flex: '1 1 260px', maxWidth: '420px', background: '#fff', border: '1px solid #ECEAE3', borderRadius: '12px', padding: '24px 26px', boxShadow: '0 18px 40px -30px rgba(40,50,25,.4)' }}>
            <h2 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: '20px', color: '#1B2433', margin: 0 }}>{t('coordsTitle')}</h2>
            <div style={{ fontSize: '14px', color: '#34352F', lineHeight: 1.6, marginTop: '14px' }}>
              <div style={{ fontWeight: 600 }}>HMD Distribution</div>
              <address style={{ fontStyle: 'normal' }}>{street}{city ? <><br />{city}</> : null}<br />{t('coordsCountry')}</address>
            </div>
            {phoneNumber ? (
              <div style={{ fontSize: '14px', marginTop: '14px' }}>
                <a href={`tel:${phoneNumber.replace(/\s+/g, '')}`} style={{ color: '#5E8E1F', fontWeight: 600, textDecoration: 'none' }} dir="ltr">{phoneLabel}</a>
              </div>
            ) : null}
          </aside>
        </div>
      </main>
      <Footer />
    </div>
  );
}
