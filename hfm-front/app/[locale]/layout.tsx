import { notFound } from 'next/navigation';
import { hasLocale, NextIntlClientProvider } from 'next-intl';
import { setRequestLocale } from 'next-intl/server';
import { routing } from '@/i18n/routing';
import { isRtl } from '@/lib/i18n-config';
import { StoreProvider } from '../store';
import { WishlistProvider } from '../wishlist';
import ConsentBanner from '../components/ConsentBanner';

// Conteneur GTM (mêmes tags GA4/Ads/remarketing que l'ancien site). Chargé UNIQUEMENT en prod
// indexable (SITE_INDEXABLE=true) ET avec un ID défini : le staging reste 100 % propre (aucune
// pollution GA4/Ads/conversions) même si NEXT_PUBLIC_GTM_ID traîne dans l'env. Cutover : les deux.
const GTM_ID = process.env.NEXT_PUBLIC_GTM_ID;
const GTM_ENABLED = !!GTM_ID && process.env.SITE_INDEXABLE === 'true';

// Consent Mode v2 : refus par défaut (EEA) AVANT le chargement de GTM ; le bandeau met à jour.
const CONSENT_DEFAULT = `
window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}window.gtag=gtag;
gtag('consent','default',{ad_storage:'denied',ad_user_data:'denied',ad_personalization:'denied',analytics_storage:'denied',functionality_storage:'granted',security_storage:'granted',wait_for_update:500});
gtag('set','ads_data_redaction',true);gtag('set','url_passthrough',true);`;

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
        {GTM_ENABLED ? (
          <>
            <script dangerouslySetInnerHTML={{ __html: CONSENT_DEFAULT }} />
            <script dangerouslySetInnerHTML={{ __html: GTM_LOADER(GTM_ID as string) }} />
          </>
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
