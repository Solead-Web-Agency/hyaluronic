import type { Metadata } from 'next';
import { notFound, permanentRedirect } from 'next/navigation';
import { setRequestLocale, getTranslations } from 'next-intl/server';
import { bridgeGetCached } from '@/lib/ps';
import { CACHE_TAGS, CACHE_TTL } from '@/lib/cacheContract';
import { idLangFor } from '@/lib/i18n-config';
import { alternatesFromSlugs, breadcrumbJsonLd, itemListJsonLd, jsonLdString, socialMeta, urlFor } from '@/lib/seo';
import { toCard, productHref } from '@/lib/cardModel';
import { Link } from '@/i18n/navigation';
import { fetchAllProducts, fetchMenu, listingFacets } from '@/lib/facets';
import { ROOT_BRANDS, ROOT_EFFECTS, ROOT_TYPOLOGIES, ROOT_ZONES, categoryHref, rootHref, visibleMenu } from '@/lib/menu';
import Chrome from '../../components/Chrome';
import Footer from '../../components/Footer';
import ProductListing from '../catalogue/ProductListing';

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

// Racines éditoriales sans produit propre : leur page = index de la rubrique (sous-catégories).
const RUBRIC_ROOTS = new Set([ROOT_TYPOLOGIES, ROOT_ZONES, ROOT_EFFECTS]);

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
  ); // pas de .catch : une panne du bridge remonte en 5xx, jamais en 404
  return (d as { category?: Cat | null })?.category ?? null;
}

// Ancien format PS « {id}-{slug} » (ex. /fr/167-rides-du-visage, encore cité dans les descriptions
// produit et indexé) -> slug actuel de la catégorie {id}. Consulté seulement quand aucun slug ne
// correspond : une vraie catégorie au slug numérique (« 131-hyamira ») reste servie telle quelle.
async function legacyCategorySlug(slug: string, locale: string): Promise<string | null> {
  const m = /^(\d+)-/.exec(slug);
  if (!m) return null;
  const d = await bridgeGetCached(
    'taxonomy',
    { action: 'all_active', id_lang: idLangFor(locale) },
    { ttl: CACHE_TTL.taxonomy, tags: [CACHE_TAGS.taxonomy] },
  ).catch(() => null);
  const cats = (d as { categories?: { id_category: number; link_rewrite: string }[] })?.categories ?? [];
  return cats.find((c) => c.id_category === Number(m[1]))?.link_rewrite ?? null;
}

export async function generateMetadata({ params }: { params: Promise<{ locale: string; category: string }> }): Promise<Metadata> {
  const { locale, category } = await params;
  const cat = await fetchCategory(category, locale);
  if (!cat || !cat.active || (cat.nb_products === 0 && !RUBRIC_ROOTS.has(cat.id_category))) {
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
  // Ancienne URL « {id}-{slug} » -> 301 vers l'URL actuelle de la catégorie.
  if (!cat) {
    const current = await legacyCategorySlug(category, locale);
    if (current) permanentRedirect(`/${locale}/${current}`);
  }
  // Slug inconnu (CMS/fonctionnel) -> 404 propre.
  if (!cat) {
    notFound();
  }
  // « Marques » (#30) : remplacée par l'index des marques (nom + nombre de produits).
  if (cat.id_category === ROOT_BRANDS) {
    permanentRedirect(`/${locale}/marque`);
  }

  const idLang = idLangFor(locale);
  const t = await getTranslations('catalogue');
  const tc = await getTranslations('common');
  const menuRaw = await fetchMenu(idLang).catch(() => []);
  const menu = visibleMenu(menuRaw);

  // Rubrique éditoriale (Par zone, Par effet, Catalogue) : page index de ses sous-catégories.
  const rubric = RUBRIC_ROOTS.has(cat.id_category) ? menu.find((r) => r.id_category === cat.id_category) : undefined;
  if (cat.active && rubric && (rubric.children ?? []).length) {
    return (
      <RubricIndex locale={locale} category={category} name={cat.name}>
        <div style={{ fontSize: '13px', color: '#8A8170', margin: '8px 0 30px' }}>{t('rubricCount', { count: (rubric.children ?? []).length })}</div>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill,minmax(230px,1fr))', gap: '14px' }}>
          {(rubric.children ?? []).map((c) => (
            <Link key={c.id_category} href={categoryHref(c)} style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: '12px', padding: '18px 20px', background: '#fff', border: '1px solid #E7E3DA', borderRadius: '14px', boxShadow: '0 2px 10px -8px rgba(40,50,25,.3)', color: '#2B2B2B', textDecoration: 'none' }}>
              <span style={{ minWidth: 0 }}>
                <span style={{ display: 'block', fontFamily: "'Spectral',serif", fontSize: '18px', lineHeight: 1.25 }}>{c.name}</span>
                <span style={{ display: 'block', fontSize: '12.5px', color: '#8A8170', marginTop: '4px' }}>{t('productsCount', { count: c.nb_products })}</span>
              </span>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#8CC63F" strokeWidth="2" strokeLinecap="round" style={{ flex: 'none' }}><path d="M9 6l6 6-6 6" /></svg>
            </Link>
          ))}
        </div>
        {cat.id_category === ROOT_TYPOLOGIES ? null : (
          <div style={{ marginTop: '28px' }}>
            <Link href="/catalogue" style={{ fontSize: '14px', fontWeight: 600, color: '#5E8E1F' }}>{tc('seeCatalogue')} →</Link>
          </div>
        )}
      </RubricIndex>
    );
  }

  // Catégorie désactivée par la recatégorisation ou vide -> 301 catalogue (évite le 404, garde le jus).
  if (!cat.active || cat.nb_products === 0) {
    permanentRedirect(`/${locale}/catalogue`);
  }

  // TOUT le listing (≤ 1000, mis en cache) : filtres et tri instantanés côté client ; les 48
  // premiers produits sont rendus côté serveur (SEO), la suite au défilement.
  const { products } = await fetchAllProducts({ id_category: cat.id_category }, idLang);
  const facets = await listingFacets(products, menuRaw);
  const cards = products.slice(0, 48).map(toCard);
  // Rubrique parente (fil d'Ariane) : Marques, Catalogue, Par zone ou Par effet.
  const parent = menu.find((r) => (r.children ?? []).some((c) => c.id_category === cat.id_category));

  return (
    <div style={{ fontFamily: "'Hanken Grotesk',sans-serif", color: '#34352F', background: 'radial-gradient(1100px 560px at 82% -6%, rgba(140,198,63,0.13), transparent 58%), radial-gradient(820px 520px at -8% 14%, rgba(95,184,154,0.10), transparent 55%), radial-gradient(700px 600px at 50% 118%, rgba(140,198,63,0.08), transparent 60%), #F3F4EF', minHeight: '100vh' }}>
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{
          __html: jsonLdString(breadcrumbJsonLd([
            { name: tc('breadcrumbHome'), url: urlFor(locale, '') },
            ...(parent ? [{ name: parent.name, url: urlFor(locale, rootHref(parent)) }] : []),
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
      <main className="hfm-wrap" style={{ maxWidth: '1340px', margin: '0 auto', padding: '34px 28px 80px' }}>
        <div style={{ fontSize: '12.5px', color: '#9A9A9A', marginBottom: '18px' }}>
          <Link href="/">{tc('home')}</Link>
          {parent ? <>{'  /  '}<Link href={rootHref(parent)}>{parent.name}</Link></> : null}
          {'  /  '}{cat.name}
        </div>
        <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: 'clamp(28px,4vw,40px)', color: '#2B2B2B', margin: 0 }}>{cat.name}</h1>
        <div style={{ fontSize: '13px', color: '#8A8170', marginTop: '8px' }}>{t('productsCount', { count: products.length })}</div>
        <ProductListing products={products} facets={facets} hideKeys={[`c${cat.id_category}`]} />
      </main>
      <Footer />
    </div>
  );
}

const PAGE_BG = 'radial-gradient(1100px 560px at 82% -6%, rgba(140,198,63,0.13), transparent 58%), radial-gradient(820px 520px at -8% 14%, rgba(95,184,154,0.10), transparent 55%), radial-gradient(700px 600px at 50% 118%, rgba(140,198,63,0.08), transparent 60%), #F3F4EF';

// Gabarit de la page index d'une rubrique (Par zone, Par effet, Catalogue).
async function RubricIndex({ locale, category, name, children }: { locale: string; category: string; name: string; children: React.ReactNode }) {
  const tc = await getTranslations('common');
  return (
    <div style={{ fontFamily: "'Hanken Grotesk',sans-serif", color: '#34352F', background: PAGE_BG, minHeight: '100vh' }}>
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{
          __html: jsonLdString(breadcrumbJsonLd([
            { name: tc('breadcrumbHome'), url: urlFor(locale, '') },
            { name, url: urlFor(locale, `/${category}`) },
          ])),
        }}
      />
      <Chrome />
      <main className="hfm-wrap" style={{ maxWidth: '1340px', margin: '0 auto', padding: '34px 28px 80px' }}>
        <div style={{ fontSize: '12.5px', color: '#9A9A9A', marginBottom: '18px' }}><Link href="/">{tc('home')}</Link>{'  /  '}{name}</div>
        <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: 'clamp(28px,4vw,40px)', color: '#2B2B2B', margin: 0 }}>{name}</h1>
        {children}
      </main>
      <Footer />
    </div>
  );
}
