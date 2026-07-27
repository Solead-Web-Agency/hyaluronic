import type { Metadata } from 'next';
import { notFound, permanentRedirect } from 'next/navigation';
import { setRequestLocale, getTranslations } from 'next-intl/server';
import { bridgeGetCached, type ProductCard as PC } from '@/lib/ps';
import { CACHE_TAGS, CACHE_TTL } from '@/lib/cacheContract';
import { idLangFor } from '@/lib/i18n-config';
import { alternatesFromSlugs, breadcrumbJsonLd, itemListJsonLd, jsonLdString, socialMeta, urlFor } from '@/lib/seo';
import { toCard, productHref } from '@/lib/cardModel';
import Chrome from '../../components/Chrome';
import Footer from '../../components/Footer';
import CategoryProducts from './CategoryProducts';
import { PRODUCT_PAGE_SIZE } from '@/lib/useProductPagination';

// Page CATÉGORIE (parité prod : /{locale}/{category}, self-canonical). Les anciennes URLs
// catégorie indexées (marques-catégories, familles) restent des pages dédiées -> pas de perte SEO.
type Cat = {
  id_category: number;
  name: string;
  link_rewrite: string;
  active: boolean;
  nb_products: number;
  id_parent: number;
  meta_title?: string;
  meta_description?: string;
  description?: string;
  alternates?: Record<string, string>; // id_lang -> slug (hreflang)
};

// Anciennes pages « virtuelles » PS (pas de vraie catégorie en base) -> redirection 301.
const VIRTUAL: Record<string, string> = {
  'nouveaux-produits': '/catalogue?filter=new',
  'nouveautes': '/catalogue?filter=new',
  'promotions': '/catalogue?filter=promo',
  'meilleures-ventes': '/catalogue?filter=best',
};

async function fetchCategory(slug: string, locale: string): Promise<Cat | null> {
  const d = await bridgeGetCached(
    'taxonomy',
    { action: 'category', slug, id_lang: idLangFor(locale) },
    { ttl: CACHE_TTL.taxonomy, tags: [CACHE_TAGS.taxonomy] },
  ).catch(() => null);
  return (d as { category?: Cat | null })?.category ?? null;
}

export async function generateMetadata({ params }: { params: Promise<{ locale: string; category: string }> }): Promise<Metadata> {
  const { locale, category } = await params;
  const cat = await fetchCategory(category, locale);
  if (!cat || !cat.active || cat.nb_products === 0) {
    return { robots: { index: false } };
  }
  // Titre/description = ceux édités en BO (parité ancien site), sinon repli propre.
  const title = cat.meta_title?.trim() ? cat.meta_title : `${cat.name} — Hyaluronic Filler Market`;
  const description = cat.meta_description?.trim() || undefined;
  return {
    title,
    description,
    // hreflang : le slug catégorie varie par langue (FR « comblement » -> EN « filler »).
    alternates: alternatesFromSlugs(locale, `/${category}`, (idLang) => {
      const s = cat.alternates?.[idLang];
      return s ? `/${s}` : null;
    }),
    ...socialMeta(locale, `/${category}`, title, description),
  };
}

export default async function CategoryPage({ params }: { params: Promise<{ locale: string; category: string }> }) {
  const { locale, category } = await params;
  setRequestLocale(locale);

  const cat = await fetchCategory(category, locale);

  // Ancienne page virtuelle PS -> redirection 301 vers le catalogue filtré.
  if (!cat && VIRTUAL[category]) {
    permanentRedirect(`/${locale}${VIRTUAL[category]}`);
  }
  // Slug inconnu (CMS/fonctionnel) -> 404 propre.
  if (!cat) {
    notFound();
  }
  // Catégorie désactivée par la recatégorisation ou vide -> 301 catalogue (évite le 404, garde le jus).
  if (!cat.active || cat.nb_products === 0) {
    permanentRedirect(`/${locale}/catalogue`);
  }

  const idLang = idLangFor(locale);
  // Page 1 rendue côté serveur (SEO, 48 produits) + total. Les pages suivantes sont fetchées à la
  // demande côté client (infinite scroll) -> on ne « charge plus tout » (une catégorie ~257 était
  // servie en un bloc de 300). Le rendu serveur initial et les redirections restent inchangés.
  const d = await bridgeGetCached(
    'products',
    { id_category: cat.id_category, limit: PRODUCT_PAGE_SIZE, page: 1, order: 'position', id_lang: idLang },
    { ttl: CACHE_TTL.products, tags: [CACHE_TAGS.products] },
  ).catch(() => ({ products: [] as PC[], total: 0 }));
  const page1 = ((d as { products?: PC[] }).products) ?? [];
  const total = Number((d as { total?: number }).total ?? page1.length);
  const cards = page1.map(toCard);
  const tc = await getTranslations('common');

  return (
    <div style={{ fontFamily: "'Hanken Grotesk',sans-serif", color: '#34352F', background: 'radial-gradient(1100px 560px at 82% -6%, rgba(140,198,63,0.13), transparent 58%), radial-gradient(820px 520px at -8% 14%, rgba(95,184,154,0.10), transparent 55%), radial-gradient(700px 600px at 50% 118%, rgba(140,198,63,0.08), transparent 60%), #F3F4EF', minHeight: '100vh' }}>
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{
          __html: jsonLdString(breadcrumbJsonLd([
            { name: tc('breadcrumbHome'), url: urlFor(locale, '') },
            { name: cat.name, url: urlFor(locale, `/${category}`) },
          ])),
        }}
      />
      {cards.length > 0 && (
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{
            __html: jsonLdString(itemListJsonLd(
              cards.map((c) => ({ name: c.name, url: urlFor(locale, productHref(c)) })),
            )),
          }}
        />
      )}
      <Chrome />
      <main className="hfm-wrap" style={{ maxWidth: '1340px', margin: '0 auto', padding: '44px 28px 80px' }}>
        <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: 'clamp(28px,4vw,40px)', color: '#2B2B2B', margin: 0 }}>{cat.name}</h1>
        <div style={{ fontSize: '13px', color: '#8A8170', margin: '8px 0 30px' }}>{cat.nb_products} produits</div>
        <CategoryProducts initial={page1} total={total} idCategory={cat.id_category} idLang={idLang} />
      </main>
      <Footer />
    </div>
  );
}
