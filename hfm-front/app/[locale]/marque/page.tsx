import type { Metadata } from 'next';
import { setRequestLocale, getTranslations } from 'next-intl/server';
import { bridgeGetCached } from '@/lib/ps';
import { CACHE_TAGS, CACHE_TTL } from '@/lib/cacheContract';
import { idLangFor } from '@/lib/i18n-config';
import { alternatesFor, breadcrumbJsonLd, jsonLdString, urlFor } from '@/lib/seo';
import { Link } from '@/i18n/navigation';
import { fetchMenu } from '@/lib/facets';
import { ROOT_BRANDS, categoryHref, nameKey, visibleMenu } from '@/lib/menu';
import Chrome from '../../components/Chrome';
import Footer from '../../components/Footer';

// INDEX DES MARQUES : nom + nombre de produits, une tuile par maison.
// Source = les marques du méga-menu (catégories filles de « Marques ») : mêmes noms et mêmes comptes
// que le menu, et elles couvrent les produits sans fabricant renseigné (Dives, Innoaesthetics…).
// Complété par les fabricants PS qui n'ont pas de catégorie-marque (Needle Concept, IBSA…) ->
// leur page /marque/{id}. Une marque sans produit actif n'apparaît pas. Ordre alphabétique.
type Entry = { name: string; href: string; count: number };
type Manu = { id_manufacturer: number; name: string; nb_products: number };

async function brandEntries(locale: string): Promise<Entry[]> {
  const idLang = idLangFor(locale);
  const [menuRaw, manus] = await Promise.all([
    fetchMenu(idLang),
    bridgeGetCached('taxonomy', { action: 'manufacturers', id_lang: idLang }, { ttl: CACHE_TTL.taxonomy, tags: [CACHE_TAGS.taxonomy] })
      .then((d) => ((d as { manufacturers?: Manu[] }).manufacturers ?? []))
      .catch(() => [] as Manu[]),
  ]);
  const brands = visibleMenu(menuRaw).find((r) => r.id_category === ROOT_BRANDS)?.children ?? [];
  const entries: Entry[] = brands.map((b) => ({ name: b.name.trim(), href: categoryHref(b), count: b.nb_products }));
  const keys = brands.map((b) => nameKey(b.name));
  for (const m of manus) {
    const mk = nameKey(m.name);
    if (!mk || !(m.nb_products > 0)) continue;
    if (keys.some((k) => k.startsWith(mk) || mk.startsWith(k))) continue;
    entries.push({ name: m.name.trim(), href: `/marque/${m.id_manufacturer}`, count: m.nb_products });
    keys.push(mk);
  }
  return entries.sort((a, b) => a.name.localeCompare(b.name, locale, { sensitivity: 'base' }));
}

export async function generateMetadata({ params }: { params: Promise<{ locale: string }> }): Promise<Metadata> {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: 'brand' });
  return {
    title: t('indexTitle'),
    description: t('indexIntro'),
    alternates: alternatesFor(locale, '/marque'),
  };
}

export default async function BrandIndexPage({ params }: { params: Promise<{ locale: string }> }) {
  const { locale } = await params;
  setRequestLocale(locale);
  const t = await getTranslations('brand');
  const tc = await getTranslations('common');
  const entries = await brandEntries(locale); // pas de .catch : une panne du bridge remonte en 5xx

  return (
    <div style={{ fontFamily: "'Hanken Grotesk',sans-serif", color: '#34352F', background: 'radial-gradient(1100px 560px at 82% -6%, rgba(140,198,63,0.13), transparent 58%), radial-gradient(820px 520px at -8% 14%, rgba(95,184,154,0.10), transparent 55%), radial-gradient(700px 600px at 50% 118%, rgba(140,198,63,0.08), transparent 60%), #F3F4EF', minHeight: '100vh' }}>
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{
          __html: jsonLdString(breadcrumbJsonLd([
            { name: tc('breadcrumbHome'), url: urlFor(locale, '') },
            { name: t('indexTitle'), url: urlFor(locale, '/marque') },
          ])),
        }}
      />
      <Chrome />
      <main data-screen-label="Marques" className="hfm-wrap" style={{ maxWidth: '1340px', margin: '0 auto', padding: '34px 28px 80px' }}>
        <div style={{ fontSize: '12.5px', color: '#9A9A9A', marginBottom: '18px' }}><Link href="/">{tc('home')}</Link>{'  /  '}{t('breadcrumbBrands')}</div>
        <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: 'clamp(28px,4vw,40px)', color: '#2B2B2B', margin: 0 }}>{t('indexTitle')}</h1>
        <p style={{ fontSize: '14.5px', lineHeight: 1.6, color: '#6E7585', margin: '10px 0 0', maxWidth: '640px' }}>{t('indexIntro')}</p>
        <div style={{ fontSize: '13px', color: '#8A8170', margin: '10px 0 30px' }}>{t('brandsCount', { count: entries.length })}</div>

        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill,minmax(220px,1fr))', gap: '12px' }}>
          {entries.map((e) => (
            <Link key={e.href} href={e.href} style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: '12px', padding: '16px 18px', background: '#fff', border: '1px solid #E7E3DA', borderRadius: '13px', boxShadow: '0 2px 10px -8px rgba(40,50,25,.3)', color: '#2B2B2B', textDecoration: 'none' }}>
              <span style={{ minWidth: 0 }}>
                <span style={{ display: 'block', fontFamily: "'Spectral',serif", fontSize: '17px', lineHeight: 1.25 }}>{e.name}</span>
                <span style={{ display: 'block', fontSize: '12.5px', color: '#8A8170', marginTop: '3px' }}>{t('productsCount', { count: e.count })}</span>
              </span>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#8CC63F" strokeWidth="2" strokeLinecap="round" style={{ flex: 'none' }}><path d="M9 6l6 6-6 6" /></svg>
            </Link>
          ))}
        </div>
      </main>
      <Footer />
    </div>
  );
}
