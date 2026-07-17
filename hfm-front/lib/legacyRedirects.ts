/**
 * Redirections 301 HISTORIQUES de l'ancien site, reprises à l'identique.
 *
 * Source : table `ps_ets_seo_redirect` du module PrestaShop `ets_seo` (28 lignes, 26 actives) —
 * un module de gestion de redirections qui n'existe pas dans la stack headless. Sans ces règles :
 *  - `/fr/juvederm-allergan` et `/fr/galderma` répondraient 200 sur le nouveau front, alors que
 *    l'ancien site les CONSOLIDE en 301 vers `/fr/juvederm` et `/fr/restylane` -> on scinderait
 *    le jus SEO entre deux pages au lieu de le concentrer (pire qu'un 404) ;
 *  - `/en/restylane-galderma`, `/fr/cytocare`, `/fr/brand/galderma`… tomberaient en 404.
 *
 * Cibles NORMALISÉES : les données d'origine omettaient parfois la locale (ex. `/fr/cytocare` ->
 * `/cytocare-1`), on préfixe donc avec la locale de la source. Les 25 cibles ont été vérifiées 200
 * sur le nouveau front. Non reprise : la source à query `/module/iqitsearch/searchiqit?s=Restylane`
 * (aucune route équivalente en headless).
 */
export const LEGACY_REDIRECTS: { source: string; destination: string }[] = [
  { source: '/fr/juvederm-allergan', destination: '/fr/juvederm' },
  { source: '/en/juvederm-allegran', destination: '/en/juvederm' },
  { source: '/es/juvederm-allegran', destination: '/es/juvederm' },
  { source: '/it/juvederm-allegran', destination: '/it/juvederm' },
  { source: '/de/juvederm-allegran', destination: '/de/juvederm' },
  { source: '/fr/restylane-galderma', destination: '/fr/restylane' },
  { source: '/fr/galderma', destination: '/fr/restylane' },
  { source: '/en/restylane-galderma', destination: '/en/restylane' },
  { source: '/es/restylane-galderma', destination: '/es/restylane' },
  { source: '/de/restylane-galderma', destination: '/de/restylane' },
  { source: '/it/restylane-galderma', destination: '/it/restylane' },
  { source: '/en/galderma', destination: '/en/restylane' },
  { source: '/es/galderma', destination: '/es/restylane' },
  { source: '/it/galderma', destination: '/it/restylane' },
  { source: '/de/galderma', destination: '/de/restylane' },
  { source: '/fr/marque-restylane', destination: '/fr/restylane' },
  { source: '/fr/brand/galderma', destination: '/fr/restylane' },
  { source: '/en/brand/galderma', destination: '/en/restylane' },
  { source: '/es/brand/galderma', destination: '/es/restylane' },
  { source: '/fr/cytocare', destination: '/fr/cytocare-1' },
  { source: '/en/cytocare', destination: '/en/cytocare-1' },
  { source: '/es/cytocare', destination: '/es/cytocare-1' },
  { source: '/it/cytocare', destination: '/it/cytocare-1' },
  { source: '/de/cytocare', destination: '/de/cytocare-1' },
  { source: '/fr/brand/restylane', destination: '/fr/restylane' },
  // Page de contact de l'ancien site : l'URL était localisée par langue (ps_meta).
  { source: '/fr/nous-contacter', destination: '/fr/contact' },
  { source: '/en/contact-us', destination: '/en/contact' },
  { source: '/de/kontakt', destination: '/de/contact' },
  { source: '/it/contattaci', destination: '/it/contact' },
  { source: '/es/contacto-con-nosotros', destination: '/es/contact' },
];
