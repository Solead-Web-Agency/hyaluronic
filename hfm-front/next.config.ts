import type { NextConfig } from "next";
import createNextIntlPlugin from "next-intl/plugin";
import { LEGACY_REDIRECTS } from "./lib/legacyRedirects";

const withNextIntl = createNextIntlPlugin("./i18n/request.ts");

const nextConfig: NextConfig = {
  turbopack: {
    // Le projet vit dans son propre dossier (plusieurs lockfiles existent sur la machine).
    root: __dirname,
  },
  images: {
    remotePatterns: [
      { protocol: "http", hostname: "localhost", port: "8080", pathname: "/img/**" },
      // Back PrestaShop en prod/staging (images catalogue servies par PS).
      { protocol: "https", hostname: "**.hyaluronicfillermarket.com", pathname: "/img/**" },
    ],
  },
  // Redirections legacy PrestaShop à EXTENSION (.html) — le matcher du middleware exclut les
  // chemins avec un point, donc on les traite ici. Ancien format produit /{locale}/{id}-{slug}.html
  // -> fiche par id (qui redirige ensuite 308 vers l'URL canonique /{categorie}/{slug}).
  async redirects() {
    return [
      // Redirections historiques de l'ancien site (module ets_seo) — exactes, donc en premier.
      ...LEGACY_REDIRECTS.map((r) => ({ ...r, permanent: true })),
      {
        source: '/:locale([a-z]{2})/:productId(\\d{1,})-:slug([^/]+\\.html)',
        destination: '/:locale/produit/:productId',
        permanent: true,
      },
      // Format legacy SANS réécriture d'URL (l'ancien PS avait PS_REWRITING_SETTINGS=0, donc
      // `/index.php?id_product=N` est le format le plus susceptible d'être indexé). Le middleware
      // ne peut PAS le couvrir : son matcher exclut les chemins contenant un point.
      // Pas de locale dans ces URLs -> on atterrit sur la locale par défaut, puis la route par id
      // redirige (308) vers l'URL canonique.
      {
        source: '/index.php',
        has: [{ type: 'query', key: 'id_product', value: '(?<pid>\\d+)' }],
        destination: '/fr/produit/:pid',
        permanent: true,
      },
      {
        source: '/index.php',
        has: [{ type: 'query', key: 'id_category', value: '(?<cid>\\d+)' }],
        destination: '/fr/catalogue?category=:cid',
        permanent: true,
      },
    ];
  },
  // Feeds Google Merchant : on sert les 9 URLs historiques (parité prod) sans rien
  // changer dans Merchant Center. Le chemin /gmerchantcenter{token}.{variante}.shop1.xml
  // est réécrit vers la route API qui proxifie le feed généré par le module (iso garanti).
  async rewrites() {
    return {
      beforeFiles: [
        {
          // Le handler lit le nom du feed depuis le pathname (préservé par le rewrite) :
          // path-to-regexp ne substitue pas un param contenant des points en query.
          source: "/:feed(gmerchantcenter[^/]+\\.xml)",
          destination: "/api/merchant-feed",
        },
      ],
    };
  },
};

export default withNextIntl(nextConfig);
