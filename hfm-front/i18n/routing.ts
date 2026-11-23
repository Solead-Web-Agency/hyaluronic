import { defineRouting } from 'next-intl/routing';
import { locales, defaultLocale } from '@/lib/i18n-config';

export const routing = defineRouting({
  locales,
  defaultLocale,
  localePrefix: 'always',
  // Pas d'en-tête HTTP « Link » avec les 22 versions de langue : sur les fiches à URL longue il dépassait la
  // taille d'en-têtes acceptée par nginx (502 « upstream sent too big header », 107 URL du site actuel au
  // crawl du 18/11). Les hreflang restent déclarés dans le <head> de chaque page (lib/seo.ts).
  alternateLinks: false,
});
