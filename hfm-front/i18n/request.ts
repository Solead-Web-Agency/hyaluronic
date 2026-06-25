import { getRequestConfig } from 'next-intl/server';
import { hasLocale } from 'next-intl';
import { routing } from './routing';
import fr from '@/messages/fr.json';

type Messages = Record<string, Record<string, unknown>>;

// Fusion profonde : les clés manquantes d'une locale retombent sur le français.
function deepMerge(base: Messages, override: Messages): Messages {
  const out: Messages = { ...base };
  for (const key of Object.keys(override)) {
    const b = out[key];
    const o = override[key];
    if (
      b && o &&
      typeof b === 'object' && typeof o === 'object' &&
      !Array.isArray(b) && !Array.isArray(o)
    ) {
      out[key] = deepMerge(b as Messages, o as Messages) as unknown as Record<string, unknown>;
    } else {
      out[key] = o;
    }
  }
  return out;
}

export default getRequestConfig(async ({ requestLocale }) => {
  const requested = await requestLocale;
  const locale = hasLocale(routing.locales, requested)
    ? requested
    : routing.defaultLocale;

  const frMessages = fr as Messages;

  if (locale === routing.defaultLocale) {
    return { locale, messages: frMessages };
  }

  let localeMessages: Messages = {};
  try {
    localeMessages = (await import(`@/messages/${locale}.json`)).default as Messages;
  } catch {
    localeMessages = {};
  }

  // Fallback FR pour toute clé absente/partielle.
  return { locale, messages: deepMerge(frMessages, localeMessages) };
});
