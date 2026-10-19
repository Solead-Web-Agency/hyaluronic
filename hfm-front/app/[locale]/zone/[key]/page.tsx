import { notFound, permanentRedirect } from 'next/navigation';
import { bridgeGetCached } from '@/lib/ps';
import { CACHE_TAGS, CACHE_TTL } from '@/lib/cacheContract';
import { idLangFor } from '@/lib/i18n-config';

// Anciennes pages « par zone » (/zone/{clé}) : elles cherchaient un mot-clé dans les noms de
// produits (3 résultats pour « lèvres », 0 pour « pommettes » -> repli sur une sélection
// générale sans rapport). Les zones existent désormais en VRAIES catégories éditoriales :
// on redirige définitivement (308) vers la catégorie, dans la langue du visiteur.
const ZONE_CATEGORIES: Record<string, number> = {
  levres: 331,
  pommettes: 332,
  cernes: 333,
  rides: 352,
  ovale: 336,
  skinbooster: 313,
};

type MenuNode = { id_category: number; link_rewrite?: string; children?: MenuNode[] };

function findCat(nodes: MenuNode[], id: number): MenuNode | null {
  for (const n of nodes) {
    if (n.id_category === id) return n;
    const hit = findCat(n.children ?? [], id);
    if (hit) return hit;
  }
  return null;
}

export default async function ZonePage({ params }: { params: Promise<{ locale: string; key: string }> }) {
  const { locale, key } = await params;
  const catId = ZONE_CATEGORIES[key];
  if (!catId) notFound();
  const d = await bridgeGetCached(
    'taxonomy',
    { action: 'menu', id_lang: idLangFor(locale) },
    { ttl: CACHE_TTL.taxonomy, tags: [CACHE_TAGS.taxonomy] },
  );
  const cat = findCat((d?.menu ?? []) as MenuNode[], catId);
  if (!cat?.link_rewrite) notFound();
  permanentRedirect(`/${locale}/${cat.link_rewrite}`);
}
