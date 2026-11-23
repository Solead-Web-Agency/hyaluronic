import type { Metadata } from 'next';
import { notFound, permanentRedirect } from 'next/navigation';
import { bridgeGetCached, isGoogleBotRequest } from '@/lib/ps';
import { CACHE_TAGS, CACHE_TTL } from '@/lib/cacheContract';
import { setRequestLocale, getTranslations } from 'next-intl/server';
import { idLangFor } from '@/lib/i18n-config';
import { alternatesFromSlugs, breadcrumbJsonLd, fillSeoVars, jsonLdString, ogLocale, textFromHtml, urlFor } from '@/lib/seo';
import { DISCONTINUED_SLUGS } from '@/lib/discontinued';
import { sanitizeCatalogHtml, structureDescription } from '@/lib/sanitize';
import Chrome from '../../../components/Chrome';
import Footer from '../../../components/Footer';
import ProductDetail, { type ProductView } from '../../produit/[id]/ProductDetail';
import { type RelatedView } from '../../produit/[id]/RelatedSections';
import { stockStatus, toCard } from '@/lib/cardModel';
import type { ProductCard as ProductCardData } from '@/lib/ps';

type RawFeature = { name?: string; value?: string };
type RawFaq = { q?: string; a?: string };
type RawCompo = { k?: string; v?: string };

// Fiche produit par SLUG (URL « parité prod » /{categorie}/{slug}). Le bridge résout
// le link_rewrite -> produit et renvoie sa catégorie canonique (pour le 301 éventuel).
// `pays` (paramètre ?pays=XX des liens des flux Google Shopping) : prix du pays du flux, pour TOUT
// visiteur de cette adresse (humain ou robot), au lieu du pays géolocalisé. Une URL = un prix.
function countryParam(raw: unknown): string | null {
  const v = String(Array.isArray(raw) ? raw[0] : raw ?? '').trim().toUpperCase();
  return /^[A-Z]{2}$/.test(v) ? v : null;
}

async function fetchProductBySlug(slug: string, locale: string, country: string | null = null) {
  const data = await bridgeGetCached(
    'products',
    { link_rewrite: slug, id_lang: idLangFor(locale), ...(country ? { country } : {}) },
    { ttl: CACHE_TTL.products, tags: [CACHE_TAGS.products] },
  ); // pas de .catch : une panne du bridge remonte en 5xx (error.tsx), jamais en 404 (désindexation)
  return (data as { product?: Record<string, unknown> }).product ?? {};
}

// Titre et description SEO de la fiche (parité ancien site) : ceux saisis en BO, variables de modèle
// remplacées (« %product-name% | %shop-name% », « %summary% »), sinon nom et résumé du produit.
function productSeo(p: Record<string, any>): { title: string; description: string } {
  const name = String(p.name ?? '');
  const vars = { name, summary: textFromHtml(p.description_short || p.description || '', 300) };
  return {
    title: fillSeoVars(p.meta_title, vars, 'title') || name,
    description: fillSeoVars(p.meta_description, vars, 'description') || textFromHtml(p.description_short || p.description || '', 160),
  };
}

export async function generateMetadata({ params }: { params: Promise<{ locale: string; category: string; slug: string }> }): Promise<Metadata> {
  const { locale, slug } = await params;
  const p = await fetchProductBySlug(slug, locale) as Record<string, any>;
  // Produit inconnu, ou bloqué dans le pays du visiteur (la page répond alors 404) : ni titre ni
  // description du produit dans l'en-tête.
  if (!p.name || p.blocked_country) {
    return { robots: { index: false } };
  }

  const { title, description } = productSeo(p);
  // Canonique = catégorie CANONIQUE du produit (pas forcément celle de l'URL demandée).
  const path = `/${p.category || 'produit'}/${slug}`;
  const images = Array.isArray(p.images) && p.images.length ? [{ url: p.images[0] as string }] : undefined;
  // hreflang : slug produit ET slug catégorie varient par langue -> map id_lang renvoyée par le bridge.
  const alt = p.alternates as Record<string, { category?: string; slug?: string }> | undefined;

  return {
    title,
    description,
    alternates: alternatesFromSlugs(locale, path, (idLang) => {
      const a = alt?.[idLang];
      return a?.slug ? `/${a.category || 'produit'}/${a.slug}` : null;
    }),
    openGraph: {
      // Pas de `type` ici : Next n'émet pas og:type=product depuis son enum, et `other` sortirait un
      // <meta name=…> (invalide pour l'OG). On pose donc <meta property="og:type"> dans le composant.
      title,
      description,
      url: urlFor(locale, path),
      images,
      siteName: 'Hyaluronic Filler Market',
      locale: ogLocale(locale),
    },
    twitter: { card: 'summary_large_image', title, description, images },
  };
}

function jsonLd(p: Record<string, any>, path: string, locale: string, homeLabel: string, googleBot = false): object[] {
  const availability = p.availability === 'in_stock' ? 'https://schema.org/InStock'
    : p.availability === 'backorder' ? 'https://schema.org/PreOrder'
    : 'https://schema.org/OutOfStock';

  const product: Record<string, unknown> = {
    '@context': 'https://schema.org',
    '@type': 'Product',
    name: p.name,
    description: fillSeoVars(p.meta_description, { name: String(p.name ?? ''), summary: textFromHtml(p.description_short || '', 500) }, 'description') || textFromHtml(p.description_short || '', 500),
    image: Array.isArray(p.images) ? p.images : [],
    sku: p.reference || undefined,
    gtin13: /^\d{13}$/.test(String(p.ean13 ?? '')) ? p.ean13 : undefined,
    brand: p.manufacturer ? { '@type': 'Brand', name: p.manufacturer } : undefined,
    offers: {
      '@type': 'Offer',
      url: urlFor(locale, path),
      priceCurrency: 'EUR',
      // Visiteur : prix TTC affiché (TVA de son pays, ou du pays ?pays= du lien du flux).
      // Robot Google : prix HT, comme les flux Shopping et comme l'ancien site (lib/bots.ts).
      price: Number((googleBot ? p.price_excl_tax : p.price_incl_tax) ?? 0).toFixed(2),
      availability,
      itemCondition: 'https://schema.org/NewCondition',
    },
  };

  // Note agrégée « Société des Avis Garantis » -> étoiles dans les résultats Google.
  if (p.reviews && p.reviews.count > 0) {
    product.aggregateRating = {
      '@type': 'AggregateRating',
      ratingValue: p.reviews.rate,
      bestRating: 5,
      worstRating: 1,
      reviewCount: p.reviews.count,
    };
    // Avis individuels (parité ancien site) : Review + Person, limités pour borner le JSON-LD.
    const items = Array.isArray(p.reviews.items) ? p.reviews.items : [];
    const reviews = items.slice(0, 20).map((r: Record<string, any>) => {
      const rv: Record<string, unknown> = {
        '@type': 'Review',
        author: { '@type': 'Person', name: (r.name && String(r.name).trim()) || 'Client' },
        reviewRating: { '@type': 'Rating', ratingValue: r.rate, bestRating: 5, worstRating: 1 },
      };
      if (r.review && String(r.review).trim()) rv.reviewBody = String(r.review);
      if (r.date) rv.datePublished = String(r.date);
      return rv;
    });
    if (reviews.length) product.review = reviews;
  }

  const blocks: object[] = [product];

  // Fil d'Ariane : Accueil > Catégorie > Produit.
  const crumbs = [{ name: homeLabel, url: urlFor(locale, '') }];
  if (p.category && p.category_name) {
    crumbs.push({ name: p.category_name, url: urlFor(locale, `/${p.category}`) });
  }
  crumbs.push({ name: p.name, url: urlFor(locale, path) });
  blocks.push(breadcrumbJsonLd(crumbs));

  if (Array.isArray(p.faq) && p.faq.length) {
    blocks.push({
      '@context': 'https://schema.org',
      '@type': 'FAQPage',
      mainEntity: (p.faq as RawFaq[]).filter((f) => f.q && f.a).map((f) => ({
        '@type': 'Question',
        name: f.q,
        acceptedAnswer: { '@type': 'Answer', text: f.a },
      })),
    });
  }
  return blocks;
}

export default async function ProductBySlugPage({ params, searchParams }: {
  params: Promise<{ locale: string; category: string; slug: string }>;
  searchParams: Promise<Record<string, string | string[] | undefined>>;
}) {
  const { locale, category, slug } = await params;
  setRequestLocale(locale);
  const country = countryParam((await searchParams).pays);
  const googleBot = await isGoogleBotRequest();
  const p = await fetchProductBySlug(slug, locale, country) as Record<string, any>;
  if (!p.name) {
    // Produit discontinué (inactif/supprimé) : 301 vers SA catégorie par défaut (résolue même si
    // le produit est inactif), sinon 404. Préserve le jus SEO + l'UX. Si cette catégorie est
    // elle-même désactivée, sa page redirige à son tour vers le catalogue.
    const tx = await bridgeGetCached(
      'products',
      { disc_slug: slug, id_lang: idLangFor(locale) },
      { ttl: CACHE_TTL.products, tags: [CACHE_TAGS.products] },
    ); // panne -> 5xx, pas 404
    const catSlug = (tx as { discontinued_category?: string | null } | null)?.discontinued_category;
    if (catSlug) {
      permanentRedirect(`/${locale}/${catSlug}`);
    }
    // Produit SUPPRIMÉ de la base (liste du client, 10/11/2026) : le bridge ne le connaît plus.
    // Son adresse est encore dans l'ancien sitemap et les annonces -> 301 vers la catégorie de
    // l'adresse si elle vend encore quelque chose, sinon vers le catalogue (jamais de 404).
    let wanted = slug;
    try { wanted = decodeURIComponent(slug); } catch { /* slug déjà décodé */ }
    if (DISCONTINUED_SLUGS.has(wanted)) {
      const tc = await bridgeGetCached(
        'taxonomy',
        { action: 'category', slug: category, id_lang: idLangFor(locale) },
        { ttl: CACHE_TTL.taxonomy, tags: [CACHE_TAGS.taxonomy] },
      ); // panne -> 5xx, pas 404
      const c = (tc as { category?: { active?: boolean; nb_products?: number } | null } | null)?.category;
      permanentRedirect(c && c.active && (c.nb_products ?? 0) > 0 ? `/${locale}/${category}` : `/${locale}/catalogue`);
    }
    notFound();
  }

  // Produit bloqué dans le pays du visiteur (module « Bloquer les produits par pays ») : la fiche
  // est introuvable (404), comme le produit l'est déjà dans les listes et la recherche (demande
  // client du 23/11/2026 ; avant, la fiche s'affichait avec un encadré « non disponible »).
  // Le pays est celui du visiteur (géolocalisation) ; pour un robot Google, celui du lien du flux
  // (?pays=) sinon celui de la langue de l'URL (lib/bots.ts). Pour un humain, ?pays= ne fixe que
  // le PRIX : il ne permet pas de contourner le blocage de son propre pays, ni ne bloque la fiche
  // à un visiteur d'un autre pays.
  let blockedHere = !!p.blocked_country;
  if (country && !googleBot) {
    const own = await fetchProductBySlug(slug, locale) as Record<string, any>;
    blockedHere = !!own.blocked_country;
  }
  if (blockedHere) {
    notFound();
  }

  // Redirection 301 vers l'URL canonique si le segment catégorie ne correspond pas
  // (préserve les anciennes URLs indexées : /fr/accueil/{slug} -> /fr/{cat}/{slug}).
  const canonicalCat = (p.category as string) || '';
  // Slug d'une autre langue sous ce préfixe (ex. /ro/riempitivi-dermici/sculptra-2-fiale, slug italien) :
  // 301 vers le slug ET la catégorie de la langue demandée.
  const ownSlug = typeof p.link_rewrite === 'string' ? p.link_rewrite : '';
  if (p.slug_from_other_lang && ownSlug && ownSlug !== slug) {
    permanentRedirect(`/${locale}/${canonicalCat || category}/${ownSlug}${country ? `?pays=${country}` : ''}`);
  }
  if (canonicalCat && canonicalCat !== category) {
    // ?pays= conservé : sinon le lien du flux perdrait son pays (et son prix) à la redirection.
    permanentRedirect(`/${locale}/${canonicalCat}/${slug}${country ? `?pays=${country}` : ''}`);
  }
  const path = `/${canonicalCat || 'produit'}/${slug}`;

  const t = await getTranslations('product');
  const tc = await getTranslations('common');
  const id = String(p.id_product);

  const view: ProductView = {
    id: Number(p.id_product),
    name: p.name ?? t('fallbackName'),
    reference: p.reference ?? '',
    ean13: p.ean13 ?? '',
    brand: p.manufacturer || p.brand || null,
    ht: p.price_excl_tax ?? 0,
    ttc: p.price_incl_tax ?? 0,
    hideTaxLine: googleBot,
    // Statut unique (lib/cardModel.ts) : le même que carte, aperçu rapide et comparateur.
    availabilityState: stockStatus(p.availability, p.available ?? (p.quantity ?? 0) > 0),
    descriptionShort: sanitizeCatalogHtml(p.description_short || ''),
    description: structureDescription(p.description || p.description_short || ''),
    images: Array.isArray(p.images) ? p.images : [],
    features: Array.isArray(p.features)
      ? (p.features as RawFeature[]).map((f) => ({ name: f.name ?? '', value: f.value ?? '' })).filter((f) => f.name)
      : [],
    keyPoints: Array.isArray(p.key_points) ? (p.key_points as string[]).filter((s) => typeof s === 'string' && s) : [],
    faq: Array.isArray(p.faq)
      ? (p.faq as RawFaq[]).map((f) => ({ q: f.q ?? '', a: f.a ?? '' })).filter((f) => f.q && f.a)
      : [],
    composition: Array.isArray(p.composition)
      ? (p.composition as RawCompo[]).map((c) => ({ k: c.k ?? '', v: c.v ?? '' })).filter((c) => c.k && c.v)
      : [],
    rpps_required: !!p.rpps_required,
    blocked_country: !!p.blocked_country,
    reviews: p.reviews ?? null,
    combinations: Array.isArray(p.combinations) ? p.combinations : [],
    priceWithoutReductionTtc: p.price_without_reduction_incl_tax ?? undefined,
    priceWithoutReductionHt: p.price_without_reduction_excl_tax ?? undefined,
    amazonButton: !!p.amazon_product_button,
    quantityDiscounts: Array.isArray(p.quantity_discounts) ? p.quantity_discounts : [],
    canonicalUrl: urlFor(locale, path),
    // Catégorie par défaut du produit (fil d'Ariane) : fournie par le bridge (category + category_name).
    category: canonicalCat && p.category_name ? { slug: canonicalCat, name: String(p.category_name) } : null,
  };

  const rel = await bridgeGetCached(
    'products',
    { related: id, id_lang: idLangFor(locale) },
    { ttl: CACHE_TTL.products, tags: [CACHE_TAGS.products] },
  ).catch(() => ({} as Record<string, any>));
  const cards = (rows: unknown): ReturnType<typeof toCard>[] =>
    Array.isArray(rows) ? (rows as ProductCardData[]).map((r) => ({ ...toCard(r), hideTaxLine: googleBot })) : [];
  const related: RelatedView = {
    boughtTogether: cards(rel.bought_together),
    youMayLike: cards(rel.you_may_like),
    brand: { id: rel.brand?.id_manufacturer ?? 0, name: rel.brand?.name ?? '', products: cards(rel.brand?.products) },
    category: { id: rel.category?.id_category ?? 0, name: rel.category?.name ?? '', products: cards(rel.category?.products) },
    zone: { id: rel.zone?.id_category ?? 0, name: rel.zone?.name ?? '', products: cards(rel.zone?.products) },
  };

  return (
    <div style={{ fontFamily: "'Hanken Grotesk',sans-serif", color: '#34352F', background: 'radial-gradient(1100px 560px at 82% -6%, rgba(140,198,63,0.13), transparent 58%), radial-gradient(820px 520px at -8% 14%, rgba(95,184,154,0.10), transparent 55%), radial-gradient(700px 600px at 50% 118%, rgba(140,198,63,0.08), transparent 60%), #F3F4EF', minHeight: '100vh' }}>
      {/* og:type=product : Next n'émet pas ce type via Metadata -> balise brute hissée au <head> par React. */}
      <meta property="og:type" content="product" />
      {jsonLd(p, path, locale, tc('breadcrumbHome'), googleBot).map((block, i) => (
        <script key={i} type="application/ld+json" dangerouslySetInnerHTML={{ __html: jsonLdString(block) }} />
      ))}
      <Chrome />
      <ProductDetail product={view} related={related} />
      <Footer />
    </div>
  );
}
