import { setRequestLocale, getTranslations } from 'next-intl/server';
import { notFound, permanentRedirect } from 'next/navigation';
import Chrome from '../../../components/Chrome';
import Footer from '../../../components/Footer';
import { sanitizeCatalogHtml } from '@/lib/sanitize';
import { bridgeGetCached } from '@/lib/ps';
import { CACHE_TAGS, CACHE_TTL } from '@/lib/cacheContract';
import { idLangFor } from '@/lib/i18n-config';
import { prepareCms } from '@/lib/cms-toc';
import { alternatesFromSlugs, socialMeta, breadcrumbJsonLd, jsonLdString, urlFor } from '@/lib/seo';

export const dynamic = 'force-dynamic';

// `link_rewrite` = slug CANONIQUE de la locale demandée (le bridge le résout) ; `alternates` =
// slug par langue. Une page CMS a un slug différent par langue (mentions-legales /
// rechtlicher-hinweis / avviso-legale / aviso-legal) et le bridge résout n'importe lequel sous
// n'importe quelle locale -> sans canonicalisation, chaque variante serait auto-canonique.
type CmsPage = {
  title: string;
  meta_description?: string;
  content: string;
  link_rewrite: string;
  date_upd?: string | null;
  alternates?: Record<string, string>;
};

async function getPage(slug: string, locale: string): Promise<CmsPage | null> {
  // Le bridge sert la traduction éditée en BO (table multilingue, 22 langues),
  // avec repli sur PrestaShop (fr/ja). `locale` cible la bonne traduction.
  const data = await bridgeGetCached(
    'content',
    { action: 'page', link_rewrite: slug, id_lang: idLangFor(locale), locale },
    { ttl: CACHE_TTL.content, tags: [CACHE_TAGS.content] },
  ).catch(() => null);
  return data?.page ?? null;
}

function formatDate(raw: string | null | undefined, locale: string): string {
  if (!raw) return '';
  const d = new Date(raw.replace(' ', 'T'));
  if (isNaN(d.getTime())) return '';
  try {
    return new Intl.DateTimeFormat(locale, { day: 'numeric', month: 'long', year: 'numeric' }).format(d);
  } catch {
    return new Intl.DateTimeFormat('fr', { day: 'numeric', month: 'long', year: 'numeric' }).format(d);
  }
}

export async function generateMetadata({ params }: { params: Promise<{ locale: string; slug: string }> }) {
  const { locale, slug } = await params;
  const page = await getPage(slug, locale);
  if (!page) {
    return { title: 'Hyaluronic Filler Market', robots: { index: false } };
  }
  const title = `${page.title} — Hyaluronic Filler Market`;
  const description = page.meta_description || undefined;
  // Canonique = slug de la locale (PAS celui demandé) : une vieille URL localisée servie sous une
  // autre locale ne doit pas devenir une variante auto-canonique concurrente.
  const path = `/content/${page.link_rewrite || slug}`;
  return {
    title,
    description,
    // hreflang : le slug CMS varie par langue -> map id_lang renvoyée par le bridge.
    alternates: alternatesFromSlugs(locale, path, (idLang) => {
      const s = page.alternates?.[idLang];
      return s ? `/content/${s}` : null;
    }),
    ...socialMeta(locale, path, title, description),
  };
}

export default async function ContentPage({ params }: { params: Promise<{ locale: string; slug: string }> }) {
  const { locale, slug } = await params;
  setRequestLocale(locale);
  const page = await getPage(slug, locale);
  if (!page) notFound();

  // 301 vers le slug de la locale : le bridge résout n'importe quel slug (toutes langues) pour
  // préserver les anciennes URLs indexées (/de/content/mentions-legales, /fr/content/aviso-legal…),
  // mais une seule URL doit être servie en 200 par locale, sinon duplicate content.
  const canonicalSlug = page.link_rewrite || slug;
  if (canonicalSlug && canonicalSlug !== slug) {
    permanentRedirect(`/${locale}/content/${canonicalSlug}`);
  }

  const t = await getTranslations('content');
  const tc = await getTranslations('common');
  // Sanitisation liste blanche AVANT la préparation (les ancres de sommaire
  // sont ajoutées ensuite par prepareCms et survivent donc au nettoyage).
  const { html, toc, subtitle } = prepareCms(sanitizeCatalogHtml(page.content));
  const updated = formatDate(page.date_upd, locale);
  const hasToc = toc.length >= 2;
  // Numérote le sommaire seulement si les titres ne portent pas déjà un numéro/« Article ».
  const numbered = toc.every((it) => !/^\s*(\d|article|art\.?|§)/i.test(it.text));

  return (
    <div style={{ fontFamily: "'Hanken Grotesk',sans-serif", color: '#34352F', background: 'radial-gradient(1100px 560px at 82% -6%, rgba(140,198,63,0.13), transparent 58%), radial-gradient(820px 520px at -8% 14%, rgba(95,184,154,0.10), transparent 55%), radial-gradient(700px 600px at 50% 118%, rgba(140,198,63,0.08), transparent 60%), #F3F4EF', minHeight: '100vh' }}>
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{
          __html: jsonLdString(breadcrumbJsonLd([
            { name: tc('breadcrumbHome'), url: urlFor(locale, '') },
            { name: page.title, url: urlFor(locale, `/content/${canonicalSlug}`) },
          ])),
        }}
      />
      <Chrome />
      <main className="hfm-wrap" style={{ maxWidth: '1100px', margin: '0 auto', padding: '52px 28px 90px' }}>
        {/* Hero : titre + sous-titre + date de mise à jour */}
        <header className="hfm-legal-hero">
          <h1>{page.title}</h1>
          {subtitle && <p className="hfm-legal-sub">{subtitle}</p>}
          {updated && <div className="hfm-legal-upd">{t('updatedOn')} {updated}</div>}
        </header>

        <div className={hasToc ? 'hfm-legal-grid' : ''}>
          {hasToc && (
            <nav className="hfm-toc" aria-label={t('summary')}>
              <div className="hfm-toc-label">{t('summary')}</div>
              <ol>
                {toc.map((it, idx) => (
                  <li key={it.id}>
                    <a href={`#${it.id}`}>{numbered ? `${idx + 1}. ${it.text}` : it.text}</a>
                  </li>
                ))}
              </ol>
            </nav>
          )}
          <article className="hfm-cms" dangerouslySetInnerHTML={{ __html: html }} />
        </div>
      </main>
      <Footer />
    </div>
  );
}
