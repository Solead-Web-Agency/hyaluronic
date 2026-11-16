import { permanentRedirect } from 'next/navigation';
import { bridgeGetCached } from '@/lib/ps';
import { CACHE_TAGS, CACHE_TTL } from '@/lib/cacheContract';
import { idLangFor } from '@/lib/i18n-config';

// Ancienne adresse des pages marque de PrestaShop : /{locale}/brand/{nom-de-la-marque}.
// L'ancien sitemap en déclare 1 530 (255 marques x 6 langues), presque toutes de la parapharmacie
// retirée du catalogue. Le nouveau site range les marques en /marque/{id} : on redirige (301) vers
// la marque si elle est encore vendue, sinon vers l'index des marques — jamais de 404.
// (Les redirections exactes de lib/legacyRedirects.ts, traitées avant par next.config, priment.)
function slugify(s: string): string {
  return s
    .normalize('NFKD')
    .replace(/[̀-ͯ]/g, '')
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
}

export default async function LegacyBrandPage({ params }: { params: Promise<{ locale: string; slug: string }> }) {
  const { locale, slug } = await params;
  const d = await bridgeGetCached(
    'taxonomy',
    { action: 'manufacturers', id_lang: idLangFor(locale) },
    { ttl: CACHE_TTL.taxonomy, tags: [CACHE_TAGS.taxonomy] },
  ); // pas de .catch : une panne du bridge remonte en 5xx, jamais en redirection hasardeuse
  const manus = (d as { manufacturers?: { id_manufacturer: number; name: string }[] }).manufacturers ?? [];
  let wanted = slug;
  try { wanted = decodeURIComponent(slug); } catch { /* déjà décodé */ }
  const key = slugify(wanted);
  const m = key ? manus.find((x) => slugify(String(x.name ?? '')) === key) : undefined;
  permanentRedirect(m ? `/${locale}/marque/${m.id_manufacturer}` : `/${locale}/marque`);
}
