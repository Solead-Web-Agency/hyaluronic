import { notFound } from 'next/navigation';
import { hasLocale, NextIntlClientProvider } from 'next-intl';
import { setRequestLocale } from 'next-intl/server';
import { routing } from '@/i18n/routing';
import { isRtl } from '@/lib/i18n-config';
import { StoreProvider } from '../store';
import { WishlistProvider } from '../wishlist';
import ConsentBanner from '../components/ConsentBanner';
import { ADS_ID } from '@/lib/gtm';

// Tout le tracking est chargé UNIQUEMENT en prod indexable : le staging reste 100 % propre
// (aucune pollution GA4/Ads/conversions) même si les IDs traînent dans l'env.
const TRACKING_ON = process.env.SITE_INDEXABLE === 'true';

// Conteneur GTM (tags GA4/remarketing). Nécessite en plus NEXT_PUBLIC_GTM_ID=GTM-NR6D62Z.
const GTM_ID = process.env.NEXT_PUBLIC_GTM_ID;
const GTM_ENABLED = !!GTM_ID && TRACKING_ON;

// Google Ads chargé EN DIRECT, comme l'ancien site (modules gadwordstracking + gremarketing le
// faisaient en plus de GTM) : on ne dépend donc pas du contenu du conteneur GTM pour les conversions
// et les audiences de remarketing. L'ID est une constante publique (lib/gtm.ts) -> pas d'env de plus
// à ne pas oublier au cutover ; SITE_INDEXABLE suffit à garder le staging propre.
const ADS_ENABLED = TRACKING_ON;

// Consent Mode v2 : refus par défaut (EEA) AVANT tout chargement Google ; le bandeau met à jour.
const CONSENT_DEFAULT = `
window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}window.gtag=gtag;
gtag('consent','default',{ad_storage:'denied',ad_user_data:'denied',ad_personalization:'denied',analytics_storage:'denied',functionality_storage:'granted',security_storage:'granted',wait_for_update:500});
gtag('set','ads_data_redaction',true);gtag('set','url_passthrough',true);`;

// gtag('config') Ads = tag de remarketing/audience sur toutes les pages (l'ancien site avait
// GR_REMARKETING_DYNAMIC=0 -> remarketing simple, pas de paramètres dynamiques à reproduire).
// On injecte gtag.js DEPUIS cet inline (et non via <script async src>) : React hisse les scripts à
// `src` tout en haut du <head>, ils pourraient donc s'exécuter AVANT le Consent Mode -> Ads
// partirait sans consentement. Ici l'ordre d'exécution est garanti (inline séquentiels).
const ADS_LOADER = (id: string) => `
(function(){var s=document.createElement('script');s.async=true;s.src='https://www.googletagmanager.com/gtag/js?id=${id}';document.head.appendChild(s);})();
gtag('js',new Date());gtag('config','${id}');`;

const GTM_LOADER = (id: string) => `
(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','${id}');`;

export function generateStaticParams() {
  return routing.locales.map((locale) => ({ locale }));
}

export default async function LocaleLayout({
  children,
  params,
}: {
  children: React.ReactNode;
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;
  if (!hasLocale(routing.locales, locale)) {
    notFound();
  }

  // Active le rendu statique pour cette locale.
  setRequestLocale(locale);

  return (
    <html lang={locale} dir={isRtl(locale) ? 'rtl' : 'ltr'} suppressHydrationWarning>
      <head>
        {/* Ordre IMPÉRATIF : consentement par défaut (refus) -> gtag Ads -> GTM. */}
        {(GTM_ENABLED || ADS_ENABLED) ? (
          <script dangerouslySetInnerHTML={{ __html: CONSENT_DEFAULT }} />
        ) : null}
        {ADS_ENABLED ? (
          <script dangerouslySetInnerHTML={{ __html: ADS_LOADER(ADS_ID) }} />
        ) : null}
        {GTM_ENABLED ? (
          <script dangerouslySetInnerHTML={{ __html: GTM_LOADER(GTM_ID as string) }} />
        ) : null}
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="" />
        <link
          href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&family=Spectral:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap"
          rel="stylesheet"
        />
      </head>
      <body suppressHydrationWarning>
        {GTM_ENABLED ? (
          <noscript>
            <iframe
              src={`https://www.googletagmanager.com/ns.html?id=${GTM_ID}`}
              height="0"
              width="0"
              style={{ display: 'none', visibility: 'hidden' }}
            />
          </noscript>
        ) : null}
        <NextIntlClientProvider>
          <StoreProvider>
            <WishlistProvider>{children}</WishlistProvider>
            <ConsentBanner />
          </StoreProvider>
        </NextIntlClientProvider>
      </body>
    </html>
  );
}
