import type { Metadata } from 'next';
import { notFound, permanentRedirect } from 'next/navigation';
import { bridgeGetCached } from '@/lib/ps';
import { CACHE_TAGS, CACHE_TTL } from '@/lib/cacheContract';
import { setRequestLocale, getTranslations } from 'next-intl/server';
import { idLangFor } from '@/lib/i18n-config';
import { alternatesFor, textFromHtml, urlFor } from '@/lib/seo';
import { sanitizeCatalogHtml, structureDescription } from '@/lib/sanitize';
import Chrome from '../../../components/Chrome';
import Footer from '../../../components/Footer';
import ProductDetail, { type ProductView } from '../../produit/[id]/ProductDetail';
import { type RelatedView } from '../../produit/[id]/RelatedSections';
import { toCard } from '@/lib/cardModel';
import type { ProductCard as ProductCardData } from '@/lib/ps';

type RawFeature = { name?: string; value?: string };
type RawFaq = { q?: string; a?: string };
type RawCompo = { k?: string; v?: string };

// Fiche produit par SLUG (URL « parité prod » /{categorie}/{slug}). Le bridge résout
// le link_rewrite -> produit et renvoie sa catégorie canonique (pour le 301 éventuel).
async function fetchProductBySlug(slug: string, locale: string) {
  const data = await bridgeGetCached(
    'products',
    { link_rewrite: slug, id_lang: idLangFor(locale) },
    { ttl: CACHE_TTL.products, tags: [CACHE_TAGS.products] },
  ).catch(() => ({}));
  return (data as { product?: Record<string, unknown> }).product ?? {};
}

export async function generateMetadata({ params }: { params: Promise<{ locale: string; category: string; slug: string }> }): Promise<Metadata> {
  const { locale, slug } = await params;
  const p = await fetchProductBySlug(slug, locale) as Record<string, any>;
  if (!p.name) {
    return { robots: { index: false } };
  }

  const title = p.meta_title || p.name;
  const description = p.meta_description || textFromHtml(p.description_short || p.description || '', 160);
  // Canonique = catégorie CANONIQUE du produit (pas forcément celle de l'URL demandée).
  const path = `/${p.category || 'produit'}/${slug}`;
  const images = Array.isArray(p.images) && p.images.length ? [{ url: p.images[0] as string }] : undefined;

  return {
    title,
    description,
    alternates: alternatesFor(locale, path),
    openGraph: {
      title,
      description,
      url: urlFor(locale, path),
      type: 'website',
      images,
      siteName: 'Hyaluronic Filler Market',
    },
  };
}

function jsonLd(p: Record<string, any>, path: string, locale: string): object[] {
  const availability = p.availability === 'in_stock' ? 'https://schema.org/InStock'
    : p.availability === 'backorder' ? 'https://schema.org/PreOrder'
    : 'https://schema.org/OutOfStock';

  const product: Record<string, unknown> = {
    '@context': 'https://schema.org',
    '@type': 'Product',
    name: p.name,
    description: p.meta_description || textFromHtml(p.description_short || '', 500),
    image: Array.isArray(p.images) ? p.images : [],
    sku: p.reference || undefined,
    gtin13: /^\d{13}$/.test(String(p.ean13 ?? '')) ? p.ean13 : undefined,
    brand: p.manufacturer ? { '@type': 'Brand', name: p.manufacturer } : undefined,
    offers: {
      '@type': 'Offer',
      url: urlFor(locale, path),
      priceCurrency: 'EUR',
      price: Number(p.price_incl_tax ?? 0).toFixed(2),
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
  }

  const blocks: object[] = [product];
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

export default async function ProductBySlugPage({ params }: { params: Promise<{ locale: string; category: string; slug: string }> }) {
  const { locale, category, slug } = await params;
  setRequestLocale(locale);
  const p = await fetchProductBySlug(slug, locale) as Record<string, any>;
  if (!p.name) {
    // Produit discontinué (inactif/supprimé) : 301 vers SA catégorie par défaut (résolue même si
    // le produit est inactif), sinon 404. Préserve le jus SEO + l'UX. Si cette catégorie est
    // elle-même désactivée, sa page redirige à son tour vers le catalogue.
    const tx = await bridgeGetCached(
      'products',
      { disc_slug: slug, id_lang: idLangFor(locale) },
      { ttl: CACHE_TTL.products, tags: [CACHE_TAGS.products] },
    ).catch(() => null);
    const catSlug = (tx as { discontinued_category?: string | null } | null)?.discontinued_category;
    if (catSlug) {
      permanentRedirect(`/${locale}/${catSlug}`);
    }
    notFound();
  }

  // Redirection 301 vers l'URL canonique si le segment catégorie ne correspond pas
  // (préserve les anciennes URLs indexées : /fr/accueil/{slug} -> /fr/{cat}/{slug}).
  const canonicalCat = (p.category as string) || '';
  if (canonicalCat && canonicalCat !== category) {
    permanentRedirect(`/${locale}/${canonicalCat}/${slug}`);
  }
  const path = `/${canonicalCat || 'produit'}/${slug}`;

  const t = await getTranslations('product');
  const id = String(p.id_product);

  const view: ProductView = {
    id: Number(p.id_product),
    name: p.name ?? t('fallbackName'),
    reference: p.reference ?? '',
    ean13: p.ean13 ?? '',
    brand: p.manufacturer || p.brand || null,
    ht: p.price_excl_tax ?? 0,
    ttc: p.price_incl_tax ?? 0,
    availabilityState: p.availability === 'in_stock' ? 'in'
      : p.availability === 'backorder' ? 'backorder'
      : p.availability === 'unavailable' ? 'out'
      : (p.available ?? (p.quantity ?? 0) > 0) ? 'in' : 'out',
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
    reviews: p.reviews ?? null,
  };

  const rel = await bridgeGetCached(
    'products',
    { related: id, id_lang: idLangFor(locale) },
    { ttl: CACHE_TTL.products, tags: [CACHE_TAGS.products] },
  ).catch(() => ({} as Record<string, any>));
  const cards = (rows: unknown): ReturnType<typeof toCard>[] =>
    Array.isArray(rows) ? (rows as ProductCardData[]).map(toCard) : [];
  const related: RelatedView = {
    boughtTogether: cards(rel.bought_together),
    youMayLike: cards(rel.you_may_like),
    brand: { id: rel.brand?.id_manufacturer ?? 0, name: rel.brand?.name ?? '', products: cards(rel.brand?.products) },
    category: { id: rel.category?.id_category ?? 0, name: rel.category?.name ?? '', products: cards(rel.category?.products) },
    zone: { id: rel.zone?.id_category ?? 0, name: rel.zone?.name ?? '', products: cards(rel.zone?.products) },
  };

  return (
    <div style={{ fontFamily: "'Hanken Grotesk',sans-serif", color: '#34352F', background: 'radial-gradient(1100px 560px at 82% -6%, rgba(140,198,63,0.13), transparent 58%), radial-gradient(820px 520px at -8% 14%, rgba(95,184,154,0.10), transparent 55%), radial-gradient(700px 600px at 50% 118%, rgba(140,198,63,0.08), transparent 60%), #F3F4EF', minHeight: '100vh' }}>
      {jsonLd(p, path, locale).map((block, i) => (
        <script key={i} type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(block) }} />
      ))}
      <Chrome />
      <ProductDetail product={view} related={related} />
      <Footer />
    </div>
  );
}
