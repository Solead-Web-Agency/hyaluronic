import type { Metadata } from 'next';
import { notFound, permanentRedirect } from 'next/navigation';
import { setRequestLocale, getTranslations } from 'next-intl/server';
import { Link } from '@/i18n/navigation';
import { alternatesFromSlugs, breadcrumbJsonLd, jsonLdString, textFromHtml, urlFor } from '@/lib/seo';
import { sanitizeCatalogHtml } from '@/lib/sanitize';
import { fetchBlogPost } from '@/lib/blog';
import Chrome from '../../../../components/Chrome';
import Footer from '../../../../components/Footer';

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

export async function generateMetadata({ params }: { params: Promise<{ locale: string; category: string; slug: string }> }): Promise<Metadata> {
  const { locale, slug } = await params;
  const p = await fetchBlogPost(locale, slug);
  if (!p) return { robots: { index: false } };

  const title = p.metaTitle || p.title;
  const description = p.metaDescription || textFromHtml(p.excerpt || p.content, 160);
  const path = `/blog/${p.category?.slug || 'article'}/${slug}`;
  const images = p.cover?.wide ? [{ url: p.cover.wide }] : undefined;

  return {
    title,
    description,
    // hreflang : slug article + slug catégorie blog varient par langue (repli langue par défaut).
    alternates: alternatesFromSlugs(locale, path, (idLang) => {
      const a = p.alternates?.[idLang];
      return a?.slug ? `/blog/${a.category || 'article'}/${a.slug}` : null;
    }),
    openGraph: {
      title,
      description,
      url: urlFor(locale, path),
      type: 'article',
      images,
      siteName: 'Hyaluronic Filler Market',
    },
  };
}

function jsonLd(p: NonNullable<Awaited<ReturnType<typeof fetchBlogPost>>>, path: string, locale: string): object {
  return {
    '@context': 'https://schema.org',
    '@type': 'BlogPosting',
    headline: p.title,
    description: p.metaDescription || textFromHtml(p.excerpt || p.content, 300),
    image: p.cover?.wide ? [p.cover.wide] : undefined,
    datePublished: p.date ? p.date.replace(' ', 'T') : undefined,
    dateModified: (p.dateUpd || p.date) ? (p.dateUpd || p.date).replace(' ', 'T') : undefined,
    author: p.author ? { '@type': 'Person', name: p.author } : { '@type': 'Organization', name: 'Hyaluronic Filler Market' },
    publisher: { '@type': 'Organization', name: 'Hyaluronic Filler Market' },
    mainEntityOfPage: { '@type': 'WebPage', '@id': urlFor(locale, path) },
    articleSection: p.category?.name || undefined,
    keywords: Array.isArray(p.tags) && p.tags.length ? p.tags.join(', ') : undefined,
  };
}

export default async function BlogArticlePage({ params }: { params: Promise<{ locale: string; category: string; slug: string }> }) {
  const { locale, category, slug } = await params;
  setRequestLocale(locale);
  const p = await fetchBlogPost(locale, slug);
  if (!p) notFound();

  // 301 canonique si le segment catégorie ne correspond pas à la catégorie de l'article.
  const canonicalCat = p.category?.slug || '';
  if (canonicalCat && canonicalCat !== category) {
    permanentRedirect(`/${locale}/blog/${canonicalCat}/${slug}`);
  }
  const path = `/blog/${canonicalCat || 'article'}/${slug}`;

  const t = await getTranslations('blog');
  const html = sanitizeCatalogHtml(p.content);
  const date = formatDate(p.date, locale);

  return (
    <div style={{ fontFamily: "'Hanken Grotesk',sans-serif", color: '#34352F', background: 'radial-gradient(1100px 560px at 82% -6%, rgba(140,198,63,0.13), transparent 58%), radial-gradient(820px 520px at -8% 14%, rgba(95,184,154,0.10), transparent 55%), radial-gradient(700px 600px at 50% 118%, rgba(140,198,63,0.08), transparent 60%), #F3F4EF', minHeight: '100vh' }}>
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: jsonLdString(jsonLd(p, path, locale)) }} />
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{
          __html: jsonLdString(breadcrumbJsonLd([
            { name: 'Accueil', url: urlFor(locale, '') },
            { name: t('title'), url: urlFor(locale, '/blog') },
            ...(p.category?.name ? [{ name: p.category.name, url: urlFor(locale, `/blog/${p.category.slug}`) }] : []),
            { name: p.title, url: urlFor(locale, path) },
          ])),
        }}
      />
      <Chrome />
      <main className="hfm-wrap" style={{ maxWidth: '860px', margin: '0 auto', padding: '44px 28px 90px' }}>
        <Link href="/blog" style={{ display: 'inline-block', fontSize: '13.5px', fontWeight: 600, color: '#5E8E1F', textDecoration: 'none', marginBottom: '24px' }}>{t('backToBlog')}</Link>

        <header style={{ marginBottom: '30px' }}>
          <div style={{ display: 'flex', flexWrap: 'wrap', gap: '10px', alignItems: 'center', fontSize: '11.5px', color: '#8A8170', textTransform: 'uppercase', letterSpacing: '.07em' }}>
            {p.category?.name && (
              <Link href={`/blog/${p.category.slug}`} style={{ color: '#8CC63F', fontWeight: 700, textDecoration: 'none' }}>{p.category.name}</Link>
            )}
            {date && (<><span>·</span><span>{t('publishedOn')} {date}</span></>)}
            {p.author && (<><span>·</span><span>{t('by')} {p.author}</span></>)}
          </div>
          <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: 'clamp(28px,4.2vw,42px)', lineHeight: 1.18, color: '#1B2433', margin: '14px 0 0' }}>{p.title}</h1>
        </header>

        {p.cover?.wide && (
          <div style={{ aspectRatio: '16/9', overflow: 'hidden', borderRadius: '14px', background: '#F7F6F2', marginBottom: '34px' }}>
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img src={p.cover.wide} alt={p.title} decoding="async" style={{ display: 'block', width: '100%', height: '100%', objectFit: 'cover' }} />
          </div>
        )}

        <article className="hfm-cms" dangerouslySetInnerHTML={{ __html: html }} />

        {Array.isArray(p.tags) && p.tags.length > 0 && (
          <div style={{ display: 'flex', flexWrap: 'wrap', gap: '9px', marginTop: '40px', paddingTop: '26px', borderTop: '1px solid #E7E3DA' }}>
            {p.tags.map((tag) => (
              <span key={tag} style={{ fontSize: '12.5px', color: '#5E6152', background: '#fff', border: '1px solid #E7E3DA', borderRadius: '999px', padding: '6px 13px' }}>#{tag}</span>
            ))}
          </div>
        )}
      </main>
      <Footer />
    </div>
  );
}
