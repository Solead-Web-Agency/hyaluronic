import type { Metadata } from "next";
import "./globals.css";

export const metadata: Metadata = {
  title: "Hyaluronic Filler Market — L'injectable de référence",
  description: "La marketplace de référence des injectables esthétiques.",
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
