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

export type LocaleMeta = { native: string; flag: string };

export const localeMeta: Record<Locale, LocaleMeta> = {
  ar: { native: 'العربية', flag: '🇸🇦' },
  bg: { native: 'Български', flag: '🇧🇬' },
  cs: { native: 'Čeština', flag: '🇨🇿' },
  da: { native: 'Dansk', flag: '🇩🇰' },
  de: { native: 'Deutsch', flag: '🇩🇪' },
  el: { native: 'Ελληνικά', flag: '🇬🇷' },
  en: { native: 'English', flag: '🇬🇧' },
  es: { native: 'Español', flag: '🇪🇸' },
  fi: { native: 'Suomi', flag: '🇫🇮' },
  fr: { native: 'Français', flag: '🇫🇷' },
  he: { native: 'עברית', flag: '🇮🇱' },
  it: { native: 'Italiano', flag: '🇮🇹' },
  ja: { native: '日本語', flag: '🇯🇵' },
  ko: { native: '한국어', flag: '🇰🇷' },
  nl: { native: 'Nederlands', flag: '🇳🇱' },
  no: { native: 'Norsk', flag: '🇳🇴' },
  pl: { native: 'Polski', flag: '🇵🇱' },
  pt: { native: 'Português', flag: '🇵🇹' },
  ro: { native: 'Română', flag: '🇷🇴' },
  sl: { native: 'Slovenščina', flag: '🇸🇮' },
  sv: { native: 'Svenska', flag: '🇸🇪' },
  zh: { native: '中文', flag: '🇨🇳' },
};
