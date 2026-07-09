import { getTranslations } from 'next-intl/server';
import { Link } from '@/i18n/navigation';
import { textFromHtml } from '@/lib/seo';
import type { BlogList, BlogCategoryCount } from '@/lib/blog';

function formatDate(raw: string, locale: string): string {
  if (!raw) return '';
  const d = new Date(raw.replace(' ', 'T'));
  if (isNaN(d.getTime())) return '';
  try {
    return new Intl.DateTimeFormat(locale, { day: 'numeric', month: 'long', year: 'numeric' }).format(d);
  } catch {
    return new Intl.DateTimeFormat('fr', { day: 'numeric', month: 'long', year: 'numeric' }).format(d);
  }
}

// Vue liste du blog, partagée par /blog (tous) et /blog/{categorie}.
// `activeCategory` = slug de la catégorie courante (null = « tous »).
export default async function BlogListView({
  locale,
  list,
  categories,
  activeCategory,
}: {
  locale: string;
  list: BlogList;
  categories: BlogCategoryCount[];
  activeCategory: string | null;
}) {
  const t = await getTranslations('blog');
  const page = list.page;

  const chipStyle = (active: boolean): React.CSSProperties => ({
    display: 'inline-flex', alignItems: 'center', gap: '7px', padding: '8px 15px', borderRadius: '999px',
    fontSize: '13px', fontWeight: 600, textDecoration: 'none', transition: 'background .2s ease',
    background: active ? 'rgba(140,198,63,.16)' : '#fff',
    color: active ? '#4B6B1B' : '#5E6152',
    border: `1px solid ${active ? 'rgba(140,198,63,.4)' : '#E7E3DA'}`,
  });

  // Base d'URL selon le contexte : /blog (tous) ou /blog/{categorie}.
  const basePath = activeCategory ? `/blog/${activeCategory}` : '/blog';
  const pageHref = (p: number) => (p > 1 ? `${basePath}?page=${p}` : basePath);

  return (
    <main className="hfm-wrap" style={{ maxWidth: '1240px', margin: '0 auto', padding: '52px 28px 90px' }}>
      <header style={{ textAlign: 'center', maxWidth: '720px', margin: '0 auto 36px' }}>
        <div style={{ fontSize: '11.5px', fontWeight: 700, letterSpacing: '.13em', textTransform: 'uppercase', color: '#8CC63F' }}>{t('eyebrow')}</div>
        <h1 style={{ fontFamily: "'Spectral',serif", fontWeight: 400, fontSize: 'clamp(30px,4.4vw,44px)', color: '#2B2B2B', margin: '10px 0 0' }}>
          {list.category ? list.category.name : t('title')}
        </h1>
        <p style={{ fontSize: '15px', color: '#6E7062', margin: '14px 0 0', lineHeight: 1.6 }}>{t('lead')}</p>
      </header>

      <div style={{ display: 'flex', flexWrap: 'wrap', gap: '10px', justifyContent: 'center', marginBottom: '38px' }}>
        <Link href="/blog" style={chipStyle(!activeCategory)}>{t('allCategories')}</Link>
        {categories.map((c) => (
          <Link key={c.id} href={`/blog/${c.slug}`} style={chipStyle(activeCategory === c.slug)}>
            {c.name} <span style={{ opacity: 0.6, fontWeight: 500 }}>({c.count})</span>
          </Link>
        ))}
      </div>

      {list.posts.length === 0 ? (
        <p style={{ textAlign: 'center', color: '#8A8170', padding: '60px 0' }}>{t('noArticles')}</p>
      ) : (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill,minmax(300px,1fr))', gap: '22px' }}>
          {list.posts.map((p) => (
            <Link
              key={p.id}
              href={`/blog/${p.category.slug}/${p.slug}`}
              style={{ display: 'flex', flexDirection: 'column', background: '#fff', border: '1px solid #ECEAE3', borderRadius: '12px', overflow: 'hidden', textDecoration: 'none', color: 'inherit' }}
            >
              <div style={{ aspectRatio: '16/9', overflow: 'hidden', background: '#F7F6F2' }}>
                {p.cover?.wide || p.cover?.src ? (
                  // eslint-disable-next-line @next/next/no-img-element
                  <img src={p.cover.wide || p.cover.src} alt={p.title} loading="lazy" decoding="async" style={{ display: 'block', width: '100%', height: '100%', objectFit: 'cover' }} />
                ) : null}
              </div>
              <div style={{ padding: '20px 22px 24px', display: 'flex', flexDirection: 'column', flex: 1 }}>
                <div style={{ display: 'flex', gap: '10px', fontSize: '11px', color: '#8A8170', textTransform: 'uppercase', letterSpacing: '.06em' }}>
                  <span style={{ color: '#8CC63F', fontWeight: 700 }}>{p.category.name}</span>
                  <span>·</span>
                  <span>{formatDate(p.date, locale)}</span>
                </div>
                <h2 style={{ fontFamily: "'Spectral',serif", fontWeight: 500, fontSize: '19px', lineHeight: 1.3, color: '#1B2433', margin: '11px 0 0' }}>{p.title}</h2>
                <p style={{ fontSize: '13.5px', color: '#6E7062', lineHeight: 1.6, margin: '10px 0 0', display: '-webkit-box', WebkitLineClamp: 3, WebkitBoxOrient: 'vertical', overflow: 'hidden' }}>
                  {textFromHtml(p.excerpt, 160)}
                </p>
                <span style={{ marginTop: 'auto', paddingTop: '16px', fontSize: '13px', fontWeight: 600, color: '#5E8E1F' }}>{t('readArticle')} →</span>
              </div>
            </Link>
          ))}
        </div>
      )}

      {list.pages > 1 && (
        <nav style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '10px', marginTop: '48px' }} aria-label="pagination">
          {page > 1 && <Link href={pageHref(page - 1)} style={chipStyle(false)}>{t('previous')}</Link>}
          <span style={{ fontSize: '13.5px', color: '#6E7062' }}>{t('pageLabel')} {page} {t('of')} {list.pages}</span>
          {page < list.pages && <Link href={pageHref(page + 1)} style={chipStyle(false)}>{t('next')}</Link>}
        </nav>
      )}
    </main>
  );
}
