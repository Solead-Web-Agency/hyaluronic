import { bridgeGetCached } from '@/lib/ps';
import { CACHE_TAGS, CACHE_TTL } from '@/lib/cacheContract';
import { setRequestLocale, getTranslations } from 'next-intl/server';
import { idLangFor } from '@/lib/i18n-config';
import Chrome from '../../../components/Chrome';
import Footer from '../../../components/Footer';
import ProductDetail, { type ProductView } from './ProductDetail';
import { type RelatedView } from './RelatedSections';
import { toCard } from '@/lib/cardModel';
import type { ProductCard as ProductCardData } from '@/lib/ps';

type RawFeature = { name?: string; value?: string };
type RawFaq = { q?: string; a?: string };
type RawCompo = { k?: string; v?: string };

export default async function ProductPage({ params }: { params: Promise<{ locale: string; id: string }> }) {
  const { locale, id } = await params;
  setRequestLocale(locale);
  const t = await getTranslations('product');
  const data = await bridgeGetCached(
    'products',
    { id_product: id, id_lang: idLangFor(locale) },
    { ttl: CACHE_TTL.products, tags: [CACHE_TAGS.products] },
  );
  const p = data.product ?? {};

  const view: ProductView = {
    id: Number(id),
    name: p.name ?? t('fallbackName'),
    reference: p.reference ?? '',
    ean13: p.ean13 ?? '',
    brand: p.manufacturer || p.brand || null,
    ht: p.price_excl_tax ?? 0,
    ttc: p.price_incl_tax ?? 0,
    inStock: p.available != null ? !!p.available : (p.quantity ?? 0) > 0,
    descriptionShort: p.description_short || '',
    description: p.description || p.description_short || '',
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
  };

  // Cross-selling (accessoires, marque, catégorie, zone) — même cache que le catalogue.
  const rel = await bridgeGetCached(
    'products',
    { related: id, id_lang: idLangFor(locale) },
    { ttl: CACHE_TTL.products, tags: [CACHE_TAGS.products] },
  );
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
      <Chrome />
      <ProductDetail product={view} related={related} />
      <Footer />
    </div>
  );
}
