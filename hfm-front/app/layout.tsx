import type { Metadata } from "next";
import "./globals.css";

// Staging (SITE_INDEXABLE != 'true') : noindex global pour ne pas doublonner le site live.
const INDEXABLE = process.env.SITE_INDEXABLE === "true";

export const metadata: Metadata = {
  title: "Hyaluronic Filler Market — L'injectable de référence",
  description: "La marketplace de référence des injectables esthétiques.",
  ...(INDEXABLE ? {} : { robots: { index: false, follow: false } }),
};

// Layout racine en pass-through : le <html>/<body> localisé est rendu
// dans app/[locale]/layout.tsx (pattern App Router de next-intl).
export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return children;
}
