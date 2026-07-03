// Configuration i18n partagée (locales, RTL, mapping PrestaShop id_lang, métadonnées).

export const locales = [
  'ar', 'bg', 'cs', 'da', 'de', 'el', 'en', 'es', 'fi', 'fr', 'he',
  'it', 'ja', 'ko', 'nl', 'no', 'pl', 'pt', 'ro', 'sl', 'sv', 'zh',
] as const;

export type Locale = (typeof locales)[number];

export const defaultLocale: Locale = 'fr';

export const rtlLocales: Locale[] = ['ar', 'he'];

export function isRtl(locale: string): boolean {
  return rtlLocales.includes(locale as Locale);
}

// Mapping locale -> id_lang PrestaShop. Les 6 premières sont les langues d'origine ;
// les suivantes (id 10-25) ont été ajoutées pour des emails transactionnels localisés
// (données catalogue copiées de l'EN, templates email traduits dans mails/{iso}/).
export const localeToIdLang: Record<string, number> = {
  fr: 1,
  en: 2,
  de: 3,
  it: 4,
  es: 5,
  ja: 8,
  pl: 10,
  ar: 11,
  bg: 12,
  cs: 13,
  da: 14,
  el: 15,
  fi: 16,
  he: 17,
  ko: 18,
  nl: 19,
  no: 20,
  pt: 21,
  ro: 22,
  sl: 23,
  sv: 24,
  zh: 25,
};

export function idLangFor(locale: string): number {
  return localeToIdLang[locale] ?? 2;
}

// `country` = code pays du drapeau servi depuis /public/flags/{country}.svg
// (SVG 4:3 issus de flag-icons, MIT — copiés par les 22 locales uniquement).
export type LocaleMeta = { native: string; country: string };

export const localeMeta: Record<Locale, LocaleMeta> = {
  ar: { native: 'العربية', country: 'sa' },
  bg: { native: 'Български', country: 'bg' },
  cs: { native: 'Čeština', country: 'cz' },
  da: { native: 'Dansk', country: 'dk' },
  de: { native: 'Deutsch', country: 'de' },
  el: { native: 'Ελληνικά', country: 'gr' },
  en: { native: 'English', country: 'gb' },
  es: { native: 'Español', country: 'es' },
  fi: { native: 'Suomi', country: 'fi' },
  fr: { native: 'Français', country: 'fr' },
  he: { native: 'עברית', country: 'il' },
  it: { native: 'Italiano', country: 'it' },
  ja: { native: '日本語', country: 'jp' },
  ko: { native: '한국어', country: 'kr' },
  nl: { native: 'Nederlands', country: 'nl' },
  no: { native: 'Norsk', country: 'no' },
  pl: { native: 'Polski', country: 'pl' },
  pt: { native: 'Português', country: 'pt' },
  ro: { native: 'Română', country: 'ro' },
  sl: { native: 'Slovenščina', country: 'si' },
  sv: { native: 'Svenska', country: 'se' },
  zh: { native: '中文', country: 'cn' },
};
