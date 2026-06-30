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
};

export default withNextIntl(nextConfig);
