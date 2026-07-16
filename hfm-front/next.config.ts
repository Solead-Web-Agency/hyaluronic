import type { NextConfig } from "next";
import createNextIntlPlugin from "next-intl/plugin";

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
      {
        source: '/:locale([a-z]{2})/:productId(\\d{1,})-:slug([^/]+\\.html)',
        destination: '/:locale/produit/:productId',
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
